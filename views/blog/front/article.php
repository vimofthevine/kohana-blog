<h2><?php echo $article->title ?></h2>
<p>
	By <?php echo ucfirst($article->author->load()->username) ?> 
	on <?php echo $article->verbose('date') ?> 
</p>
<?php echo $article->text ?>

<?php echo $comment_form ?> 
<?php echo $comment_list ?> 
