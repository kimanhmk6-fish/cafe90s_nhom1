# Góc Cà Phê 90s — Website đặt hàng trực tuyến

Bài tập lớn môn **Thực hành thiết kế và triển khai website lập trình PHP hướng đối tượng**.
PHP OOP thuần theo mô hình **MVC tự xây** · PDO + Prepared Statement · MySQL/MariaDB · HTML/CSS/JavaScript (fetch/AJAX).

## 1. Cài đặt (XAMPP)

1. Copy thư mục dự án vào `C:\xampp\htdocs\cafe90snhom1` (hoặc `git clone` repo của nhóm vào đó).
2. Bật **Apache** và **MySQL** trong XAMPP. Cần PHP ≥ 8.1, bật `pdo_mysql`, `fileinfo`, `mbstring` (mặc định XAMPP đã bật).
3. Mở phpMyAdmin → tab **Import** → chọn `database/cafe90snhom1_tieng_viet.sql` (tạo DB `cafe90snhom1` + 12 bảng + dữ liệu mẫu).
   Hoặc import riêng `database/schema.sql` rồi `database/seed.sql`.
4. Copy `.env.example` thành `.env`, sửa `DB_USER` / `DB_PASS` nếu máy bạn khác mặc định (XAMPP: `root`, mật khẩu rỗng).
5. Truy cập: **http://localhost/cafe90snhom1/**

Chạy nhanh không cần Apache (chỉ cần PHP + MySQL):

```bash
php -S localhost:8000 -t public public/index.php
```

Tài khoản demo (xem `database/seed.sql`):

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Admin (vào `/admin`) | admin@goccaphe90s.com | Admin@123 |
| Khách hàng | kimanh@example.com | Khach@123 |
| Khách hàng | hang.tran@example.com | Khach@123 |

> Đổi mật khẩu Admin và thông tin ngân hàng (`BANK_ID`, `BANK_ACCOUNT` trong `.env`) trước khi dùng thật.

## Xem nhanh giao diện (không cần cài gì)

Mở thư mục `preview/` rồi **double-click `index.html`** (hoặc `_danh-sach.html` để xem đủ các trang: khách hàng, giỏ hàng, đặt hàng thành công, hồ sơ, toàn bộ trang Admin).
Đây chỉ là bản chụp HTML tĩnh để xem thiết kế; chức năng thật chạy khi cài theo mục 1.

Muốn Dashboard / Quản lý đơn / Lịch sử đơn có dữ liệu mẫu: import thêm `database/demo_orders.sql` sau khi import file SQL chính.

## Vị trí file giao diện

| Thành phần | File |
|---|---|
| HTML/PHP template khách hàng | `app/Views/layouts/client.php`, `app/Views/client/*.php`, `app/Views/partials/*.php` |
| HTML/PHP template Admin | `app/Views/layouts/admin.php`, `app/Views/admin/**/*.php` |
| CSS | `public/assets/css/app.css` (chung), `site.css` (khách), `admin.css` (quản trị) |
| JavaScript | `public/assets/js/app.js` |
| Ảnh minh họa | `public/assets/images/` |

## 2. Chức năng

**Khách hàng:** trang chủ (món nổi bật) · thực đơn có tìm kiếm / lọc danh mục / sắp xếp giá / phân trang · popup chọn size + số lượng ·
đăng ký, đăng nhập, ghi nhớ đăng nhập, quên/đặt lại mật khẩu · giỏ hàng AJAX (thêm, sửa số lượng, xóa) · đặt hàng + phí giao hàng ·
thanh toán chuyển khoản **VietQR** · theo dõi trạng thái đơn · hồ sơ cá nhân, upload avatar, đổi mật khẩu, lịch sử đơn (hủy đơn khi còn "Chờ xác nhận").

**Admin:** đăng nhập riêng + phân quyền theo `role` · Dashboard (số liệu, đơn theo trạng thái, doanh thu 7 ngày, đơn mới nhất) ·
quản lý sản phẩm (tìm kiếm, lọc, phân trang, upload ảnh, bật/tắt nổi bật & hiển thị, tự tạo giá S/M/L) · quản lý danh mục ·
quản lý đơn hàng (lọc theo ngày / trạng thái / từ khóa, chi tiết, cập nhật trạng thái, đối soát VietQR thủ công, **xuất CSV**) ·
quản lý người dùng (tìm kiếm, phân trang, khóa/mở khóa, nâng Admin).

**Ngoài phạm vi** (theo tài liệu): tin tức, yêu thích, đánh giá, liên hệ.

## 3. Nghiệp vụ chính

- **Giá size** (ERD): S = giá gốc, M = +5.000đ, L = +10.000đ. Bánh/đồ ăn không có dòng trong `product_sizes` → `size_id = NULL`, dùng `base_price`.
- **Giỏ hàng**: 1 user = 1 giỏ (`carts.user_id` UNIQUE); thêm trùng (sản phẩm + size) thì cộng dồn số lượng, tối đa 20/món.
- **Tạo đơn**: dùng *transaction* — ghi `orders`, `order_details` (snapshot tên, size, giá lúc mua), `payments` (pending) rồi xóa giỏ; lỗi thì rollback.
  Mã đơn dạng `GCP-20261025-001`, phí giao hàng cấu hình bằng `SHIPPING_FEE`.
- **Trạng thái đơn**: `pending → confirmed → preparing → shipping → completed` (hoặc `cancelled`).
  Chỉ được chuyển từ "Chờ xác nhận" sang các bước sau khi Admin đã đối soát **Đã thanh toán** (có mã giao dịch ngân hàng). Đơn đã hoàn thành/hủy thì khóa.
- **Doanh thu** Dashboard = đơn `completed` và thanh toán `paid`.
- **Sản phẩm đã có trong đơn** không bị xóa cứng mà chuyển sang ẩn (khóa ngoại RESTRICT). Danh mục chỉ xóa được khi không còn sản phẩm.
- **Người dùng**: Admin không thể khóa / hạ quyền chính mình hay Admin khác.
- **VietQR**: ảnh QR tạo qua `img.vietqr.io` (cần Internet); nội dung chuyển khoản = mã đơn. Hệ thống **không** tự xác nhận giao dịch — Admin đối soát thủ công (đúng phạm vi đề tài).
- **Quên mật khẩu**: chưa cấu hình gửi email nên liên kết đặt lại được hiển thị ngay trên trang (token băm, hiệu lực 30 phút, dùng 1 lần).

## 4. Bảo mật đã áp dụng

`password_hash` / `password_verify` · Prepared Statement 100% · CSRF token cho mọi form và AJAX · escape đầu ra bằng `e()` ·
`session_regenerate_id` khi đăng nhập · giới hạn đăng nhập sai (5 lần / 60 giây) · cookie ghi nhớ lưu token băm ·
upload kiểm tra dung lượng (2MB), MIME thật bằng `finfo`, chỉ jpg/png/webp, đổi tên ngẫu nhiên, chặn chạy PHP trong `public/uploads` (`.htaccess`) ·
middleware chặn khách vào `/admin` (403) · khách chỉ xem được đơn của chính mình.

## 5. Cấu trúc thư mục

```
goc-ca-phe-90s/
├─ app/
│  ├─ Core/          Router, Database (PDO), BaseModel, Controller, Pagination
│  ├─ Middleware/    AuthMiddleware, AdminMiddleware
│  ├─ Helpers/       functions.php (hàm dùng chung), UploadHelper
│  ├─ Models/        User, PasswordReset, Category, Product, Size, Cart, Order, Payment
│  ├─ Controllers/   Client/ (Home, Product, Auth, Profile, Cart, Order)  ·  Admin/ (Auth, Dashboard, Product, Category, Order, User)
│  └─ Views/         layouts/, partials/, client/, admin/, errors/   (không chứa SQL)
├─ config/           database.php (đọc từ .env)
├─ database/         schema.sql · seed.sql · cafe90snhom1_tieng_viet.sql (gộp)
├─ public/           index.php (điểm vào) · assets/{css,js,images} · uploads/{products,avatars}
├─ routes/web.php    khai báo URL → Controller@method
└─ tests/run.php     kiểm thử nhanh Model/nghiệp vụ:  php tests/run.php
```

Luồng xử lý: `public/index.php` → `bootstrap.php` (autoload, session) → `Router` → Middleware → Controller → Model (PDO) → View.

## 6. Ghi chú

- Giao diện dựng theo file demo thiết kế (`giao_diện_demo_design_web_.pdf`): ảnh hero, ảnh quán, ảnh món được cắt từ chính file demo, nằm trong `public/assets/images/`. Muốn đổi ảnh món: upload lại trong trang Admin → Sản phẩm.
- Phông chữ lấy từ Google Fonts (cần Internet); khi offline trình duyệt dùng phông thay thế, giao diện vẫn dùng được.
- Dữ liệu mẫu có 11 món / 3 danh mục (Cà phê · Trà & Đá xay · Bánh ngọt & Ăn nhẹ), tên món + mô tả + giá "từ" lấy theo màn hình demo; vẫn tuân theo ERD (size M +5.000đ, L +10.000đ).
- Hình decor (sóng nâu, cành lá, hoa) là SVG tự vẽ trong `public/assets/images/deco-*.svg`.
