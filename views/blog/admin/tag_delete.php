<h2><?php echo __('Delete Tag :name?', array(':name'=>$tag->name)) ?></h2>
<p>
	Are you sure you want to delete the tag, <?php echo $tag->name ?>?
	This action cannot be undone.
</p>
<p>
	This tag will be removed from all posts containing this tag.
</p>
<?php
	echo Form::open();
	echo Form::submit('yes', 'Yes');
	echo Form::submit('no', 'No');
	echo Form::close();
?>
