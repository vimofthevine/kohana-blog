<h2><?php echo $legend ?></h2>

<?php foreach ($articles as $article): ?>
<div>
	<h2><?php echo HTML::anchor($article->permalink, $article->title) ?></h2>
	<p>
		<strong>Name : </strong> <?php echo $article->author->load()->username ?><br />
		<strong>Date : </strong> <?php echo $article->verbose('date') ?><br />
	</p>
</div>
<?php endforeach; ?>

