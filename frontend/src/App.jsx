import { useEffect, useState } from 'react'
import { api } from './api'
import Login from './components/Login'
import StudentPage from './components/StudentPage'

export default function App() {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    api('/me').then(({ user }) => setUser(user)).catch(() => setUser(null)).finally(() => setLoading(false))
  }, [])

  if (loading) return <div className="splash">Đang tải hệ thống…</div>
  if (!user) return <Login onLogin={setUser} />

  async function logout() {
    await api('/logout', { method: 'POST' })
    setUser(null)
  }

  return (
    <div className="app-shell">
      <aside>
        <div className="brand"><span>H</span><div>QUẢN LÝ<br />HỌC SINH THPT</div></div>
        <p className="nav-label">NGHIỆP VỤ</p>
        <button className="nav-active">♙ Quản lý học sinh</button>
      </aside>
      <main>
        <header><div><strong>Hệ thống quản lý nhà trường</strong><small>Frontend React · Backend Laravel API</small></div><div className="user"><span>{user.name}</span><button onClick={logout}>Đăng xuất</button></div></header>
        <StudentPage permissions={user.permissions} />
      </main>
    </div>
  )
}
