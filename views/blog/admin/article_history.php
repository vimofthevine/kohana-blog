<h2><?php echo __('":title" History', array(':title'=>$article->title)) ?> 
	<small><?php echo HTML::anchor($request->uri(
		array('action'=>'list', 'id'=>NULL)), 'back') ?></small>
</h2>
<?php
    echo form::open();

	echo '<p class="submit">';
	echo Form::submit('submit', __('View Diff'));
	echo '</p>';

	// Create revision list
	$grid = new Grid;
	$grid->column('radio')->field('version')->title('Version 1')->name('ver1');
	$grid->column('radio')->field('version')->title('Version 2')->name('ver2');
	$grid->column()->field('version')->title('Revision');
	$grid->column()->field('editor')->title('Editor');
	$grid->column('date')->field('date')->title('Date');
	$grid->column()->field('comment_list')->title('Comments');
	$grid->data($article->revisions);

    echo $grid;
    echo form::close();
?>
