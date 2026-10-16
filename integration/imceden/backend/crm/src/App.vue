<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { crmGet, crmUpload, crmWrite } from './api'

function inputDate(daysAgo = 0) {
  const date = new Date()
  date.setHours(12, 0, 0, 0)
  date.setDate(date.getDate() - daysAgo)
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

const tabs = [
  ['dashboard', 'Resumen'], ['analytics', 'Dashboard CRM'], ['customers', 'Clientes'], ['segments', 'Segmentos'],
  ['tasks', 'Tareas'], ['campaigns', 'Campañas'], ['loyalty', 'Fidelización'],
  ['messaging/status', 'Comunicaciones'], ['messaging/inbox', 'Bandeja WhatsApp'],
  ['messaging/templates', 'Plantillas'],
  ['messaging/bot-rules', 'Bot y respuestas'],
  ['sync/status', 'Sincronización']
]
const active = ref('dashboard')
const data = ref(null)
const loading = ref(false)
const error = ref('')
const search = ref('')
const saving = ref(false)
const success = ref('')
const form = ref({})
const customer360 = ref(null)
const campaignSend = ref(null)
const consentConfirmed = ref(false)
const botMessage = ref('')
const botDraft = ref(null)
const consentForm = ref({ status: 'unknown', source: 'registro_manual' })
const connection = ref({ status: 'disconnected', base64: null })
const connecting = ref(false)
const batchSending = ref(false)
const batchProgress = ref({ completed: 0, total: 0 })
const campaignAudienceLoading = ref(false)
const aiSettings = ref({
  enabled: false, auto_reply_enabled: false, provider: 'simulation', model: '', api_key: '', system_prompt: ''
})
const showAiKey = ref(false)
const savingAi = ref(false)
const selectedConversation = ref(null)
const conversationMessages = ref([])
const loadingMessages = ref(false)
const importingCustomers = ref(false)
const customerFileInput = ref(null)
const campaignCreateMediaInput = ref(null)
const campaignMediaInput = ref(null)
const campaignMediaKind = ref('image')
const campaignMedia = ref(null)
const uploadingMedia = ref(false)
const manualMessage = ref('')
const sendingManual = ref(false)
const syncingContacts = ref(false)
const syncingCustomers = ref(false)
const taskCustomers = ref([])
const editingTaskId = ref(null)
const editingSegmentId = ref(null)
const editingBotRuleId = ref(null)
const botRuleTestMessage = ref('')
const botRuleTestResult = ref(null)
const testingBotRule = ref(false)
const editingCustomer = ref(null)
const savingCustomer = ref(false)
const segmentPreview = ref(null)
const previewingSegment = ref(false)
const refreshingSegmentId = ref(null)
const taskFilters = ref({ status: 'open', priority: '', customer: '' })
const loyaltySearch = ref('')
const loyaltyMovementFilter = ref('all')
const inboxFilter = ref('new')
const labelFilter = ref('all')
const savingLabel = ref(false)
const analyticsRange = ref({ from: inputDate(13), to: inputDate(0) })
const applyingAnalyticsRange = ref(false)
const conversationLabels = [
  { key: 'new', name: 'Nuevo', color: '#1976d2' },
  { key: 'potential_buyer', name: 'Posible comprador', color: '#f57c00' },
  { key: 'follow_up', name: 'Seguimiento', color: '#7b1fa2' },
  { key: 'sale_closed', name: 'Venta cerrada', color: '#0a9f55' },
  { key: 'not_interested', name: 'No interesado', color: '#687386' }
]
let qrTimer = null
let inboxTimer = null
let sessionTimer = null

const title = computed(() => tabs.find(([key]) => key === active.value)?.[1] || 'CRM')
const filteredTasks = computed(() => {
  if (active.value !== 'tasks' || !Array.isArray(data.value)) return []
  return data.value.filter(task => {
    const statusOk = !taskFilters.value.status
      || (taskFilters.value.status === 'open'
        ? ['pending', 'in_progress'].includes(task.status)
        : task.status === taskFilters.value.status)
    const priorityOk = !taskFilters.value.priority || task.priority === taskFilters.value.priority
    const customerOk = !taskFilters.value.customer || String(task.customer_id || '') === String(taskFilters.value.customer)
    return statusOk && priorityOk && customerOk
  })
})
const tableRows = computed(() => active.value === 'tasks'
  ? filteredTasks.value
  : (active.value === 'loyalty' ? data.value?.accounts : data.value))
const inboxCounts = computed(() => {
  const conversations = data.value?.conversations || []
  return {
    all: conversations.length,
    new: conversations.filter(row => row.last_direction === 'inbound' && row.status !== 'resolved').length,
    attended: conversations.filter(row => row.last_direction !== 'inbound' || row.status === 'resolved').length
  }
})
const filteredConversations = computed(() => {
  let conversations = data.value?.conversations || []
  if (inboxFilter.value === 'new') {
    conversations = conversations.filter(row => row.last_direction === 'inbound' && row.status !== 'resolved')
  }
  if (inboxFilter.value === 'attended') {
    conversations = conversations.filter(row => row.last_direction !== 'inbound' || row.status === 'resolved')
  }
  if (labelFilter.value !== 'all') {
    conversations = conversations.filter(row => (row.sales_label || 'new') === labelFilter.value)
  }
  return conversations
})
const taskCounts = computed(() => {
  const tasks = Array.isArray(data.value) ? data.value : []
  return {
    all: tasks.length,
    pending: tasks.filter(task => task.status === 'pending').length,
    in_progress: tasks.filter(task => task.status === 'in_progress').length,
    done: tasks.filter(task => task.status === 'done').length,
    overdue: tasks.filter(task => task.due_at && new Date(String(task.due_at).replace(' ', 'T')) < new Date() && !['done', 'cancelled'].includes(task.status)).length
  }
})
const loyaltySummary = computed(() => {
  const accounts = data.value?.accounts || []
  const earned = accounts.reduce((sum, row) => sum + Number(row.lifetime_earned || 0), 0)
  const redeemed = accounts.reduce((sum, row) => sum + Number(row.lifetime_redeemed || 0), 0)
  return {
    customers: accounts.length,
    balance: accounts.reduce((sum, row) => sum + Number(row.balance || 0), 0),
    earned,
    redeemed,
    redemptionRate: earned ? Math.round(redeemed * 100 / earned) : 0
  }
})
const filteredLoyaltyAccounts = computed(() => {
  const query = loyaltySearch.value.trim().toLocaleLowerCase()
  return (data.value?.accounts || []).filter(row => !query
    || row.display_name.toLocaleLowerCase().includes(query)
    || String(row.customer_id).includes(query))
})
const filteredLoyaltyMovements = computed(() => (data.value?.movements || []).filter(row =>
  loyaltyMovementFilter.value === 'all'
  || (loyaltyMovementFilter.value === 'earned' ? Number(row.points) > 0 : Number(row.points) < 0)
))
const selectedLoyaltyAccount = computed(() => (data.value?.accounts || []).find(
  row => String(row.customer_id) === String(form.value.customer_id || '')
))
const botRuleSummary = computed(() => {
  const rules = Array.isArray(data.value) ? data.value : []
  return {
    total: rules.length,
    active: rules.filter(rule => Number(rule.is_active) === 1).length,
    handoff: rules.filter(rule => Number(rule.handoff) === 1).length,
    automatic: rules.filter(rule => Number(rule.is_active) === 1 && Number(rule.handoff) !== 1).length
  }
})
const customerSummary = computed(() => {
  const customers = Array.isArray(data.value) ? data.value : []
  return {
    total: customers.length,
    withPhone: customers.filter(customer => customer.phone).length,
    purchases: customers.reduce((sum, customer) => sum + Number(customer.purchase_count || 0), 0),
    spent: customers.reduce((sum, customer) => sum + Number(customer.total_spent || 0), 0)
  }
})
const aiModelOptions = computed(() => {
  if (aiSettings.value.provider === 'gemini') {
    return [
      ['gemini-3.6-flash', 'Gemini 3.6 Flash — recomendado'],
      ['gemini-3.5-flash-lite', 'Gemini 3.5 Flash-Lite — económico'],
      ['gemini-3.5-flash', 'Gemini 3.5 Flash — avanzado'],
      ['gemini-3.1-pro-preview', 'Gemini 3.1 Pro — vista previa']
    ]
  }
  if (aiSettings.value.provider === 'openai') {
    return [
      ['gpt-5.6-luna', 'GPT-5.6 Luna — recomendado y económico'],
      ['gpt-5.6-terra', 'GPT-5.6 Terra — equilibrado'],
      ['gpt-5.6-sol', 'GPT-5.6 Sol — máxima capacidad']
    ]
  }
  return []
})

async function load() {
  loading.value = true
  error.value = ''
  try {
    if (active.value === 'dashboard') {
      const [metrics, customers] = await Promise.all([crmGet('dashboard'), crmGet('customers', { limit: 8 })])
      data.value = { ...metrics, topCustomers: customers }
    } else if (active.value === 'tasks') {
      const [tasks, customers] = await Promise.all([
        crmGet('tasks'), crmGet('customers', { limit: 200 })
      ])
      data.value = tasks
      taskCustomers.value = customers
    } else if (active.value === 'analytics') {
      data.value = await crmGet('analytics', analyticsRange.value)
      analyticsRange.value = { ...data.value.range }
    } else {
      data.value = await crmGet(active.value, active.value === 'customers' ? { q: search.value } : {})
      if (['messaging/status', 'messaging/inbox'].includes(active.value) && data.value?.config) {
        aiSettings.value = {
          enabled: Boolean(data.value.config.ai_enabled),
          auto_reply_enabled: Boolean(data.value.config.ai_auto_reply_enabled),
          provider: data.value.config.ai_provider || 'simulation',
          model: data.value.config.ai_model || '',
          api_key: '',
          system_prompt: data.value.config.ai_system_prompt || ''
        }
        showAiKey.value = false
        if (active.value === 'messaging/status') {
          connection.value = await crmGet('messaging/connection/status')
        }
      }
    }
  } catch (e) {
    error.value = e.message === 'AUTH_REQUIRED'
      ? 'La sesión venció. Ingrese nuevamente al sistema.'
      : `No se pudo cargar: ${e.message}`
  } finally {
    loading.value = false
  }
}

async function openCustomer(id) {
  try {
    customer360.value = await crmGet(`customers/${id}`)
    consentForm.value = {
      status: customer360.value.customer.whatsapp_consent || 'unknown',
      source: 'registro_manual'
    }
  } catch (e) { error.value = `No se pudo abrir el cliente: ${e.message}` }
}

async function saveConsent() {
  try {
    await crmWrite(`customers/${customer360.value.customer.id}/consent`, 'POST', consentForm.value)
    customer360.value.customer.whatsapp_consent = consentForm.value.status
    success.value = 'Consentimiento de WhatsApp actualizado y auditado.'
  } catch (e) { error.value = `No se pudo actualizar el consentimiento: ${e.message}` }
}

async function importCustomerFile(event) {
  const file = event.target.files?.[0]
  if (!file) return
  importingCustomers.value = true
  error.value = ''
  success.value = ''
  try {
    const result = await crmUpload('customers/import', file)
    success.value = `Importación terminada: ${result.created} creados, ${result.updated} actualizados y ${result.rejected} rechazados.`
    await load()
  } catch (e) {
    error.value = `No se pudo importar el Excel: ${e.message}`
  } finally {
    importingCustomers.value = false
    event.target.value = ''
  }
}

async function prepareCampaign(campaign) {
  try {
    const [customers, segments] = await Promise.all([
      crmGet('customers', { limit: 200 }),
      crmGet('segments')
    ])
    let content = {}
    try { content = JSON.parse(campaign.content_json || '{}') } catch (_) {}
    campaignSend.value = {
      campaign,
      message: content.message || '',
      segments,
      audienceSource: 'purchases',
      segmentId: '',
      filters: { minPurchases: 1, maxPurchases: '', maxRecency: 365, minSpent: 0 },
      customers: customers.filter(customer => customer.phone).map(customer => ({
        ...customer, selected: true, sendStatus: '', sending: false
      }))
    }
    clearCampaignMedia()
    if (content.media_token) {
      campaignMedia.value = {
        token: content.media_token,
        type: content.media_type || 'image',
        name: content.media_name || 'Archivo adjunto',
        size: Number(content.media_size || 0),
        preview: ''
      }
    }
    consentConfirmed.value = false
  } catch (e) { error.value = `No se pudo preparar la campaña: ${e.message}` }
}

async function buildCampaignAudience() {
  if (!campaignSend.value) return
  campaignAudienceLoading.value = true
  error.value = ''
  try {
    const filters = campaignSend.value.filters
    const payload = campaignSend.value.audienceSource === 'segment'
      ? { segment_id: Number(campaignSend.value.segmentId) }
      : { rules: {
          frequency: { min: Number(filters.minPurchases || 0), ...(filters.maxPurchases !== '' ? { max: Number(filters.maxPurchases) } : {}) },
          recency_days: { max: Number(filters.maxRecency || 36500) },
          monetary: { min: Number(filters.minSpent || 0) }
        } }
    const result = await crmWrite('campaigns/audience', 'POST', payload)
    campaignSend.value.audience = result
    campaignSend.value.customers = result.customers.map(customer => ({
      ...customer, selected: Boolean(customer.phone), sendStatus: '', sending: false
    }))
    consentConfirmed.value = false
  } catch (e) {
    error.value = `No se pudo generar la audiencia: ${e.message}`
  } finally {
    campaignAudienceLoading.value = false
  }
}

function toggleCampaignCustomers(selected) {
  for (const customer of campaignSend.value?.customers || []) {
    customer.selected = selected && Boolean(customer.phone)
  }
}

function openCampaignMedia(kind) {
  campaignMediaKind.value = kind
  if (!campaignMediaInput.value) return
  campaignMediaInput.value.accept = kind === 'video'
    ? 'video/mp4,video/quicktime,video/3gpp'
    : 'image/jpeg,image/png,image/webp,image/gif'
  campaignMediaInput.value.click()
}

function openCampaignCreateMedia(kind) {
  campaignMediaKind.value = kind
  if (!campaignCreateMediaInput.value) return
  campaignCreateMediaInput.value.accept = kind === 'video'
    ? 'video/mp4,video/quicktime,video/3gpp'
    : 'image/jpeg,image/png,image/webp,image/gif'
  campaignCreateMediaInput.value.click()
}

async function uploadCampaignMedia(event) {
  const file = event.target.files?.[0]
  if (!file) return
  uploadingMedia.value = true
  error.value = ''
  try {
    const uploaded = await crmUpload('messaging/media', file)
    clearCampaignMedia()
    campaignMedia.value = { ...uploaded, preview: URL.createObjectURL(file) }
  } catch (e) {
    error.value = `No se pudo adjuntar el archivo: ${e.message}`
  } finally {
    uploadingMedia.value = false
    event.target.value = ''
  }
}

function clearCampaignMedia() {
  if (campaignMedia.value?.preview) URL.revokeObjectURL(campaignMedia.value.preview)
  campaignMedia.value = null
}

function closeCampaign() {
  clearCampaignMedia()
  campaignSend.value = null
}

function personalizedMessage(customer) {
  return campaignSend.value.message.includes('{{nombre}}')
    ? campaignSend.value.message.replaceAll('{{nombre}}', customer.display_name)
    : campaignSend.value.message
}

async function sendWhatsApp(customer) {
  if (!consentConfirmed.value) return false
  customer.sending = true
  customer.sendStatus = ''
  try {
    await crmWrite('messaging/send', 'POST', {
      campaign_id: campaignSend.value.campaign.id,
      customer_id: customer.id,
      message: campaignSend.value.message,
      media_token: campaignMedia.value?.token || '',
      consent_confirmed: true
    })
    customer.sendStatus = 'Enviado'
    return true
  } catch (e) {
    customer.sendStatus = `Error: ${e.message}`
    return false
  } finally {
    customer.sending = false
  }
}

async function sendAllWhatsApp() {
  if (!consentConfirmed.value || batchSending.value) return
  const pending = campaignSend.value.customers.filter(customer => customer.selected && customer.phone && customer.sendStatus !== 'Enviado')
  if (!pending.length) return
  const accepted = window.confirm(`Se enviarán ${pending.length} mensajes por WhatsApp. ¿Desea continuar?`)
  if (!accepted) return

  batchSending.value = true
  batchProgress.value = { completed: 0, total: pending.length }
  for (const customer of pending) {
    await sendWhatsApp(customer)
    batchProgress.value.completed += 1
    if (batchProgress.value.completed < batchProgress.value.total) {
      await new Promise(resolve => setTimeout(resolve, 2500))
    }
  }
  batchSending.value = false
}

async function refresh() {
  if (active.value !== 'dashboard' && active.value !== 'customers') return load()
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    const result = await crmWrite('sync/customers', 'POST', {})
    success.value = `${result.processed} clientes sincronizados correctamente.`
    await load()
  } catch (e) {
    error.value = `No se pudo sincronizar: ${e.message}`
    loading.value = false
  }
}

async function runCustomerSync() {
  if (syncingCustomers.value) return
  syncingCustomers.value = true
  error.value = ''
  success.value = ''
  try {
    const result = await crmWrite('sync/customers', 'POST', {})
    success.value = `${result.processed} clientes sincronizados correctamente.`
    await load()
  } catch (e) {
    error.value = `La sincronización falló: ${e.message}`
    await load()
  } finally {
    syncingCustomers.value = false
  }
}

function syncStatusLabel(status) {
  return { running: 'En ejecución', completed: 'Completada', failed: 'Fallida' }[status] || status
}

function resetForm() {
  form.value = active.value === 'tasks'
    ? { title: '', description: '', priority: 'normal', due_at: '', customer_id: '', assigned_user: '', status: 'pending' }
    : active.value === 'segments'
      ? {
          name: '', description: '',
          use_frequency: true, min_frequency: 2, max_frequency: '',
          use_recency: true, min_recency: '', max_recency: 90,
          use_monetary: false, min_monetary: 0, max_monetary: ''
        }
      : active.value === 'campaigns'
        ? { name: '', channel: 'whatsapp', message: '' }
        : active.value === 'messaging/templates'
          ? { name: '', channel: 'whatsapp', subject: '', body: '' }
        : active.value === 'messaging/bot-rules'
            ? { name: '', trigger_text: '', response_text: '', priority: 100, handoff: false, is_active: true }
        : active.value === 'loyalty'
          ? { customer_id: '', points: 1, note: '' } : {}
  if (active.value === 'tasks') editingTaskId.value = null
  if (active.value === 'segments') {
    editingSegmentId.value = null
    segmentPreview.value = null
  }
  if (active.value === 'messaging/bot-rules') editingBotRuleId.value = null
}

function editBotRule(rule) {
  editingBotRuleId.value = Number(rule.id)
  form.value = {
    name: rule.name,
    trigger_text: rule.trigger_text,
    response_text: rule.response_text,
    priority: Number(rule.priority),
    handoff: Number(rule.handoff) === 1,
    is_active: Number(rule.is_active) === 1
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function toggleBotRule(rule) {
  try {
    await crmWrite(`messaging/bot-rules/${rule.id}`, 'PATCH', {
      ...rule,
      handoff: Number(rule.handoff) === 1,
      is_active: Number(rule.is_active) !== 1
    })
    success.value = `Regla ${Number(rule.is_active) === 1 ? 'pausada' : 'activada'} correctamente.`
    await load()
  } catch (e) { error.value = `No se pudo cambiar el estado: ${e.message}` }
}

async function deleteBotRule(rule) {
  if (!window.confirm(`¿Eliminar la automatización "${rule.name}"?`)) return
  try {
    await crmWrite(`messaging/bot-rules/${rule.id}`, 'DELETE', {})
    success.value = 'Automatización eliminada correctamente.'
    if (editingBotRuleId.value === Number(rule.id)) resetForm()
    await load()
  } catch (e) { error.value = `No se pudo eliminar: ${e.message}` }
}

async function testBotRule() {
  if (!botRuleTestMessage.value.trim()) return
  testingBotRule.value = true
  try {
    botRuleTestResult.value = await crmWrite('messaging/assistant/draft', 'POST', { message: botRuleTestMessage.value })
  } catch (e) { error.value = `No se pudo probar el bot: ${e.message}` }
  finally { testingBotRule.value = false }
}

function loyaltyMovementLabel(type, points) {
  if (Number(points) < 0 || type === 'redeem') return 'Canje'
  return type === 'earn' ? 'Acumulación' : type === 'expire' ? 'Vencimiento' : 'Ajuste'
}

function readableDate(value) {
  if (!value) return '—'
  const date = new Date(String(value).replace(' ', 'T'))
  return Number.isNaN(date.getTime()) ? value : new Intl.DateTimeFormat('es-PE', {
    day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
  }).format(date)
}

function customerRecency(value) {
  if (!value) return 'Sin compras registradas'
  const date = new Date(String(value).replace(' ', 'T'))
  const days = Math.max(0, Math.floor((Date.now() - date.getTime()) / 86400000))
  return days === 0 ? 'Compró hoy' : days === 1 ? 'Compró ayer' : `Hace ${days} días`
}

function customerLevel(customer) {
  const purchases = Number(customer.purchase_count || 0)
  const spent = Number(customer.total_spent || 0)
  if (purchases >= 5 || spent >= 500) return 'Preferente'
  if (purchases >= 2 || spent >= 100) return 'Frecuente'
  return 'Nuevo'
}

function editCustomer(customer) {
  editingCustomer.value = {
    id: Number(customer.id),
    display_name: customer.display_name || '',
    document_number: customer.document_number || '',
    phone: customer.phone || '',
    email: customer.email || ''
  }
}

async function saveCustomer() {
  if (!editingCustomer.value) return
  savingCustomer.value = true
  error.value = ''
  try {
    await crmWrite(`customers/${editingCustomer.value.id}`, 'PATCH', editingCustomer.value)
    success.value = 'Datos del cliente actualizados correctamente.'
    editingCustomer.value = null
    await load()
  } catch (e) { error.value = `No se pudo actualizar el cliente: ${e.message}` }
  finally { savingCustomer.value = false }
}

function segmentRulesFromForm() {
  const rules = {}
  if (form.value.use_frequency) {
    rules.frequency = { min: form.value.min_frequency }
    if (form.value.max_frequency !== '') rules.frequency.max = form.value.max_frequency
  }
  if (form.value.use_recency) {
    rules.recency_days = {}
    if (form.value.min_recency !== '') rules.recency_days.min = form.value.min_recency
    if (form.value.max_recency !== '') rules.recency_days.max = form.value.max_recency
  }
  if (form.value.use_monetary) {
    rules.monetary = { min: form.value.min_monetary }
    if (form.value.max_monetary !== '') rules.monetary.max = form.value.max_monetary
  }
  return rules
}

function segmentRulesText(row) {
  const rules = typeof row.rules_json === 'string' ? JSON.parse(row.rules_json || '{}') : (row.rules_json || {})
  const parts = []
  if (rules.frequency?.min !== undefined) parts.push(`${rules.frequency.min}+ compras`)
  if (rules.frequency?.max !== undefined) parts.push(`máx. ${rules.frequency.max} compras`)
  if (rules.recency_days?.min !== undefined) parts.push(`desde ${rules.recency_days.min} días`)
  if (rules.recency_days?.max !== undefined) parts.push(`compró hace ≤ ${rules.recency_days.max} días`)
  if (rules.monetary?.min !== undefined) parts.push(`gastó S/ ${rules.monetary.min}+`)
  if (rules.monetary?.max !== undefined) parts.push(`gasto máx. S/ ${rules.monetary.max}`)
  return parts
}

async function previewCurrentSegment() {
  previewingSegment.value = true
  error.value = ''
  try {
    segmentPreview.value = await crmWrite('segments/preview', 'POST', { rules: segmentRulesFromForm() })
  } catch (e) {
    error.value = `No se pudo calcular la vista previa: ${e.message}`
  } finally {
    previewingSegment.value = false
  }
}

function editSegment(row) {
  const rules = typeof row.rules_json === 'string' ? JSON.parse(row.rules_json || '{}') : (row.rules_json || {})
  editingSegmentId.value = Number(row.id)
  segmentPreview.value = null
  form.value = {
    name: row.name, description: row.description || '',
    use_frequency: Boolean(rules.frequency),
    min_frequency: rules.frequency?.min ?? 0, max_frequency: rules.frequency?.max ?? '',
    use_recency: Boolean(rules.recency_days),
    min_recency: rules.recency_days?.min ?? '', max_recency: rules.recency_days?.max ?? '',
    use_monetary: Boolean(rules.monetary),
    min_monetary: rules.monetary?.min ?? 0, max_monetary: rules.monetary?.max ?? ''
  }
}

async function refreshSegment(row) {
  refreshingSegmentId.value = Number(row.id)
  try {
    const result = await crmWrite(`segments/${row.id}/refresh`, 'POST', {})
    success.value = `"${row.name}" actualizado: ${result.member_count} cliente(s).`
    await load()
  } catch (e) {
    error.value = `No se pudo recalcular: ${e.message}`
  } finally {
    refreshingSegmentId.value = null
  }
}

async function deleteSegment(row) {
  if (!window.confirm(`¿Eliminar el segmento "${row.name}"?`)) return
  try {
    await crmWrite(`segments/${row.id}`, 'DELETE', {})
    success.value = 'Segmento eliminado correctamente.'
    if (editingSegmentId.value === Number(row.id)) resetForm()
    await load()
  } catch (e) {
    error.value = `No se pudo eliminar: ${e.message}`
  }
}

async function save() {
  saving.value = true
  error.value = ''
  success.value = ''
  try {
    if (active.value === 'tasks' && editingTaskId.value) {
      await crmWrite(`tasks/${editingTaskId.value}`, 'PATCH', form.value)
    } else if (active.value === 'segments') {
      const payload = {
        name: form.value.name, description: form.value.description,
        rules: segmentRulesFromForm()
      }
      if (editingSegmentId.value) {
        await crmWrite(`segments/${editingSegmentId.value}`, 'PATCH', payload)
      } else {
        await crmWrite('segments', 'POST', payload)
      }
    } else if (active.value === 'loyalty') {
      await crmWrite('loyalty/adjustments', 'POST', form.value)
    } else if (active.value === 'messaging/bot-rules' && editingBotRuleId.value) {
      await crmWrite(`messaging/bot-rules/${editingBotRuleId.value}`, 'PATCH', form.value)
    } else if (active.value === 'campaigns') {
      await crmWrite('campaigns', 'POST', {
        ...form.value,
        media_token: campaignMedia.value?.token || '',
        media_type: campaignMedia.value?.type || '',
        media_name: campaignMedia.value?.name || '',
        media_size: Number(campaignMedia.value?.size || 0)
      })
    } else {
      await crmWrite(active.value, 'POST', form.value)
    }
    success.value = active.value === 'tasks' && editingTaskId.value
      ? 'Tarea actualizada correctamente.'
      : active.value === 'segments'
        ? `Segmento ${editingSegmentId.value ? 'actualizado' : 'creado'} y miembros calculados correctamente.`
        : active.value === 'messaging/bot-rules'
          ? `Automatización ${editingBotRuleId.value ? 'actualizada' : 'creada'} correctamente.`
          : 'Registro guardado correctamente.'
    resetForm()
    if (active.value === 'campaigns') clearCampaignMedia()
    await load()
  } catch (e) {
    error.value = `No se pudo guardar: ${e.message}`
  } finally {
    saving.value = false
  }
}

async function setTaskStatus(id, status) {
  try {
    await crmWrite(`tasks/${id}`, 'PATCH', { status })
    success.value = 'Tarea actualizada.'
    await load()
  } catch (e) { error.value = `No se pudo actualizar: ${e.message}` }
}

function editTask(task) {
  editingTaskId.value = Number(task.id)
  form.value = {
    title: task.title || '',
    description: task.description || '',
    status: task.status || 'pending',
    priority: task.priority || 'normal',
    customer_id: task.customer_id || '',
    assigned_user: task.assigned_user || '',
    due_at: task.due_at ? String(task.due_at).replace(' ', 'T').slice(0, 16) : ''
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function deleteTask(task) {
  if (!window.confirm(`¿Eliminar la tarea "${task.title}"?`)) return
  try {
    await crmWrite(`tasks/${task.id}`, 'DELETE', {})
    success.value = 'Tarea eliminada correctamente.'
    if (editingTaskId.value === Number(task.id)) resetForm()
    await load()
  } catch (e) { error.value = `No se pudo eliminar la tarea: ${e.message}` }
}

function taskStatusLabel(status) {
  return { pending: 'Pendiente', in_progress: 'En proceso', done: 'Completada', cancelled: 'Cancelada' }[status] || status
}

function taskPriorityLabel(priority) {
  return { high: 'Alta', normal: 'Normal', low: 'Baja' }[priority] || priority
}

function taskDueInfo(task) {
  if (!task.due_at) return { text: 'Sin fecha límite', state: 'none' }
  const due = new Date(String(task.due_at).replace(' ', 'T'))
  const now = new Date()
  const diffHours = Math.ceil((due - now) / 3600000)
  const formatted = due.toLocaleString('es-PE', { dateStyle: 'medium', timeStyle: 'short' })
  if (['done', 'cancelled'].includes(task.status)) return { text: formatted, state: 'closed' }
  if (diffHours < 0) return { text: `Vencida · ${formatted}`, state: 'overdue' }
  if (diffHours <= 24) return { text: `Vence pronto · ${formatted}`, state: 'soon' }
  return { text: formatted, state: 'active' }
}

async function queueCampaign(campaign) {
  try {
    const result = await crmWrite(`campaigns/${campaign.id}/prepare`, 'POST', {})
    success.value = `${result.created} mensajes preparados en modo ${result.mode}. Sólo se incluyeron clientes con consentimiento.`
  } catch (e) { error.value = `No se pudo preparar la cola: ${e.message}` }
}

async function testAssistant() {
  botDraft.value = null
  try {
    botDraft.value = await crmWrite('messaging/assistant/draft', 'POST', { message: botMessage.value })
  } catch (e) { error.value = `No se pudo probar el asistente: ${e.message}` }
}

function changeAiProvider() {
  if (aiSettings.value.provider === 'gemini') {
    aiSettings.value.model = 'gemini-3.6-flash'
  } else if (aiSettings.value.provider === 'openai') {
    aiSettings.value.model = 'gpt-5.6-luna'
  } else if (aiSettings.value.provider === 'simulation') {
    aiSettings.value.model = ''
    aiSettings.value.enabled = false
  }
}

async function deleteCampaign(campaign) {
  if (!window.confirm(`¿Eliminar la campaña "${campaign.name}"? Esta acción no se puede deshacer.`)) return
  error.value = ''
  success.value = ''
  try {
    await crmWrite(`campaigns/${campaign.id}`, 'DELETE', {})
    success.value = 'Campaña eliminada correctamente.'
    await load()
  } catch (e) {
    error.value = `No se pudo eliminar la campaña: ${e.message}`
  }
}

async function saveAiSettings() {
  savingAi.value = true
  error.value = ''
  success.value = ''
  try {
    const result = await crmWrite('messaging/ai-settings', 'POST', aiSettings.value)
    data.value.config = { ...data.value.config, ...result }
    aiSettings.value.api_key = ''
    showAiKey.value = false
    success.value = 'Asistente de IA configurado. La clave quedó guardada y oculta.'
  } catch (e) {
    error.value = `No se pudo guardar la IA: ${e.message}`
  } finally {
    savingAi.value = false
  }
}

async function connectWhatsApp() {
  if (qrTimer) {
    clearInterval(qrTimer)
    qrTimer = null
  }
  connecting.value = true
  error.value = ''
  try {
    connection.value = normalizeWhatsAppConnection(
      await crmWrite('messaging/connection/start', 'POST', {})
    )
    await refreshQr()
    if (connection.value.status !== 'connected') {
      qrTimer = setInterval(refreshQr, 3000)
    }
  } catch (e) {
    error.value = e.message === 'MESSAGING_GATEWAY_OFFLINE'
      ? 'El servicio local de WhatsApp no está iniciado.'
      : `No se pudo iniciar WhatsApp: ${e.message}`
    connecting.value = false
  }
}

async function disconnectWhatsApp() {
  if (!window.confirm('¿Desea desvincular este WhatsApp? Dejarán de recibirse y enviarse mensajes hasta vincular un QR nuevo.')) return
  connecting.value = true
  error.value = ''
  success.value = ''
  try {
    connection.value = await crmWrite('messaging/connection/disconnect', 'POST', {})
    clearInterval(qrTimer)
    success.value = 'WhatsApp fue desvinculado correctamente.'
  } catch (e) {
    error.value = `No se pudo desvincular WhatsApp: ${e.message}`
  } finally {
    connecting.value = false
  }
}

async function refreshQr() {
  try {
    const result = await crmWrite('messaging/connection/qr', 'POST', {})
    connection.value = normalizeWhatsAppConnection(result)
    if (connection.value.base64) {
      connecting.value = false
      error.value = ''
    }
    if (connection.value.status === 'connected') {
      clearInterval(qrTimer)
      qrTimer = null
      connecting.value = false
      await load()
    }
  } catch (e) {
    connecting.value = false
    error.value = `No se pudo consultar el QR: ${e.message}`
  }
}

function normalizeWhatsAppConnection(result) {
  const payload = result?.data && typeof result.data === 'object'
    ? result.data
    : (result || {})
  const rawQr = payload.base64 || payload.qr || payload.qrcode || payload.code || ''
  const base64 = rawQr
    ? (String(rawQr).startsWith('data:image/')
        ? String(rawQr)
        : `data:image/png;base64,${rawQr}`)
    : null

  return {
    ...payload,
    status: payload.status || connection.value.status || 'disconnected',
    base64: payload.status === 'connected'
      ? null
      : (base64 || connection.value.base64 || null),
  }
}

async function openConversation(conversation) {
  selectedConversation.value = conversation
  loadingMessages.value = true
  try {
    const [rows] = await Promise.all([
      crmGet(`messaging/conversations/${conversation.id}/messages`, { limit: 200 }),
      conversation.unread_count
        ? crmWrite(`messaging/conversations/${conversation.id}/read`, 'POST', {})
        : Promise.resolve()
    ])
    conversationMessages.value = rows.slice().reverse()
    conversation.unread_count = 0
  } catch (e) {
    error.value = `No se pudieron cargar los mensajes: ${e.message}`
  } finally {
    loadingMessages.value = false
  }
}

async function sendManualMessage() {
  const message = manualMessage.value.trim()
  if (!selectedConversation.value || !message || sendingManual.value) return
  sendingManual.value = true
  error.value = ''
  try {
    await crmWrite(`messaging/conversations/${selectedConversation.value.id}/send`, 'POST', { message })
    manualMessage.value = ''
    await refreshInbox()
  } catch (e) {
    error.value = `No se pudo enviar el mensaje: ${e.message}`
  } finally {
    sendingManual.value = false
  }
}

async function deleteConversation() {
  const conversation = selectedConversation.value
  if (!conversation) return
  const confirmed = window.confirm(
    `¿Eliminar la conversación con "${conversation.customer_name}" y todo su historial del CRM?\n\nEl chat original continuará existiendo en WhatsApp.`
  )
  if (!confirmed) return
  error.value = ''
  try {
    const result = await crmWrite(`messaging/conversations/${conversation.id}`, 'DELETE', {})
    selectedConversation.value = null
    conversationMessages.value = []
    manualMessage.value = ''
    success.value = `Conversación eliminada (${result.deleted_messages} mensajes).`
    await refreshInbox()
  } catch (e) {
    error.value = `No se pudo eliminar la conversación: ${e.message}`
  }
}

async function syncInboxContacts() {
  syncingContacts.value = true
  error.value = ''
  try {
    await crmWrite('messaging/inbox/sync-contacts', 'POST', {})
    await refreshInbox()
    success.value = 'Nombres y teléfonos actualizados desde WhatsApp.'
  } catch (e) {
    error.value = `No se pudieron actualizar los contactos: ${e.message}`
  } finally {
    syncingContacts.value = false
  }
}

function responseLabel(conversation) {
  if (conversation.status === 'resolved') return 'Cerrada'
  if (conversation.last_direction === 'inbound') return 'Pendiente de responder'
  return conversation.last_sender_type === 'bot' ? 'Respondida por IA' : 'Respondida por asesor'
}

async function refreshInbox() {
  if (active.value !== 'messaging/inbox') return
  try {
    const result = await crmGet('messaging/inbox', { limit: 100 })
    data.value = result
    if (selectedConversation.value) {
      const current = result.conversations.find(row => row.id === selectedConversation.value.id)
      if (current) await openConversation(current)
    }
  } catch (e) {
    error.value = `No se pudo actualizar la bandeja: ${e.message}`
  }
}

async function toggleAutoReply() {
  if (!data.value?.config?.ai_key_configured) {
    error.value = 'Primero configure una clave de Gemini o ChatGPT en Comunicaciones.'
    aiSettings.value.auto_reply_enabled = false
    return
  }
  aiSettings.value.enabled = true
  savingAi.value = true
  error.value = ''
  success.value = ''
  try {
    const result = await crmWrite('messaging/ai-settings', 'POST', aiSettings.value)
    data.value.config = { ...data.value.config, ...result }
    aiSettings.value.api_key = ''
    success.value = result.ai_auto_reply_enabled
      ? 'Respuestas automáticas de IA activadas.'
      : 'Respuestas automáticas desactivadas. Los mensajes seguirán llegando a la bandeja.'
  } catch (e) {
    aiSettings.value.auto_reply_enabled = !aiSettings.value.auto_reply_enabled
    error.value = `No se pudo cambiar la respuesta automática: ${e.message}`
  } finally {
    savingAi.value = false
  }
}

watch(active, () => {
  if (active.value !== 'messaging/status') clearInterval(qrTimer)
  clearInterval(inboxTimer)
  selectedConversation.value = null
  conversationMessages.value = []
  manualMessage.value = ''
  success.value = ''
  resetForm()
  load().then(() => {
    if (active.value === 'messaging/inbox') inboxTimer = setInterval(refreshInbox, 5000)
  })
})
function verifySession() {
  crmGet('health').catch(() => {})
}

function labelMeta(label) {
  return conversationLabels.find(item => item.key === (label || 'new')) || conversationLabels[0]
}

function analyticsDailyMax() {
  const rows = data.value?.daily_messages || []
  return Math.max(1, ...rows.flatMap(row => [Number(row.inbound), Number(row.outbound)]))
}

function analyticsLine(series) {
  const rows = data.value?.daily_messages || []
  const max = analyticsDailyMax()
  if (!rows.length) return ''
  return rows.map((row, index) => {
    const x = rows.length === 1 ? 50 : (index * 100) / (rows.length - 1)
    const y = 88 - (Number(row[series]) * 72) / max
    return `${x.toFixed(2)},${y.toFixed(2)}`
  }).join(' ')
}

function showAnalyticsDay(index) {
  const rows = data.value?.daily_messages || []
  const stride = Math.max(1, Math.ceil(rows.length / 7))
  return index === 0 || index === rows.length - 1 || index % stride === 0
}

async function applyAnalyticsRange() {
  if (!analyticsRange.value.from || !analyticsRange.value.to) {
    error.value = 'Seleccione la fecha inicial y final.'
    return
  }
  if (analyticsRange.value.from > analyticsRange.value.to) {
    error.value = 'La fecha inicial no puede ser posterior a la fecha final.'
    return
  }
  applyingAnalyticsRange.value = true
  success.value = ''
  try {
    await load()
  } finally {
    applyingAnalyticsRange.value = false
  }
}

function setAnalyticsPeriod(days) {
  analyticsRange.value = { from: inputDate(days - 1), to: inputDate(0) }
  applyAnalyticsRange()
}

function analyticsLabelWidth(total) {
  const max = Math.max(1, ...(data.value?.labels || []).map(row => Number(row.total)))
  return `${Math.max(4, (Number(total) * 100) / max)}%`
}

function operationalStatusLabel(status) {
  return {
    pending: 'Pendientes', in_progress: 'En proceso', done: 'Completadas', cancelled: 'Canceladas',
    draft: 'Borradores', active: 'Activas', paused: 'Pausadas', completed: 'Finalizadas'
  }[status] || status
}

function openModule(module) {
  active.value = module
}

async function changeConversationLabel(label) {
  if (!selectedConversation.value || savingLabel.value) return
  savingLabel.value = true
  error.value = ''
  try {
    const result = await crmWrite(
      `messaging/conversations/${selectedConversation.value.id}/label`,
      'PATCH',
      { label }
    )
    selectedConversation.value.sales_label = result.sales_label
    const row = data.value?.conversations?.find(item => item.id === selectedConversation.value.id)
    if (row) row.sales_label = result.sales_label
    success.value = `Chat etiquetado como "${labelMeta(result.sales_label).name}".`
  } catch (e) {
    error.value = `No se pudo cambiar la etiqueta: ${e.message}`
  } finally {
    savingLabel.value = false
  }
}

function verifyVisibleSession() {
  if (!document.hidden) verifySession()
}

onMounted(() => {
  resetForm()
  load()
  sessionTimer = setInterval(verifySession, 5000)
  window.addEventListener('focus', verifySession)
  document.addEventListener('visibilitychange', verifyVisibleSession)
})
onUnmounted(() => {
  clearInterval(qrTimer)
  clearInterval(inboxTimer)
  clearInterval(sessionTimer)
  window.removeEventListener('focus', verifySession)
  document.removeEventListener('visibilitychange', verifyVisibleSession)
})
</script>

<template>
  <div class="shell">
    <aside>
      <div class="brand"><span>N</span><div>NeuroSoft<small>CRM Moda &amp; Retail</small></div></div>
      <button v-for="[key, label] in tabs" :key="key" :class="{ active: active === key }" @click="active = key">
        {{ label }}
      </button>
      <a href="/index.php">← Volver al sistema</a>
    </aside>
    <main>
      <header><div><small>RELACIONES CON CLIENTES</small><h1>{{ title }}</h1></div><button class="refresh" @click="refresh">{{ active === 'dashboard' || active === 'customers' ? 'Sincronizar clientes' : 'Actualizar' }}</button></header>
      <div v-if="error" class="notice error">{{ error }}</div>
      <div v-if="success" class="notice success">{{ success }}</div>
      <div v-if="loading" class="notice">Cargando información…</div>
      <template v-if="!loading && active === 'dashboard' && data">
        <section class="home-hero">
          <div><small>CENTRO DE RELACIONES CON CLIENTES</small><h2>Todo lo importante, listo para actuar</h2>
            <p>Supervise oportunidades, conversaciones y clientes desde una sola vista.</p></div>
          <div class="home-health"><span class="online"></span><div><b>CRM operativo</b><small>Última sincronización: {{ data.last_sync_at || 'Aún no registrada' }}</small></div></div>
        </section>
        <section class="home-metrics">
          <button type="button" @click="openModule('customers')"><span class="metric-icon customers">C</span><div><small>CLIENTES ACTIVOS</small><strong>{{ data.customers || 0 }}</strong><em>{{ data.recent_customers || 0 }} compraron recientemente</em></div></button>
          <button type="button" @click="openModule('messaging/inbox')"><span class="metric-icon conversations">W</span><div><small>CHATS PENDIENTES</small><strong>{{ data.pending_conversations || 0 }}</strong><em>Requieren atención</em></div></button>
          <button type="button" @click="openModule('messaging/inbox')"><span class="metric-icon opportunities">★</span><div><small>OPORTUNIDADES</small><strong>{{ data.potential_buyers || 0 }}</strong><em>{{ data.closed_sales || 0 }} ventas cerradas</em></div></button>
          <button type="button" @click="openModule('tasks')"><span class="metric-icon tasks">✓</span><div><small>TAREAS ABIERTAS</small><strong>{{ data.open_tasks || 0 }}</strong><em>Seguimientos activos</em></div></button>
          <button type="button" @click="openModule('loyalty')"><span class="metric-icon loyalty">◆</span><div><small>PUNTOS VIGENTES</small><strong>{{ data.loyalty_points || 0 }}</strong><em>Programa de fidelización</em></div></button>
          <button type="button" @click="openModule('analytics')"><span class="metric-icon revenue">S/</span><div><small>CONSUMO REGISTRADO</small><strong>S/ {{ Number(data.tracked_revenue || 0).toFixed(2) }}</strong><em>Acumulado en CRM</em></div></button>
        </section>
        <section class="home-actions">
          <div><small>ACCESOS RÁPIDOS</small><h2>¿Qué desea hacer ahora?</h2></div>
          <button type="button" @click="openModule('messaging/inbox')"><span>01</span><b>Atender WhatsApp</b><small>{{ data.pending_conversations || 0 }} pendientes</small></button>
          <button type="button" @click="openModule('segments')"><span>02</span><b>Crear segmento</b><small>Agrupar clientes</small></button>
          <button type="button" @click="openModule('campaigns')"><span>03</span><b>Nueva campaña</b><small>Preparar promoción</small></button>
          <button type="button" @click="openModule('sync/status')"><span>04</span><b>Sincronizar</b><small>Actualizar información</small></button>
        </section>
        <section class="home-content">
          <article class="home-recent">
            <header><div><small>ACTIVIDAD COMERCIAL</small><h2>Clientes con consumo reciente</h2></div><button type="button" @click="openModule('customers')">Ver todos</button></header>
            <div v-if="data.topCustomers?.length" class="recent-list">
              <button v-for="customer in data.topCustomers" :key="customer.id" type="button" @click="openCustomer(customer.id)">
                <span class="customer-avatar">{{ customer.display_name.charAt(0) }}</span>
                <span class="customer-name"><b>{{ customer.display_name }}</b><small>{{ customer.last_purchase_at || 'Sin fecha de compra' }}</small></span>
                <span><small>COMPRAS</small><b>{{ customer.purchase_count }}</b></span>
                <span><small>CONSUMO</small><b>S/ {{ customer.total_spent }}</b></span>
                <i>Ver 360 →</i>
              </button>
            </div>
            <div v-else class="empty">Sin clientes sincronizados todavía.</div>
          </article>
          <aside class="home-priorities">
            <header><small>PRIORIDADES</small><h2>Atención recomendada</h2></header>
            <button type="button" class="urgent" @click="openModule('messaging/inbox')"><span>{{ data.pending_conversations || 0 }}</span><div><b>Conversaciones pendientes</b><small>Responder mejora la oportunidad de venta.</small></div></button>
            <button type="button" class="opportunity" @click="openModule('messaging/inbox')"><span>{{ data.potential_buyers || 0 }}</span><div><b>Posibles compradores</b><small>Dar seguimiento antes de que pierdan interés.</small></div></button>
            <button type="button" class="risk" @click="openModule('segments')"><span>{{ data.at_risk_customers || 0 }}</span><div><b>Clientes inactivos</b><small>Más de 90 días sin comprar.</small></div></button>
            <button type="button" class="neutral" @click="openModule('campaigns')"><span>{{ data.active_campaigns || 0 }}</span><div><b>Campañas activas</b><small>Revise alcance y próximos envíos.</small></div></button>
          </aside>
        </section>
      </template>
      <template v-else-if="!loading && active === 'analytics' && data">
        <form class="analytics-range" @submit.prevent="applyAnalyticsRange">
          <div><b>Período del dashboard</b><small>Los indicadores y gráficas se recalculan usando este rango.</small></div>
          <label>Desde<input v-model="analyticsRange.from" type="date" :max="analyticsRange.to || inputDate(0)" required /></label>
          <label>Hasta<input v-model="analyticsRange.to" type="date" :min="analyticsRange.from" :max="inputDate(0)" required /></label>
          <button type="submit" :disabled="applyingAnalyticsRange">{{ applyingAnalyticsRange ? 'Aplicando…' : 'Aplicar' }}</button>
          <div class="range-shortcuts">
            <button type="button" @click="setAnalyticsPeriod(7)">7 días</button>
            <button type="button" @click="setAnalyticsPeriod(14)">14 días</button>
            <button type="button" @click="setAnalyticsPeriod(30)">30 días</button>
          </div>
        </form>
        <section class="analytics-kpis" aria-label="Indicadores principales del CRM">
          <article><small>CONVERSACIONES</small><strong>{{ data.summary.conversations }}</strong><span>Total registrado</span></article>
          <article class="warning"><small>PENDIENTES</small><strong>{{ data.summary.pending_attention }}</strong><span>Esperan respuesta</span></article>
          <article class="buyer"><small>POSIBLES COMPRADORES</small><strong>{{ data.summary.potential_buyers }}</strong><span>Oportunidades abiertas</span></article>
          <article class="closed"><small>VENTAS CERRADAS</small><strong>{{ data.summary.closed_sales }}</strong><span>{{ data.summary.conversion_rate }}% de conversión etiquetada</span></article>
          <article><small>MENSAJES DEL PERÍODO</small><strong>{{ data.summary.messages_period }}</strong><span>{{ data.summary.inbound_total }} recibidos · {{ data.summary.outbound_total }} enviados</span></article>
          <article><small>CLIENTES CRM</small><strong>{{ data.summary.customers }}</strong><span>S/ {{ Number(data.summary.customer_revenue).toFixed(2) }} acumulado actual</span></article>
        </section>
        <section class="analytics-grid">
          <article class="analytics-panel activity-chart">
            <header><div><h2>Actividad de mensajes</h2><p>Del {{ data.range.from }} al {{ data.range.to }}.</p></div>
              <div class="chart-legend"><span class="inbound">Recibidos</span><span class="outbound">Enviados</span></div>
            </header>
            <svg viewBox="0 0 100 100" preserveAspectRatio="none" role="img" aria-label="Mensajes recibidos y enviados por día">
              <line v-for="y in [16,40,64,88]" :key="y" x1="0" :y1="y" x2="100" :y2="y" class="grid-line" />
              <polyline :points="analyticsLine('inbound')" class="line inbound" />
              <polyline :points="analyticsLine('outbound')" class="line outbound" />
            </svg>
            <div class="chart-days">
              <span v-for="(row,index) in data.daily_messages" :key="row.day" v-show="showAnalyticsDay(index)">{{ row.label }}</span>
            </div>
          </article>
          <article class="analytics-panel label-chart">
            <header><div><h2>Embudo comercial</h2><p>Distribución actual de conversaciones por etiqueta.</p></div></header>
            <div v-for="row in data.labels" :key="row.label" class="label-bar">
              <div><span class="dot" :style="{ background: labelMeta(row.label).color }"></span><b>{{ labelMeta(row.label).name }}</b><strong>{{ row.total }}</strong></div>
              <span class="bar-track"><i :style="{ width: analyticsLabelWidth(row.total), background: labelMeta(row.label).color }"></i></span>
            </div>
            <div v-if="!data.labels.length" class="empty">Todavía no hay conversaciones etiquetadas.</div>
          </article>
          <article class="analytics-panel message-balance">
            <header><div><h2>Balance de atención</h2><p>Comparación de mensajes recibidos y enviados.</p></div></header>
            <div class="balance-visual" :style="{ '--inbound-share': `${data.summary.messages_total ? (data.summary.inbound_total * 100 / data.summary.messages_total) : 0}%` }">
              <div class="balance-ring"><strong>{{ data.summary.messages_total }}</strong><span>mensajes</span></div>
              <div class="balance-values">
                <p><span class="inbound"></span><b>Recibidos</b><strong>{{ data.summary.inbound_total }}</strong></p>
                <p><span class="outbound"></span><b>Enviados</b><strong>{{ data.summary.outbound_total }}</strong></p>
              </div>
            </div>
          </article>
          <article class="analytics-panel operations-chart">
            <header><div><h2>Operación CRM</h2><p>Tareas y campañas registradas por estado.</p></div></header>
            <div class="operation-group"><b>Tareas</b>
              <p v-for="row in data.tasks" :key="`task-${row.status}`"><span>{{ operationalStatusLabel(row.status) }}</span><strong>{{ row.total }}</strong></p>
              <small v-if="!data.tasks.length">Sin tareas registradas.</small>
            </div>
            <div class="operation-group"><b>Campañas</b>
              <p v-for="row in data.campaigns" :key="`campaign-${row.status}`"><span>{{ operationalStatusLabel(row.status) }}</span><strong>{{ row.total }}</strong></p>
              <small v-if="!data.campaigns.length">Sin campañas registradas.</small>
            </div>
          </article>
        </section>
        <p class="analytics-updated">Actualizado: {{ data.generated_at }}</p>
      </template>
      <section v-else-if="!loading" class="card">
        <form v-if="['campaigns','messaging/templates'].includes(active)" class="editor" @submit.prevent="save">
          <h2>{{ active === 'tasks' && editingTaskId ? 'Editar tarea' : 'Nuevo registro' }}</h2>
          <template v-if="active === 'campaigns'">
            <input v-model="form.name" required maxlength="160" placeholder="Nombre de la campaña" />
            <select v-model="form.channel"><option value="manual">Manual / simulación</option><option value="whatsapp">WhatsApp</option><option value="email">Correo</option><option value="sms">SMS</option></select>
            <textarea v-model="form.message" placeholder="Mensaje (no se enviará automáticamente)"></textarea>
            <div class="campaign-attachment-editor">
              <b>Aviso multimedia (opcional)</b>
              <small>Adjunte una imagen de hasta 5 MB o un video de hasta 12 MB.</small>
              <div class="media-picker">
                <input ref="campaignCreateMediaInput" class="file-hidden" type="file" @change="uploadCampaignMedia" />
                <button type="button" class="media-icon image" :disabled="uploadingMedia" @click="openCampaignCreateMedia('image')">
                  <span>📷</span><b>Elegir imagen</b>
                </button>
                <button type="button" class="media-icon video" :disabled="uploadingMedia" @click="openCampaignCreateMedia('video')">
                  <span>🎬</span><b>Elegir video</b>
                </button>
                <div v-if="uploadingMedia" class="media-loading">Cargando archivo…</div>
                <div v-if="campaignMedia" class="media-preview">
                  <img v-if="campaignMedia.type === 'image' && campaignMedia.preview" :src="campaignMedia.preview" alt="Vista previa de campaña" />
                  <video v-else-if="campaignMedia.preview" :src="campaignMedia.preview" controls></video>
                  <span v-else class="saved-media-icon">{{ campaignMedia.type === 'video' ? '🎬' : '🖼️' }}</span>
                  <div><b>{{ campaignMedia.name }}</b><small>{{ (campaignMedia.size / 1048576).toFixed(2) }} MB</small></div>
                  <button type="button" title="Quitar archivo" @click="clearCampaignMedia">×</button>
                </div>
              </div>
            </div>
          </template>
          <template v-else-if="active === 'messaging/templates'">
            <input v-model="form.name" required maxlength="120" placeholder="Nombre de la plantilla" />
            <select v-model="form.channel"><option value="whatsapp">WhatsApp</option><option value="email">Correo</option><option value="sms">SMS</option></select>
            <input v-model="form.subject" maxlength="180" placeholder="Asunto (opcional)" />
            <textarea v-model="form.body" required maxlength="1200" placeholder="Hola {{nombre}}, tenemos una promoción para ti"></textarea>
          </template>
          <button :disabled="saving">{{ saving ? 'Guardando…' : (active === 'tasks' && editingTaskId ? 'Guardar cambios' : 'Guardar') }}</button>
          <button v-if="active === 'tasks' && editingTaskId" type="button" class="cancel-edit" @click="resetForm">Cancelar edición</button>
        </form>
        <div v-if="active === 'tasks'" class="task-workspace">
          <section class="task-overview">
            <article><small>TOTAL</small><strong>{{ taskCounts.all }}</strong><span>Tareas registradas</span></article>
            <article class="pending"><small>PENDIENTES</small><strong>{{ taskCounts.pending }}</strong><span>Por iniciar</span></article>
            <article class="progress"><small>EN PROCESO</small><strong>{{ taskCounts.in_progress }}</strong><span>En atención</span></article>
            <article class="overdue"><small>VENCIDAS</small><strong>{{ taskCounts.overdue }}</strong><span>Requieren prioridad</span></article>
            <article class="done"><small>COMPLETADAS</small><strong>{{ taskCounts.done }}</strong><span>Trabajo finalizado</span></article>
          </section>
          <section class="task-editor">
            <header><div><small>GESTIÓN DE SEGUIMIENTO</small><h2>{{ editingTaskId ? 'Editar tarea' : 'Nueva tarea' }}</h2><p>Registre el próximo paso y asígnelo a una persona responsable.</p></div>
              <button v-if="editingTaskId" type="button" @click="resetForm">Cancelar edición</button></header>
            <form @submit.prevent="save">
              <label class="task-title">Título de la tarea<input v-model="form.title" required maxlength="180" placeholder="Ej. Llamar para confirmar pedido o cotización" /></label>
              <label class="task-description">Descripción<textarea v-model="form.description" maxlength="2000" placeholder="Contexto, compromiso o resultado esperado"></textarea></label>
              <label>Prioridad<select v-model="form.priority"><option value="low">Baja</option><option value="normal">Normal</option><option value="high">Alta</option></select></label>
              <label>Fecha límite<input v-model="form.due_at" type="datetime-local" /></label>
              <label>Cliente relacionado<select v-model="form.customer_id"><option value="">Sin cliente relacionado</option><option v-for="customer in taskCustomers" :key="customer.id" :value="customer.id">{{ customer.display_name }}</option></select></label>
              <label>Responsable<input v-model="form.assigned_user" maxlength="100" placeholder="Nombre del vendedor o asesor" /></label>
              <label v-if="editingTaskId">Estado<select v-model="form.status"><option value="pending">Pendiente</option><option value="in_progress">En proceso</option><option value="done">Completada</option><option value="cancelled">Cancelada</option></select></label>
              <button class="task-save" :disabled="saving">{{ saving ? 'Guardando…' : editingTaskId ? 'Guardar cambios' : 'Crear tarea' }}</button>
            </form>
          </section>
          <section class="task-board">
            <header><div><small>AGENDA COMERCIAL</small><h2>Seguimientos y pendientes</h2></div><strong>{{ filteredTasks.length }} resultado(s)</strong></header>
            <div class="task-filterbar">
              <label>Estado<select v-model="taskFilters.status"><option value="open">Abiertas</option><option value="">Todas</option><option value="pending">Pendientes</option><option value="in_progress">En proceso</option><option value="done">Completadas</option><option value="cancelled">Canceladas</option></select></label>
              <label>Prioridad<select v-model="taskFilters.priority"><option value="">Todas</option><option value="high">Alta</option><option value="normal">Normal</option><option value="low">Baja</option></select></label>
              <label>Cliente<select v-model="taskFilters.customer"><option value="">Todos los clientes</option><option v-for="customer in taskCustomers" :key="customer.id" :value="customer.id">{{ customer.display_name }}</option></select></label>
            </div>
            <div v-if="filteredTasks.length" class="task-cards">
              <article v-for="task in filteredTasks" :key="task.id" :class="[`priority-${task.priority}`, `status-${task.status}`]">
                <header><div><span class="task-priority">{{ taskPriorityLabel(task.priority) }}</span><span class="task-status">{{ taskStatusLabel(task.status) }}</span></div><small>#{{ task.id }}</small></header>
                <h3>{{ task.title }}</h3><p>{{ task.description || 'Sin descripción adicional.' }}</p>
                <div class="task-meta"><span><small>CLIENTE</small><b>{{ task.customer_name || 'Sin cliente' }}</b></span><span><small>RESPONSABLE</small><b>{{ task.assigned_user || 'Sin asignar' }}</b></span></div>
                <div class="task-due" :class="taskDueInfo(task).state">{{ taskDueInfo(task).text }}</div>
                <footer><button v-if="task.status === 'pending'" class="start" @click="setTaskStatus(task.id, 'in_progress')">Iniciar</button><button v-if="!['done','cancelled'].includes(task.status)" class="complete" @click="setTaskStatus(task.id, 'done')">Completar</button><button @click="editTask(task)">Editar</button><button class="delete" @click="deleteTask(task)">Eliminar</button></footer>
              </article>
            </div>
            <div v-else class="task-empty"><span>✓</span><h3>No hay tareas con estos filtros</h3><p>{{ taskCounts.all ? 'Cambie los filtros para consultar otras tareas.' : 'Cree la primera tarea para organizar el seguimiento de sus clientes.' }}</p></div>
          </section>
        </div>
        <div v-if="active === 'loyalty' && data" class="loyalty-workspace">
          <section class="loyalty-overview">
            <article class="balance"><span>◆</span><div><small>SALDO DISPONIBLE</small><strong>{{ loyaltySummary.balance.toLocaleString() }}</strong><em>puntos vigentes</em></div></article>
            <article><small>CLIENTES AFILIADOS</small><strong>{{ loyaltySummary.customers }}</strong><em>con saldo registrado</em></article>
            <article class="earned"><small>PUNTOS OTORGADOS</small><strong>{{ loyaltySummary.earned.toLocaleString() }}</strong><em>histórico acumulado</em></article>
            <article class="redeemed"><small>PUNTOS CANJEADOS</small><strong>{{ loyaltySummary.redeemed.toLocaleString() }}</strong><em>{{ loyaltySummary.redemptionRate }}% de utilización</em></article>
          </section>

          <section class="loyalty-adjustment">
            <header><div><small>OPERACIÓN DE PUNTOS</small><h2>Registrar movimiento</h2><p>Premie una compra o registre el canje de un beneficio.</p></div>
              <span :class="{ available: selectedLoyaltyAccount }">{{ selectedLoyaltyAccount ? `${selectedLoyaltyAccount.balance} puntos disponibles` : 'Seleccione un cliente' }}</span>
            </header>
            <form @submit.prevent="save">
              <label>Cliente<select v-model.number="form.customer_id" required><option value="">Seleccione cliente</option><option v-for="customer in data.customers || []" :key="customer.id" :value="customer.id">{{ customer.display_name }}</option></select></label>
              <label>Tipo de movimiento<select :value="Number(form.points) < 0 ? 'redeem' : 'earn'" @change="form.points = $event.target.value === 'redeem' ? -Math.max(1, Math.abs(Number(form.points || 1))) : Math.max(1, Math.abs(Number(form.points || 1)))"><option value="earn">Otorgar puntos</option><option value="redeem">Canjear puntos</option></select></label>
              <label>Cantidad<input :value="Math.abs(Number(form.points || 0))" required type="number" min="1" @input="form.points = Number(form.points) < 0 ? -Math.abs(Number($event.target.value)) : Math.abs(Number($event.target.value))" /></label>
              <label class="loyalty-note">Motivo<input v-model="form.note" required maxlength="255" placeholder="Ej. Compra frecuente, promoción o canje de descuento" /></label>
              <button :disabled="saving || !form.customer_id || !form.points || (Number(form.points) < 0 && Math.abs(Number(form.points)) > Number(selectedLoyaltyAccount?.balance || 0))">{{ saving ? 'Registrando…' : Number(form.points) < 0 ? 'Confirmar canje' : 'Otorgar puntos' }}</button>
            </form>
            <p v-if="Number(form.points) < 0 && form.customer_id && Math.abs(Number(form.points)) > Number(selectedLoyaltyAccount?.balance || 0)" class="loyalty-warning">El cliente no tiene saldo suficiente para realizar este canje.</p>
          </section>

          <section class="loyalty-content">
            <article class="loyalty-clients">
              <header><div><small>CARTERA FIDELIZADA</small><h2>Clientes y saldos</h2></div><strong>{{ filteredLoyaltyAccounts.length }} cliente(s)</strong></header>
              <div class="loyalty-search"><span>⌕</span><input v-model="loyaltySearch" placeholder="Buscar cliente por nombre o código" /></div>
              <div v-if="filteredLoyaltyAccounts.length" class="loyalty-list">
                <div v-for="account in filteredLoyaltyAccounts" :key="account.customer_id">
                  <span class="loyalty-avatar">{{ account.display_name.charAt(0) }}</span>
                  <div><b>{{ account.display_name }}</b><small>Cliente #{{ account.customer_id }} · Actualizado {{ readableDate(account.updated_at) }}</small></div>
                  <p><strong>{{ Number(account.balance).toLocaleString() }}</strong><small>disponibles</small></p>
                  <div class="loyalty-history"><span>+{{ Number(account.lifetime_earned).toLocaleString() }} ganados</span><span>−{{ Number(account.lifetime_redeemed).toLocaleString() }} canjeados</span></div>
                  <button type="button" @click="form.customer_id = Number(account.customer_id)">Gestionar</button>
                </div>
              </div>
              <div v-else class="loyalty-empty"><span>◆</span><h3>Sin clientes para mostrar</h3><p>{{ loyaltySearch ? 'No encontramos coincidencias con la búsqueda.' : 'Los clientes aparecerán cuando reciban sus primeros puntos.' }}</p></div>
            </article>

            <article class="loyalty-ledger">
              <header><div><small>TRAZABILIDAD</small><h2>Últimos movimientos</h2></div><select v-model="loyaltyMovementFilter"><option value="all">Todos</option><option value="earned">Otorgados</option><option value="redeemed">Canjeados</option></select></header>
              <div v-if="filteredLoyaltyMovements.length" class="movement-list">
                <div v-for="movement in filteredLoyaltyMovements" :key="movement.id">
                  <span :class="Number(movement.points) >= 0 ? 'positive' : 'negative'">{{ Number(movement.points) >= 0 ? '+' : '' }}{{ movement.points }}</span>
                  <div><b>{{ movement.display_name }}</b><small>{{ loyaltyMovementLabel(movement.movement_type, movement.points) }} · {{ movement.note || 'Sin observación' }}</small></div>
                  <time>{{ readableDate(movement.created_at) }}</time>
                </div>
              </div>
              <div v-else class="loyalty-empty compact"><span>↕</span><h3>Sin movimientos</h3><p>Los ajustes y canjes quedarán registrados aquí.</p></div>
            </article>
          </section>
        </div>
        <div v-if="active === 'messaging/bot-rules'" class="bot-rule-workspace">
          <section class="bot-rule-overview">
            <article class="primary"><span>✦</span><div><small>AUTOMATIZACIONES</small><strong>{{ botRuleSummary.total }}</strong><em>reglas configuradas</em></div></article>
            <article><small>ACTIVAS</small><strong>{{ botRuleSummary.active }}</strong><em>respondiendo clientes</em></article>
            <article><small>RESPUESTAS DIRECTAS</small><strong>{{ botRuleSummary.automatic }}</strong><em>sin intervención humana</em></article>
            <article><small>DERIVACIONES</small><strong>{{ botRuleSummary.handoff }}</strong><em>pasan a un asesor</em></article>
          </section>

          <section class="bot-rule-studio">
            <header><div><small>DISEÑADOR DE CONVERSACIONES</small><h2>{{ editingBotRuleId ? 'Editar automatización' : 'Nueva respuesta inteligente' }}</h2><p>Defina qué debe detectar el bot y cómo debe atender al cliente.</p></div><button v-if="editingBotRuleId" type="button" @click="resetForm">Cancelar edición</button></header>
            <form @submit.prevent="save">
              <label>Nombre interno<input v-model="form.name" required maxlength="120" placeholder="Ej. Consulta de horarios" /><small>Solo lo verá su equipo.</small></label>
              <label>Cuando el mensaje contenga<input v-model="form.trigger_text" required maxlength="250" placeholder="Ej. horario, atienden, abierto" /><small>Use una palabra o frase fácil de reconocer.</small></label>
              <label class="bot-response">Respuesta para el cliente<textarea v-model="form.response_text" required maxlength="1200" placeholder="Hola, nuestro horario de atención es…"></textarea><small>{{ (form.response_text || '').length }} / 1200 caracteres</small></label>
              <label>Orden de prioridad<select v-model.number="form.priority"><option :value="10">Muy alta</option><option :value="50">Alta</option><option :value="100">Normal</option><option :value="200">Baja</option></select><small>Las reglas prioritarias se evalúan primero.</small></label>
              <div class="bot-options">
                <label :class="{ selected: form.is_active }"><input v-model="form.is_active" type="checkbox" /><span><b>Automatización activa</b><small>Disponible para responder.</small></span></label>
                <label :class="{ selected: form.handoff }"><input v-model="form.handoff" type="checkbox" /><span><b>Derivar a un asesor</b><small>Marca la conversación para atención humana.</small></span></label>
              </div>
              <button class="bot-rule-save" :disabled="saving">{{ saving ? 'Guardando…' : editingBotRuleId ? 'Guardar cambios' : 'Crear automatización' }}</button>
            </form>
          </section>

          <section class="bot-rule-content">
            <article class="bot-rule-library">
              <header><div><small>BIBLIOTECA DE RESPUESTAS</small><h2>Flujos configurados</h2></div><strong>{{ botRuleSummary.active }} activos</strong></header>
              <div v-if="data?.length" class="bot-rule-cards">
                <div v-for="rule in data" :key="rule.id" :class="{ inactive: Number(rule.is_active) !== 1 }">
                  <header><span :class="Number(rule.is_active) === 1 ? 'active' : 'paused'">{{ Number(rule.is_active) === 1 ? 'Activa' : 'Pausada' }}</span><small>Prioridad {{ rule.priority }}</small></header>
                  <h3>{{ rule.name }}</h3>
                  <p class="bot-trigger"><small>SI EL CLIENTE DICE</small><b>“{{ rule.trigger_text }}”</b></p>
                  <p class="bot-answer"><small>EL BOT RESPONDE</small>{{ rule.response_text }}</p>
                  <span v-if="Number(rule.handoff) === 1" class="handoff">↗ Deriva la conversación a un asesor</span>
                  <footer><button type="button" @click="toggleBotRule(rule)">{{ Number(rule.is_active) === 1 ? 'Pausar' : 'Activar' }}</button><button type="button" @click="editBotRule(rule)">Editar</button><button type="button" class="danger" @click="deleteBotRule(rule)">Eliminar</button></footer>
                </div>
              </div>
              <div v-else class="bot-rule-empty"><span>✦</span><h3>Aún no existen automatizaciones</h3><p>Cree la primera respuesta para atender consultas repetitivas.</p></div>
            </article>

            <article class="bot-rule-simulator">
              <header><div><small>PRUEBA EN VIVO</small><h2>Simulador del chatbot</h2><p>Compruebe la respuesta sin enviar mensajes reales.</p></div></header>
              <div class="simulator-phone">
                <div class="simulator-head"><span>N</span><div><b>Asistente NeuroSoft</b><small>En línea · entorno de prueba</small></div></div>
                <div class="simulator-chat">
                  <p class="welcome">Escriba como lo haría un cliente para comprobar qué regla se activa.</p>
                  <div v-if="botRuleTestMessage" class="customer-bubble">{{ botRuleTestMessage }}</div>
                  <div v-if="botRuleTestResult" class="bot-bubble">{{ botRuleTestResult.draft }}<small>{{ botRuleTestResult.handoff ? 'Se derivaría a un asesor' : 'Respuesta automática' }}</small></div>
                </div>
                <form @submit.prevent="testBotRule"><input v-model="botRuleTestMessage" maxlength="500" placeholder="Ej. ¿Cuál es su horario?" /><button :disabled="testingBotRule || !botRuleTestMessage.trim()">{{ testingBotRule ? '…' : 'Enviar' }}</button></form>
              </div>
            </article>
          </section>
        </div>
        <div v-if="active === 'customers'" class="customers-workspace">
          <section class="customer-overview">
            <article class="primary"><span>☷</span><div><small>CLIENTES VISIBLES</small><strong>{{ customerSummary.total }}</strong><em>en esta consulta</em></div></article>
            <article><small>CON TELÉFONO</small><strong>{{ customerSummary.withPhone }}</strong><em>disponibles para contactar</em></article>
            <article><small>COMPRAS ACUMULADAS</small><strong>{{ customerSummary.purchases }}</strong><em>operaciones registradas</em></article>
            <article><small>CONSUMO ACUMULADO</small><strong>S/ {{ customerSummary.spent.toFixed(2) }}</strong><em>valor de la cartera visible</em></article>
          </section>
          <section class="customer-directory">
            <header><div><small>DIRECTORIO COMERCIAL</small><h2>Clientes</h2><p>Consulte su actividad, información de contacto y perfil de compra.</p></div></header>
            <div class="customer-toolbar">
              <label><span>⌕</span><input v-model="search" placeholder="Buscar por nombre, documento o teléfono" @keyup.enter="load" /></label>
              <button @click="load">Buscar cliente</button>
              <input ref="customerFileInput" class="file-hidden" type="file" accept=".xlsx,.csv" @change="importCustomerFile" />
              <button class="import" :disabled="importingCustomers" @click="customerFileInput?.click()">{{ importingCustomers ? 'Importando…' : 'Importar desde Excel' }}</button>
              <small>Excel: Nombre y Teléfono obligatorios; Documento y Correo opcionales.</small>
            </div>
            <div v-if="data?.length" class="customer-table-wrap">
              <table class="customer-table">
                <thead><tr><th>Cliente</th><th>Contacto</th><th>Documento</th><th>Última compra</th><th>Compras</th><th>Consumo total</th><th>Perfil</th><th>Acciones</th></tr></thead>
                <tbody><tr v-for="customer in data" :key="customer.id">
                  <td><div class="customer-name"><span>{{ customer.display_name.charAt(0) }}</span><div><b>{{ customer.display_name }}</b><small>Código interno #{{ customer.id }}</small></div></div></td>
                  <td><b>{{ customer.phone || 'Sin teléfono' }}</b><small>{{ customer.email || 'Sin correo registrado' }}</small></td>
                  <td>{{ customer.document_number || 'Sin documento' }}</td>
                  <td><b>{{ customerRecency(customer.last_purchase_at) }}</b><small>{{ readableDate(customer.last_purchase_at) }}</small></td>
                  <td><strong>{{ customer.purchase_count }}</strong></td>
                  <td><strong>S/ {{ Number(customer.total_spent || 0).toFixed(2) }}</strong></td>
                  <td><span class="customer-level" :class="customerLevel(customer).toLowerCase()">{{ customerLevel(customer) }}</span></td>
                  <td><div class="customer-actions"><button @click="openCustomer(customer.id)">Ver perfil 360</button><button @click="editCustomer(customer)">Editar</button></div></td>
                </tr></tbody>
              </table>
            </div>
            <div v-else class="customer-empty"><span>⌕</span><h3>No se encontraron clientes</h3><p>Pruebe con otro término o sincronice los clientes del sistema de ventas.</p></div>
          </section>
        </div>
        <div v-if="active === 'segments'" class="segment-workspace">
          <section class="segment-builder">
            <header>
              <div><small>SEGMENTACIÓN DINÁMICA</small><h2>{{ editingSegmentId ? 'Editar segmento' : 'Crear nuevo segmento' }}</h2>
                <p>Combine criterios para encontrar automáticamente a los clientes adecuados.</p></div>
              <button v-if="editingSegmentId" type="button" class="segment-cancel" @click="resetForm">Cancelar edición</button>
            </header>
            <div class="segment-identity">
              <label>Nombre del segmento<input v-model="form.name" maxlength="120" placeholder="Ej. Clientes VIP recientes" /></label>
              <label>Descripción<input v-model="form.description" maxlength="500" placeholder="Objetivo comercial de este grupo" /></label>
            </div>
            <div class="segment-rule-grid">
              <article :class="{ enabled: form.use_frequency }">
                <label class="rule-toggle"><input v-model="form.use_frequency" type="checkbox" /><span><b>Frecuencia de compra</b><small>Cuántas veces compró el cliente</small></span></label>
                <div><label>Mínimo<input v-model.number="form.min_frequency" type="number" min="0" :disabled="!form.use_frequency" /></label>
                  <label>Máximo<input v-model="form.max_frequency" type="number" min="0" placeholder="Sin límite" :disabled="!form.use_frequency" /></label></div>
              </article>
              <article :class="{ enabled: form.use_recency }">
                <label class="rule-toggle"><input v-model="form.use_recency" type="checkbox" /><span><b>Recencia</b><small>Días transcurridos desde su última compra</small></span></label>
                <div><label>Desde<input v-model="form.min_recency" type="number" min="0" placeholder="0" :disabled="!form.use_recency" /></label>
                  <label>Hasta<input v-model.number="form.max_recency" type="number" min="0" placeholder="Sin límite" :disabled="!form.use_recency" /></label></div>
              </article>
              <article :class="{ enabled: form.use_monetary }">
                <label class="rule-toggle"><input v-model="form.use_monetary" type="checkbox" /><span><b>Valor monetario</b><small>Gasto acumulado del cliente</small></span></label>
                <div><label>Gasto mínimo S/<input v-model.number="form.min_monetary" type="number" min="0" step="0.01" :disabled="!form.use_monetary" /></label>
                  <label>Gasto máximo S/<input v-model="form.max_monetary" type="number" min="0" step="0.01" placeholder="Sin límite" :disabled="!form.use_monetary" /></label></div>
              </article>
            </div>
            <div class="segment-builder-actions">
              <button type="button" class="preview" :disabled="previewingSegment" @click="previewCurrentSegment">{{ previewingSegment ? 'Calculando…' : 'Vista previa' }}</button>
              <button type="button" class="save" :disabled="saving || !form.name" @click="save">{{ saving ? 'Guardando…' : editingSegmentId ? 'Guardar cambios' : 'Crear segmento' }}</button>
            </div>
            <aside v-if="segmentPreview" class="segment-preview">
              <div><small>CLIENTES COINCIDENTES</small><strong>{{ segmentPreview.member_count }}</strong><span>La vista muestra hasta 20 ejemplos.</span></div>
              <ul><li v-for="customer in segmentPreview.customers" :key="customer.id"><b>{{ customer.display_name }}</b><span>{{ customer.purchase_count }} compras · S/ {{ customer.total_spent }} · hace {{ customer.recency_days ?? '—' }} días</span></li></ul>
              <p v-if="!segmentPreview.customers.length">Ningún cliente cumple actualmente estas condiciones.</p>
            </aside>
          </section>
          <section class="segment-library">
            <header><div><h2>Segmentos guardados</h2><p>Los miembros se recalculan con la información actual del punto de venta.</p></div><strong>{{ data?.length || 0 }} segmento(s)</strong></header>
            <div class="segment-cards">
              <article v-for="row in data || []" :key="row.id">
                <header><div><small>SEGMENTO #{{ row.id }}</small><h3>{{ row.name }}</h3></div><strong>{{ row.member_count }}<small>clientes</small></strong></header>
                <p>{{ row.description || 'Sin descripción.' }}</p>
                <div class="segment-rules"><span v-for="rule in segmentRulesText(row)" :key="rule">{{ rule }}</span></div>
                <footer><small>Actualizado: {{ row.refreshed_at || 'Pendiente de cálculo' }}</small><div>
                  <button type="button" :disabled="refreshingSegmentId === Number(row.id)" @click="refreshSegment(row)">{{ refreshingSegmentId === Number(row.id) ? 'Calculando…' : 'Recalcular' }}</button>
                  <button type="button" @click="editSegment(row)">Editar</button>
                  <button type="button" class="danger" @click="deleteSegment(row)">Eliminar</button>
                </div></footer>
              </article>
              <div v-if="!data?.length" class="empty">Cree el primer segmento para organizar automáticamente a sus clientes.</div>
            </div>
          </section>
        </div>
        <div v-if="active === 'messaging/inbox' && data" class="inbox">
          <div class="inbox-toolbar">
            <div>
              <h2>Bandeja de conversaciones</h2>
              <p>Todos los mensajes directos recibidos por el WhatsApp vinculado.</p>
            </div>
            <div class="inbox-actions">
              <button type="button" :disabled="syncingContacts" @click="syncInboxContacts">
                {{ syncingContacts ? 'Actualizando…' : 'Actualizar contactos' }}
              </button>
            <label class="inbox-ai-toggle">
              <input v-model="aiSettings.auto_reply_enabled" type="checkbox" :disabled="savingAi" @change="toggleAutoReply" />
              <span><b>Respuestas automáticas IA</b><small>{{ aiSettings.auto_reply_enabled ? 'Activadas' : 'Desactivadas' }}</small></span>
            </label>
            </div>
          </div>
          <nav class="inbox-tabs" aria-label="Filtrar conversaciones">
            <button :class="{ active: inboxFilter === 'new' }" @click="inboxFilter = 'new'">
              Nuevas / pendientes <span>{{ inboxCounts.new }}</span>
            </button>
            <button :class="{ active: inboxFilter === 'attended' }" @click="inboxFilter = 'attended'">
              Atendidas <span>{{ inboxCounts.attended }}</span>
            </button>
            <button :class="{ active: inboxFilter === 'all' }" @click="inboxFilter = 'all'">
              Todas <span>{{ inboxCounts.all }}</span>
            </button>
            <label class="label-filter">
              Etiqueta
              <select v-model="labelFilter">
                <option value="all">Todas las etiquetas</option>
                <option v-for="label in conversationLabels" :key="label.key" :value="label.key">{{ label.name }}</option>
              </select>
            </label>
          </nav>
          <div class="inbox-layout">
            <aside class="conversation-list">
              <button v-for="conversation in filteredConversations" :key="conversation.id"
                :class="{ selected: selectedConversation?.id === conversation.id }"
                @click="openConversation(conversation)">
                <span class="avatar">{{ (conversation.customer_name || '?').charAt(0).toUpperCase() }}</span>
                <span class="conversation-info">
                  <b>{{ conversation.customer_name }}</b>
                  <small>{{ conversation.phone }}</small>
                  <strong class="sales-label" :style="{ '--label-color': labelMeta(conversation.sales_label).color }">
                    {{ labelMeta(conversation.sales_label).name }}
                  </strong>
                  <i :class="{ pending: conversation.last_direction === 'inbound' }">{{ responseLabel(conversation) }}</i>
                </span>
                <em v-if="conversation.unread_count">{{ conversation.unread_count }}</em>
              </button>
              <div v-if="!filteredConversations.length" class="empty">
                {{ inboxFilter === 'new' ? 'No hay mensajes pendientes de responder.' : inboxFilter === 'attended' ? 'Todavía no hay conversaciones atendidas.' : 'Todavía no hay conversaciones.' }}
              </div>
            </aside>
            <section class="chat-panel">
              <template v-if="selectedConversation">
                <header class="chat-header">
                  <div><b>{{ selectedConversation.customer_name }}</b><small>{{ selectedConversation.phone }}</small></div>
                  <div class="chat-header-actions">
                    <label class="chat-label-picker" :style="{ '--label-color': labelMeta(selectedConversation.sales_label).color }">
                      <span>Etiqueta comercial</span>
                      <select :value="selectedConversation.sales_label || 'new'" :disabled="savingLabel"
                        @change="changeConversationLabel($event.target.value)">
                        <option v-for="label in conversationLabels" :key="label.key" :value="label.key">{{ label.name }}</option>
                      </select>
                    </label>
                    <span>{{ aiSettings.auto_reply_enabled ? 'IA respondiendo' : 'Atención manual' }}</span>
                    <button type="button" class="delete-conversation" @click="deleteConversation">Eliminar conversación</button>
                  </div>
                </header>
                <div class="messages">
                  <div v-if="loadingMessages" class="empty">Cargando mensajes…</div>
                  <article v-for="message in conversationMessages" :key="message.id"
                    :class="message.direction === 'outbound' ? 'outbound' : 'inbound'">
                    <p>{{ message.body }}</p>
                    <small>{{ message.sender_type === 'bot' ? 'IA' : message.sender_type === 'customer' ? 'Cliente' : 'Asesor' }} · {{ message.created_at }}</small>
                  </article>
                  <div v-if="!loadingMessages && !conversationMessages.length" class="empty">No hay mensajes para mostrar.</div>
                </div>
                <form class="chat-composer" @submit.prevent="sendManualMessage">
                  <textarea v-model="manualMessage" maxlength="4000" rows="2"
                    placeholder="Escriba una respuesta al cliente…"
                    @keydown.enter.exact.prevent="sendManualMessage"></textarea>
                  <button :disabled="sendingManual || !manualMessage.trim()">
                    {{ sendingManual ? 'Enviando…' : 'Enviar' }}
                  </button>
                </form>
              </template>
              <div v-else class="chat-placeholder">Seleccione una conversación para ver sus mensajes.</div>
            </section>
          </div>
        </div>
        <div v-else-if="active === 'sync/status' && data" class="sync-panel">
          <div class="sync-summary">
            <article><small>ORIGEN</small><strong>{{ data.source_name }}</strong></article>
            <article><small>CLIENTES EN VENTAS</small><strong>{{ data.source_total }}</strong></article>
            <article><small>CLIENTES EN CRM</small><strong>{{ data.crm_total }}</strong></article>
            <article><small>ÚLTIMO ESTADO</small><strong>{{ data.last_run ? syncStatusLabel(data.last_run.status) : 'Sin ejecutar' }}</strong></article>
          </div>
          <section class="sync-action">
            <div>
              <h2>Sincronizar clientes</h2>
              <p>Actualiza nombres, teléfonos, compras, gasto acumulado y clasificación RFM desde el punto de venta.</p>
              <small>No elimina las ventas ni modifica documentos del sistema principal.</small>
            </div>
            <button :disabled="syncingCustomers" @click="runCustomerSync">
              {{ syncingCustomers ? 'Sincronizando…' : 'Sincronizar ahora' }}
            </button>
          </section>
          <section class="sync-history">
            <h2>Historial de sincronizaciones</h2>
            <table v-if="data.history.length">
              <thead><tr><th>Origen</th><th>Estado</th><th>Procesados</th><th>Inicio</th><th>Final</th><th>Detalle</th></tr></thead>
              <tbody><tr v-for="(run, index) in data.history" :key="index">
                <td>{{ run.source_name }}</td>
                <td><span class="sync-badge" :class="run.status">{{ syncStatusLabel(run.status) }}</span></td>
                <td>{{ run.rows_processed }}</td><td>{{ run.started_at }}</td><td>{{ run.finished_at || '—' }}</td>
                <td class="sync-error">{{ run.error_message || 'Correcto' }}</td>
              </tr></tbody>
            </table>
            <div v-else class="empty">Todavía no se realizó ninguna sincronización. Pulse “Sincronizar ahora”.</div>
          </section>
        </div>
        <div v-else-if="active === 'messaging/status' && data" class="communications">
          <div class="status-grid">
            <article><small>PROVEEDOR</small><strong>{{ data.provider.provider }}</strong></article>
            <article><small>MODO</small><strong>{{ data.provider.mode }}</strong></article>
            <article><small>CONFIGURADO</small><strong>{{ data.provider.configured ? 'Sí' : 'No' }}</strong></article>
            <article><small>ENVÍO REAL</small><strong>{{ data.provider.enabled ? 'Activado' : 'Apagado' }}</strong></article>
          </div>
          <div class="connection-panel">
            <div>
              <h2>Conectar WhatsApp</h2>
              <p>Use un número exclusivo de pruebas. En el teléfono abra WhatsApp → Dispositivos vinculados → Vincular dispositivo.</p>
            </div>
            <div class="connection-actions">
              <button v-if="connection.status !== 'connected'" @click="connectWhatsApp">{{ connecting ? 'Esperando lectura…' : 'Generar código QR' }}</button>
              <button v-else class="disconnect" :disabled="connecting" @click="disconnectWhatsApp">
                {{ connecting ? 'Desvinculando…' : 'Desvincular WhatsApp' }}
              </button>
            </div>
            <div v-if="connection.base64" class="qr-box">
              <img :src="connection.base64" alt="Código QR temporal para vincular WhatsApp" />
              <b>Escanee este código desde WhatsApp</b>
            </div>
            <div v-else class="connection-state">Estado: {{ connection.status || 'sin conexión' }}</div>
          </div>
          <form class="ai-settings" @submit.prevent="saveAiSettings">
            <div class="ai-settings-heading">
              <div>
                <h2>Asistente con inteligencia artificial</h2>
                <p>Configure Gemini o ChatGPT. La clave se guarda localmente y nunca vuelve a mostrarse.</p>
              </div>
              <label class="switch-label"><input v-model="aiSettings.enabled" type="checkbox" :disabled="aiSettings.provider === 'simulation'" /> Activar IA</label>
            </div>
            <div class="ai-settings-grid">
              <label>Proveedor
                <select v-model="aiSettings.provider" @change="changeAiProvider">
                  <option value="simulation">Sin IA / simulación</option>
                  <option value="gemini">Google Gemini</option>
                  <option value="openai">OpenAI ChatGPT</option>
                </select>
              </label>
              <label>Modelo
                <select v-model="aiSettings.model" :disabled="aiSettings.provider === 'simulation'">
                  <option v-if="aiSettings.provider === 'simulation'" value="">Seleccione primero un proveedor</option>
                  <option v-for="[modelId, modelLabel] in aiModelOptions" :key="modelId" :value="modelId">{{ modelLabel }}</option>
                </select>
              </label>
              <label class="api-key-field">Clave API
                <span>
                  <input v-model="aiSettings.api_key" :type="showAiKey ? 'text' : 'password'" maxlength="500"
                    :disabled="aiSettings.provider === 'simulation'"
                    :placeholder="data.config.ai_key_configured ? 'Clave guardada ••••••••' : 'Pegue aquí su clave API'" autocomplete="new-password" />
                  <button type="button" :disabled="aiSettings.provider === 'simulation'" :aria-label="showAiKey ? 'Ocultar clave' : 'Mostrar clave'" @click="showAiKey = !showAiKey">{{ showAiKey ? '🙈' : '👁' }}</button>
                </span>
                <small v-if="data.config.ai_key_configured">Clave configurada. Déjela vacía para conservarla.</small>
              </label>
            </div>
            <label class="auto-reply-check">
              <input v-model="aiSettings.auto_reply_enabled" type="checkbox" :disabled="!aiSettings.enabled" />
              Responder automáticamente los mensajes nuevos de WhatsApp
              <small>No responde en grupos y deriva a una persona cuando una regla así lo indique.</small>
            </label>
            <label class="prompt-field">Instrucciones de comportamiento (prompt)
              <textarea v-model="aiSettings.system_prompt" required minlength="40" maxlength="16000"
                placeholder="Explique cómo debe hablar, vender y cuándo debe derivar a una persona."></textarea>
              <small>{{ aiSettings.system_prompt.length.toLocaleString() }} / 16,000 caracteres. El asistente usará estas instrucciones para responder como vendedor. No coloque contraseñas ni datos sensibles.</small>
            </label>
            <button class="save-ai" :disabled="savingAi">{{ savingAi ? 'Guardando…' : 'Guardar configuración de IA' }}</button>
          </form>
          <div class="bot-tester">
            <h2>Probar asistente</h2>
            <p>Genera únicamente un borrador; no envía mensajes.</p>
            <div><input v-model="botMessage" maxlength="500" placeholder="Ejemplo: ¿Cuál es su horario?" @keyup.enter="testAssistant" /><button :disabled="!botMessage" @click="testAssistant">Probar respuesta</button></div>
            <blockquote v-if="botDraft">{{ botDraft.draft }}<small>Proveedor: {{ botDraft.provider }} · {{ botDraft.handoff ? 'Derivar a asesor' : 'Borrador IA' }}</small></blockquote>
          </div>
          <h2>Cola de mensajes</h2>
          <div class="table-wrap">
            <table v-if="data.outbox.length">
              <thead><tr><th>Cliente</th><th>Canal</th><th>Destino</th><th>Estado</th><th>Intentos</th><th>Disponible</th></tr></thead>
              <tbody><tr v-for="row in data.outbox" :key="row.id"><td>{{ row.customer_name }}</td><td>{{ row.channel }}</td><td>{{ row.destination }}</td><td><span class="badge">{{ row.status }}</span></td><td>{{ row.attempts }}</td><td>{{ row.available_at }}</td></tr></tbody>
            </table>
            <div v-else class="empty">La cola está vacía. Prepare una campaña de WhatsApp para probarla.</div>
          </div>
        </div>
        <div v-else-if="!['segments','tasks','loyalty','messaging/bot-rules','customers'].includes(active)" class="table-wrap">
          <table v-if="Array.isArray(tableRows) && tableRows.length">
            <thead><tr><th v-for="(_, key) in tableRows[0]" :key="key" v-show="key !== 'content_json' && !(active === 'tasks' && key === 'customer_id')">{{ key.replaceAll('_', ' ') }}</th><th v-if="['tasks','customers','campaigns'].includes(active)">Acciones</th></tr></thead>
            <tbody><tr v-for="(row, index) in tableRows" :key="row.id || index"><td v-for="(value, key) in row" :key="key" v-show="key !== 'content_json' && !(active === 'tasks' && key === 'customer_id')" :class="{ overdue: active === 'tasks' && key === 'due_at' && value && new Date(value) < new Date() && !['done','cancelled'].includes(row.status) }">{{ active === 'tasks' && key === 'status' ? taskStatusLabel(value) : (value ?? '—') }}</td><td v-if="active === 'tasks'" class="actions"><button v-if="row.status === 'pending'" class="mini start" @click="setTaskStatus(row.id, 'in_progress')">Iniciar</button><button v-if="!['done','cancelled'].includes(row.status)" class="mini" @click="setTaskStatus(row.id, 'done')">Completar</button><button class="mini edit" @click="editTask(row)">Editar</button><button class="mini danger" @click="deleteTask(row)">Eliminar</button></td><td v-if="active === 'customers'"><button class="mini" @click="openCustomer(row.id)">Ver 360</button></td><td v-if="active === 'campaigns'" class="actions"><button class="mini whatsapp" @click="prepareCampaign(row)">Envío manual</button><button v-if="row.channel === 'whatsapp'" class="mini" @click="queueCampaign(row)">Preparar cola</button><button class="mini danger" @click="deleteCampaign(row)">Eliminar</button></td></tr></tbody>
          </table>
          <div v-else class="empty">No hay registros para mostrar.</div>
        </div>
      </section>
      <div v-if="customer360" class="modal-backdrop" @click.self="customer360 = null">
        <section class="customer-modal">
          <button class="close" @click="customer360 = null">×</button>
          <small>CLIENTE 360</small><h2>{{ customer360.customer.display_name }}</h2>
          <div class="customer-kpis">
            <article><small>VISITAS</small><strong>{{ customer360.customer.purchase_count }}</strong></article>
            <article><small>GASTO TOTAL</small><strong>S/ {{ customer360.customer.total_spent }}</strong></article>
            <article><small>TICKET PROMEDIO</small><strong>S/ {{ customer360.customer.average_ticket }}</strong></article>
            <article><small>RFM</small><strong>{{ customer360.customer.rfm_score }}</strong></article>
          </div>
          <div class="recommendation"><b>Acción recomendada:</b> {{ customer360.customer.recommended_action }}</div>
          <div class="consent-panel">
            <div><b>Consentimiento WhatsApp</b><small>Registre únicamente una autorización verificable del cliente.</small></div>
            <select v-model="consentForm.status"><option value="unknown">Sin confirmar</option><option value="granted">Autorizado</option><option value="denied">No autorizado</option></select>
            <input v-model="consentForm.source" maxlength="60" placeholder="Origen: formulario, mostrador…" />
            <button class="mini" @click="saveConsent">Guardar consentimiento</button>
          </div>
          <div class="detail-grid">
            <div><h3>Últimas compras</h3><table v-if="customer360.purchases.length"><thead><tr><th>Fecha</th><th>Documento</th><th>Total</th></tr></thead><tbody><tr v-for="p in customer360.purchases" :key="p.id"><td>{{ p.purchase_at }}</td><td>{{ p.document }}</td><td>S/ {{ p.total }}</td></tr></tbody></table><p v-else>Sin compras registradas.</p></div>
            <div><h3>Productos favoritos</h3><table v-if="customer360.favorites.length"><thead><tr><th>Producto</th><th>Cantidad</th><th>Importe</th></tr></thead><tbody><tr v-for="p in customer360.favorites" :key="p.id"><td>{{ p.product }}</td><td>{{ p.quantity }}</td><td>S/ {{ p.amount }}</td></tr></tbody></table><p v-else>Sin productos registrados.</p></div>
          </div>
        </section>
      </div>
      <div v-if="editingCustomer" class="modal-backdrop" @click.self="editingCustomer = null">
        <section class="customer-edit-modal">
          <button class="close" @click="editingCustomer = null">×</button>
          <small>ACTUALIZAR INFORMACIÓN</small><h2>Editar cliente</h2>
          <p>Corrija los datos de contacto utilizados por el CRM y las campañas.</p>
          <form @submit.prevent="saveCustomer">
            <label>Nombre completo<input v-model="editingCustomer.display_name" required maxlength="180" /></label>
            <label>Documento de identidad<input v-model="editingCustomer.document_number" maxlength="30" placeholder="DNI o RUC" /></label>
            <label>Teléfono / WhatsApp<input v-model="editingCustomer.phone" maxlength="30" placeholder="Ej. 999888777" /></label>
            <label>Correo electrónico<input v-model="editingCustomer.email" type="email" maxlength="180" placeholder="cliente@correo.com" /></label>
            <div><button type="button" class="cancel" @click="editingCustomer = null">Cancelar</button><button :disabled="savingCustomer">{{ savingCustomer ? 'Guardando…' : 'Guardar cambios' }}</button></div>
          </form>
          <small class="customer-sync-note">Nota: una sincronización posterior puede actualizar nuevamente los datos provenientes del sistema de ventas.</small>
        </section>
      </div>
      <div v-if="campaignSend" class="modal-backdrop" @click.self="closeCampaign">
        <section class="customer-modal campaign-modal">
          <button class="close" @click="closeCampaign">×</button>
          <small>CAMPAÑA POR WHATSAPP</small><h2>{{ campaignSend.campaign.name }}</h2>
          <section class="campaign-audience-builder">
            <header><div><small>1 · DEFINIR AUDIENCIA</small><h3>¿A quién desea enviar?</h3><p>Use un segmento guardado o genere una lista según el comportamiento de compra.</p></div></header>
            <div class="audience-source">
              <label :class="{ active: campaignSend.audienceSource === 'purchases' }"><input v-model="campaignSend.audienceSource" type="radio" value="purchases" /> Según sus compras</label>
              <label :class="{ active: campaignSend.audienceSource === 'segment' }"><input v-model="campaignSend.audienceSource" type="radio" value="segment" /> Lista segmentada</label>
            </div>
            <div v-if="campaignSend.audienceSource === 'purchases'" class="audience-filters">
              <label>Compras mínimas<input v-model.number="campaignSend.filters.minPurchases" type="number" min="0" /></label>
              <label>Compras máximas<input v-model="campaignSend.filters.maxPurchases" type="number" min="0" placeholder="Sin límite" /></label>
              <label>Días desde última compra<input v-model.number="campaignSend.filters.maxRecency" type="number" min="0" /></label>
              <label>Gasto mínimo S/<input v-model.number="campaignSend.filters.minSpent" type="number" min="0" step="0.01" /></label>
            </div>
            <label v-else class="segment-pull">Segmento guardado<select v-model="campaignSend.segmentId"><option value="">Seleccione una lista</option><option v-for="segment in campaignSend.segments" :key="segment.id" :value="segment.id">{{ segment.name }} ({{ segment.member_count }} clientes)</option></select></label>
            <button class="build-audience" type="button" :disabled="campaignAudienceLoading || (campaignSend.audienceSource === 'segment' && !campaignSend.segmentId)" @click="buildCampaignAudience">{{ campaignAudienceLoading ? 'Generando lista…' : 'Generar lista de clientes' }}</button>
            <div v-if="campaignSend.audience" class="audience-result"><span><b>{{ campaignSend.audience.total }}</b> encontrados</span><span><b>{{ campaignSend.audience.with_phone }}</b> con teléfono</span><span><b>{{ campaignSend.audience.authorized }}</b> autorizados</span></div>
          </section>
          <label class="message-label">Mensaje
            <textarea v-model="campaignSend.message" maxlength="900" placeholder="Escriba la promoción"></textarea>
          </label>
          <div class="media-picker">
            <input ref="campaignMediaInput" class="file-hidden" type="file" @change="uploadCampaignMedia" />
            <button class="media-icon image" :disabled="uploadingMedia" @click="openCampaignMedia('image')">
              <span>📷</span><b>Subir imagen</b>
            </button>
            <button class="media-icon video" :disabled="uploadingMedia" @click="openCampaignMedia('video')">
              <span>🎬</span><b>Subir video</b>
            </button>
            <div v-if="uploadingMedia" class="media-loading">Cargando archivo…</div>
            <div v-if="campaignMedia" class="media-preview">
              <img v-if="campaignMedia.type === 'image' && campaignMedia.preview" :src="campaignMedia.preview" alt="Vista previa de campaña" />
              <video v-else-if="campaignMedia.preview" :src="campaignMedia.preview" controls></video>
              <span v-else class="saved-media-icon">{{ campaignMedia.type === 'video' ? '🎬' : '🖼️' }}</span>
              <div><b>{{ campaignMedia.name }}</b><small>{{ (campaignMedia.size / 1048576).toFixed(2) }} MB</small></div>
              <button title="Quitar archivo" @click="clearCampaignMedia">×</button>
            </div>
          </div>
          <label class="consent-check">
            <input v-model="consentConfirmed" type="checkbox" />
            Confirmo que estos clientes autorizaron recibir promociones.
          </label>
          <div class="batch-actions">
            <button class="send-all" :disabled="!consentConfirmed || batchSending || !campaignSend.customers.some(customer => customer.selected && customer.phone)" @click="sendAllWhatsApp">
              {{ batchSending ? `Enviando ${batchProgress.completed}/${batchProgress.total}…` : `Enviar seleccionados (${campaignSend.customers.filter(customer => customer.selected && customer.phone).length})` }}
            </button>
            <small>Se envían uno por uno con una pausa de seguridad.</small>
          </div>
          <p v-if="!campaignSend.customers.length" class="notice">No existen clientes con teléfono válido.</p>
          <table v-else>
            <thead><tr><th><input type="checkbox" checked title="Seleccionar todos" @change="toggleCampaignCustomers($event.target.checked)" /></th><th>Cliente</th><th>Compras</th><th>Gasto</th><th>Teléfono</th><th>Autorización</th><th>Vista previa</th><th></th></tr></thead>
            <tbody><tr v-for="customer in campaignSend.customers" :key="customer.id">
              <td><input v-model="customer.selected" type="checkbox" :disabled="!customer.phone" /></td>
              <td>{{ customer.display_name }}</td><td>{{ customer.purchase_count ?? '—' }}</td><td>S/ {{ customer.total_spent ?? '0.00' }}</td><td>{{ customer.phone || 'Sin teléfono' }}</td>
              <td><span class="consent-badge" :class="customer.consent_status">{{ customer.consent_status === 'granted' ? 'Autorizado' : customer.consent_status === 'denied' ? 'No autorizado' : 'Sin confirmar' }}</span></td>
              <td class="message-preview">{{ personalizedMessage(customer) }}</td>
              <td><button class="mini whatsapp" :disabled="!customer.selected || !customer.phone || !consentConfirmed || customer.sending || customer.sendStatus === 'Enviado'" @click="sendWhatsApp(customer)">{{ customer.sending ? 'Enviando…' : customer.sendStatus === 'Enviado' ? 'Enviado ✓' : 'Enviar ahora' }}</button><small v-if="customer.sendStatus && customer.sendStatus !== 'Enviado'" class="send-error">{{ customer.sendStatus }}</small></td>
            </tr></tbody>
          </table>
          <p class="privacy-note">Cada botón envía únicamente a ese cliente mediante la sesión vinculada y registra la operación.</p>
        </section>
      </div>
    </main>
  </div>
</template>
