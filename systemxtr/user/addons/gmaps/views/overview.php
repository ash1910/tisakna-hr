<div class="box">
    <div class="tbl-ctrls">
        <?php if(isset($action_buttons) && !empty($action_buttons)):?>
            <fieldset class="tbl-search right">
                <?php foreach($action_buttons as $title => $link):?>
                    <a class="btn tn action " href="<?=$link?>"><?=lang($title)?></a>
                <?php endforeach;?>
            </fieldset>
        <?php endif?>
        <?php if(isset($title_page)):?><h1><?=$title_page?></h1><?php endif?>
        <?=ee('CP/Alert')->get(GMAPS_MAP.'_notice')?>
        <?=ee('View')->make('_shared/table')->render($table); ?>
        <?php if(isset($pagination)):?>
            <?=$pagination?>
        <?php endif;?>
    </div>
</div>

<?php
// Individual confirm delete modals
if(!empty($ids))
{
    foreach($ids as $id)
    {
        $modal_vars = array(
            'name'      => 'modal-confirm-' . $id['id'],
            'form_url'	=> $action_url,
            'hidden'	=> array(
                'delete'	=> $id['id']
            ),
            'checklist'	=> array(
                array(
                    'kind' => GMAPS_NAME,
                    'desc' => $id['msg'].' '.$id['id']
                )
            )
        );

        $modal = $this->make('ee:_shared/modal_confirm_remove')->render($modal_vars);
        ee('CP/Modal')->addModal($id['id'], $modal);
    }
}
?>