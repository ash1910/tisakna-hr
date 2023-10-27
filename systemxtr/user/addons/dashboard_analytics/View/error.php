<div>
<?php if($this->enabled('realtime')) : ?>
	<div class="da-realtime-inner">
		<p class="da-error"><?=$error;?></p>
	</div>
<?php endif; ?>

<?php if($this->enabled('monthly')) : ?>
	<div class="da-monthly-inner">
		<p class="da-error"><?=$error;?></p>
	</div>
<?php endif; ?>
</div>