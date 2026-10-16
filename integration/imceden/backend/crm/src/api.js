const endpoint = '/api/crm/index.php'

function redirectWhenSessionExpired(response) {
  if (response.status !== 401) return
  const target = window.top || window
  target.location.replace('/index.php')
}

async function responsePayload(response) {
  const payload = await response.json().catch(() => ({}))
  redirectWhenSessionExpired(response)
  if (!response.ok) throw new Error(payload.error || `HTTP ${response.status}`)
  return payload
}

export async function crmGet(route, params = {}) {
  const query = new URLSearchParams({ route, ...params })
  const response = await fetch(`${endpoint}?${query}`, {
    credentials: 'same-origin',
    cache: 'no-store',
    headers: { Accept: 'application/json' }
  })
  const payload = await responsePayload(response)
  return payload.data ?? payload
}

export async function crmWrite(route, method, body) {
  const csrfToken = window.parent?.CRM_CONTEXT?.csrfToken || ''
  const response = await fetch(`${endpoint}?${new URLSearchParams({ route })}`, {
    method,
    credentials: 'same-origin',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
    body: JSON.stringify(body)
  })
  const payload = await responsePayload(response)
  return payload.data
}

export async function crmUpload(route, file) {
  const csrfToken = window.parent?.CRM_CONTEXT?.csrfToken || ''
  const form = new FormData()
  form.append('file', file)
  const response = await fetch(`${endpoint}?${new URLSearchParams({ route })}`, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { Accept: 'application/json', 'X-CSRF-Token': csrfToken },
    body: form
  })
  const payload = await responsePayload(response)
  return payload.data
}
