<?php

/**
 * Cập nhật nội dung trang About sang tiếng Anh (một lần).
 *
 * Vì sao cần script này?
 *   Giá trị About trong Customizer được lưu vào option `theme_mods_jobscout`.
 *   Khi option đã tồn tại, WordPress LUÔN dùng giá trị đã lưu và bỏ qua default
 *   trong inc/about.php. Nếu chỉ sửa file PHP, trang About vẫn hiện nội dung cũ.
 *
 * Cách chạy (tại thư mục gốc dự án):
 *   php tools/update-about-english.php
 *
 * Script chỉ ghi các key `nhomg_about_*` (không đụng setting khác, không đụng
 * ảnh Customizer, không đụng page/menu). Chạy lại nhiều lần vẫn an toàn.
 *
 * @package CMS_NhomG
 */

if ('cli' !== PHP_SAPI) {
	exit("Chỉ chạy được từ dòng lệnh.\n");
}

define('WP_USE_THEMES', false);
require dirname(__DIR__) . '/wp-load.php';

/* Nội dung tiếng Anh cần ghi vào theme mods. */
$content = array(
	'nhomg_about_title'         => "SHARING \"OMOTENASHI\"\nWITH THE WORLD",
	'nhomg_about_hero_subtitle' => 'Japanese hospitality, brought to life in hotels and restaurants across the globe.',
	'nhomg_about_heading'       => 'WHO WE ARE',
	'nhomg_about_vision_title'  => 'Our Vision',
	'nhomg_about_vision_text'   => 'We build hotels and restaurants around the world that guests remember long after they check out, and that leave every partner, teammate and community better than we found them.',
	'nhomg_about_mission_title' => 'Our Mission',
	'nhomg_about_mission_text'  => 'To make Japanese hospitality the standard the world measures itself against.',
	'nhomg_about_value_title'   => 'Our Core Value',
	'nhomg_about_value_text'    => "\"If I were the guest...\"\nWe serve the way we would want to be served, anticipating the smallest need before it is ever spoken.",
	'nhomg_about_info_heading'  => 'Company Info',
	'nhomg_about_established'   => 'April 1993',
	'nhomg_about_head_office'   => 'Marunouchi 2-1-1, Chiyoda, Tokyo',
	'nhomg_about_capital'       => '100,000,000 JPY',
	'nhomg_about_ceo'           => 'Yutaka Noda',
	'nhomg_about_employees'     => 'Full time: 830 / Total: 1,600',
	'nhomg_about_what_heading'  => 'Hotels, restaurants, banquets and weddings — crafted end to end',
	'nhomg_about_what_text'     => 'We stay with a project from the first sketch to the final bell. Concept, design, build, staffing and day-to-day management all sit under one roof, so the promise made on paper is still the promise kept on opening night.',
	'nhomg_about_what_text2'    => 'What we create is never generic. We read each city, each neighbourhood and each guest, then design the details that make a place feel like it could belong nowhere else. The result is service that feels warm rather than formal, attentive rather than intrusive — and a genuine reason to come back.',
);

$theme  = wp_get_theme();
$target = $theme->get_stylesheet();
$mods   = get_option('theme_mods_' . $target);

if (! is_array($mods)) {
	$mods = array();
}

$before = array();
$after  = array();

foreach ($content as $key => $value) {
	$before[$key] = isset($mods[$key]) ? (string) $mods[$key] : '(chưa có)';
	$mods[$key]   = $value;
	$after[$key]  = $value;
}

$saved = update_option('theme_mods_' . $target, $mods);

echo "Theme: {$target}\n";
echo 'Option theme_mods_' . $target . ': ' . ($saved ? 'đã cập nhật' : 'không thay đổi (giá trị giống nhau)') . "\n\n";

printf("%-28s %s\n", 'KEY', 'GIÁ TRỊ TRƯỚC -> SAU');
foreach ($after as $key => $value) {
	printf(
		"%-28s %s\n    -> %s\n",
		$key,
		wp_trim_words(str_replace("\n", ' ', $before[$key]), 10, '…') ? wp_trim_words(str_replace("\n", ' ', $before[$key]), 10, '…') : '""',
		wp_trim_words(str_replace("\n", ' ', $value), 12, '…')
	);
}

echo "\nXong. Mở http://cms-nhomg.local/about/ để kiểm tra.\n";
