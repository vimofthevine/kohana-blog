<h2>Delete Category <?php echo $category->name ?>?</h2>
<p>
	Are you sure you want to delete the category, <?php echo $category->name ?>?
	This action cannot be undone.
</p>
<p>
	All posts belonging to this category will become uncategorized.
</p>
<?php
	echo Form::open();
	echo Form::submit('yes', 'Yes');
	echo Form::submit('no', 'No');
	echo Form::close();
?>
