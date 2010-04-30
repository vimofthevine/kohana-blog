<h2><?php echo $legend; ?></h2>
<?php echo Form::open(); ?> 

<?php foreach ($category->inputs(FALSE) as $field=>$input): ?>
<?php echo isset($errors[$field]) ? '<p class="error">'.$errors[$field].'</p>' : ''; ?> 
<p>
	<?php echo $category->label($field); ?> 
	<?php echo $input; ?> 
</p>
<?php endforeach; ?>

<p class="submit">
	<?php echo Form::submit('submit', $submit); ?> 
</p>
<?php echo Form::close(); ?> 
