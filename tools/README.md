# Thiết lập trang cho máy từng thành viên

## Vì sao cần bước này?

- **Code** (theme: PHP, CSS, template…) đi theo **git** → `git pull` là có.
- **Trang** (page như About, Contact, News…) lại nằm trong **database local** của mỗi máy → **git KHÔNG kéo được**.

Vì thế, sau khi `git pull` về máy mới, các trang có thể chưa tồn tại. Khi đó menu trên navbar
(ví dụ **ABOUT**) sẽ **không có link** → bấm vào không được. Đây là lỗi hay gặp nhất khi làm nhóm.

## Cách chạy (1 lệnh duy nhất)

Mở terminal tại thư mục gốc dự án (`CMS_NhomG/`), rồi chạy:

```powershell
php tools/setup-pages.php
```

Script sẽ tự động:
- Tạo các trang còn thiếu: **About** (template *About Us*), **Contact** (template *Contact Us*), **News**, **Jobs**.
- Gán template đúng cho từng trang.
- Gán liên kết menu (Customizer `cmsng_page_*`) để navbar trỏ thẳng.
- In ra danh sách link menu để bạn kiểm tra ngay.

### Các tuỳ chọn khác

```powershell
php tools/setup-pages.php --list     # chỉ xem sẽ tạo gì, KHÔNG thay đổi
php tools/setup-pages.php --force    # gán lại template + menu cho tất cả trang
```

> An toàn: script **idempotent** — chạy lại nhiều lần không tạo trùng, không xoá nội dung bạn đã nhập.
> Trang đã tồn tại sẽ được bỏ qua (trừ khi dùng `--force`).

## Sau khi chạy

Mở website, kiểm tra menu: HOME · JOBS · NEWS · ABOUT · CONTACT — cả 5 đều bấm được.

## Máy mới: tạo `wp-config.php` trước (quan trọng)

`wp-config.php` bị `.gitignore` nên **không có trong repo**. Nếu thiếu file này, WordPress sẽ chuyển tới
`/wp-admin/setup-config.php` — **đừng điền vào đó**, vì nó sẽ tạo một site mới và ghi đè cấu hình.

```powershell
Copy-Item tools\wp-config.local.php.example wp-config.php
```

Sau đó import database dùng chung (thư mục `database/`) qua phpMyAdmin, rồi mở `http://cms-nhomg.local/`.

## Lưu ý cho nhóm

- File `wp-config.php` bị `.gitignore` (mỗi máy 1 DB riêng) → **luôn phải chạy script này** trên máy mới.
- Muốn dữ liệu (nội dung trang) giống nhau 100% giữa các máy thì cần chia sẻ DB
  (WP Migrate DB / All-in-One WP Migration) — script này chỉ tạo **khung trang**, không copy nội dung.
