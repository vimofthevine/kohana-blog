<h2>Delete Post <?php echo $article->title ?>?</h2>
<p>
	Are you sure you want to delete the article, <?php echo $article->title ?>?
	This action cannot be undone.
</p>
<p>
	All comments belonging to this article will be deleted.
</p>
<?php
	echo Form::open();
	echo Form::submit('yes', 'Yes');
	echo Form::submit('no', 'No');
	echo Form::close();
?>
