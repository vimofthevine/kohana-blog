<h2><?php echo $legend ?></h2>

<?php foreach ($comments as $comment): ?>
<div>
	<h2>To <?php echo HTML::anchor($comment->parent->load()->permalink, $comment->parent->title) ?></h2>
	<p>
		<strong>Name : </strong> <?php echo $comment->name ?><br />
		<strong>Date : </strong> <?php echo $comment->verbose('date') ?><br />
	</p>
	<p><?php echo strip_tags(Text::limit_words($comment->text, 15, '...')) ?></p>
</div>
<?php endforeach; ?>

