<div class="ChannelFilesField cfix">
    <?php  if (isset($fields_location)==TRUE) {   ?> 
    <div  class="CFLocSettings" id="CFLocSettings" >
        <?php  
   /*    echo "<pre>";
    print_r($fields_location);
    echo "</pre>";
    die;*/
        foreach ($fields_location as $name => $settinglos) {
     //  echo $name."<br>";
        
           if ($name === 'local' || $name === 's3' || $name ==='cloudfiles' || $name ==='ftp' || $name ==='sftp'){
             echo '<div class="CFUpload_'.$name.'">';
               foreach ($settinglos as $name => $settingfieds) {
                $this->embed('ee:_shared/form/fieldset', array('name' => $name, 'settings' => $settingfieds ,'setting' => $settingfieds ,'group' => FALSE ));
               }
             echo '</div>';
           } else {
                if ($name === 0 ){
                     //echo '<div  class="cf_upload_type" >';  
                      $this->embed('ee:_shared/form/fieldset', array('name' => $name, 'settings' => $settinglos ,'setting' => $settinglos ,'group' => FALSE ));
                   //    echo "</div>";
                }else { 
                     $this->embed('ee:_shared/form/fieldset', array('name' => $name, 'settings' => $settinglos ,'setting' => $settinglos ,'group' => FALSE ));
                }
          }
      }
// die;
       ?>    
        
    </div> 
       <?php
    }
    ?>
    </div>