/* Integration test: temporarily saves a native Custom HTML widget, then restores it. */
'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs/promises');
const os = require('node:os');
const path = require('node:path');
const { promisify } = require('node:util');
const execFile = promisify(require('node:child_process').execFile);
const { chromium } = require(process.argv[2] || 'playwright');
const php = process.argv[3];
const root = path.resolve(process.argv[4]);
const url = process.argv[5];
const output = path.resolve(process.argv[6] || 'artifacts/hero-widget/widget');
const sidebar = 'lam-home-find-jobs';
const theme = path.join(root, 'wp-content/themes/jobscout');
const phpRun = async (code, ...args) => {
    const r = await execFile(php, ['-d', 'xdebug.mode=off', '-r', "require $argv[1] . '/wp-load.php'; " + code, root, ...args], { timeout: 15000, maxBuffer: 8 * 1024 * 1024 });
    return r.stdout.trim() ? JSON.parse(r.stdout) : null;
};
const snapshotCode = `
$images = [];
foreach ( get_posts(['post_type'=>'attachment','post_status'=>'inherit','post_mime_type'=>'image','numberposts'=>100]) as $p ) {
    if (is_readable(get_attached_file($p->ID))) $images[] = wp_get_attachment_url($p->ID);
    if (count($images) >= 2) break;
}
echo wp_json_encode(['sidebars'=>get_option('sidebars_widgets'),'widgets'=>get_option('widget_custom_html',null),'blocks'=>get_option('widget_block'),'plugins'=>get_option('active_plugins'),'hero_url'=>lam_home_background_url('hero'),'images'=>$images,'registered'=>isset($GLOBALS['wp_registered_sidebars']['lam-home-find-jobs'])], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
`;
const setCode = `
$d = json_decode(file_get_contents($argv[2]), true);
$widgets = (array)get_option('widget_custom_html', []);
$widgets[$d['number']] = ['title'=>'Hero integration test label','content'=>$d['html']];
$widgets['_multiwidget'] = 1;
update_option('widget_custom_html', $widgets);
$sidebars = (array)get_option('sidebars_widgets', []);
$sidebars['lam-home-find-jobs'] = ['custom_html-' . $d['number']];
wp_set_sidebars_widgets($sidebars);
echo wp_json_encode(['saved'=>true]);
`;
const restoreCode = `
$d = json_decode(file_get_contents($argv[2]), true);
$widgets = (array)get_option('widget_custom_html', []);
unset($widgets[$d['number']]);
if ($d['snapshot']['widgets'] === null && count(array_diff(array_keys($widgets), ['_multiwidget'])) === 0) {
    delete_option('widget_custom_html');
} else {
    if (isset($d['snapshot']['widgets']['_multiwidget'])) $widgets['_multiwidget'] = $d['snapshot']['widgets']['_multiwidget'];
    update_option('widget_custom_html', $widgets);
}
$sidebars = (array)get_option('sidebars_widgets', []);
if (array_key_exists('lam-home-find-jobs', $d['snapshot']['sidebars'])) $sidebars['lam-home-find-jobs'] = $d['snapshot']['sidebars']['lam-home-find-jobs'];
else unset($sidebars['lam-home-find-jobs']);
wp_set_sidebars_widgets($sidebars);
echo wp_json_encode(['restored'=>true]);
`;
(async () => {
    await fs.mkdir(output, { recursive: true });
    const state = await phpRun(snapshotCode);
    assert(state.registered, 'Hero sidebar must be registered');
    const number = Math.max(0, ...Object.keys(state.widgets || {}).filter(k => /^\d+$/.test(k)).map(Number)) + 1;
    const temp = await fs.mkdtemp(path.join(os.tmpdir(), 'cmsng-hero-widget-'));
    const backup = path.join(temp, 'before.json');
    const payload = path.join(temp, 'widget.json');
    await fs.writeFile(backup, JSON.stringify({ number, snapshot: state }));
    const sample = await fs.readFile(path.join(theme, 'sections/home/assets/hero-widget.html'), 'utf8');
    const save = async html => {
        await fs.writeFile(payload, JSON.stringify({ number, html }));
        await phpRun(setCode, payload);
    };
    const reports = [];
    let browser;
    let restored = false;
    const original = await (await fetch(url)).text();
    const career = html => html.match(/<div class="nhomg-career-widget"[\s\S]*?(?=<section class="lam-home-news")/)?.[0];
    const newsletter = html => html.match(/<section class="cmsng-newsletter"[\s\S]*?<\/section>/)?.[0];
    try {
        browser = await chromium.launch({ headless: true });
        const contexts = [];
        for (const width of [1440, 1920, 768, 375]) {
            const context = await browser.newContext({ viewport: { width, height: 1000 }, deviceScaleFactor: 1 });
            contexts.push({ width, context, page: await context.newPage() });
        }
        const measure = async (page, width, label) => {
            await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
            await page.evaluate(async () => {
                await Promise.race([document.fonts.ready, new Promise(r => setTimeout(r, 4000))]);
                const img = document.querySelector('.lam-home-hero-image');
                if (img) await img.decode().catch(() => {});
            });
            const m = await page.evaluate(() => {
                const e = s => document.querySelector(s);
                const box = s => { const r = e(s).getBoundingClientRect(); return [r.x,r.y,r.width,r.height]; };
                const img = e('.lam-home-hero-image'), form = e('.lam-home-search');
                return {
                    title: e('#lam-home-title').textContent.trim(),
                    description: e('.lam-home-intro').textContent.trim(),
                    image: img.getAttribute('src'), imageLoaded: img.complete && img.naturalWidth > 0,
                    imageFit: getComputedStyle(img).objectFit,
                    hero: box('.lam-home-hero'), titleBox: box('#lam-home-title'), introBox: box('.lam-home-intro'),
                    imageBox: box('.lam-home-hero-image'), formBox: box('.lam-home-search'),
                    overlay: getComputedStyle(e('.lam-home-hero'), '::before').backgroundColor,
                    action: form.action, method: form.method,
                    fields: [...form.elements].filter(c => c.name).map(c => ({name:c.name,value:c.value})),
                    formCount: e('.lam-home-hero').querySelectorAll('form').length,
                    nestedForms: form.querySelectorAll('form').length,
                    widgetCount: document.querySelectorAll('.lam-home-hero-widget').length,
                    noOverflow: document.documentElement.scrollWidth <= innerWidth,
                    formBelowCopy: form.getBoundingClientRect().top >= e('.lam-home-hero-content').getBoundingClientRect().bottom,
                    zoom: visualViewport.scale
                };
            });
            assert.equal(m.formCount, 1);
            assert.equal(m.nestedForms, 0);
            assert(m.noOverflow && m.formBelowCopy && m.imageLoaded);
            assert.equal(m.imageFit, 'cover');
            assert.equal(m.zoom, 1);
            assert.equal(m.method, 'get');
            await page.screenshot({ path: path.join(output, label + '-' + width + '.png'), fullPage: true });
            reports.push({ label, width, ...m });
            return m;
        };
        // Empty content with an admin label must still show the complete default.
        await save('');
        const fallback = new Map();
        for (const item of contexts) {
            const m = await measure(item.page, item.width, 'empty-widget-fallback');
            assert.equal(m.title, 'FIND YOUR DREAM JOBS');
            assert.equal(m.widgetCount, 0);
            fallback.set(item.width, m);
        }
        // The same HTML as the default must have the same geometry in a native widget.
        await save(sample.replace('THAY_URL_ANH_TU_THU_VIEN_VAO_DAY', state.hero_url));
        for (const item of contexts) {
            const m = await measure(item.page, item.width, 'widget-equivalent');
            const old = fallback.get(item.width);
            assert.equal(m.widgetCount, 1);
            for (const key of ['hero','titleBox','introBox','imageBox','formBox']) {
                assert(m[key].every((value,i) => Math.abs(value-old[key][i]) <= 1), key + ' layout differs at ' + item.width);
            }
            for (const key of ['action','method','fields','overlay']) assert.deepEqual(m[key],old[key]);
        }
        assert(state.images.length, 'A real Media Library image is required for the edited-widget test');
        const newTitle = 'TÌM CƠ HỘI VIỆC LÀM PHÙ HỢP VỚI BẠN';
        const newDescription = 'Thay đổi ảnh, tiêu đề và mô tả trực tiếp trong Custom HTML. Nội dung dài vẫn xuống dòng tự nhiên và form tìm việc ở phía dưới. '.repeat(6).trim();
        let edited = sample.replace('THAY_URL_ANH_TU_THU_VIEN_VAO_DAY', state.images[0])
            .replace('>FIND YOUR DREAM JOBS</h1>', '>'+newTitle+'</h1>')
            .replace(/<p class="lam-home-intro">[\s\S]*?<\/p>/, '<p class="lam-home-intro">'+newDescription+'</p>');
        await save(edited);
        for (const item of contexts) {
            const m = await measure(item.page, item.width, 'widget-edited');
            assert.equal(m.title, newTitle);
            assert.equal(m.description, newDescription);
            assert.equal(m.image, state.images[0]);
            assert.deepEqual(m.fields, fallback.get(item.width).fields);
        }
        const page = contexts[0].page;
        const keyword = (await page.locator('.lam-home-job-title a').first().textContent()).trim();
        await page.locator('[name="search_keywords"]').fill(keyword);
        await page.locator('[name="search_location"]').selectOption('');
        await Promise.all([
            page.waitForURL(u => u.searchParams.get('search_keywords') === keyword),
            page.locator('.lam-home-search-submit').click()
        ]);
        const searched = new URL(page.url());
        assert.equal(searched.searchParams.get('search_keywords'), keyword);
        assert.equal(searched.searchParams.get('search_location'), '');
        assert.equal(searched.hash, new URL(fallback.get(1440).action).hash);
        assert((await page.locator('.lam-home-job-title a').allTextContents()).some(t => t.trim() === keyword));
        const updated = await (await fetch(url)).text();
        assert.equal(career(updated),career(original), 'Career widget changed');
        assert.equal(newsletter(updated),newsletter(original), 'Newsletter markup changed');
        console.log('PASS: empty fallback, equivalent widget and edited image/title/long description at four viewports; native GET search; Career/newsletter unchanged.');
    } finally {
        try { await phpRun(restoreCode, backup); restored = true; }
        finally { if (browser) await browser.close(); }
        const after = await phpRun(snapshotCode);
        assert.deepEqual(after.sidebars, state.sidebars, 'Sidebar assignments were not restored');
        assert.deepEqual(after.widgets, state.widgets, 'Custom HTML settings were not restored');
        assert.deepEqual(after.blocks, state.blocks, 'Career/block content changed');
        assert.deepEqual(after.plugins, state.plugins, 'Plugin configuration changed');
        await fs.writeFile(path.join(output, 'results.json'), JSON.stringify({ restored, backup, cases: reports }, null, 2));
        console.log('PASS: all original sidebar, Custom HTML, Career/block and plugin settings restored.');
    }
})().catch(e => { console.error(e); process.exitCode = 1; });
