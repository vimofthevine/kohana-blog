<h2><?php echo $legend ?></h2>
<?php if (count($articles) == 0): ?>
<p>
	There are no posts as this time
	(<?php echo HTML::anchor(Route::$current
		->uri(array('action'=>'new')), 'create one') ?>).
</p>
<?php else:
	// Create article list
	$grid = new Grid;
	$grid->column()->field('id')->title('ID');
	$grid->column()->field('title')->title('Title');
	$grid->column()->field('state')->title('State');
	$grid->column('action')->title('Actions')->text('Edit')->class('edit')
		->route($request)->params(array('action'=>'edit'));
	$grid->column('action')->title('')->text('History')->class('history')
		->route($request)->params(array('action'=>'history'));
	$grid->data($articles);

	echo $pagination;
	echo $grid->render();
	echo $pagination;

endif;

