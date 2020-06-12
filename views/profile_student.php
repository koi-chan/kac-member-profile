<?php
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

$furigana = get_furigana();
$grade = get_grade();

ob_start(); ?>
<div class="profile">
<h3><?= get_the_title($post->ID); ?> / <?= $furigana ?></h3>

<?php if(get_post_meta($post->ID, 'position', true) != 'なし'): ?>
<div class="position"><?= get_post_meta($post->ID, 'position', true); ?></div>
<?php endif; ?>

<div class="wp-block-image">
<figure class="alignright">
<?php if(get_post_meta($post->ID, 'picture', true)): ?>
<img class="thumbnail" src="<?= wp_get_attachment_url(get_post_meta($post->ID, 'picture', true)) ?>" alt="<?= get_the_title($post->ID); ?>" />
<?php else: ?>
<img class="thumbnail" src="<?= plugins_url('images/img_nophoto.jpg', dirname(__FILE__)) ;?>" alt="no photo" />
<?php endif; ?>
</figure>
</div><!-- .wp-block-image -->

<dl>
	<dt>学年</dt><dd><?= $grade ?>年生</dd>
	<dt>入部までの登山歴</dt><dd><?= get_post_meta($post->ID, 'history', true); ?>年</dd>
	<dt>登ったことのある、もしくは登ってみたい山</dt><dd><?= get_post_meta($post->ID, 'mountains', true); ?></dd>
	<dt>入部動機</dt><dd><?= get_post_meta($post->ID, 'reason', true); ?></dd>
	<dt>自己PR</dt><dd><?= get_post_meta($post->ID, 'pr', true); ?></dd>
	<dt>何か一言</dt><dd><?= get_post_meta($post->ID, 'words', true); ?></dd>
</dl>
</div><!-- .profile -->
<?php $output .= ob_get_clean(); ?>
