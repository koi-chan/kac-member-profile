<div class="wrap">
<h2>部員紹介</h2>
<form method="post" action="options.php">
<?php
settings_fields('kacmp');
do_settings_sections('kacmp');
?>
<table>
<tbody>
<tr>
	<th>氏名</th>
	<td><input type="text" name="name" value="" /></td>
</tr>
</table>
<?php submit_button(); ?>
</form>
</div><!-- .wrap -->
