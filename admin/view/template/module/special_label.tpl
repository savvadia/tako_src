<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-special_label" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
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
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-special_label" class="form-horizontal">
                    <!-- STATUS -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                        <div class="col-sm-10">
                            <select name="special_label_enabled" id="input-status" class="form-control">
                                <?php if ($special_label_enabled) { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <!-- SHOW_PERCENTAGE -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-show-percentage"><?php echo $entry_show_percentage; ?></label>
                        <div class="col-sm-10">
                            <select name="special_label_show_percentage" id="input-show-percentage" class="form-control">
                                <?php if ($special_label_show_percentage) { ?>
                                <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                                <option value="0"><?php echo $text_no; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_yes; ?></option>
                                <option value="0" selected="selected"><?php echo $text_no; ?></option>
                                <?php } ?>
                            </select>

                        </div>
                    </div>
                    <!-- SHOW_PERCENTAGE -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-show-sign"><?php echo $entry_show_sign; ?></label>
                        <div class="col-sm-10">
                            <select name="special_label_show_sign" id="input-show-sign" class="form-control">
                                <?php if ($special_label_show_sign) { ?>
                                <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                                <option value="0"><?php echo $text_no; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_yes; ?></option>
                                <option value="0" selected="selected"><?php echo $text_no; ?></option>
                                <?php } ?>
                            </select>

                        </div>
                    </div>
                    <!-- SET_LABELS -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label" ><?php echo $entry_special_label; ?></label>
                        <div class="col-sm-10">
                            <?php $label_row=0;foreach ($special_label_label as $label) { ?>
                            <div class="input-group pull-left">
                                <span class="input-group-addon">
                                    <img src="<?php echo $label['image']; ?>" title="<?php echo $label['name']; ?>" />
                                </span>
                                <input type="text" name="special_label_label[<?php echo $label_row; ?>][label]" 
                                       value="<?php echo $label['label']; ?>" placeholder="<?php echo $entry_placeholder; ?>" class="form-control" />
                                <input type='hidden' name='special_label_label[<?php echo $label_row; ?>][language_id]' value="<?php echo $label['language_id']; ?>">
                                <input type='hidden' name='special_label_label[<?php echo $label_row; ?>][image]' value="<?php echo $label['image']; ?>">
                                <input type='hidden' name='special_label_label[<?php echo $label_row; ?>][name]' value="<?php echo $label['name']; ?>">
                            </div>
                            <?php if (isset($error_label[$label_row])) { ?>
                            <div class="text-danger"><?php echo $error_label[$label_row]; ?></div>
                            <?php } ?>
                            <?php $label_row++;}  ?>
                        </div>
                    </div>
                    <!-- SET_STYLE -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-style"><?php echo $entry_style; ?></label>
                        <div class="col-sm-10">
                            <textarea style="height: 300px;" type="text" name="special_label_style" id="input-style" class="form-control"><?php echo $special_label_style ; ?></textarea>
                        </div>
                        <div class="col-sm-10 col-sm-offset-2" style="margin-top: 5px;">
                            <p class="pull-left"><?php echo $entry_css_nodes; ?>: <i><?php echo $entry_css_nodes_content; ?></i></p>
                            <a href="#" id="load-default-style" class="pull-right"><?php echo $entry_load_default_style; ?></a>
                        </div>
                    </div>
                </form>
                <textarea id="default-textarea" class="hidden"><?php echo $special_label_default_style; ?></textarea>
                <script>
                    $('#load-default-style').click(function (e) {
                        e.preventDefault();
                        if (confirm("<?php echo $entry_confirm_default_style_loading; ?>")) {
                            $('#input-style').val($("#default-textarea").val());
                        }
                    });
                </script>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>