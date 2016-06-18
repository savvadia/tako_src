<?php 
/**
 * @total-module  Seo Friendly Urls
 * @author-name   ◘ Dotbox Creative
 * @copyright   Copyright (C) 2014 ◘ Dotbox Creative www.dotbox.eu
 */
 ?>
<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">

<div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-options_to_list" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
<div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
<!-- form -->      
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-super_seo" class="form-horizontal">
          <ul class="nav nav-tabs">
                      <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
                      
                      <li><a href="#tab-info" data-toggle="tab"><?php echo $tab_info; ?></a></li> 
          </ul>
    <div class="tab-content">  
  <div class="tab-pane active" id="tab-general">

     

      <div class="table-responsive">
          <table id="myUrlTable" class="table table-bordered table-hover">
            <thead>
              <tr>
                <td class="text-left"> # </td>       
                <td class="text-left"><?php echo $entry_route; ?></td>
                <td class="text-left"><?php echo $entry_url; ?></td>  
                <td style="width: 93px;"></td>
              </tr>
            </thead>
            <tbody>
              <?php $i =0 ;?>
              <?php if (isset($seo_urls)) { ?>
              
                <?php foreach ($seo_urls as $seo_url) { ?>
                  <?php $i++;?>    
                  <tr>
                    <td class="text-left"><?php echo $i;?>.</td>
                    <td class="text-left"><?php echo $seo_url['route']; ?></td>
                    <td class="text-left"><?php echo $seo_url['keyword']; ?></td>
                    <td> <a href="<?php echo $seo_url['delete']; ?>" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger"><i class="fa fa-trash-o"></i> <?php echo $button_delete; ?></a></td>     
                  </tr>
                <?php } ?>
              <?php $i++;?>   
              <tr>
                <td class="text-left"><?php echo $i;?>.</td>
                <td><input class="form-control" type="text" name="route[<?php echo $i;?>][route]" /></td>
                <td><input class="form-control" type="text" name="route[<?php echo $i;?>][url]" /></td>
                <td></td>
              </tr>
              <?php } ?>

              

            </tbody>
          </table>
        </div>

        <div class="form-group text-right"> 
            <div class="col-sm-12">
              <button type="button" id="nexturlbutton" data-lastrow="<?php echo $i; ?>" onclick="addOptionValue(this.getAttribute('data-lastrow'));" data-toggle="tooltip" title="<?php echo $button_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i> <?php echo $button_add; ?></button>


              

            </div>
       </div>


       </div>

        <div class="tab-pane" id="tab-info">

         <div class="form-group"> 
            <div class="col-sm-12">
              <h4><?php echo $entry_des; ?></h4>
            </div>
        </div>

         <div class="form-group"> 
            <div class="col-sm-12">
              <h4><?php echo $entry_examples_title; ?></h4>
              <h4><?php echo $entry_examples; ?></h4>
            </div>
        </div>

          <div class="form-group"> 



            
<div class="col-sm-12"> 
<p style="font-size: 15px;"><b>Thank you for using our extensions.</b></p>
<p>To get support email us to <a href="mailto:support@dotbox.sk">support@dotbox.sk</a>.</p>
<p>We are happy to help.</p>

<p>If you like what you see leave us a comment and rate our extensions.</p>
 
<p style="font-size: 14px;">To check-out our other extensions <a href="http://www.opencart.com/index.php?route=extension/extension&filter_username=dotbox" target="_blank"><button style="padding: 3px 13px;" title="click here" class="btn btn-primary btn-success">click here</button></a> .</p>

<p style="font-size: 14px;">To get the powerful typing/textarena  <b>ck-editor</b> <a href="http://www.opencart.com/index.php?route=extension/extension/info&extension_id=22651" target="_blank"><button style="padding: 3px 13px;" title="click here" class="btn btn-primary">click here</button></a> .</p>
</div>

          </div>
        </div> 
      </div>
     </form>   
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
function addOptionValue(option_row) { 
      var datatuchange = parseInt(option_row) + 1;
      html  =  '<tr>';
      html  += '<td class="text-left">' + datatuchange + '.</td>';     
      html  += '<td><input class="form-control" type="text" name="route[' + datatuchange + '][route]" /></td>';
      html  += '<td><input class="form-control" type="text" name="route[' + datatuchange + '][url]" /></td>';
      html  += '<td><button type="button" onclick="deleteRow(this)" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger"><i class="fa fa-trash-o"></i> <?php echo $button_delete; ?></button></td>';
      html  += '</tr>';          

      $('#myUrlTable > tbody:last-child').append(html);

      $('#nexturlbutton').attr('data-lastrow','' + datatuchange + '');
}

function deleteRow(btn) {
      var row = btn.parentNode.parentNode;
      row.parentNode.removeChild(row);
}

</script>
<?php echo $footer; ?>