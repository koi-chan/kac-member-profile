<?php
$furigana = $this->get_furigana();
$grade = $this->get_grade();

ob_start(); ?>
<div class="profile-container">
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

<div class="profile">
<h4>学年</h4><p><?= $grade ?>年生</p>
<h4>出身中学</h4><p><?= get_post_meta($post->ID, 'juniorhigh', true); ?></p>
<h4>入部までの登山歴</h4><p><?= get_post_meta($post->ID, 'history', true); ?>年</p>
<h4>登ったことのある、もしくは登ってみたい山</h4><p><?= get_post_meta($post->ID, 'mountains', true); ?></p>
<h4>入部動機</h4><p><?= get_post_meta($post->ID, 'reason', true); ?></p>
<h4>自己PR</h4><p><?= get_post_meta($post->ID, 'pr', true); ?></p>
<h4>何か一言</h4><p><?= get_post_meta($post->ID, 'words', true); ?></p>
</div><!-- .profile -->

</div><!-- .profile-container -->
<?php $output .= ob_get_clean(); ?>
