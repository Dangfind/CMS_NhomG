/**
 * QA logo header/footer: kiểm tra hiển thị ảnh, kích thước thực, không tràn layout.
 * Chạy: node <skill>/browser.mjs "URL" --script tools/qa-header-logo.mjs
 */
export default async function run(page) {
	const measure = () => {
		const img = document.querySelector('.cmsng-header .custom-logo');
		const brandText = document.querySelector('.cmsng-header .cmsng-brand-link');
		const headerInner = document.querySelector('.cmsng-header-inner');
		const nav = document.querySelector('.cmsng-header-navigation');
		return {
			hasLogoImage: !!img,
			fallsBackToText: !!brandText,
			logoBox: img
				? {
					natural: `${img.naturalWidth}x${img.naturalHeight}`,
					rendered: `${Math.round(img.getBoundingClientRect().width)}x${Math.round(img.getBoundingClientRect().height)}`,
					alt: img.alt,
					src: img.currentSrc.split('/').pop(),
				}
				: null,
			headerHeight: headerInner ? Math.round(headerInner.getBoundingClientRect().height) : null,
			navVisible: nav ? nav.getBoundingClientRect().width > 0 : null,
			horizontalScroll: document.documentElement.scrollWidth > window.innerWidth + 1,
		};
	};

	const desktop = await page.evaluate(measure);
	await page.setViewportSize({ width: 390, height: 844 });
	await page.reload({ waitUntil: 'domcontentloaded' });
	await page.waitForTimeout(800);
	const mobile = await page.evaluate(measure);
	await page.screenshot({ path: 'd:/CMS/CMS_NhomG/_header_logo_mobile.png' });

	return { desktop, mobile };
}
