<?php
	echo Form::open(), PHP_EOL;
	echo '<p>', PHP_EOL;
	echo Form::hidden('classify_id', $id), PHP_EOL;

	$options = array('nop' => 'Do nothing');
	if ( ! $is_ham)
	{
		$options += array(
			'learn_ham'    => 'Approve more comments like this',
			'unlearn_spam' => 'Comments like these aren\'t spam',
		);
	}
	if ( ! $is_spam)
	{
		$options += array(
			'learn_spam'   => 'Mark more comments like this as spam',
			'unlearn_ham'  => 'Comments like these are spam',
		);
	}
	echo Form::label('classify_option', 'Classification Options: '), PHP_EOL;
	echo Form::select('classify_option', $options, 'nop'), PHP_EOL;

	if ( ! $is_ham)
	{
		echo Form::submit('classify_ham', __('Approve')), PHP_EOL;
	}

	if ( ! $is_spam)
	{
		echo Form::submit('classify_spam', __('Mark as Spam')), PHP_EOL;
	}

	echo HTML::anchor( Request::instance()->uri(array(
		'action' => 'edit',
		'id'     => $id,
	)), Form::submit('edit', __('Edit')) ), PHP_EOL;

	echo HTML::anchor( Request::instance()->uri(array(
		'action' => 'delete',
		'id'     => $id,
	)), Form::submit('delete', __('Delete')) ), PHP_EOL;

	echo '</p>', PHP_EOL;
	echo Form::close();
?> 
