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
| Kế toán (dữ liệu mẫu) | ketoan@school.com | Ketoan@123456 |

Tên trường in trên phiếu thu / phiếu điểm: biến `TEN_TRUONG` trong `.env`.

Chạy test: `php artisan test`

### Gửi email / SMS cho phụ huynh

Tin nhắn được gửi qua hàng đợi, cần chạy worker: `php artisan queue:work` (lệnh `composer dev` đã chạy sẵn).

- **Email**: cấu hình SMTP trong `.env` (`MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`).
  Mặc định `MAIL_MAILER=log` chỉ ghi email vào `storage/logs/laravel.log`.
- **SMS**: `SMS_DRIVER=log` (mặc định, chỉ ghi log) hoặc `SMS_DRIVER=webhook` để gửi
  `POST {"to": "<số điện thoại>", "message": "<nội dung>"}` kèm `Authorization: Bearer <SMS_WEBHOOK_TOKEN>`
  tới `SMS_WEBHOOK_URL` — nối tới nhà cung cấp SMS brandname / Zalo ZNS mà trường đăng ký.
- Email phụ huynh nhập ở hồ sơ học sinh hoặc cột `email_phu_huynh` khi nhập Excel; SMS gửi tới số của mẹ, nếu trống thì của cha.

## Chức năng

- **Học sinh**: hồ sơ, ảnh thẻ, xem điểm, hạnh kiểm, khen thưởng - kỷ luật, in phiếu điểm cá nhân (PDF).
- **Nhập học sinh từ Excel** (.xlsx, .xls, .csv): nhận tiêu đề tiếng Việt ("Họ và tên", "Ngày sinh", "Lớp"...),
  ô ngày của Excel, giữ số 0 đầu SĐT/CCCD, bỏ qua học sinh đã có, báo lỗi theo từng dòng; file mẫu .xlsx.
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
- **Học phí & khoản thu**: danh mục khoản thu (học phí, BHYT, quỹ...) theo năm học/khối, miễn giảm theo đối tượng
  chính sách, công nợ theo lớp (xuất Excel), lập phiếu thu và in (số tiền bằng chữ), hủy phiếu có lý do, sổ thu.
- **Thông báo nội bộ**: đăng theo vai trò nhận, ghim, hạn hiển thị; hiện trên Dashboard.
- **Tin nhắn phụ huynh** (`/tin-nhan`): soạn tin theo toàn trường / khối / lớp / từng học sinh, chèn `{ho_ten}`, `{lop}`...;
  gửi qua trang tra cứu (luôn có), email và SMS; theo dõi trạng thái từng người nhận, đã xem, gửi lại tin lỗi.
  Tự động: báo vắng không phép khi điểm danh, nhắc nộp học phí (kèm số tiền từng khoản), gửi kết quả học kỳ / cả năm,
  báo khen thưởng - kỷ luật.
- **Tra cứu cho phụ huynh** (`/tra-cuu`, không cần đăng nhập): mã học sinh + ngày sinh → điểm, kết quả học tập/rèn luyện,
  vắng 30 ngày gần đây, học phí còn nợ. Không hiển thị CCCD/địa chỉ/SĐT; giới hạn 10 lượt/phút.
- **Báo cáo**: bảng điểm lớp, xuất PDF / Excel.
- **Tài khoản**: chỉ quản trị viên quản lý; tài khoản tự đăng ký phải chờ kích hoạt.

## Phân quyền

| Chức năng | Quản trị viên | GV chủ nhiệm | GV bộ môn | Kế toán |
|---|---|---|---|---|
| Tài khoản, lớp, môn, TKB, phân công, thông báo (thêm/sửa/xóa) | ✔ | | | |
| Nhập điểm | ✔ | theo phân công | theo phân công | |
| Điểm danh, hạnh kiểm, khen thưởng | ✔ | lớp chủ nhiệm | | |
| Tổng kết | ✔ | lớp chủ nhiệm | | |
| Xét lên lớp | ✔ | | | |
| Xem công nợ học phí | ✔ | lớp chủ nhiệm | | ✔ |
| Thu tiền, khoản thu, miễn giảm, sổ thu | ✔ | | | ✔ |
| Hủy phiếu thu | ✔ | | | |
| Gửi tin nhắn phụ huynh | ✔ toàn trường | lớp chủ nhiệm | | ✔ toàn trường |

Danh sách quyền và vai trò nằm ở `app/Support/PhanQuyen.php`; migration `dong_bo_phan_quyen` tự bổ sung quyền mới cho CSDL đang chạy.
