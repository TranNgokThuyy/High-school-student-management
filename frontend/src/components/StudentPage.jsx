import { useEffect, useState } from 'react'
import { api } from '../api'
import StudentForm from './StudentForm'

export default function StudentPage({ permissions }) {
  const [students, setStudents] = useState([]), [classes, setClasses] = useState([])
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 })
  const [filters, setFilters] = useState({ search: '', lop_hoc_id: '', trang_thai: '' })
  const [applied, setApplied] = useState(filters), [editing, setEditing] = useState(undefined)
  const [loading, setLoading] = useState(true), [notice, setNotice] = useState('')
  const can = permission => permissions.includes(permission)

  async function load(page = 1) {
    setLoading(true)
    try {
      const query = new URLSearchParams({ ...applied, page });
      [...query.entries()].forEach(([key, value]) => !value && query.delete(key))
      const result = await api(`/hoc-sinh?${query}`)
      setStudents(result.data); setMeta({ current_page: result.current_page, last_page: result.last_page, total: result.total })
    } catch (e) { setNotice(e.message) } finally { setLoading(false) }
  }
  useEffect(() => { api('/lop-hoc').then(r => setClasses(r.data)); }, [])
  useEffect(() => { load() }, [applied])

  async function remove(student) {
    if (!confirm(`Xóa học sinh “${student.ho_ten}”?`)) return
    try { await api(`/hoc-sinh/${student.id}`, { method: 'DELETE' }); setNotice('Đã xóa học sinh.'); load(meta.current_page) }
    catch (e) { setNotice(e.message) }
  }
  function saved() { setEditing(undefined); setNotice('Đã lưu thông tin học sinh.'); load(meta.current_page) }

  return <section className="content">
    <div className="page-heading"><div><h1>Quản lý học sinh</h1><p>{meta.total} học sinh trong hệ thống</p></div>{can('them-hoc-sinh') && <button className="primary" onClick={() => setEditing(null)}>＋ Thêm học sinh</button>}</div>
    {notice && <div className="notice" onClick={() => setNotice('')}>{notice}<span>×</span></div>}
    <form className="filters" onSubmit={e => { e.preventDefault(); setApplied({ ...filters }) }}>
      <input placeholder="Tìm tên, mã học sinh, CCCD…" value={filters.search} onChange={e => setFilters({ ...filters, search: e.target.value })} />
      <select value={filters.lop_hoc_id} onChange={e => setFilters({ ...filters, lop_hoc_id: e.target.value })}><option value="">Tất cả lớp</option>{classes.map(c => <option key={c.id} value={c.id}>{c.ten_lop} · {c.nam_hoc}</option>)}</select>
      <select value={filters.trang_thai} onChange={e => setFilters({ ...filters, trang_thai: e.target.value })}><option value="">Tất cả trạng thái</option>{['Đang học', 'Đã tốt nghiệp', 'Thôi học', 'Chuyển trường'].map(s => <option key={s}>{s}</option>)}</select>
      <button className="primary">Tìm kiếm</button><button type="button" onClick={() => { const reset = { search: '', lop_hoc_id: '', trang_thai: '' }; setFilters(reset); setApplied(reset) }}>Đặt lại</button>
    </form>
    <div className="table-card"><table><thead><tr><th>Mã HS</th><th>Họ và tên</th><th>Ngày sinh</th><th>Lớp</th><th>Liên hệ</th><th>Trạng thái</th><th></th></tr></thead><tbody>
      {loading ? <tr><td colSpan="7" className="empty">Đang tải dữ liệu…</td></tr> : students.length === 0 ? <tr><td colSpan="7" className="empty">Không tìm thấy học sinh</td></tr> : students.map(s => <tr key={s.id}>
        <td><code>{s.ma_hoc_sinh}</code></td><td><strong>{s.ho_ten}</strong><small>{s.gioi_tinh}</small></td><td>{new Date(s.ngay_sinh).toLocaleDateString('vi-VN')}</td><td>{s.lop_hoc ? `${s.lop_hoc.ten_lop} · ${s.lop_hoc.nam_hoc}` : '—'}</td><td>{s.so_dien_thoai || s.email || '—'}</td><td><span className={`status ${s.trang_thai === 'Đang học' ? 'active' : ''}`}>{s.trang_thai}</span></td>
        <td className="actions">{can('sua-hoc-sinh') && <button onClick={() => setEditing(s)}>Sửa</button>}{can('xoa-hoc-sinh') && <button className="danger" onClick={() => remove(s)}>Xóa</button>}</td>
      </tr>)}</tbody></table>
      <div className="pagination"><button disabled={meta.current_page <= 1} onClick={() => load(meta.current_page - 1)}>← Trước</button><span>Trang {meta.current_page}/{meta.last_page}</span><button disabled={meta.current_page >= meta.last_page} onClick={() => load(meta.current_page + 1)}>Sau →</button></div>
    </div>
    {editing !== undefined && <StudentForm student={editing} classes={classes} onClose={() => setEditing(undefined)} onSaved={saved} />}
  </section>
}
