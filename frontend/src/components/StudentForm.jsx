import { useEffect, useState } from 'react'
import { api } from '../api'

const empty = { ho_ten: '', ngay_sinh: '', gioi_tinh: 'Nam', lop_hoc_id: '', cccd: '', so_dien_thoai: '', email: '', dan_toc: 'Kinh', dia_chi_thuong_tru: '', trang_thai: 'Đang học' }

export default function StudentForm({ student, classes, onClose, onSaved }) {
  const [form, setForm] = useState(empty)
  const [errors, setErrors] = useState({})
  const [busy, setBusy] = useState(false)
  useEffect(() => setForm(student ? { ...empty, ...student, lop_hoc_id: student.lop_hoc_id || '' } : empty), [student])
  const change = e => setForm({ ...form, [e.target.name]: e.target.value })

  async function submit(e) {
    e.preventDefault(); setBusy(true); setErrors({})
    const payload = { ...form, lop_hoc_id: form.lop_hoc_id || null }
    try {
      await api(student ? `/hoc-sinh/${student.id}` : '/hoc-sinh', { method: student ? 'PUT' : 'POST', body: JSON.stringify(payload) })
      onSaved()
    } catch (error) { setErrors(error.errors || { general: [error.message] }) }
    finally { setBusy(false) }
  }

  return <div className="modal-backdrop" onMouseDown={e => e.target === e.currentTarget && onClose()}><form className="modal" onSubmit={submit}>
    <div className="modal-title"><div><h2>{student ? 'Cập nhật học sinh' : 'Thêm học sinh'}</h2><p>Thông tin có dấu * là bắt buộc</p></div><button type="button" className="icon-button" onClick={onClose}>×</button></div>
    {errors.general && <div className="error-box">{errors.general[0]}</div>}
    <div className="form-grid">
      <Field label="Họ và tên *" name="ho_ten" value={form.ho_ten} onChange={change} error={errors.ho_ten} />
      <Field label="Ngày sinh *" name="ngay_sinh" type="date" value={form.ngay_sinh?.slice(0, 10)} onChange={change} error={errors.ngay_sinh} />
      <Select label="Giới tính *" name="gioi_tinh" value={form.gioi_tinh} onChange={change} options={['Nam', 'Nữ', 'Khác']} error={errors.gioi_tinh} />
      <label>Lớp học<select name="lop_hoc_id" value={form.lop_hoc_id} onChange={change}><option value="">Chưa xếp lớp</option>{classes.map(c => <option key={c.id} value={c.id}>{c.ten_lop} · {c.nam_hoc}</option>)}</select>{errors.lop_hoc_id && <small className="field-error">{errors.lop_hoc_id[0]}</small>}</label>
      <Field label="CCCD" name="cccd" value={form.cccd || ''} onChange={change} error={errors.cccd} />
      <Field label="Số điện thoại" name="so_dien_thoai" value={form.so_dien_thoai || ''} onChange={change} error={errors.so_dien_thoai} />
      <Field label="Email" name="email" type="email" value={form.email || ''} onChange={change} error={errors.email} />
      <Field label="Dân tộc" name="dan_toc" value={form.dan_toc || ''} onChange={change} error={errors.dan_toc} />
      <Select label="Trạng thái *" name="trang_thai" value={form.trang_thai} onChange={change} options={['Đang học', 'Đã tốt nghiệp', 'Thôi học', 'Chuyển trường']} error={errors.trang_thai} />
      <label className="wide">Địa chỉ<input name="dia_chi_thuong_tru" value={form.dia_chi_thuong_tru || ''} onChange={change} /></label>
    </div>
    <div className="modal-actions"><button type="button" onClick={onClose}>Hủy</button><button className="primary" disabled={busy}>{busy ? 'Đang lưu…' : 'Lưu học sinh'}</button></div>
  </form></div>
}

function Field({ label, error, ...props }) { return <label>{label}<input {...props} />{error && <small className="field-error">{error[0]}</small>}</label> }
function Select({ label, options, error, ...props }) { return <label>{label}<select {...props}>{options.map(o => <option key={o}>{o}</option>)}</select>{error && <small className="field-error">{error[0]}</small>}</label> }
