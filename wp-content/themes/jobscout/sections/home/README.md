# Home — Lam/Home

Home gọi `get_header()` và `get_footer()` đúng một lần. Newsletter nằm trong footer chung. CSS nội dung chỉ nằm trong `.lam-home`; không có bản header/footer riêng cho Home.

## Hai URL ảnh nền để gắn sau

Sửa hai giá trị `hero` và `career` trong `assets/backgrounds.php`. Hiện chúng trỏ đến:
- `sections/home/assets/hero.jpg`: phong cảnh Kyoto, chùa và ánh nắng.
- `sections/home/assets/career.jpg`: hoa anh đào và kiến trúc.

Có thể đặt file vào hai vị trí này, thay bằng URL ảnh thật, hoặc chọn ảnh trong **Giao diện → Tùy biến → Home — Content**. Ảnh Customizer được ưu tiên, tiếp theo file có sẵn jpg/jpeg/webp/png, cuối cùng hai URL chờ. Hai file nền hiện chưa tồn tại theo yêu cầu; hai URL này sẽ trả 404 đến khi bổ sung ảnh. Overlay và màu nền dự phòng đã có.

Logo thương hiệu gốc cũng chưa có trong theme/uploads và `custom_logo` chưa được đặt. Chọn logo trong **Site Identity** để cả header và footer cùng dùng; không tái tạo phần bị che đen trong ảnh mẫu.

## Nguồn dữ liệu và sửa lỗi

Home dùng CPT `job_listing` đã đăng ký trong `inc/nhomg-jobs.php`, không đăng ký CPT mới.

- Provider NhomG lưu logo công ty ở featured image, highlights ở `_nhomg_highlights`. Home đã đọc đúng hai nguồn này. Vẫn ưu tiên `_company_logo` nếu có, và dùng excerpt/content khi không có highlights.
- Database có 7 jobs, trong đó một Chief Operating Officer bị trùng. Truy vấn cũ lấy sáu tin mới nhất nên bỏ Hotel Manager. Sáu tin tham chiếu đã có `_lam_home_order` từ 1 đến 6 theo thứ tự: Hotel Manager, General Manager, Banquet Manager, Bellman, Chief Operating Officer, Loss Prevention Officer.
- Database có 9 posts, gồm các bản trùng tiêu đề và một slug Project Development bị trùng. Bốn bài Home được chọn theo permalink thật đang resolve; giữ nguyên URL, nội dung và các bản trùng.
- Bốn bài được sửa featured image theo đúng quan hệ: Project/storefront, Restaurant/roof, Hospitality/cherry blossoms, Venue/koi. Ba excerpt trống lấy đoạn Lorem ipsum của bài Project Development có sẵn, đúng nội dung mẫu. Không đổi nội dung bài hoặc ngày tạo.
- Không xóa hoặc đổi tên bài thật. Số lượng vẫn là 7 jobs, 9 posts.

`_lam_home_order` là thứ tự biên tập riêng Home: số nhỏ đứng trước, sau đó jobs tự sắp theo featured/ngày và news theo ngày. Có ô **Home position** trong màn hình sửa job/bài viết; để trống để dùng thứ tự tự động. Cài đặt không ảnh hưởng query của Jobs/News hoặc trang chi tiết. Job đã tuyển, hết hạn, draft/password vẫn bị loại dù có số thứ tự.

`tools/repair-reference.php` là công cụ CLI kiểm tra/sửa dữ liệu hiện có, không chạy từ web hoặc tự chạy trên request Home:
```powershell
php sections/home/tools/repair-reference.php
php sections/home/tools/repair-reference.php --apply
```
Công cụ tìm bài theo slug/permalink và ảnh theo filename, kiểm tra dữ liệu trước khi sửa, sao lưu excerpt/thumbnail/order vào thư mục temp trước khi ghi bằng WordPress API. Không chứa hostname, đường dẫn máy hoặc ID cố định.

Lần áp dụng 09/10/2026 đã sao lưu tại `C:\Users\LAM\AppData\Local\Temp\cms1050.tmp`. Đây là bản sao trạng thái trước sửa dữ liệu, không phải file code giao diện.

## Tìm kiếm và điều hướng

Form gửi GET `search_keywords`, `search_location`, giữ các tham số permalink dạng plain và không dùng `s` ở Home. Khi có trang WPJM phù hợp thì chuyển đến trang đó; hiện provider NhomG dùng bộ lọc Home. Từ khóa tìm tiêu đề/nội dung, công ty, kỹ năng và taxonomy.

Địa điểm lấy từ jobs đang mở, cộng các điểm tìm kiếm được cấu hình trong **Home — Content → Additional search locations**. Tokyo được cấu hình mặc định và gửi đúng giá trị `Tokyo`; dữ liệu hiện có chỉ ở Ho Chi Minh City nên tìm Tokyo trả trạng thái không có kết quả. Không đổi địa điểm các jobs để giả có việc ở Tokyo. Home chưa submit vẫn hiển thị sáu Top Jobs; sau submit giữ bộ lọc thực tế.

View More Jobs, More About Us và menu dùng các URL chung từ WordPress. Các mục chưa có trang đích tiếp tục hiển thị trạng thái chưa cấu hình, không tạo link giả.

## Component chung

Chỉ chỉnh `css/cmsng-chrome.css`, `template-parts/cmsng-footer.php` và thêm `images/google-g.svg`:
- Header rộng tối đa 1320px, cao tối thiểu 86px ở desktop; brand bên trái, menu/CTA bên phải; giữ gạch chân HOME và menu mobile.
- Newsletter đúng hai dòng “Subscribe To” / “Our Newsletter”, thu lại container và cân ô email/nút.
- Footer căn giữa, Google nhiều màu trên nền trắng. Khi copyright cấu hình trống, dùng năm hiện tại và tên website thật.
- Giữ logic newsletter hiện có; chưa có backend lưu/gửi email thì không báo đăng ký thành công.
Không sửa `header.php`, `footer.php`, JS chung hoặc liên kết của các trang khác.

## File thay đổi trong lần sửa này

- `sections/home/functions.php`: adapter logo/highlights, thứ tự Home, Tokyo, URL nền.
- `sections/home/news.php`: query tin theo thứ tự Home.
- `sections/home/home.css`: typography tin tức để đoạn mô tả và tiêu đề dài vừa card.
- `sections/home/editor.php`: trường thứ tự có nonce/quyền sửa bài.
- `sections/home/assets/backgrounds.php`, `assets/README.md`: hai URL nền chờ bổ sung.
- `sections/home/tools/repair-reference.php`: sửa dữ liệu có sao lưu, CLI-only.
- `sections/home/tests/search.php`: thêm kiểm tra thứ tự, bản ghi meta trùng, lọc và query isolation.
- `css/cmsng-chrome.css`, `template-parts/cmsng-footer.php`, `images/google-g.svg`.
- `sections/home/README.md`: tài liệu và kết quả hiện tại.

## Kiểm tra ngày 09/10/2026

- PHP syntax: toàn bộ PHP trong Home, front-page và footer component đều đạt.
- 36 kiểm tra Home SQLite trong RAM đạt: bộ lọc, eligibility, SQL-like input, route, excerpt/image fallback, thứ tự biên tập, meta trùng và query isolation.
- 17 kiểm tra chrome routing đạt.
- Kiểm thử DOM menu/newsletter tại 1440/1024/768/375px đạt; đây không phải kiểm thử layout trực quan.
- HTTP anonymous: Home 200, không admin bar; đúng một header/footer/newsletter, 6 jobs có 3 highlights, 4 news có ảnh/excerpt, Tokyo chọn mặc định, copyright không trống.
- 24 tài nguyên local đang sử dụng (gồm 6 logo công ty, 4 ảnh blog, Google SVG, CSS/JS) trả 200. Hai ảnh nền chờ cố ý chưa có được tách riêng.
- 10 permalink card trả 200; bốn permalink news resolve đúng ID của bài đang hiển thị.
- Tìm Hotel trả 4 jobs (gồm bản Chief trùng vẫn được giữ), HCMC trả tối đa 6, Bellman + HCMC trả 1, Tokyo và từ khóa không tồn tại trả 0, có trạng thái rỗng.
- Database vẫn có 7 jobs, 9 posts; không commit/push.

Công cụ browser lỗi khởi động `codex app-server: The system cannot find the path specified (os error 3)`. Chưa chụp trang mới hoặc kiểm chứng tràn ngang/layout ở cùng viewport/zoom. Breakpoint có sẵn và các phần tử co giãn đã được rà soát trong CSS, nhưng cần xác nhận trực quan khi browser hoạt động. Không tuyên bố khớp 100%.

Khác biệt còn lại: hai ảnh nền do người dùng sẽ gắn sau; thiếu logo thương hiệu; ảnh blog gốc hiện chỉ khoảng 110px nên có thể mờ khi phóng lên khung 200px; chưa xác định được font gốc từ mẫu và vẫn dùng font Nunito Sans hiện có của theme.
