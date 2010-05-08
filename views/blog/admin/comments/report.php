<h2><?php echo $legend ?></h2>

<?php foreach ($comments as $comment): ?>
<div class="box">
	<h2>Comment #<?php echo $comment->id ?></h2>
	<p>
		<strong>Classification : </strong> <?php echo ucfirst($comment->state) ?><br />
		<strong>Name : </strong> <?php echo $comment->name ?><br />
		<strong>Email : </strong> <?php echo $comment->email ?><br />
		<strong>URL : </strong> <?php echo $comment->url ?><br />
		<strong>Date : </strong> <?php echo date('F jS, Y', $comment->date) ?><br />
	</p>
	<p><?php echo $comment->text ?></p>
</div>
<?php endforeach ?>
