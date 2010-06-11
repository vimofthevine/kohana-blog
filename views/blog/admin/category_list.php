<h2><?php echo __('Category List') ?></h2>

<?php if (count($categories) == 0): ?>
<p>
	There are no categories at this time
	(<?php echo HTML::anchor($request->uri(
		array('action'=>'new')), 'create one') ?>).
</p>
<?php else:
	// Create category list
	$grid = new Grid;
	$grid->column()->field('id')->title('ID');
	$grid->column()->field('name')->title('Name');
	$grid->column('action')->title('Actions')->text('Edit')->class('edit')
		->route($request)->params(array('action'=>'edit'));
	$grid->column('action')->title('')->text('Delete')->class('delete')
		->route($request)->params(array('action'=>'delete'));
	$grid->data($categories);

	echo $grid->render();

endif;

