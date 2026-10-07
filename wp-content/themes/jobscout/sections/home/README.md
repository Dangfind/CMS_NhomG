# Home — nhánh Lam/Home

Home hiển thị tự động từ `front-page.php` khi theme JobScout đang kích hoạt, kể cả khi **Cài đặt → Đọc** còn chọn bài viết mới nhất. Không cần đổi database để bật giao diện. Nếu dùng trang chủ tĩnh, giữ trang đó ở mục Homepage và chọn trang News riêng ở Posts page.

Chỉ sửa `front-page.php` và bổ sung một đoạn `require` trong `functions.php`. Toàn bộ code mới nằm trong `sections/home/`. Không sửa `header.php`, `footer.php`, module/CSS/JS header-footer hoặc các template của trang khác. `front-page.php` gọi `get_header()` và `get_footer()` đúng một lần. Nội dung Home mở `main.lam-home`; hook nội dung riêng chỉ thay wrapper trên request Home và giữ `#acc-content` để footer chung đóng đúng.

## File

- `functions.php`: dữ liệu, tìm kiếm, URL, Customizer, enqueue CSS chỉ ở Home.
- `template.php`: bốn section theo thứ tự mẫu.
- `hero.php`, `jobs.php`, `career.php`, `news.php`: markup từng section.
- `job-card.php`, `news-card.php`: card riêng Home, tái sử dụng bằng `get_template_part( 'sections/home/job-card', null, array( 'post' => $wp_post ) )` hoặc `news-card`.
- `home.css`: selector giới hạn trong `.lam-home`, breakpoint cho tablet/mobile; không nạp thư viện mới.
- `demo.php`: công cụ quản trị tạo mẫu theo yêu cầu chủ động.
- `assets/README.md`: vị trí đặt ảnh gốc.
- `tests/search.php`: kiểm thử độc lập, dùng SQLite trong RAM và bộ biên dịch `WP_Meta_Query` gốc; không mở database dự án.

Không cần JavaScript riêng: tìm kiếm dùng GET và chạy đầy đủ khi tắt JavaScript. Giữ nguyên JavaScript của header/footer.

## Tài nguyên và cấu hình

**Giao diện → Tùy biến → Home — Content**: chọn ảnh nền banner Nhật Bản và ảnh Career hoa anh đào. Hoặc đặt tài nguyên gốc trong `sections/home/assets/hero.jpg` và `career.jpg` (hỗ trợ jpeg/webp/png). Ảnh trong Customizer được ưu tiên. Chưa có ảnh thì dùng nền màu trơn, không thay bằng ảnh bất kỳ và không cắt screenshot làm nền.

Ảnh mẫu gốc là 1440 × 3470. Nội dung triển khai theo kích thước tương ứng: banner 540px, khung danh sách 1050px, hai cột job 510px và logo 120px; Career tối thiểu 500px; news hai cột với ảnh 200px. Font kế thừa Nunito Sans hiện có của JobScout; chưa có file font gốc để xác nhận chính xác typography. Chiều cao header/footer hiện tại được giữ nguyên theo phạm vi yêu cầu.

URL About và View More Jobs đọc cấu hình dùng chung qua `cmsng_section_url()`. Chọn các trang thật trong **CMS_NhomG — Header & Footer**. Khi chưa có đích, nút hiển thị không liên kết (`aria-disabled`), không sinh URL giả.

## Dữ liệu việc làm và tìm kiếm

Không đăng ký CPT mới. Đọc `job_listing` của provider hiện có:

| Thông tin | Nguồn |
| --- | --- |
| Tên, URL, ngày tạo | post title, permalink, post date |
| Logo / công ty | `_company_logo` (attachment ID hoặc URL), `_company_name` |
| Loại / chuyên mục | `job_listing_type`, `job_listing_category` |
| Địa điểm | `_job_location`; hỗ trợ geolocation và taxonomy `job_listing_region` / `job_listing_location` |
| Nổi bật | `_featured`; sắp xếp trước, sau đó ngày đăng mới nhất |
| Còn hiệu lực | `publish`, không password, `_filled` khác 1 hoặc thiếu, `_job_expires` chưa quá ngày hiện tại hoặc trống/thiếu |
| Mô tả | excerpt, nếu thiếu dùng content; lấy tối đa ba dòng/bullet, bỏ HTML/shortcode |

Mặc định lấy 6 việc. Không có provider hoặc dữ liệu thì hiện trạng thái rỗng, không truy vấn nhầm `post`.

Form dùng `search_keywords` và `search_location`, không dùng tham số `s` để tránh biến Home thành tìm kiếm tin tức. Giá trị được kiểm tra kiểu, sanitize và giới hạn 200 ký tự. Địa điểm lấy từ các việc đang mở; chỉ hiển thị Tokyo mặc định nếu dữ liệu thật có Tokyo. Trạng thái mặc định Top Jobs vẫn là các việc mới/nổi bật; địa điểm trong form được áp dụng khi người dùng submit. Sau submit giữ nguyên giá trị đã chọn, kể cả địa điểm vừa hết dữ liệu. Có thông báo không tìm thấy và liên kết Clear filters.

Nếu đã có trang Jobs chứa shortcode `[jobs]` và WP Job Manager đang hoạt động, form chuyển đến permalink của trang đó bằng tham số chuẩn, giữ các query param định tuyến của permalink dạng `?page_id=...`. Ưu tiên trang Jobs được cấu hình dùng chung rồi đến tùy chọn WPJM. Khi chưa có trang xử lý, hoặc có taxonomy địa điểm cần bộ lọc riêng, kết quả lọc ngay tại Top Jobs trên Home. Từ khóa tìm tiêu đề/nội dung, công ty, kỹ năng và chuyên mục. Query/filter chỉ áp dụng trên truy vấn việc làm phụ của Home, có tháo hook sau khi chạy; không đổi main query.

Tích hợp WPJM sử dụng API và tham số đã kiểm tra trong [mã nguồn WP Job Manager chính thức](https://github.com/Automattic/WP-Job-Manager/blob/master/wp-job-manager-functions.php).

## Tin tức

Đọc 4 `post` công khai mới nhất, bỏ sticky priority, loại bài password; không lẫn `job_listing`. Ảnh ưu tiên featured image, sau đó ảnh hợp lệ đầu tiên trong content bằng HTML API WordPress. Nếu cả hai thiếu thì giữ khung màu xám. Excerpt ưu tiên trường excerpt rồi tạo từ content bỏ shortcode/HTML. Ảnh, tiêu đề và Read More mở permalink của bài tương ứng. Hai vòng lặp card đều gọi `wp_reset_postdata()` sau truy vấn phụ.

## Tạo dữ liệu mẫu (tùy chọn)

Chưa tự tạo hoặc thay đổi dữ liệu trong quá trình triển khai. Để tạo mẫu:

1. Cài/kích hoạt provider `job_listing` mà dự án sử dụng (JobScout hiện hỗ trợ WP Job Manager).
2. Quản trị viên mở **Công cụ → Home sample data**, đọc mô tả và bấm **Create sample data**.
3. Công cụ tạo 6 jobs và 4 bài mẫu bằng WordPress API, không sửa trang, menu hoặc bản ghi thật. Jobs đặt hạn 90 ngày và địa điểm Ho Chi Minh City. Gắn logo công ty, ảnh bài viết gốc sau đó.

Yêu cầu POST, quyền quản trị/publish và nonce hợp lệ. Chống tạo trùng bằng `_lam_home_demo_key`, kể cả bản ghi đã bỏ vào thùng rác. Bản ghi đã có không bị ghi đè; có thể chạy lại sau lỗi từng phần. Không có thao tác seed trong Home/init và không có SQL ghi trực tiếp.

## Kết quả kiểm tra và giới hạn

Chạy từ thư mục theme:

```powershell
php -l functions.php
php -l front-page.php
Get-ChildItem sections/home -Recurse -Filter '*.php' | ForEach-Object { php -l $_.FullName }
php sections/home/tests/search.php
```

Đã kiểm tra cú pháp, tìm từ khóa/địa điểm/kết hợp, tên công ty/kỹ năng, featured-first, hết hạn/đã tuyển/draft/password, taxonomy địa điểm, dữ liệu rỗng, thiếu ảnh và đầu vào bất thường bằng bộ fixture SQLite trong RAM. Kiểm tra HTTP local xác nhận một header/newsletter/footer, đủ bốn section, giữ bộ lọc và trạng thái rỗng đúng; CSS Home không xuất hiện ở Sample Page. File dùng chung được đối chiếu SHA256, giữ nguyên.

Website local hiện chỉ có Sample Page và bài Hello world, chưa có WP Job Manager, bản ghi việc làm, trang About/Jobs hoặc ảnh gốc. Vì vậy kiểm thử tìm kiếm có kết quả dùng fixture; chưa kiểm tra với dữ liệu việc làm thật hoặc chạy công cụ seed trong trang quản trị.

Công cụ trình duyệt lỗi khởi động `codex app-server`. Chưa chụp Home, kiểm tra layout trực quan/tràn ngang tại 1440/1024/768/375px hoặc đối chiếu pixel với ảnh mẫu. Hai ảnh nền, logo công ty, ảnh tin và font gốc còn thiếu nên chưa thể khớp toàn bộ ảnh. Không thay đổi thành phần dùng chung để bù sai lệch header/footer.
