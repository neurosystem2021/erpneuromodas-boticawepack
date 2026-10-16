'use strict'

const http = require('http')
const fs = require('fs')
const path = require('path')
let wppconnect = null

const localConfigPath = path.join(__dirname, 'config.local.json')
const localConfig = fs.existsSync(localConfigPath) ? JSON.parse(fs.readFileSync(localConfigPath, 'utf8')) : {}
const host = '127.0.0.1'
const port = Number(process.env.WHATSAPP_GATEWAY_PORT || localConfig.port || 8788)
const apiKey = process.env.WHATSAPP_GATEWAY_API_KEY || localConfig.apiKey || 'local-development-only-change-me'
const webhookUrl = process.env.CRM_WHATSAPP_WEBHOOK_URL || 'http://127.0.0.1/api/crm/webhook.php'
const sessionRoot = path.join(__dirname, '.sessions')
const sessions = new Map()
const seenIncomingMessages = new Set()

function json(response, status, payload) {
  response.writeHead(status, {
    'Content-Type': 'application/json; charset=utf-8',
    'Cache-Control': 'no-store',
    'X-Content-Type-Options': 'nosniff'
  })
  response.end(JSON.stringify(payload))
}

function authorized(request) {
  return request.headers.apikey === apiKey
}

function safeName(value) {
  const name = String(value || 'neurorest-local').trim()
  if (!/^[a-zA-Z0-9_-]{3,60}$/.test(name)) throw new Error('INVALID_SESSION_NAME')
  return name
}

function stateFor(name) {
  if (!sessions.has(name)) {
    sessions.set(name, {
      name, status: 'disconnected', qr: null, client: null, error: null,
      lastInboundAt: null, lastWebhookAt: null, lastWebhookError: null,
      eventCount: 0, ignoredFromMe: 0, ignoredGroup: 0, ignoredUnsupported: 0,
      lastUnsupportedSuffix: null, unreadPoll: null, launchPromise: null,
      listenersBound: false, captureSince: 0, lastPollAt: null,
      lastPollError: null, lastPollCount: 0
    })
  }
  return sessions.get(name)
}

function withTimeout(promise, milliseconds, fallback) {
  return Promise.race([
    promise,
    new Promise(resolve => setTimeout(() => resolve(fallback), milliseconds))
  ])
}

function messageIdOf(message) {
  if (typeof message.id === 'string') return message.id
  if (message.id && typeof message.id._serialized === 'string') return message.id._serialized
  return ''
}

function serializedWid(value) {
  if (typeof value === 'string') return value
  return value && typeof value._serialized === 'string' ? value._serialized : ''
}

async function resolveContact(client, chatId) {
  let phoneId = chatId.endsWith('@c.us') ? chatId : ''
  let contact = null
  try {
    const entry = await client.getPnLidEntry(chatId)
    phoneId = serializedWid(entry && entry.phoneNumber) || phoneId
    contact = entry && entry.contact ? entry.contact : null
  } catch (_) {}
  try {
    contact = { ...(contact || {}), ...(await client.getContact(phoneId || chatId) || {}) }
  } catch (_) {}
  const phone = phoneId.replace(/\D/g, '')
  const displayName = String(
    (contact && (contact.name || contact.verifiedName || contact.pushname || contact.shortName)) || ''
  ).trim().slice(0, 160)
  return { chat_id: chatId, phone, display_name: displayName }
}

async function forwardIncomingMessage(state, client, message) {
  state.eventCount += 1
  if (!message) return
  if (message.fromMe) {
    state.ignoredFromMe += 1
    return
  }
  if (message.isGroupMsg) {
    state.ignoredGroup += 1
    return
  }
  if (message.type && message.type !== 'chat') {
    state.ignoredUnsupported += 1
    state.lastUnsupportedSuffix = `type:${message.type}`
    return
  }
  const chatId = serializedWid(message.from) || String(message.from || '')
  if (!/@(?:c\.us|lid)$/.test(chatId)) {
    state.ignoredUnsupported += 1
    state.lastUnsupportedSuffix = chatId.includes('@') ? chatId.split('@').pop() : 'missing'
    return
  }
  const body = String(message.body || '').trim()
  const messageId = messageIdOf(message)
  if (!body || !messageId) return
  if (seenIncomingMessages.has(messageId)) return
  seenIncomingMessages.add(messageId)
  setTimeout(() => seenIncomingMessages.delete(messageId), 300000)
  state.lastInboundAt = new Date().toISOString()
  state.lastWebhookError = null
  try {
    const messageName = String(message.notifyName || message.sender && message.sender.pushname || '').trim()
    const fallbackContact = {
      chat_id: chatId,
      phone: chatId.endsWith('@c.us') ? chatId.replace(/\D/g, '') : '',
      display_name: messageName.slice(0, 160)
    }
    const contact = await withTimeout(resolveContact(client, chatId), 1500, fallbackContact)
    const response = await fetch(webhookUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', apikey: apiKey },
      body: JSON.stringify({
        chat_id: chatId,
        message_id: messageId,
        phone: contact.phone,
        display_name: contact.display_name,
        body
      })
    })
    if (!response.ok) console.error(`Webhook CRM respondió HTTP ${response.status}`)
    if (response.ok) state.lastWebhookAt = new Date().toISOString()
    else state.lastWebhookError = `HTTP ${response.status}`
  } catch (error) {
    state.lastWebhookError = error.message
    console.error(`No se pudo entregar el mensaje al CRM: ${error.message}`)
  }
}

async function safeSendText(client, destination, message) {
  return client.page.evaluate(async ({ to, content }) => {
    const sent = await window.WPP.chat.sendTextMessage(to, content, { waitForAck: true })
    const rawId = sent && sent.id
    const id = typeof rawId === 'string'
      ? rawId
      : rawId && typeof rawId._serialized === 'string'
        ? rawId._serialized
        : rawId && typeof rawId.toString === 'function'
          ? rawId.toString()
          : ''
    return { id }
  }, { to: destination, content: message })
}

function bindIncomingListeners(state, client) {
  if (state.listenersBound) return
  const handler = (message) => forwardIncomingMessage(state, client, message)
  client.onMessage(handler)
  if (typeof client.onAnyMessage === 'function') client.onAnyMessage(handler)
  state.listenersBound = true
}

function startUnreadPolling(state, client) {
  if (state.unreadPoll) clearInterval(state.unreadPoll)
  state.unreadPoll = setInterval(async () => {
    if (state.status !== 'connected') return
    try {
      const messages = await client.page.evaluate((since) => {
        const store = window.WPP && window.WPP.whatsapp && window.WPP.whatsapp.ChatStore
        const chats = store && typeof store.getModelsArray === 'function' ? store.getModelsArray() : []
        const rows = []
        for (const chat of chats) {
          if (chat.msgs && typeof chat.msgs.getModelsArray === 'function') {
            rows.push(...chat.msgs.getModelsArray())
          }
        }
        return rows
          .filter(message => {
            const timestamp = Number(message.t || message.timestamp || 0)
            const fromMe = Boolean(message.isSentByMe || (message.id && message.id.fromMe))
            return timestamp >= since && !fromMe && !message.isStatusV3
          })
          .slice(-100)
          .map(message => window.WAPI.processMessageObj(message, true, false))
          .filter(Boolean)
      }, state.captureSince)
      state.lastPollAt = new Date().toISOString()
      state.lastPollError = null
      state.lastPollCount = Array.isArray(messages) ? messages.length : 0
      if (Array.isArray(messages)) {
        for (const message of messages) await forwardIncomingMessage(state, client, message)
      }
    } catch (error) {
      state.lastPollError = error && error.message ? error.message : String(error)
    }
  }, 3000)
  if (typeof state.unreadPoll.unref === 'function') state.unreadPoll.unref()
}

async function startSession(name) {
  const state = stateFor(name)
  if (state.launchPromise || state.client || ['starting', 'qr', 'connected'].includes(state.status)) return state
  state.status = 'starting'
  state.error = null
  state.qr = null

  // WPPConnect/Puppeteer can take several seconds to load on Windows. Loading it
  // at process startup prevented the HTTP server (including /health) from ever
  // becoming available, so PHP reported MESSAGING_GATEWAY_OFFLINE.
  if (!wppconnect) wppconnect = require('@wppconnect-team/wppconnect')

  state.launchPromise = wppconnect.create({
    session: name,
    catchQR: (base64Qr) => {
      state.qr = base64Qr
      state.status = 'qr'
    },
    statusFind: (status) => {
      if (status === 'isLogged' || status === 'qrReadSuccess') {
        state.status = 'connected'
        state.qr = null
      } else if (status === 'notLogged') {
        // The QR callback can arrive after statusFind. Keep the session active so
        // the UI continues polling instead of allowing a duplicate browser start.
        state.status = 'qr'
      } else if (['qrReadFail', 'browserClose', 'desconnectedMobile', 'serverClose'].includes(status)) {
        state.status = 'disconnected'
      }
    },
    headless: true,
    useChrome: true,
    logQR: false,
    autoClose: 0,
    deviceSyncTimeout: 0,
    tokenStore: 'file',
    folderNameToken: sessionRoot,
    disableWelcome: true,
    updatesLog: false,
    puppeteerOptions: {
      executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe'
    },
    browserArgs: ['--no-sandbox', '--disable-setuid-sandbox']
  }).then((client) => {
    state.client = client
    state.status = 'connected'
    state.qr = null
    state.captureSince = Math.floor(Date.now() / 1000) - 5
    state.listenersBound = false
    client.onStateChange((nextState) => {
      if (nextState === 'CONNECTED') {
        state.status = 'connected'
        bindIncomingListeners(state, client)
      }
    })
    bindIncomingListeners(state, client)
    // WhatsApp Web reloads once after pairing/synchronizing. Rebind after that
    // reload because callbacks registered immediately after the QR can be lost.
    setTimeout(() => {
      if (state.client === client && state.status === 'connected') {
        bindIncomingListeners(state, client)
        startUnreadPolling(state, client)
      }
    }, 30000)
  }).catch((error) => {
    state.status = 'error'
    state.error = error && error.message ? error.message : 'No se pudo iniciar WhatsApp.'
  }).finally(() => {
    state.launchPromise = null
  })
  return state
}

async function bodyOf(request) {
  let body = ''
  for await (const chunk of request) {
    body += chunk
    if (body.length > 18000000) throw new Error('PAYLOAD_TOO_LARGE')
  }
  return body ? JSON.parse(body) : {}
}

const server = http.createServer(async (request, response) => {
  try {
    const url = new URL(request.url, `http://${host}:${port}`)
    if (url.pathname === '/health') return json(response, 200, { ok: true })
    if (!authorized(request)) return json(response, 401, { error: 'UNAUTHORIZED' })
    const name = safeName(url.searchParams.get('name'))
    const state = stateFor(name)

    if (request.method === 'POST' && url.pathname === '/session/start') {
      await startSession(name)
      return json(response, 202, { name, status: state.status })
    }
    if (request.method === 'GET' && url.pathname === '/session/status') {
      return json(response, 200, {
        name,
        status: state.status,
        error: state.error,
        last_inbound_at: state.lastInboundAt,
        last_webhook_at: state.lastWebhookAt,
        last_webhook_error: state.lastWebhookError,
        event_count: state.eventCount,
        ignored_from_me: state.ignoredFromMe,
        ignored_group: state.ignoredGroup,
        ignored_unsupported: state.ignoredUnsupported,
        last_unsupported_suffix: state.lastUnsupportedSuffix,
        last_poll_at: state.lastPollAt,
        last_poll_error: state.lastPollError,
        last_poll_count: state.lastPollCount
      })
    }
    if (request.method === 'GET' && url.pathname === '/session/qr') {
      return json(response, state.qr ? 200 : 202, { name, status: state.status, base64: state.qr })
    }
    if (request.method === 'POST' && url.pathname === '/session/logout') {
      if (state.client) {
        // WhatsApp may navigate/destroy the page while logout() is awaiting its
        // result. That still means the unlink succeeded, so cleanup must remain
        // idempotent instead of returning HTTP 500 to the CRM.
        try { await state.client.logout() } catch (_) {}
        try { await state.client.close() } catch (_) {}
      }
      state.client = null
      if (state.unreadPoll) clearInterval(state.unreadPoll)
      state.unreadPoll = null
      state.status = 'disconnected'
      state.qr = null
      state.error = null
      state.lastWebhookError = null
      try { fs.rmSync(path.join(sessionRoot, name), { recursive: true, force: true }) } catch (_) {}
      return json(response, 200, { name, status: state.status, unlinked: true })
    }
    if (request.method === 'POST' && url.pathname === '/messages/send') {
      if (!state.client || state.status !== 'connected') {
        return json(response, 409, { error: 'WHATSAPP_NOT_CONNECTED' })
      }
      const input = await bodyOf(request)
      const digits = String(input.phone || '').replace(/\D/g, '')
      const message = String(input.message || '').trim()
      if (digits.length < 8 || !message) return json(response, 422, { error: 'INVALID_MESSAGE' })
      const destination = `${digits.startsWith('51') ? digits : `51${digits}`}@c.us`
      const result = await safeSendText(state.client, destination, message)
      return json(response, 200, { id: result.id || null, chat_id: destination })
    }
    if (request.method === 'POST' && url.pathname === '/messages/send-chat') {
      if (!state.client || state.status !== 'connected') {
        return json(response, 409, { error: 'WHATSAPP_NOT_CONNECTED' })
      }
      const input = await bodyOf(request)
      const chatId = String(input.chat_id || '').trim()
      const message = String(input.message || '').trim()
      if (!/^[a-zA-Z0-9:_-]+@(?:c\.us|lid)$/.test(chatId) || !message) {
        return json(response, 422, { error: 'INVALID_CHAT_MESSAGE' })
      }
      const result = await safeSendText(state.client, chatId, message)
      return json(response, 200, { id: result.id || null, chat_id: chatId })
    }
    if (request.method === 'POST' && url.pathname === '/messages/send-media') {
      if (!state.client || state.status !== 'connected') {
        return json(response, 409, { error: 'WHATSAPP_NOT_CONNECTED' })
      }
      const input = await bodyOf(request)
      const digits = String(input.phone || '').replace(/\D/g, '')
      const media = String(input.media || '')
      const filename = String(input.filename || 'campana').replace(/[^a-zA-Z0-9._-]/g, '').slice(0, 100)
      const caption = String(input.caption || '').trim().slice(0, 900)
      if (digits.length < 8 || !/^data:(?:image|video)\/[a-zA-Z0-9.+-]+;base64,/.test(media)) {
        return json(response, 422, { error: 'INVALID_MEDIA_MESSAGE' })
      }
      const destination = `${digits.startsWith('51') ? digits : `51${digits}`}@c.us`
      const result = await state.client.sendFile(destination, media, {
        filename,
        caption,
        useTemplateButtons: false
      })
      return json(response, 200, { id: messageIdOf(result) || null, chat_id: destination })
    }
    if (request.method === 'POST' && url.pathname === '/contacts/resolve') {
      if (!state.client || state.status !== 'connected') {
        return json(response, 409, { error: 'WHATSAPP_NOT_CONNECTED' })
      }
      const input = await bodyOf(request)
      const chatIds = Array.isArray(input.chat_ids) ? input.chat_ids.slice(0, 200) : []
      const validIds = chatIds.map(value => String(value || '').trim())
        .filter(value => /^[a-zA-Z0-9:_-]+@(?:c\.us|lid)$/.test(value))
      const contacts = []
      for (const chatId of validIds) contacts.push(await resolveContact(state.client, chatId))
      return json(response, 200, { contacts })
    }
    return json(response, 404, { error: 'NOT_FOUND' })
  } catch (error) {
    return json(response, 500, { error: error && error.message ? error.message : 'INTERNAL_ERROR' })
  }
})

fs.mkdirSync(sessionRoot, { recursive: true })
server.listen(port, host, () => {
  console.log(`NeuroRest WhatsApp gateway listo en http://${host}:${port}`)
})
