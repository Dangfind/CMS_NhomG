/**
 * QA responsive cho trang About: kiểm tra tràn ngang + chụp màn hình mobile.
 * Chạy: node <skill>/browser.mjs "http://cms-nhomg.local/?page_id=57" --script tools/qa-about-mobile.mjs
 */
export default async function run(page) {
	await page.setViewportSize({ width: 390, height: 844 });
	await page.reload({ waitUntil: 'domcontentloaded' });
	await page.waitForSelector('.nhomg-about-hero__cta');
	await page.waitForTimeout(900);

	// Nút chỉ là anchor: click phải cuộn tới #nhomg-about-intro.
	await page.click('.nhomg-about-hero__cta');
	await page.waitForTimeout(1200);

	const metrics = await page.evaluate(() => {
		const doc = document.documentElement;
		const overflowing = [];
		document.querySelectorAll('.nhomg-about *').forEach((el) => {
			const r = el.getBoundingClientRect();
			if (r.width > 0 && (r.right > window.innerWidth + 1 || r.left < -1)) {
				overflowing.push({
					tag: el.tagName.toLowerCase(),
					cls: (el.className || '').toString().slice(0, 60),
					right: Math.round(r.right),
				});
			}
		});
		const gap = document.querySelector('.nhomg-about-gap');
		const target = document.getElementById('nhomg-about-intro');
		return {
			viewport: window.innerWidth,
			scrollWidth: doc.scrollWidth,
			hasHorizontalScroll: doc.scrollWidth > window.innerWidth + 1,
			overflowing: overflowing.slice(0, 8),
			gapHeight: gap ? Math.round(gap.getBoundingClientRect().height) : null,
			hashAfterClick: window.location.hash,
			targetTopAfterClick: target ? Math.round(target.getBoundingClientRect().top) : null,
			smoothClass: doc.classList.contains('nhomg-about-smooth'),
			scrollBehavior: getComputedStyle(doc).scrollBehavior,
		};
	});

	await page.screenshot({ path: 'd:/CMS/CMS_NhomG/_about_check_mobile.png' });
	return metrics;
}
