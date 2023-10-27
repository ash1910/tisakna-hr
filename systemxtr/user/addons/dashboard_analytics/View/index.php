<?php if(!empty($no_access)) : ?>

	<div class="box">
		<h1><?=$heading;?></h1>
		<div class="settings">
			<?=ee('CP/Alert')->get('da-error')?>
		</div>
	</div>

<?php else : ?>
	
	<?php if($token_status == 'empty' || $token_status == 'error') : ?>
		
		<div class="box da-profile-intro">
			<?php $this->embed('ee:_shared/form', $form_vars)?>
		</div>	
	
	<?php else : ?>
	
		<div class="box">
			<?php $this->embed('ee:_shared/form', $form_vars)?>
		</div>
		
	<?php endif; ?>

<?php endif; ?>