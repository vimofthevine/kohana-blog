<h2><?php echo $legend; ?></h2>
<?php echo Form::open(); ?> 

<?php echo isset($errors['name']) ? '<p class="error">'.$errors['name'].'</p>' : ''; ?> 
<p>
	<?php echo $tag->label('name'); ?> 
	<?php echo $tag->input('name'); ?> 
</p>

<p class="submit">
	<?php echo Form::submit('submit', $submit); ?> 
</p>
<?php echo Form::close(); ?> 
