<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <h1><?php echo $category_mgr_heading_title; ?></h1>
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
                <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $category_mgr_heading_title; ?></h3>
            </div>
            <div class="panel-body">
                <div class="container-fluid">
                    <div id="left-pane" style="float: left; width: 320px; height: 100%; margin-right: 10px;">
                        <div id="left-pane-toolbar" style="height: 40px; width: 100%; padding-right: 6px;">
                            <button id="btnCategoryEdit" onclick="onCategoryEdit(); return false;" type="button" data-toggle="tooltip" class="btn btn-sm btn-primary" title="<?php echo $button_category_edit; ?>"><i class="fa fa-pencil"></i></button>
                            <button onclick="onCollapseTree(); return false;" type="button" style="float: right;" data-toggle="tooltip" class="btn btn-sm btn-primary" title="<?php echo $button_category_collapse; ?>"><i class="fa fa-angle-double-up"></i></button>
                            <button onclick="onExpandTree(); return false;" type="button" style="float: right; margin-right: 6px;" data-toggle="tooltip" class="btn btn-sm btn-primary" title="<?php echo $button_category_expand; ?>"><i class="fa fa-angle-double-down"></i></button>
                        </div>
                        <div id="jstree"></div>
                    </div>
                    <div id="products-list" style="overflow:hidden;">
<a target="_blank" href="http://forkus.tmweb.ru/oc201/admin/index.php?route=catalog/category_mgr&cm_demo=1" type="button" class="btn btn-info"><i class="fa fa-money"> Try Advanced Acute Category Manager+</i></a>
                        <div id="products-toolbar" style="margin-left: 14px; margin-top: -1px;">
                        </div>
                        <table id="products-table"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript"><!--
const MIN_W = 640;
const MIN_H = 480

$(function () {
    window.j = $.noConflict(true);

    window.j('#jstree')
            .jstree({
                'core' : {
                    'check_callback' : function(operation, node, node_parent, node_position, more) {
                        if (operation === 'move_node') {
                            return false;
                        }
                    },
                    'multiple' : false,
                    'data' : {
                        'url' : 'index.php?route=catalog/category_mgr_lite/tree&token=<?php echo $token; ?>',
                        'data' : function (node) {
                            return { 'id' : node.id, 'operation' : 'get_node' };
                        }
                    }},
                'plugins' : ['wholerow']
            }
    )
            .on('refresh.jstree', function () {
                window.j('#jstree').jstree("rename_node", "0", "<?php echo $text_category; ?>");
                if (window.open_node) {
                    window.open_node = false;
                    var selectedNode = window.j('#jstree').jstree(true).get_selected(false);
                    if (selectedNode.length == 1)
                        window.j('#jstree').jstree("open_node", selectedNode);
                }
            })
            .on('ready.jstree', function () {
                window.j('#jstree').jstree("open_node", "0");
                window.j('#jstree').jstree("rename_node", "0", "<?php echo $text_category; ?>");
                window.j('#jstree').jstree("select_node", "0");
            })
            .on("changed.jstree", function (e, data) {

                var obj = null;
                var root = true;
                if (data.node !== undefined) {
                    obj = data.node.data;
                    root = data.node.id == "0";
                }
                checkUIState(obj, data.selected.length, root);
                if (obj != null && data.selected.length == 1) {
                    reloadProductList(true);
                }
            })
    ;

    window.j('#products-table').bootstrapTable({
        pagination: true,
        sidePagination: 'server',
        method: 'get',
        url: 'index.php?route=catalog/category_mgr_lite/products&token=<?php echo $token; ?>',
        queryParams: function (p) {
            var selectedNodes = window.j('#jstree').jstree(true).get_selected(false);
            var node = (selectedNodes.length == 1) ? node = selectedNodes[0] : 0;
            window.selected_category = node;
            var real_offset = p.offset;
            if (window.reset_offset) {
                real_offset = 0;
                window.reset_offset = false;
            }
            return {
                id: node,
                order: p.order,
                sort: p.sort,
                limit: p.limit,
                offset: real_offset
            };
        },
        cache: false,
        height: 780,
        rowStyle: rowStyle,
        sortName: 'name',
        sortOrder: 'asc',
        toolbar: '#products-toolbar',
        toolbarAlign: 'right',
        searchAlign: 'left',
        striped: false,
        showToggle: true,
        showRefresh: true,
        pageSize: 10,
        pageList: [10, 20, 50, 100],
        columns: [{
            field: 'product_id',
            visible: false
        },
            {
                field: 'status',
                visible: false
            },{
                field: 'image',
                title: '<?php echo $column_image; ?>',
                align: 'center',
                valign: 'middle',
                formatter: imgFormatter
            }, {
                field: 'name',
                title: '<?php echo $column_name; ?>',
                valign: 'middle',
                align: 'left',
                sortable: true
            }, {
                field: 'model',
                title: '<?php echo $column_model; ?>',
                valign: 'middle',
                sortable: true,
                align: 'left'
            }, {
                field: 'price',
                title: '<?php echo $column_price; ?>',
                align: 'left',
                valign: 'middle',
                sortable: true,
                formatter: priceFormatter
            }, {
                field: 'quantity',
                title: '<?php echo $column_quantity; ?>',
                sortable: true,
                valign: 'middle',
                formatter: quantityFormatter,
                align: 'right'
            }, {
                field: 'status_text',
                title: '<?php echo $column_status; ?>',
                valign: 'middle',
                sortable: true,
                align: 'left'
            }, {
                field: 'action',
                title: '<?php echo $column_action; ?>',
                valign: 'middle',
                align: 'center',
                formatter: actionFormatter
            }]
    }).on('click-row.bs.table', function (e, row, $element) {
        window.selected_products = [row.product_id];

    });
    var selectedNode = window.j('#jstree').jstree(true).get_selected(false);
    checkUIState(null, selectedNode.length, true);

});

$(window).resize(function () {
    window.j('#products-table').bootstrapTable('resetView');
});

function reloadProductList(reset_offset) {
    if (reset_offset) window.reset_offset = true;
    window.j("#products-table").bootstrapTable('refresh', {silent: true});
}

function rowStyle(row, index) {
    if (row.status == "0")
        return {
            classes: 'active'
        };
    return {
        classes: ''
    };
}

function quantityFormatter(value) {
    var cls;
    if (value <= 0) cls = "label-danger";
    else if (value <= 5) cls = "label-warning";
    else cls = "label-success";
    return '<h5><span class="label '+cls+'">'+ value + '</span></h5>';
}

function imgFormatter(value) {
    return '<img src="'+value+'"/>';
}

function statusFormatter(value) {
    return value ? '<?php echo $text_enabled; ?>' : '<?php echo $text_disabled; ?>';
}

function priceFormatter(value, row) {
    if (row.special) {
        return '<span style="text-decoration: line-through;">'+value+'</span><br/><span style="color: #b00;">'+row.special+'</span>';
    }
    else {
        return value;
    }
}

function actionFormatter(value, row) {
    return '<button onclick="onProductEdit('+row.product_id+'); return false;" type="button" data-toggle="tooltip" class="btn btn-xs btn-primary" title="<?php echo $button_product_edit; ?>"><i class="fa fa-pencil"></i></button>';
}

function onProductEdit(product_id) {
    window.j('#dialog').remove();
    var window_w = window.j(window).width();
    var window_h = window.j(window).height();
    var h = Math.max(window_h * 3 / 4, MIN_H) | 0;
    var w = Math.max(window_w * 3 / 4, MIN_W) | 0;
    var url;
    if (product_id == -1)
        url = 'index.php?route=catalog/product/add&token=<?php echo $token; ?>';
    else
        url = 'index.php?route=catalog/product/edit&token=<?php echo $token; ?>&product_id=' + product_id;
    window.j('#content').append('<div id="dialog" style="background: gray; padding: 10px;"><iframe id="productFormIframe" src="'+url+'" style="padding: 0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');
    window.j('#dialog').dialog({
        title: '',
        width: w,
        height: h,
        resizable: true,
        modal: true
    });
    window.j(".ui-dialog").css("z-index", "3000");
    window.j('#productFormIframe').load(function() {
        var cnt = window.j('#productFormIframe').contents();
        var saved = cnt.find('div.alert.alert-success').length;
        if (!saved) {
            cnt.find('.breadcrumb').hide();
            cnt.find('#footer').hide();
            cnt.find('#column-left').remove();
            cnt.find('#header').hide();
            var cancel = cnt.find('a[href*="catalog/product"');
            cancel.removeAttr("href");
            cancel.click(function(){ parent.closeProductDlg(0); } );
        }
        else {
            parent.closeProductDlg(1);
        }

    })
}

function closeCategoryDlg(ok) {
    window.j('#dialog').dialog('close');
    if (ok) {
        window.open_node = true;
        window.j('#jstree').jstree("refresh");
    }
}

function closeProductDlg(ok) {
    window.j('#dialog').dialog('close');
    if (ok) {
        window.j("#products-table").bootstrapTable('refresh');
    }
}

function onExpandTree() {
    window.j('#jstree').jstree("open_all");
}

function onCollapseTree() {
    window.j('#jstree').jstree("close_all");
}

function onCategoryEdit() {
    var selectedNode= window.j('#jstree').jstree(true).get_selected(false);
    if (selectedNode.length == 1) {
        doCategoryEdit(selectedNode);
    }
}

function setButtonState(selector, state) {
    if (state) {
        window.j(selector).removeClass('disabled').addClass('active');
    }
    else {
        window.j(selector).removeClass('active').addClass('disabled');
    }
}
function checkUIState(data, selectedCount, root) {
    if (root) {
        setButtonState('#btnCategoryEdit', false);
        return;
    }
    var status = 0;
    if (data != null)
        status = parseInt(data.status);
    setButtonState('#btnCategoryEdit', selectedCount == 1);
}

function htmlDecode(value){
    if (value) {
        return jQuery('<div/>').html(value).text();
    } else {
        return '';
    }
}
function doLaunchModalController(url) {
    var window_w = window.j(window).width();
    var window_h = window.j(window).height();
    var h = Math.max(window_h * 3 / 4, MIN_H) | 0;
    var w = Math.max(window_w * 3 / 4, MIN_W) | 0;

    window.j('#dialog').remove();
    window.j('#content').append('<div id="dialog" style="background:gray; padding:10px;"><iframe id="categoryFormIframe" src="'+url+'" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="yes" scrolling="auto"></iframe></div>');
    window.j('#dialog').dialog({
        title: '',
        width: w,
        height: h,
        resizable: true,
        modal: true
    });
    window.j(".ui-dialog").css("z-index", "3000");
    window.j('#categoryFormIframe').load(function() {
        var cnt = window.j('#categoryFormIframe').contents();
        var saved = cnt.find('div.alert.alert-success').length;
        if (!saved) {
            cnt.find('.breadcrumb').hide();
            cnt.find('#footer').hide();
            cnt.find('#header').hide();
            cnt.find('#column-left').remove();
            var cancel = cnt.find('a[href*="catalog/category"');
            cancel.removeAttr("href");
            cancel.click(function(){ parent.closeCategoryDlg(0); });
        }
        else {
            parent.closeCategoryDlg(1);
        }

    })
}

function doCategoryEdit(category_id) {
    doLaunchModalController('index.php?route=catalog/category/edit&category_id='+category_id+'&token=<?php echo $token; ?>', 0, '');
}//--></script>
<?php echo $footer; ?>