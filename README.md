# Hệ thống Quản lý Học sinh THPT

Ứng dụng Laravel 12 (Blade + Bootstrap 5) quản lý học sinh trường THPT, phân quyền bằng `spatie/laravel-permission`.

## Cài đặt nhanh

```bash
composer install
cp .env.example .env        # mặc định dùng SQLite; đổi DB_* nếu dùng MySQL
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan db:seed --class=DemoDataSeeder   # (tuỳ chọn) dữ liệu mẫu để chạy thử
npm install && npm run build
php artisan serve
```

Tài khoản mặc định:

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Quản trị viên | admin@school.com | Admin@123456 |
| Giáo viên chủ nhiệm | hoa.gvcn@school.com | Gvcn@123456 |
| Giáo viên bộ môn (dữ liệu mẫu) | nam.gvbm@school.com | Gvbm@123456 |

Chạy test: `php artisan test`

## Chức năng

- **Học sinh**: hồ sơ, ảnh thẻ, nhập danh sách từ Excel/CSV, xem điểm, hạnh kiểm, khen thưởng - kỷ luật.
- **Lớp học, Môn học, Thời khóa biểu** (chống trùng lịch lớp / giáo viên).
- **Phân công giảng dạy**: gán giáo viên – môn – lớp theo học kỳ, xem tải giảng dạy (tiết/tuần).
  Giáo viên chỉ được nhập điểm môn/lớp mình được phân công.
- **Điểm số**: nhập điểm TX, GK, CK; tự tính ĐTB môn.
- **Điểm danh hằng ngày**: GVCN điểm danh lớp mình (có mặt / vắng có phép / vắng không phép / đi trễ),
  tổng hợp chuyên cần theo học kỳ; form hạnh kiểm có nút lấy số liệu từ điểm danh.
- **Hạnh kiểm & chuyên cần**: tự tính hạnh kiểm cả năm từ HK1 + HK2.
- **Tổng kết & Lên lớp** (Thông tư 22/2021/TT-BGDĐT): xếp loại học tập (Tốt/Khá/Đạt/Chưa đạt),
  rèn luyện, danh hiệu Học sinh Xuất sắc / Giỏi, xét lên lớp (nghỉ quá 45 buổi → ở lại),
  chuyển học sinh sang lớp năm học mới, công nhận tốt nghiệp khối 12.
- **Khen thưởng - Kỷ luật** theo năm học.
- **Báo cáo**: bảng điểm lớp, xuất PDF / Excel.
- **Tài khoản**: chỉ quản trị viên quản lý; tài khoản tự đăng ký phải chờ kích hoạt.

## Phân quyền

| Chức năng | Quản trị viên | GV chủ nhiệm | GV bộ môn |
|---|---|---|---|
| Tài khoản, lớp, môn, TKB, phân công (thêm/sửa/xóa) | ✔ | | |
| Nhập điểm | ✔ | theo phân công | theo phân công |
| Điểm danh, hạnh kiểm, khen thưởng | ✔ | lớp chủ nhiệm | |
| Tổng kết | ✔ | lớp chủ nhiệm | |
| Xét lên lớp | ✔ | | |

Danh sách quyền và vai trò nằm ở `app/Support/PhanQuyen.php`; migration `dong_bo_phan_quyen` tự bổ sung quyền mới cho CSDL đang chạy.
