let csrfToken = null

async function ensureCsrf() {
  if (csrfToken) return csrfToken
  const response = await fetch('/api/csrf-token', { credentials: 'include' })
  csrfToken = (await response.json()).token
  return csrfToken
}

export async function api(path, options = {}) {
  const method = options.method || 'GET'
  const headers = { Accept: 'application/json', ...options.headers }
  if (method !== 'GET' && method !== 'HEAD') headers['X-CSRF-TOKEN'] = await ensureCsrf()
  if (options.body && !(options.body instanceof FormData)) headers['Content-Type'] = 'application/json'

  const response = await fetch(`/api${path}`, { ...options, method, headers, credentials: 'include' })
  if (response.status === 204) return null
  const data = await response.json().catch(() => ({}))
  if (!response.ok) {
    if (response.status === 419) csrfToken = null
    const error = new Error(data.message || 'Không thể kết nối đến máy chủ.')
    error.status = response.status
    error.errors = data.errors || {}
    throw error
  }
  return data
}
