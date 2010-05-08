<div class="box">
	<h2>Blog Management</h2>
	<p>
		<ul>
			<li>Manage
				<ul>
					<li><?php echo HTML::anchor(Route::get('admin_blog')->uri(array('controller'=>'article', 'action'=>'list')), 'Articles') ?></li>
					<li><?php echo HTML::anchor(Route::get('admin_blog')->uri(array('controller'=>'category', 'action'=>'list')), 'Categories') ?></li>
					<li><?php echo HTML::anchor(Route::get('admin_blog')->uri(array('controller'=>'tag', 'action'=>'list')), 'Tags') ?></li>
					<li><?php echo HTML::anchor(Route::get('admin_blog')->uri(array('controller'=>'comment', 'action'=>'list')), 'Comments') ?></li>
				</ul>
			</li>
<?php if (isset($links)): ?>
			<li>Quick Links
				<ul>
<?php foreach($links as $text=>$link): ?>
					<li><?php echo HTML::anchor($link, $text) ?></li>
<?php endforeach; ?>
				</ul>
			</li>
<?php endif; ?>
		</ul>
	</p>
</div>
