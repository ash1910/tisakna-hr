<div class="col-group align-right">
    <div class="col w-12">
        <div class="box">
            <h1>Add Settings</h1>
            <?=form_open(ee('CP/URL', 'addons/settings/store_sending_email_before_payment') ,'class="settings"');?>

            <fieldset class="col-group required last">
                <div class="setting-txt col w-8">
                    <h3><?php echo lang("payment_method");?></h3>
                </div>
                <div class="setting-field col w-8 last">
                <?php
                    // fields select
                    echo form_multiselect('payment_method[]', $payment_methods, @$current['payment_method'],'style="min-width:150px;min-height:100px;"');		
                ?>
                </div>
            </fieldset>


            <fieldset class="form-ctrls"><?=form_submit('submit', lang('submit'), 'class="submit btn"')?></p></fieldset>

            <?=form_close()?>

        </div>
    </div>
</div>