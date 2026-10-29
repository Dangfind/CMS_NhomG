# Home layout — 28/10/2026

Đã chạy Chromium headless với zoom 100%, deviceScaleFactor 1 tại 1440, 1920, 768 và 375 CSS px. 77 kiểm tra DOM đạt, không có JavaScript page error.

| Viewport | Jobs trên/dưới | Career trên/dưới | Blog trên/dưới | Grid hàng/cột | Chiều rộng trang |
| --- | --- | --- | --- | --- | --- |
| 1440 | 64/64 | 100.61/100.63 | 64/64 | 24/24 | 1440 |
| 1920 | 64/64 | 100.61/100.63 | 64/64 | 24/24 | 1920 |
| 768 | 48/48 | 75.02/75.03 | 48/48 | 24/24 | 768 |
| 375 | 40/40 | 47.45/47.47 | 40/40 | 20/20 | 375 |

Khoảng trên/dưới đo từ mép section đến bounding box của nhóm nội dung. Chênh lệch phần thập phân dưới 0,02px do trình duyệt làm tròn subpixel.

Trước sửa ở desktop: Jobs 60/70px; Career 70/111,23px; blog 60/80px; grid Jobs 28/30px, grid blog 50/30px; footer 64/80px.

Thương hiệu dùng chung: G việc; header 32px (+14,3%), footer 36px (+12,5%). Header vẫn 80px. Newsletter input và nút đều 46px, cùng tâm theo chiều dọc ở desktop/tablet. Footer cân trên/dưới.

Đã kiểm tra hàng card bằng nhau, khung ảnh đồng nhất, không tràn ngang, menu mobile mở/đóng bằng Escape, tìm kiếm GET thật bằng tiêu đề việc đang có. Kiểm tra thêm tiêu đề dài, chuỗi không dấu cách và đoạn mô tả dài bằng thay đổi DOM trong phiên test; không sửa dữ liệu bài viết.

Cấu hình sidebar/widget và danh sách plugin có cùng SHA256 trước/sau. Markup CAREER từ widget và newsletter được đối chiếu giữ nguyên. JS, form action và shortcode Sassy Social Share không thay đổi.

Ảnh sau sửa:

- [1440px](after/home-1440.png)
- [1920px](after/home-1920.png)
- [768px](after/home-768.png)
- [375px](after/home-375.png)
- [Số đo đầy đủ](after/measurements.json)

File code đã sửa: sections/home/home.css, sections/home/functions.php, css/cmsng-chrome.css, inc/cmsng-chrome.php. Thêm tests/home-layout.cjs để chạy lại kiểm tra.

Giới hạn: tin nhắn hiện tại không kèm ảnh khoanh đỏ mới để so sánh trực tiếp. Sáu file logo gốc Screenshot-2026-10-28-082406.png, -082400.png, -082353.png, -082345.png, -082338.png và -082329.png thiếu trong uploads; Home giữ khung và thông tin ảnh chưa có, không gửi yêu cầu tải ảnh hỏng. Các bản ghi Media không bị xóa hoặc sửa.

Plugin Newsletter chưa kích hoạt ở nhánh hiện tại; form không có action xử lý. Lần này giữ nguyên trạng thái đó, không kiểm chứng lưu email và không báo lưu thành công. Plugin Sassy Social Share vẫn kích hoạt, đủ bốn liên kết chia sẻ.

Không commit/push.
