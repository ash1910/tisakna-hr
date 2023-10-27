<?= form_open($post_url, array('id' => 'store_datatable_search')) ?>
    <fieldset class="store_table_fields">
        <div class="store_datatable_field_long">
            <?= lang('store.search', 'keywords') ?>
            <?= form_input('keywords', $search['keywords']) ?>
        </div>
        <div class="store_datatable_field">
            <?= lang('results_per_page', 'per_page') ?>
            <?= form_dropdown('per_page', $per_page_select_options, $per_page) ?>
        </div>
    </fieldset>
<?= form_close(); ?>

<div class="container-fluid container-paddingtb">

<?= form_open($post_url, array('id' => 'store_datatable')) ?>
    <?= $table_html ?>
    <?php //print_r($pagination_links);?>
	
  <div class="paginate">
	 <ul>
	 <?php 
		$class='';
		if(isset($pagination_links) && sizeof($pagination_links)>1){
			foreach($pagination_links as  $key=>$val){ 
				if(is_array($val)){
				foreach($val as $main) { 
					if($key!='page' && isset($main['pagination_url'])){
					?>				
						<li><a href="<?php echo $main['pagination_url'];?>"><?php echo $main['text'];?></a></li>
					<?php 
					}
					else if(isset($main['pagination_url'])){
						if($main['current_page']==$main['pagination_page_number'])	$class='act';
						else $class='';						
					?>				
						<li><a href="<?php echo $main['pagination_url'];?>" class="<?php echo $class; ?>"><?php echo $main['pagination_page_number'];?></a></li>
					<?php	
					}
				}
				}
			}
		}
	?>	
	</ul>
   </div>
<?= form_close() ?>

</div>