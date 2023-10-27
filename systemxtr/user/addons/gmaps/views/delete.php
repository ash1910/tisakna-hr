<div class="box">
	<h1>Delete cache</h1>
	<form class="settings">
		<?=ee('CP/Alert')->get(GMAPS_MAP.'_delete_confirm')?>

		<?php if($url != ''):?>
			<a href="<?=$url?>" class="btn">Yes i`m sure</a>
		<?php endif;?>
	</form>
</div>