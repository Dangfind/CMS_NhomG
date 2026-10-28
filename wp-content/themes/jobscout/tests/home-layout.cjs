/* Read-only browser QA for the live local site. No newsletter submits or sharing. Search uses its existing GET form. */
'use strict';
const fs = require('node:fs/promises');
const path = require('node:path');
const { chromium } = require(process.argv[2] || 'playwright');
const url = process.argv[3];
const output = path.resolve(process.argv[4] || 'artifacts/home-layout');
const strict = process.argv.includes('--assert');
if (!url) throw new Error('Pass the local Home URL as argument 3.');
const near = (a, b) => Math.abs(a - b) <= 1;
(async () => {
    await fs.mkdir(output, { recursive: true });
    const browser = await chromium.launch({ headless: true });
    const reports = [];
    try {
        for (const width of [1440, 1920, 768, 375]) {
            const context = await browser.newContext({ viewport: { width, height: 1000 }, deviceScaleFactor: 1 });
            const page = await context.newPage();
            const pageErrors = [];
            page.on('pageerror', e => pageErrors.push(e.message));
            await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
            await page.evaluate(async () => {
                await Promise.race([document.fonts.ready, new Promise(r => setTimeout(r, 5000))]);
                document.querySelectorAll('img').forEach(img => img.loading = 'eager');
                await Promise.race([
                    Promise.all([...document.images].map(img => img.decode().catch(() => {}))),
                    new Promise(r => setTimeout(r, 5000))
                ]);
            });
            const metrics = await page.evaluate(() => {
                const rect = element => {
                    if (!element) return null;
                    const r = element.getBoundingClientRect();
                    return { x: r.x, y: r.y, width: r.width, height: r.height, right: r.right, bottom: r.bottom };
                };
                const first = s => document.querySelector(s);
                const box = s => rect(first(s));
                const padding = s => {
                    const e = first(s), c = getComputedStyle(e);
                    return { top: parseFloat(c.paddingTop), bottom: parseFloat(c.paddingBottom) };
                };
                const grid = (selector, card, image) => {
                    const e = first(selector), c = getComputedStyle(e);
                    const cards = [...e.querySelectorAll(card)].map(rect);
                    const images = [...e.querySelectorAll(image)].map(rect);
                    return { rowGap: parseFloat(c.rowGap), columnGap: parseFloat(c.columnGap), cards, images };
                };
                const jobs = box('.lam-home-jobs'), jobTitle = box('#lam-home-jobs-title');
                const jobGrid = box('.lam-home-job-grid'), button = box('.lam-home-more-wrap');
                const news = box('.lam-home-news'), newsTitle = box('#lam-home-news-title'), newsGrid = box('.lam-home-news-grid');
                const career = box('.lam-home-career'), careerInner = box('.lam-home-career-inner');
                const footer = box('.cmsng-footer-main'), brand = box('.cmsng-footer-brand');
                const share = box('.cmsng-footer-inner > .heateor_sss_sharing_container') || box('.cmsng-socials');
                const newsletter = ['.cmsng-newsletter-title', '.cmsng-email', '.cmsng-subscribe'].map(box);
                return {
                    firstJobTitle: first('.lam-home-job-title a')?.textContent.trim(), brokenImages: [...document.images].filter(img => !img.complete || !img.naturalWidth).map(img => img.currentSrc || img.src), viewport: innerWidth, pageWidth: document.documentElement.scrollWidth, zoom: visualViewport.scale,
                    header: box('.cmsng-header'), brands: [...document.querySelectorAll('.cmsng-brand-link')].map(e => ({ text: e.textContent.trim(), fontSize: getComputedStyle(e).fontSize })),
                    jobs: { padding: padding('.lam-home-jobs'), titleGap: jobGrid.y - jobTitle.bottom, actionGap: button.y - jobGrid.bottom, outerTop: jobTitle.y - jobs.y, outerBottom: jobs.bottom - button.bottom, ...grid('.lam-home-job-grid', '.lam-home-job-card', '.lam-home-logo-frame') },
                    career: { padding: padding('.lam-home-career'), topSpace: careerInner.y - career.y, bottomSpace: career.bottom - careerInner.bottom },
                    news: { padding: padding('.lam-home-news'), titleGap: newsGrid.y - newsTitle.bottom, outerTop: newsTitle.y - news.y, outerBottom: news.bottom - newsGrid.bottom, ...grid('.lam-home-news-grid', '.lam-home-news-card', '.lam-home-news-image-link') },
                    newsletter: { boxes: newsletter, centers: newsletter.map(r => r.y + r.height / 2) },
                    footer: { padding: padding('.cmsng-footer-main'), topSpace: brand.y - footer.y, bottomSpace: share ? footer.bottom - share.bottom : null },
                    newsletterReady: !!first('.cmsng-newsletter-form')?.getAttribute('action'),
                    socialCount: document.querySelectorAll('.cmsng-footer-inner .heateor_sss_sharing_ul > a').length
                };
            });
            await page.screenshot({ path: path.join(output, 'home-' + width + '.png'), fullPage: true });
            const toggle = page.locator('.cmsng-menu-toggle');
            let navigationWorks = true;
            if (width <= 1024) {
                await toggle.click();
                navigationWorks = await page.locator('.cmsng-header-navigation').isVisible();
                navigationWorks = navigationWorks && await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth);
                await page.keyboard.press('Escape');
                navigationWorks = navigationWorks && !(await page.locator('.cmsng-header-navigation').isVisible());
            } else {
                navigationWorks = !(await toggle.isVisible()) && await page.locator('.cmsng-header-navigation').isVisible();
            }
            const stress = await page.evaluate(() => {
                const job = document.querySelector('.lam-home-job-title a');
                const news = document.querySelector('.lam-home-news-card-title a');
                const excerpt = document.querySelector('.lam-home-news-excerpt');
                if (job) job.textContent = 'GIÁM ĐỐC VẬN HÀNH KHÁCH SẠN VÀ KHU NGHỈ DƯỠNG QUỐC TẾ '.repeat(4);
                if (news) news.textContent = 'LongArticleTitleWithoutAnySpaces'.repeat(7);
                if (excerpt) excerpt.textContent = 'Longer article description to check wrapping and equal row heights. '.repeat(15);
                const paired = s => {
                    const cards = [...document.querySelectorAll(s)].map(e => e.getBoundingClientRect());
                    return cards.length < 2 || Math.abs(cards[0].top - cards[1].top) > 1 || Math.abs(cards[0].height - cards[1].height) <= 1;
                };
                const title = document.querySelector('.lam-home-job-title')?.getBoundingClientRect();
                const date = document.querySelector('.lam-home-job-date')?.getBoundingClientRect();
                return { noHorizontalOverflow: document.documentElement.scrollWidth <= innerWidth, jobRowsEqual: paired('.lam-home-job-card'), newsRowsEqual: paired('.lam-home-news-card'), jobDateDoesNotOverlapTitle: !title || !date || date.top >= title.bottom };
            });
            const sameRows = cards => cards.every((card, i) => i === 0 || !near(card.y, cards[i - 1].y) || near(card.height, cards[i - 1].height));
            const checks = {
                zoom100: metrics.zoom === 1,
                headerHeightPreserved: metrics.header.height <= 82,
                renderedImagesLoad: metrics.brokenImages.length === 0,
                navigationWorks,
                noHorizontalOverflow: metrics.pageWidth <= width,
                jobsOuterBalanced: near(metrics.jobs.outerTop, metrics.jobs.outerBottom),
                careerCentered: near(metrics.career.topSpace, metrics.career.bottomSpace),
                newsOuterBalanced: near(metrics.news.outerTop, metrics.news.outerBottom),
                headingsSameGap: near(metrics.jobs.titleGap, metrics.news.titleGap),
                jobsGapsEqual: near(metrics.jobs.rowGap, metrics.jobs.columnGap),
                newsGapsEqual: near(metrics.news.rowGap, metrics.news.columnGap),
                jobRowsEqual: sameRows(metrics.jobs.cards),
                newsRowsEqual: sameRows(metrics.news.cards),
                logoFramesUniform: metrics.jobs.images.every(r => near(r.width, metrics.jobs.images[0].width) && near(r.height, metrics.jobs.images[0].height)),
                newsFramesUniform: metrics.news.images.every(r => near(r.width, metrics.news.images[0].width) && near(r.height, metrics.news.images[0].height)),
                footerCentered: metrics.footer.bottomSpace !== null && near(metrics.footer.topSpace, metrics.footer.bottomSpace),
                newsletterControlsSameHeight: near(metrics.newsletter.boxes[1].height, metrics.newsletter.boxes[2].height),
                newsletterDesktopCentered: width < 768 || metrics.newsletter.centers.every(c => near(c, metrics.newsletter.centers[0])),
                ...stress
            };
            if (width === 1440 && metrics.firstJobTitle) {
                await page.locator('[name="search_keywords"]').fill(metrics.firstJobTitle);
                await page.locator('[name="search_location"]').selectOption('');
                await Promise.all([
                    page.waitForURL(u => u.searchParams.get('search_keywords') === metrics.firstJobTitle),
                    page.locator('.lam-home-search-submit').click()
                ]);
                checks.searchWorks = await page.locator('.lam-home-job-title a').allTextContents().then(titles => titles.some(title => title.trim() === metrics.firstJobTitle));
            }
            reports.push({ width, metrics, checks, pageErrors });
            console.log(JSON.stringify({ width, failed: Object.entries(checks).filter(([,ok]) => !ok).map(([name]) => name), pageErrors }));
            await context.close();
        }
        await fs.writeFile(path.join(output, 'measurements.json'), JSON.stringify(reports, null, 2));
        if (strict && reports.some(r => Object.values(r.checks).some(ok => !ok) || r.pageErrors.length)) process.exitCode = 1;
    } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exitCode = 1; });
