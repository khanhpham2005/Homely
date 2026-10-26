# Homely

Website bán đồ gia dụng Homely, xây dựng bằng PHP thuần với các chức năng sản phẩm, giỏ hàng, đặt hàng, tài khoản thành viên và quản trị viên.

## Chức năng theo đối tượng

### Khách chưa đăng nhập

- Xem trang chủ, giới thiệu, sản phẩm và chi tiết sản phẩm.
- Tìm kiếm sản phẩm.
- Xem câu hỏi thường gặp và thông tin liên hệ.
- Đăng ký tài khoản và đăng nhập.
- Khi thêm sản phẩm vào giỏ hàng hoặc mở giỏ hàng, hệ thống chuyển tới trang đăng nhập.

### Thành viên đã đăng nhập

- Sửa hồ sơ cá nhân và thông tin liên hệ.
- Đổi mật khẩu và đổi avatar.
- Xem, thêm, cập nhật số lượng và xóa sản phẩm trong giỏ hàng riêng.
- Đặt hàng và thanh toán.
- Xem danh sách đơn hàng, xem chi tiết đơn và hủy đơn đang chờ xử lý.
- Thêm hoặc xóa sản phẩm khỏi danh sách yêu thích.
- Bình luận và đánh giá sản phẩm bằng số sao; đánh giá chờ quản trị viên duyệt.
- Gửi liên hệ và xem lịch sử liên hệ, phản hồi từ Homely.
- Xem câu hỏi và câu trả lời trong mục Hỏi đáp.
- Đăng xuất tài khoản.

### Quản trị viên

- Xem tổng quan hoạt động quản trị.
- Quản lý người dùng: thêm, sửa, xóa, khóa/mở khóa và reset mật khẩu.
- Quản lý sản phẩm: thêm, sửa, xóa, cập nhật giá, mô tả và ảnh sản phẩm.
- Quản lý đơn hàng: xem chi tiết và cập nhật trạng thái đơn.
- Quản lý bình luận/đánh giá: xem, duyệt/ẩn và xóa.
- Quản lý liên hệ: xem nội dung, đánh dấu đã đọc, phản hồi và đánh dấu đã phản hồi.
- Quản lý hỏi đáp: thêm, sửa, xóa câu hỏi và câu trả lời.
- Quản lý nội dung các trang cố định và mô tả SEO.
- Quản lý thông tin công ty như số điện thoại, email, địa chỉ và giờ làm việc.
- Cập nhật hồ sơ và đăng xuất tài khoản quản trị.

## Công nghệ sử dụng

- PHP 
- MySQL/MariaDB của XAMPP
- HTML5, CSS3 và JavaScript thuần
- PDO để kết nối MySQL
- Apache và URL Rewrite qua `.htaccess`
- Không sử dụng framework/CMS/CMF PHP, Composer, npm hoặc thư viện build frontend

## Cấu trúc chính

```text
ltw/
├── index.php                       # Router và xử lý giao diện/chức năng
├── app/
│   ├── db.php                      # Kết nối MySQL bằng PDO
│   └── helpers.php                 # Helper dùng chung
├── config/
│   └── config.php                  # Cấu hình ứng dụng và database
├── assets/
│   ├── css/style.css               # CSS giao diện
│   ├── js/app.js                   # JavaScript thuần
│   └── images/                     # Ảnh local và placeholder
├── database.sql                    # Schema và dữ liệu mẫu
├── database_migration_utf8mb4.sql  # Migration cho database cũ
├── sitemap.xml
├── robots.txt
└── .htaccess
```

## Chạy trên XAMPP

1. Chép thư mục dự án vào:

   ```text
   C:\xampp\htdocs\ltw
   ```

   Nếu XAMPP được cài ở ổ khác, dùng đúng thư mục `htdocs` tương ứng.

2. Mở **XAMPP Control Panel**, Start:

   - Apache
   - MySQL

3. Mở `http://localhost/phpmyadmin`.

4. Import file `database.sql`. File này sẽ tự tạo database `homely` và các bảng cần thiết.

5. Kiểm tra cấu hình trong `config/config.php`:

   ```php
   DB_HOST = 127.0.0.1
   DB_NAME = homely
   DB_USER = root
   DB_PASS = ''
   ```

   Cấu hình mặc định phù hợp với MySQL XAMPP chưa đặt mật khẩu cho tài khoản `root`.

6. Truy cập website:

   ```text
   http://localhost/ltw/
   ```

## Tài khoản quản trị mẫu

- Email: `admin@gmail.com`
- Mật khẩu: `123456`

## Lưu ý

- Ảnh sản phẩm và avatar được lưu local trong `assets/images`.
- Nếu gặp lỗi kết nối database, hãy kiểm tra MySQL đã được Start và thông tin trong `config/config.php`.
