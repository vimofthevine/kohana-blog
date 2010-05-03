<h2><?php echo $article->title ?></h2>
<p>
	By <?php echo ucfirst($article->author->load()->username) ?>
	on <?php echo date('F jS Y \a\t g:s a', $article->date) ?>
</p>
<?php echo $article->text ?>
