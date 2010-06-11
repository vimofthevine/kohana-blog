<h2><?php echo __('Tag List') ?></h2>

<?php if (count($tags) == 0): ?>
<p>
	There are no tags at this time
	(<?php echo HTML::anchor($request->uri(
		array('action'=>'new')), 'create one') ?>).
</p>
<?php else:
	// Create tag list
	$grid = new Grid;
	$grid->column()->field('id')->title('ID');
	$grid->column()->field('name')->title('Name');
	$grid->column('action')->title('Actions')->text('Edit')->class('edit')
		->route($request)->params(array('action'=>'edit'));
	$grid->column('action')->title('')->text('Delete')->class('delete')
		->route($request)->params(array('action'=>'delete'));
	$grid->data($tags);

	echo $grid->render();

endif;

