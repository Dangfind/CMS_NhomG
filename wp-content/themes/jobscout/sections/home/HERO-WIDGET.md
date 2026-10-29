# Banner Home — Custom HTML widget

Vào **Giao diện → Widget → Trang chủ - Find Your Dream Jobs** (ID `lam-home-find-jobs`), thêm một widget **Custom HTML / HTML tùy chỉnh** và dán toàn bộ [hero-widget.html](assets/hero-widget.html).

- Thay `THAY_URL_ANH_TU_THU_VIEN_VAO_DAY` trong `src` bằng URL ảnh lấy từ Thư viện.
- Sửa chữ bên trong `h1.lam-home-title` và `p.lam-home-intro`.
- Giữ các class và `id="lam-home-title"`. Không thêm section bao ngoài, PHP hoặc thẻ form.
- Tiêu đề quản lý của widget được ẩn; tiêu đề hiển thị chỉnh trực tiếp trong HTML.
- Chỉ dùng một widget cho khu vực banner này. Gỡ widget hoặc để HTML trống sẽ dùng ảnh/chữ mặc định.

Ảnh dùng `img.lam-home-hero-image`, nằm phủ toàn banner với `object-fit: cover`; overlay tối nằm trong CSS. Widget chỉ chứa ảnh và chữ. Form PHP nằm ngoài nội dung widget, luôn ở trong banner và phía dưới chữ, giữ nguyên action/method/hidden fields/giá trị địa điểm.

Mặc định và widget dùng chung `lam-home-hero-content`, `lam-home-hero-copy`, class tiêu đề/mô tả và toàn bộ CSS. Khoảng cách của widget WordPress được chuẩn hóa để không cộng thêm margin của `.textwidget p`.

Ảnh mặc định vẫn theo cấu hình Home cũ: Customizer, file ảnh có sẵn hoặc URL trong `assets/backgrounds.php`. Widget có nội dung sẽ thay ảnh/chữ mặc định. Không có mã tạo widget tự động trên request frontend.

Đã kiểm tra trên website local bằng Chromium headless, zoom 100%, tại 1440/1920/768/375px:
- Lưu widget Custom HTML thật bằng WordPress API; kiểm tra cả widget trống có nhãn quản lý.
- Nội dung widget giống mặc định cho hình học giống nhau (sai số tối đa 1px).
- Lưu ảnh từ Media Library, tiêu đề và mô tả dài khác; tải lại Home và xác nhận cả ba cập nhật.
- Ảnh tải được và cover, chỉ có một form, không form lồng nhau, không chồng chữ/form hoặc tràn ngang.
- Click SEARCH JOB và kiểm tra tham số GET/địa điểm/permalink, có kết quả thực tế.
- Markup CAREER/newsletter giữ nguyên; các option sidebar, Custom HTML, block widget và plugin được khôi phục sau kiểm tra.
- Sau kiểm tra, khu vực widget mới vẫn trống như ban đầu và Home dùng banner mặc định.

Kiểm tra tích hợp nằm trong `tests/hero-widget.cjs`; script tạo bản sao cấu hình trong thư mục temp, chỉ thêm widget thử vào khu vực mới và khôi phục ở `finally`. Không thay đổi nội dung CAREER.

Ảnh và kết quả chi tiết: `artifacts/hero-widget/widget/`. Có 12 trường hợp (3 trạng thái × 4 viewport), cùng bản sao cấu hình trước thử tại đường dẫn ghi trong `results.json`.
