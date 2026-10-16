<?php

/**
 * CMS_NhomG — tạo/cập nhật các trang WordPress cần thiết cho từng giao diện.
 *
 * Chạy 1 lần sau khi `git pull` trên máy mỗi thành viên:
 *     php tools/setup-pages.php
 *
 * Vì sao cần script này?
 *   Code (theme) đi theo git, nhưng TRANG (page) nằm trong DATABASE local.
 *   Nếu máy bạn chưa có trang "About", menu ABOUT trên navbar sẽ không có href
 *   -> bấm không được. Script này tự tạo/khôi phục các trang đó + gán template.
 *
 * An toàn: idempotent — chạy lại nhiều lần không tạo trùng, không ghi đè nội dung
 *          của trang đã tồn tại (trừ khi dùng --force).
 *
 * Cách dùng:
 *     php tools/setup-pages.php            # tạo trang còn thiếu, giữ nguyên trang có sẵn
 *     php tools/setup-pages.php --force    # gán lại template + menu cho TẤT CẢ
 *     php tools/setup-pages.php --list     # chỉ liệt kê, không thay đổi gì
 */

// Chạy được cả từ WP-CLI và từ dòng lệnh thường.
if (defined('WP_CLI') && WP_CLI) {
    require_once dirname(__DIR__) . '/wp-load.php';
} elseif (PHP_SAPI === 'cli') {
    require_once dirname(__DIR__) . '/wp-load.php';
} else {
    // Truy cập qua web: yêu cầu quyền quản trị.
    require_once dirname(__DIR__) . '/wp-load.php';
    if (! current_user_can('manage_options')) {
        wp_die('Bạn cần quyền quản trị để chạy script này.');
    }
}

$args  = isset($argv) ? array_slice($argv, 1) : array();
$force = in_array('--force', $args, true);
$list  = in_array('--list', $args, true);

/**
 * Danh sách các trang cần có cho mỗi giao diện.
 *
 * key        => dữ liệu trang
 *   slug      : đường dẫn (dùng cho get_page_by_path + menu fallback)
 *   title     : tiêu đề hiển thị
 *   template  : template file trong theme ('default' = mặc định)
 *   option    : option WP cần gán ID trang này (nếu có), ví dụ page_for_posts
 *   theme_mod : key Customizer cần gán ID trang này (nếu có)
 *   content   : nội dung khởi tạo (rỗng = để trống, admin tự điền)
 */
$pages = array(
    'about' => array(
        'slug'     => 'about',
        'title'    => 'About',
        'template' => 'templates/about.php',
        'theme_mod' => 'cmsng_page_about',
        'content'  => '',
    ),
    'contact' => array(
        'slug'     => 'contact',
        'title'    => 'Contact',
        'template' => 'templates/contact.php',
        'theme_mod' => 'cmsng_page_contact',
        'content'  => '',
    ),
    'news' => array(
        'slug'     => 'news',
        'title'    => 'NEWS',
        'template' => 'default', // page-news.php có thể nhận qua slug, giữ mặc định cho an toàn
        'theme_mod' => 'cmsng_page_news',
        'content'  => '',
    ),
    'jobs' => array(
        'slug'     => 'jobs',
        'title'    => 'JOBS',
        'template' => 'default',
        'theme_mod' => 'cmsng_page_jobs',
        'content'  => '',
    ),
);

$created = array();
$updated = array();
$skipped = array();

foreach ($pages as $key => $conf) {
    $existing = get_page_by_path($conf['slug']);

    if ($existing && 'page' === $existing->post_type) {
        $page_id = (int) $existing->ID;
        if ($list) {
            $skipped[] = sprintf('%s (ID %d) — đã tồn tại', $conf['slug'], $page_id);
            continue;
        }
        if ($force) {
            $updated[] = sprintf('%s (ID %d) — cập nhật', $conf['slug'], $page_id);
        } else {
            $skipped[] = sprintf('%s (ID %d) — đã tồn tại (bỏ qua)', $conf['slug'], $page_id);
        }
    } else {
        if ($list) {
            $created[] = sprintf('%s — SẼ TẠO', $conf['slug']);
            continue;
        }
        $page_id = wp_insert_post(array(
            'post_title'   => $conf['title'],
            'post_name'    => $conf['slug'],
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => isset($conf['content']) ? $conf['content'] : '',
        ), true);

        if (is_wp_error($page_id)) {
            echo "  [LỖI] Không tạo được '{$conf['slug']}': " . $page_id->get_error_message() . "\n";
            continue;
        }
        $page_id   = (int) $page_id;
        $created[] = sprintf('%s (ID %d) — đã tạo', $conf['slug'], $page_id);
    }

    // Gán template (chỉ khi khác 'default').
    if ('default' !== $conf['template']) {
        update_post_meta($page_id, '_wp_page_template', $conf['template']);
    }

    // Gán Customizer (cmsng_page_*) để menu trỏ thẳng, không cần dò slug.
    if (! empty($conf['theme_mod'])) {
        set_theme_mod($conf['theme_mod'], $page_id);
    }

    // Gán option WP (ví dụ page_for_posts cho trang News).
    if (! empty($conf['option'])) {
        update_option($conf['option'], $page_id);
    }
}

// ---- Kết quả ----
echo "\n=== CMS_NhomG — Thiết lập trang ===\n";
if ($list) {
    echo "(chế độ --list: không thay đổi gì)\n";
}
if ($created) {
    echo "\nĐã tạo mới:\n";
    foreach ($created as $line) {
        echo '  + ' . $line . "\n";
    }
}
if ($updated) {
    echo "\nĐã cập nhật:\n";
    foreach ($updated as $line) {
        echo '  * ' . $line . "\n";
    }
}
if ($skipped) {
    echo "\nBỏ qua (đã có sẵn):\n";
    foreach ($skipped as $line) {
        echo '  - ' . $line . "\n";
    }
}
if (! $created && ! $updated) {
    echo "\nKhông có thay đổi. Mọi trang cần thiết đã tồn tại.\n";
}

echo "\n=== Kiểm tra link menu ===\n";
foreach (array('home', 'jobs', 'news', 'about', 'contact') as $section) {
    if (function_exists('cmsng_section_url')) {
        $url = cmsng_section_url($section);
        printf("  %-8s -> %s\n", $section, $url ? $url : '(TRỐNG — trang chưa có!)');
    }
}
echo "\nXong. Nếu 'about' báo (TRỐNG), hãy chắc rằng trang About đã publish và template là About Us.\n";
