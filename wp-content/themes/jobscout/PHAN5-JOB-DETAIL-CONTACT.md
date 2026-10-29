# Phần 5 — Job Detail + Contact (ảnh 5 và 7)

Người làm: TungNguyen27. Thiết kế gốc: `wp-content/plugins/job-design/5-job detail.png`, `7-contact us.png`.

## Chức năng

**Trang Job Detail** (`/job/<tên-việc-làm>/`)
- Breadcrumb Home / All Jobs / Job Detail.
- Logo công ty, tiêu đề, ngày tạo, nhãn Loại việc | Danh mục | Địa điểm.
- Nút **SHARE**: menu Facebook, Twitter, LinkedIn, Copy link.
- Nút **APPLY JOB**: có email thì mở soạn mail, có URL thì mở link, để trống thì sang trang Contact.
- 4 mục nội dung: Overview about Company, Our Key Skills, Why You'll Love Working Here, Location.
- **Staff Rating** (sao, hỗ trợ nửa sao) và **Company Photos** (ảnh đầu + nhãn "+N", bấm mở xem ảnh lớn, chuyển bằng ‹ › hoặc phím mũi tên).
- **OTHER JOBS**: 6 việc làm cùng danh mục, thiếu thì bù việc mới nhất.

**Trang Contact** (page dùng template **Contact Us**)
- Banner CONTACT US (ảnh = ảnh đại diện của page, mặc định `images/contact-hero.jpg`).
- Địa chỉ trụ sở, For Employers / For Jobseekers, số điện thoại bấm để gọi.
- Form liên hệ **Contact Form 7** bên dưới (nội dung của page Contact).

**Thẻ việc làm dùng chung** (`template-parts/content-job-card.php`): dùng ở OTHER JOBS và trang All Jobs, đúng thiết kế ảnh 4/5.

## Nhập dữ liệu trong admin

| Muốn sửa | Ở đâu |
|---|---|
| Việc làm (công ty, địa điểm, email ứng tuyển, 4 mục nội dung, highlights, đánh giá, ảnh công ty) | *Jobs → All Jobs → sửa Job*, hộp **Job Detail (NhomG)**; logo = *Featured image* |
| Loại việc / Danh mục | *Jobs → Job Types / Job Categories* |
| Chữ, địa chỉ, số điện thoại trang Contact | *Appearance → Customize → Contact Page* |
| Form liên hệ (ô nhập, email nhận) | *Contact → Contact Forms → Contact Us* (tab Form / Mail) |
| Banner trang All Jobs | *Appearance → Widgets → Jobs Hero Banner Widget* (Custom HTML) |

## Gửi mail (plugin)

- **WP Mail SMTP**: gửi mọi mail của WordPress qua Gmail. Mỗi máy tự nhập ở *WP Mail SMTP → Settings*: Other SMTP, `smtp.gmail.com`, TLS, cổng 587, Gmail + **App Password** (https://myaccount.google.com/apppasswords). Thử ở *Tools → Email Test*.
- **Newsletter**: form SUBSCRIBE ở footer gửi tới plugin, người đăng ký nhận mail chào mừng. Danh sách ở *Newsletter → Subscribers*.
- **Contact Form 7**: form trên trang Contact. Shortcode lấy ở đầu trang sửa form, dán vào nội dung page Contact.

Sau khi pull: vào *Plugins* bấm **Activate** cho WP Mail SMTP, Newsletter, Contact Form 7. Cấu hình của plugin (Gmail, form, trang Newsletter) nằm trong database nên không có trong git. Không commit mật khẩu.

## File

| File | Vai trò |
|---|---|
| `inc/jobs.php` | Post type `job_listing` + taxonomy (khi chưa có WP Job Manager), meta box, helper, nạp CSS/JS, Customizer trang Contact |
| `single-job_listing.php` | Gọi `template-parts/content-job-detail.php` |
| `template-parts/content-job-detail.php` | Giao diện trang Job Detail |
| `template-parts/content-job-card.php` | Thẻ việc làm dùng chung |
| `templates/contact.php` | Page template "Contact Us" |
| `css/nhomg-job-contact.css` | CSS Job Detail, Contact, thẻ việc làm, form Contact Form 7 |
| `js/nhomg-job-detail.js` | Nút Share, xem ảnh lớn |
| `js/nhomg-admin-job.js` | Chọn ảnh công ty trong meta box |
| `images/contact-hero.jpg` | Ảnh banner Contact mặc định |
| `archive-job_listing.php` (phần chỉnh) | Banner sát header cao 360px, dùng thẻ việc làm chung, nền xám |
| `template-parts/cmsng-footer.php`, `js/cmsng-chrome.js` (của phần 1, chỉnh nhỏ) | Form Newsletter gửi tới plugin Newsletter |

## Dữ liệu mẫu

`database/cms_nhomg_v2.sql` (import sau v1) có 7 việc làm và trang Contact. Các bản database sau của nhóm đã gồm dữ liệu này.
