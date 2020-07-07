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
add_action('init', array($kacmp, 'register_cpt_member_profile'));
add_action('the_content', array($kacmp, 'rewrite_post_content'));

class KAC_Member_Profile {
	private $student_positions = array('主将', '副将', '主務', '副務', 'なし');
	private $staff_positions = array('部長', '副部長', 'ヘッドコーチ', 'コーチ', '監督');

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
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capability_type' => 'post'
		);
		register_post_type('member_profile', $args);

		add_theme_support('post-thumbnails', array('member_profile'));
		set_post_thumbnail_size(200, 200, true);
	}

	public function rewrite_post_content($content) {
		global $post;

		if($post->post_type === 'member_profile') {
			return "[member_profile]";
		}

		return $content;
	}

	public function __construct() {
		add_shortcode('member_profiles', array($this, 'output_member_profiles_handler'));
		add_shortcode('member_profile', array($this, 'output_member_profile_handler'));
		add_action('wp_enqueue_scripts', array($this, 'register_styles_handler'), 9999);
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

		// 役職付きの生徒→役職無しの生徒→先生・コーチの順番
		foreach(array_merge($this->student_positions, $this->staff_positions) as $value) {
			$query_args['meta_query']['p']['value'] = $value;
			$output .= $this->wp_loop($query_args);
		}

		return $output;
	}

	// 指定された人の紹介だけを表示する
	public function output_member_profile_handler() {
		return $this->personal_profile();
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

	// 経歴・主な山行歴をマークアップする
	// param [String] $original カスタムフィールドのデータ
	// return [String] HTML
	function markup_histories($original) {
		$orig_array = array_values(
			array_filter(
				array_map('trim', explode("\n", $original)),
				'strlen'
			)
		);

		$markuped_array = [['', array()]];
		foreach($orig_array as $line) {
			$exploded_line = explode(':', $line, 2);
			if(count($exploded_line) == 2) {
				$markuped_array[] = array(
					$exploded_line[0],
					array($exploded_line[1])
				);
			} else {
				$markuped_array[array_key_last($markuped_array)][1][] = $exploded_line[0];
			}
		}

		if($markuped_array[0][0] == '' && count($markuped_array[0][1]) == 0) {
			array_shift($markuped_array);
		}
		$markuped = '<dl class="histories">';
		foreach($markuped_array as $line) {
			if($line[0] == '') {
				$markuped .= '<dd>';
			} else {
				$markuped .= "<dt>{$line[0]}</dt><dd>";
			}
			$markuped .= implode('<br />', $line[1]);
			$markuped .= '</dd>';
		}
		return $markuped . '</dl>';
	}

	function get_furigana() {
		global $post;
		$furigana = '';
		foreach(array_reverse(explode('-', $post->post_name)) as $value) {
			$furigana .= ucfirst(strtolower($value)) . ' ';
		}
		return $furigana;
	}

	// 学年を入学年度・留年歴から計算する
	// return [Integer]
	function get_grade() {
		global $post;

		// 強制出力学年の確認
		$grade = get_post_meta($post->ID, 'grade', true);
		if(!is_numeric($grade)) {
			return $grade;
		}
		if($grade > 0 && $grade < 3) {
			return $grade;
		} elseif($grade != 0) {
			return -2;
		}

		// 入学年度・留年歴から計算する
		$today = getdate();
		$enter_year = get_post_meta($post->ID, 'enter_year', true);
		if(!is_numeric($enter_year) || $enter_year > $today['year']) {
			return -3;
		}
		$stay_count = get_post_meta($post->ID, 'stay_count', true);
		if(!is_numeric($stay_count) || $stay_count < 0 || $stay_count > 3) {
			return -4;
		}

		if($today['mon'] < 4) {
			$grade = $today['year'] - $enter_year;
		} else {
			$grade = $today['year'] - $enter_year + 1;
		}

		return $grade - $stay_count;
	}

}
?>
