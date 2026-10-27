{{-- Kiểm tra dữ liệu dùng chung cho mọi form:
     1) Gắn lỗi máy chủ trả về vào đúng ô nhập (kể cả form chưa tự hiển thị @error)
     2) Kiểm tra ngay khi gõ các ô số điện thoại, CCCD, họ tên, năm học (cùng luật với máy chủ) --}}
<script>
(function () {
    const loiMayChu = @json($errors->getMessages());

    const tenTruong = (khoa) => khoa.split('.').map((p, i) => i === 0 ? p : `[${p}]`).join('');
    const timO = (khoa) => document.querySelector(`[name="${CSS.escape(tenTruong(khoa))}"]`)
        || document.querySelector(`[name="${CSS.escape(tenTruong(khoa))}[]"]`);

    const conLai = [];
    Object.entries(loiMayChu).forEach(([khoa, dsLoi]) => {
        const o = timO(khoa);
        if (!o || o.type === 'hidden') { conLai.push(...dsLoi); return; }
        o.classList.add('is-invalid');
        const cha = o.closest('.input-group') || o;
        const daCo = cha.parentElement.querySelector('.invalid-feedback, .text-danger.small, small.text-danger');
        if (!daCo) {
            const div = document.createElement('div');
            div.className = 'invalid-feedback d-block';
            div.textContent = dsLoi[0];
            cha.insertAdjacentElement('afterend', div);
        }
        o.addEventListener('input', () => o.classList.remove('is-invalid'), { once: true });
    });
    // Lỗi không gắn được vào ô nào (VD: lỗi theo từng học sinh trong bảng) và trang chưa có khung báo lỗi
    if (conLai.length && !document.querySelector('.alert-danger:not(.alert-flash)')) {
        const vung = document.querySelector('.content-area, .container');
        if (vung) {
            const box = document.createElement('div');
            box.className = 'alert alert-danger';
            conLai.forEach(t => { const d = document.createElement('div'); d.textContent = t; box.appendChild(d); });
            vung.prepend(box);
        }
    }

    const so = (v) => v.replace(/[\s.\-()]/g, '').replace(/^(\+84|84)(?=[1-9]\d{8,9}$)/, '0');
    const luat = [
        { khop: n => /(^|\[)(so_dien_thoai\w*|phone)\]?$/.test(n),
          dung: v => /^(0[35789]\d{8}|02\d{9})$/.test(so(v)),
          loi: 'Số điện thoại không hợp lệ (VD: 0912345678).', mode: 'tel' },
        { khop: n => /(^|\[)cccd\]?$/.test(n),
          dung: v => { const s = v.replace(/[\s.\-]/g, ''); return /^\d{9}$/.test(s) || (/^\d{12}$/.test(s) && +s.slice(0, 3) >= 1 && +s.slice(0, 3) <= 96); },
          loi: 'CCCD gồm 12 chữ số (hoặc CMND cũ 9 chữ số).', mode: 'numeric' },
        { khop: n => /^(ho_ten|ho_ten_cha|ho_ten_me)$/.test(n) || n === 'name',
          dung: v => /^[\p{L}\p{M}][\p{L}\p{M}\s'.-]*$/u.test(v.trim()) && v.trim().split(/\s+/).length >= 2,
          loi: 'Nhập đầy đủ họ và tên, chỉ gồm chữ cái.' },
        { khop: n => n === 'nam_hoc',
          dung: v => { const m = v.match(/^(\d{4})-(\d{4})$/); return !!m && +m[2] === +m[1] + 1; },
          loi: 'Năm học có dạng YYYY-YYYY, hai năm liên tiếp (VD: 2025-2026).' },
    ];

    document.querySelectorAll('input[type=text], input[type=tel], input:not([type])').forEach(o => {
        const l = luat.find(x => x.khop(o.name));
        if (!l) return;
        if (l.mode) o.setAttribute('inputmode', l.mode);
        const kiemTra = () => o.setCustomValidity(o.value.trim() === '' || l.dung(o.value) ? '' : l.loi);
        o.addEventListener('input', kiemTra);
        o.addEventListener('blur', () => { kiemTra(); if (o.value) o.reportValidity(); });
        kiemTra();
    });
})();
</script>
