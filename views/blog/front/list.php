<h2><?php echo $legend ?></h2>
<?php if (count($articles) == 0): ?>
<p>
	There are no articles that have been published.
</p>
<?php else: ?>
<?php echo $pagination->render() ?> 

<?php foreach ($articles as $article): ?>
	<article>
		<header>
			<h3><?php echo HTML::anchor($article->permalink, $article->title) ?></h3>
			<p>
				by <?php echo ucfirst($article->author->load()->username) ?> on
				<time datetime="<?php echo $article->date ?>">
					<?php echo $article->verbose('date') ?> 
				</time><br />

				Posted to <?php echo HTML::anchor($article->category_link,
					ucfirst($article->category->name)) ?><br ?>
				Tagged in <?php echo $article->tag_list ?> 
			</p>
		</header>
		<p><?php echo $article->excerpt ?></p>
	</article>
<?php endforeach; ?>

<?php echo $pagination->render() ?> 

<?php endif; ?>

