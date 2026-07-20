import { useState } from 'react'
import { api } from '../api'

export default function Login({ onLogin }) {
  const [form, setForm] = useState({ email: '', password: '', remember: false })
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)

  async function submit(event) {
    event.preventDefault(); setBusy(true); setError('')
    try {
      const { user } = await api('/login', { method: 'POST', body: JSON.stringify(form) })
      onLogin(user)
    } catch (e) { setError(Object.values(e.errors || {})[0]?.[0] || e.message) }
    finally { setBusy(false) }
  }

  return <div className="login-page"><form className="login-card" onSubmit={submit}>
    <div className="school-mark">H</div><h1>Quản lý học sinh THPT</h1><p>Đăng nhập để tiếp tục</p>
    {error && <div className="error-box">{error}</div>}
    <label>Email<input type="email" value={form.email} onChange={e => setForm({ ...form, email: e.target.value })} required /></label>
    <label>Mật khẩu<input type="password" value={form.password} onChange={e => setForm({ ...form, password: e.target.value })} required /></label>
    <label className="check"><input type="checkbox" checked={form.remember} onChange={e => setForm({ ...form, remember: e.target.checked })} /> Ghi nhớ đăng nhập</label>
    <button className="primary" disabled={busy}>{busy ? 'Đang đăng nhập…' : 'Đăng nhập'}</button>
  </form></div>
}
