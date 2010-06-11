<h2>
	Changes for "<?php echo $article->title; ?>" (Ver <?php echo $ver1 ?>
	to Ver <?php echo $ver2 ?>)
	<small><?php echo HTML::anchor(Route::get('admin/blog')->uri(array(
		'controller'=>'article', 'action'=>'history', 'id'=>$article->id)), 'back') ?></small>
</h2>
<?php echo $diff; ?>
