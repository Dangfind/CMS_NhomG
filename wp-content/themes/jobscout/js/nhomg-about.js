/**
 * Trang About — khoảng trắng giữa banner và khối "About Us".
 *
 * Banner cao tối thiểu 420px và tiêu đề nằm giữa, nên bên dưới tiêu đề luôn
 * còn một khoảng trống. Nếu cộng thêm khoảng trắng cố định (.nhomg-about-gap)
 * thì phần chuyển tiếp trông bị hụt hẫng. Ở đây ta chừa lại một khoảng nhỏ
 * (không nhỏ hơn MIN) rồi cho phần trên khối About Us gánh phần còn lại, do đó
 * tổng khoảng cách luôn bằng đúng một giá trị mong muốn ở mọi kích thước màn hình.
 *
 * Không dùng JS của theme hay thư viện ngoài; CSS vẫn hoạt động khi JS bị tắt.
 */
(function () {
	'use strict';

	var MIN_GAP = 28; // px — khoảng tối thiểu, tránh hai khối dính vào nhau

	function syncGap() {
		var about = document.querySelector('.nhomg-about');
		var hero = document.querySelector('.nhomg-about-hero');
		var gap = document.querySelector('.nhomg-about-gap');
		var intro = document.querySelector('.nhomg-about-intro');
		// Đo cả cụm nội dung (tiêu đề + phụ đề + nút), không chỉ riêng tiêu đề.
		var title = document.querySelector('.nhomg-about-hero__inner') || document.querySelector('.nhomg-about-hero__title');

		if (!about || !gap) return;
		// Trang không có khối intro thì khoảng trắng cũng không cần.
		if (!intro || !hero || !title) {
			gap.style.height = '';
			return;
		}

		var heroBox = hero.getBoundingClientRect();
		var titleBox = title.getBoundingClientRect();
		// Phần trống từ đáy tiêu đề xuống cuối banner.
		var leftover = Math.max(0, heroBox.bottom - titleBox.bottom);
		var height = Math.max(0, MIN_GAP - leftover);

		gap.style.height = height + 'px';
		about.setAttribute('data-gap-ready', '');
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', syncGap);
	} else {
		syncGap();
	}

	var resizeTimer = null;
	window.addEventListener('resize', function () {
		if (resizeTimer) clearTimeout(resizeTimer);
		resizeTimer = setTimeout(syncGap, 100);
	});

	// Font web tải xong có thể làm tiêu đề đổi chiều cao -> tính lại.
	if (document.fonts && document.fonts.ready) {
		document.fonts.ready.then(syncGap);
	}

	// Cuộn mượt cho anchor trong trang (nút banner). CSS lo phần còn lại.
	if (document.querySelector('.nhomg-about')) {
		document.documentElement.classList.add('nhomg-about-smooth');
	}
})();
