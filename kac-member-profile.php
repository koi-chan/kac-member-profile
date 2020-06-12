<?php
/*
 * Plugin Name:		KAC Member Profile
 * Plugin URI:		
 * Description:		make member profile page
 * Version:		0.0.1
 * Author:		koi-chan
 * Author URI:		https://twitter.com/koichan779
 * License:		GPL2
 */

// 他のプラグインで定義されていたらエラー終了する
if(class_exists('KAC_Member_Profile') || class_exists('KACMP_INSTALL')) {
}

$kacmp = new KAC_Member_Profile();
//add_action('admin_menu', array($kacmp, 'add_plugin_menu_handler'));
add_action('init', array($kacmp, 'register_cpt_member_profile'));

class KAC_Member_Profile {
	private $staff_positions = array('部長', '副部長', 'ヘッドコーチ', 'コーチ', '監督');

	public function add_plugin_menu_handler() {
		add_menu_page(
			'部員紹介',
			'部員紹介',
			'manage_options',
			'kacmp',
			array($this, 'display_plugin_main_menu'),
			'',
			40,
		);
		register_setting(
			'kacmp',
			'name'
		);

		add_submenu_page(
			'kacmp',
			'新規部員紹介を追加',
			'新規追加',
			'manage_options',
			'kacmp-list',
			array($this, 'display_plugin_sub_menu_new')
		);
	}

	function display_plugin_main_menu() {
		include_once('views/list.php');
		wp_enqueue_style('kacmp', plugins_url('styles/menu.css'));
	}
	function abc_validation($input) {
	}

	function display_plugin_sub_menu_new() {
		include_once('views/new.php');
		wp_enqueue_style('kacmp', plugins_url('styles/menu.css'));
	}

	// カスタム投稿タイプを定義する
	public function register_cpt_member_profile() {
		$labels = array(
			'name' => __('部員紹介', 'member_profiles'),
			'singular_name' => __('部員紹介', 'member_profile'),
			'add_new' => __('新規追加', 'new_member_profile'),
			'add_new_item' => __('部員を追加する', 'member_profile'),
			'edit_item' => __('部員紹介編集', 'edit_member_profilen'),
			'new_item' => __('新規', 'member_profile'),
			'view_item' => __('閲覧', 'member_profile'),
			'search_items' => __('部員検索', 'member_profile'),
			'not_found' => __('部員が見つかりません', 'member_profile'),
			'not_found_in_trash' => __('ゴミ箱にはありません', 'member_profile'),
			'parent_item_colon' => __('親メンバー', 'member_profile'),
			'menu_name' => __('部員紹介', 'member_profile'),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => true,
			'supports' => array(
				'title',
				'thumbnail',
				'custom-fields',
				'author',
				'revisions'
			),
			'taxonomies' => array(),

			'public' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,

			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'has_archive' => true,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capability_type' => 'post'
		);
		register_post_type('member_profile', $args);

		add_theme_support('post-thumbnails', array('member_profile'));
		set_post_thumbnail_size(200, 200, true);
	}

	public function __construct() {
		add_shortcode('member_profiles', array($this, 'output_member_profiles_handler'));
		add_shortcode('member_profile', array($this, 'output_member_profile_handler'));
		add_action('wp_enqueue_scripts', array($this, 'register_styles_handler'));
	}

	public function register_styles_handler() {
		wp_enqueue_style('kacmp_profile', plugin_dir_url(__FILE__) . 'styles/profile.css'); 
	}

	// 全員の紹介を表示する
	public function output_member_profiles_handler() {
		$output = '';

		$query_args = array(
			'nopaging' => true,
			'posts_per_page' => -1,
			'post_type' => 'member_profile',
			'post_status' => 'publish',
			'orderby' => array(
				'e' => 'ASC',
			),
			'meta_query' => array(
				'p' => array(
					'key' => 'position',
					'value' => ''
				),
				'e' => array(
					'key' => 'enter_year',
					'compare' => 'EXISTS',
					'type' => 'NUMERIC'
				),
			)
		);

		// 役職付きの生徒
		foreach(array('主将', '副将', '主務', '副務') as $value) {
			$query_args['meta_query']['p']['value'] = $value;
			$output .= $this->wp_loop($query_args);
		}

		// 役職無しの生徒
		$query_args['meta_query']['p']['value'] = 'なし';
		$output .= $this->wp_loop($query_args);

		// 先生・コーチ
		foreach($this->staff_positions as $value) {
			$query_args['meta_query']['p']['value'] = $value;
			$output .= $this->wp_loop($query_args);
		}

		return $output;
	}

	// 指定された人の紹介だけを表示する
	public function output_member_profile_handler() {
		$escaped_atts = shortcode_atts(array('id' => null), $atts);
		return $this->output_member_profile($escaped_atts['id']);
	}

	function output_member_profile($target = null) {
		$ids = $this->get_target_ids($target);
		return '<p>output!!</p>';
	}

	// 条件から記事を検索し、表示する HTML を返す
	// $query_args [Array] 検索条件
	// return [String]
	function wp_loop($query_args) {
		$output = '';

		$loop = new WP_Query($query_args);
		if($loop->have_posts()) {
			while($loop->have_posts()) {
				$loop->the_post();
				$output .= $this->personal_profile();
			}
		}

		wp_reset_postdata();
		return $output;
	}

	// 1人分のプロフィールを、view で整形して返す
	// return [String] HTML
	function personal_profile() {
		global $post;
		$output = '';

		if(in_array(get_post_meta($post->ID, 'position', true), $this->staff_positions)) {
			// 教員・コーチ用プロファイル view
			include('views/profile_staff.php');
		} else {
			include('views/profile_student.php');
		}

		return $output;
	}
}
?>
