<?php

/**
 * Gán logo ảnh vào Site Identity (tuỳ chọn `site_logo`) để kiểm chứng header.
 *
 * Chạy: php tools/set-demo-logo.php <tên-file-trong-uploads>
 * Ví dụ: php tools/set-demo-logo.php logo-demo-440x120.png
 *
 * Ghi chú: chỉ cần thiết khi test tự động. Trên site thật, quản trị viên
 * upload logo qua Appearance > Customize > Site Identity.
 *
 * @package CMS_NhomG
 */

if ('cli' !== PHP_SAPI) {
	exit("Chỉ chạy được từ dòng lệnh.\n");
}

define('WP_USE_THEMES', false);
require dirname(__DIR__) . '/wp-load.php';

$file = isset($argv[1]) ? basename($argv[1]) : 'logo-demo-440x120.png';
$path = WP_CONTENT_DIR . '/uploads/' . $file;

if (! file_exists($path)) {
	exit("Không tìm thấy file: {$path}\n");
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

// Nếu ảnh đã được import trước đó thì dùng lại, tránh tạo trùng.
$existing = get_posts(
	array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_wp_attached_file',
		'meta_value'     => '2026/10/' . basename($file),
	)
);

if ($existing) {
	$attachment_id = (int) $existing[0];
} else {
	$attachment_id = media_handle_sideload(
		array('name' => $file, 'tmp_name' => $path),
		0,
		'Logo demo (test header)'
	);
	if (is_wp_error($attachment_id)) {
		exit('Lỗi import: ' . $attachment_id->get_error_message() . "\n");
	}
}

set_theme_mod('custom_logo', $attachment_id);

$src = wp_get_attachment_image_src($attachment_id, 'full');
printf(
	"OK attachment_id=%d %s (%dx%d)\nsite_logo=%s\n",
	$attachment_id,
	wp_basename($src[0]),
	$src[1],
	$src[2],
	get_theme_mod('custom_logo')
);
