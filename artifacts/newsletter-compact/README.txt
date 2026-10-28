Đã cập nhật form CF7 Newsletter (ID 125, hash faa0341) bằng API của plugin.
Mẫu form đã lưu: wp-content/themes/jobscout/template-parts/newsletter-cf7.html.
Chỉ thay thuộc tính form; các cấu hình mail, thông báo và additional settings giữ nguyên.

Kiểm tra ở zoom 100%: 1920/1440px cao 116px, title/input/Turnstile/Submit chung tâm dọc;
768px xác minh và Submit cùng hàng; 375px xuống hàng tự nhiên, không tràn ngang.
Một form, một trường your-email bắt buộc, một Turnstile; method POST/action giữ nguyên.

Cloudflare api.js bị ERR_NETWORK_ACCESS_DENIED trong Chromium kiểm thử.
Ảnh after ghi lại vùng dành cho Turnstile 300x65px; không mô phỏng ô xác minh
hoặc gửi form. Chưa xác nhận thao tác xác minh/gửi thực tế.
