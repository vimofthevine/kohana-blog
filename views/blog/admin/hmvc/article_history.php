<h2>"<?php echo $article->title; ?>" History
	<small><?php echo HTML::anchor(Request::instance()->uri(array('action'=>'list', 'id'=>NULL)), 'back') ?></small>
</h2>
<?php
    echo form::open();
    echo $grid;
    echo form::close();
?>
