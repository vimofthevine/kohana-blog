<h2><?php echo $legend ?></h2>
<?php echo $pagination->render() ?> 

<?php foreach ($articles as $article): ?>
	<article>
		<header>
			<h3><?php echo HTML::anchor( Route::get('blog_permalink')->uri(array(
				'year'=>$article->year,
				'month'=>$article->month,
				'day'=>$article->day,
				'slug'=>$article->slug)), $article->title) ?></h3>
			<p>
				by <?php echo ucfirst($article->author->load()->username) ?> on
				<time datetime="<?php echo $article->date ?>">
					<?php echo date('F jS, Y \a\t g:s a', $article->date) ?>
				</time><br />

				Posted to <?php echo HTML::anchor( Route::get('blog_filter')->uri(array(
					'action'=>'category', 'name'=>$article->category->load()->name)),
					ucfirst($article->category->name)) ?><br />
				Tagged in 
<?php foreach ($article->tags as $tag): ?>
					<?php echo HTML::anchor( Route::get('blog_filter')->uri(array(
						'action'=>'tag', 'name'=>$tag->name)), ucfirst($tag->name)) ?>
<?php endforeach; ?>
			</p>
		</header>
		<p><?php echo Text::limit_words($article->text,100,'...') ?></p>
	</article>
<?php endforeach; ?>

<?php echo $pagination->render() ?> 
