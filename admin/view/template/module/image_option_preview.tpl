<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-image_option_preview" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
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
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-image_option_preview" class="form-horizontal">
                    <!-- STATUS -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                        <div class="col-sm-10">
                            <select name="image_option_preview_enabled" id="input-status" class="form-control">
                                <?php if ($image_option_preview_enabled) { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select>

                        </div>
                    </div>
                    
                    <!-- OPTION_ID -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-option_id"><?php echo $entry_option_id; ?></label>
                        <div class="col-sm-10">
                            <select name="image_option_preview_option_id" id="input-option_id" class="form-control">
                                <?php foreach ($image_options as $image_option) { ?>
                                <?php if ($image_option_preview_option_id == $image_option['option_id']) { ?>
                                <option value="<?php echo $image_option['option_id']; ?>" selected="selected"><?php echo $image_option['name']; ?></option>

                                <?php } else { ?>

                                <option value="<?php echo $image_option['option_id']; ?>"><?php echo $image_option['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                            <?php if ($error_option_id) { ?>
                            <div class="text-danger"><?php echo $error_option_id; ?></div>
                            <?php }else if (count($image_options)==1 && $image_options[0]['option_id']==-1) { ?>
                            <div class="alert alert-info" style="margin-top:5px;margin-bottom: 0px;"><i class="fa fa-exclamation-circle"></i> <?php echo $info_no_image_option; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
                            <?php } ?>
                        </div>
                        
                    </div>
                    
                    <!-- SHOW_ZERO -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-show_zero"><?php echo $entry_show_zero; ?></label>
                        <div class="col-sm-10">
                            <select name="image_option_preview_show_zero" id="input-show_zero" class="form-control">
                                <?php if ($image_option_preview_show_zero) { ?>
                                <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                                <option value="0"><?php echo $text_no; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_yes; ?></option>
                                <option value="0" selected="selected"><?php echo $text_no; ?></option>
                                <?php } ?>
                            </select>

                        </div>
                    </div>
                    
                    <!-- SHOW_NAME -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-show_name"><?php echo $entry_show_name; ?></label>
                        <div class="col-sm-10">
                            <select name="image_option_preview_show_name" id="input-show_name" class="form-control">
                                <?php if ($image_option_preview_show_name) { ?>
                                <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                                <option value="0"><?php echo $text_no; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_yes; ?></option>
                                <option value="0" selected="selected"><?php echo $text_no; ?></option>
                                <?php } ?>
                            </select>

                        </div>
                    </div>
                    
                    <!-- SET_HEIGHT -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-thumb_height"><?php echo $entry_thumb_height; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="image_option_preview_thumb_height" id="input-thumb_height" class="form-control" value="<?php echo $image_option_preview_thumb_height ; ?>">
                            <?php if ($error_height) { ?>
                            <div class="text-danger"><?php echo $error_height; ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <!-- SET_WIDTH -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-thumb_width"><?php echo $entry_thumb_width; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="image_option_preview_thumb_width" id="input-thumb_width" class="form-control" value="<?php echo $image_option_preview_thumb_width ; ?>">
                            <?php if ($error_width) { ?>
                            <div class="text-danger"><?php echo $error_width; ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <!-- SET_STYLE -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-style"><?php echo $entry_style; ?></label>
                        <div class="col-sm-10">
                            <textarea style="height: 300px;" type="text" name="image_option_preview_style" id="input-style" class="form-control"><?php echo $image_option_preview_style ; ?></textarea>
                            <a style="float: right; margin-top: 5px;" href="#" id='load-default-style'><?php echo $entry_load_default_style; ?></a>
                            <textarea style="display:none;" type="text" name="image_option_preview_default_style" id="input-default-style" class="form-control"><?php echo $image_option_preview_default_style ; ?></textarea>
                        </div>
                    </div>
                    
                    
                </form>
            </div>
        </div>
        <script>
        $('#load-default-style').click(function(e){e.preventDefault();$('#input-style').val($('#input-default-style').val())});
        </script>
    </div>
    <?php echo $footer; ?>