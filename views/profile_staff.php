<?php
$furigana = '';
foreach(array_reverse(explode('-', $post->post_name)) as $value) {
	$furigana .= ucfirst(strtolower($value)) . ' ';
}
ob_start(); ?>
<div class="profile">
<h3><?= get_the_title($post->ID); ?> / <?php
foreach(array_reverse(explode('-', $post->post_name)) as $value) {
	echo ucfirst(strtolower($value)) . ' ';
}?></h3>

<div class="position"><?= get_post_meta($post->ID, 'position', true); ?></div>

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
	<dt>経歴</dt>
	<dd><?= get_post_meta($post->ID, 'histories', true); ?></dd>
</dl>
</div><!-- .profile -->
<?php $output .= ob_get_clean(); ?>
