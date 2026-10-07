# Header / Footer dùng chung — CMS_NhomG

Theme JobScout đang kích hoạt sẽ dùng giao diện mới tự động. Không cần sửa database, tạo trang, cài plugin hoặc chạy migration.

## Cấu hình trong WordPress

1. **Giao diện → Tùy biến → Nhận diện website (Site Identity) → Logo**: tải logo gốc. Logo này dùng chung ở header và footer. Chưa có logo thì giữ vùng trống, không dựng logo bằng chữ.
2. **Giao diện → Menu → Quản lý vị trí**: gán menu header vào **Primary** với thứ tự HOME, JOBS, NEWS, ABOUT, CONTACT; gán menu footer vào **CMS_NhomG Footer** với thứ tự JOBS, COMPANIES, BLOG, ABOUT, CONTACT. Dùng trang thật hoặc URL đúng của website.
3. **Tùy biến → CMS_NhomG — Header & Footer**: chọn trang JOBS, NEWS/BLOG, ABOUT, CONTACT, COMPANIES và SUBMIT JOB; điền URL Facebook, Google, LINE, Twitter. Xóa dòng RECRUITING nếu dòng đó đã nằm trong ảnh logo.
4. **Tùy biến → General Settings → Header Settings**: cấu hình nhãn và URL nút đăng việc bằng các thiết lập JobScout hiện có. URL nút được ưu tiên; nếu để trống hoặc `#`, dùng trang SUBMIT JOB được chọn. Nhãn trống dùng SUBMIT JOB.
5. **Tùy biến → Footer Settings → Footer Copyright Text**: nhập nội dung bản quyền thực tế. Mặc định để trống vì chưa có nội dung gốc.

Khi chưa gán menu, fallback hiện đúng thứ tự mô tả, chỉ tạo liên kết đến trang đã xuất bản. Trang chưa tồn tại, nút đăng việc chưa cấu hình và mạng xã hội chưa có URL được hiển thị ở trạng thái không liên kết. Không có URL giả. Nếu dùng WP Job Manager, bộ phân giải có thể dùng các trang Jobs / Submit Job đã cấu hình của plugin.

Menu active dựa vào URL trang được chọn và ngữ cảnh WordPress: `job_listing`/archive/taxonomy thuộc JOBS; bài viết/category/tag/author/date thuộc NEWS. Nếu HOME và NEWS dùng cùng URL (trang chủ đang hiển thị bài viết), bật trường **CSS Classes** trong Screen Options của màn hình Menu và thêm `cmsng-section-news` vào NEWS/BLOG. Có thể dùng `cmsng-section-jobs` cho mục JOBS có URL tùy chỉnh. Các class này là vai trò được cấu hình, không suy đoán theo nhãn menu.

## Gọi từ template

```php
<?php get_header(); ?>
<!-- Nội dung trang, đóng các wrapper do chính trang mở. -->
<?php get_footer(); ?>
```

Giữ một lần gọi mỗi hàm. Không chép HTML chrome vào trang và không tự đóng `#page`, `#acc-content` hay `.site-content > .container`; các hook gốc JobScout quản lý chúng. `header.php` và `footer.php` vẫn là đầu vào duy nhất; module thay callback giao diện, giữ hook tài liệu và wrapper.

Đường dẫn hiện có: Home dùng `front-page.php` (ủy quyền `index.php`/`page.php` theo cấu hình); News dùng `index.php`; News Detail dùng `single.php`; Job Detail dùng `single-job_listing.php`. About, All Jobs, Contact chưa có template riêng, dùng `page.php` khi có trang WordPress tương ứng. Không xây nội dung cho các trang này trong nhiệm vụ header/footer. Site local hiện chỉ có Sample Page và bài viết mặc định, chưa cài WP Job Manager.

## Newsletter và tài nguyên còn thiếu

Newsletter hiện chỉ có giao diện và kiểm tra email bằng `type="email"`, `required` và `reportValidity()`. JavaScript chặn submit, không gửi request, không lưu email và không báo thành công. Với email hợp lệ, form báo chức năng chưa sẵn sàng. Khi JavaScript tắt, nút bị vô hiệu hóa và có thông báo tương ứng. Cần tích hợp một bộ xử lý lưu/gửi thực sự trong nhiệm vụ riêng.

Chưa nhận được ảnh mẫu; trong theme và tài nguyên dự án chưa có logo gốc/font riêng của mẫu. Hiện dùng font Nunito Sans và Font Awesome có sẵn của JobScout. Màu, khoảng cách và kích thước CSS là giá trị triển khai theo mô tả, cần đối chiếu lại khi có ảnh. Bản quyền và URL mạng xã hội chờ thông tin thực tế.

## Kiểm tra

```powershell
php -l functions.php
php -l header.php
php -l footer.php
php -l inc/cmsng-chrome.php
php -l template-parts/cmsng-header.php
php -l template-parts/cmsng-footer.php
php tests/chrome-routing.php
node --check js/cmsng-chrome.js
node tests/chrome.test.js
```

Các kiểm thử độc lập không kết nối WordPress/database. Kiểm thử JavaScript mô phỏng trạng thái tại 1440, 1024, 768, 375px; không thay thế kiểm tra layout bằng trình duyệt. Đã kiểm tra HTML từ website local để xác nhận chrome và assets xuất hiện một lần. Công cụ trình duyệt lỗi khởi động `codex app-server`, vì vậy chưa chụp ảnh hoặc xác minh tràn ngang/layout trực quan ở bốn viewport và chưa thể đối chiếu ảnh mẫu.

## File triển khai

- `functions.php`: nạp module.
- `header.php`, `footer.php`: cập nhật chú thích callback; giữ nguyên hook và wrapper.
- `inc/cmsng-chrome.php`: hook, menu, active state, URL, logo, Customizer, enqueue.
- `template-parts/cmsng-header.php`, `template-parts/cmsng-footer.php`: markup dùng chung.
- `css/cmsng-chrome.css`, `js/cmsng-chrome.js`: style có tiền tố, menu mobile và kiểm tra email.
- `tests/chrome-routing.php`, `tests/chrome.test.js`: kiểm thử logic.
- `HEADER-FOOTER.md`: tài liệu bàn giao này.
