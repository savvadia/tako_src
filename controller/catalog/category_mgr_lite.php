<?php
class ControllerCatalogCategoryMgrLite extends Controller {

    private $error = array();

    public function products() {
        $json = array();
        $products = array();
        $product_total = 0;
        if (isset($this->request->get['id']) && isset($this->request->get['limit']) && isset($this->request->get['offset'])) {
            $id = $this->request->get['id'];
            $limit = $this->request->get['limit'];
            $offset = $this->request->get['offset'];
            $sort = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'name';
            $order = isset($this->request->get['order']) ? $this->request->get['order'] : 'desc';
            if ($id >= 0) {

                $this->load->model('catalog/category_mgr_lite');
                $this->load->model('catalog/product');
                $this->load->model('tool/image');

                $data = array(
                    'filter_category_id' => $id,
                    'sort' => $sort,
                    'order' => $order,
                    'start'           => $offset,
                    'limit'           => $limit

                );
                $product_total = $this->model_catalog_category_mgr_lite->getTotalProducts($data);
                $results = $this->model_catalog_category_mgr_lite->getProducts($data);
                foreach ($results as $result) {

                    if ($result['image'] && file_exists(DIR_IMAGE . $result['image'])) {
                        $image = $this->model_tool_image->resize($result['image'], 48, 48);
                    } else {
                        $image = $this->model_tool_image->resize('no_image.png', 48, 48);
                    }

                    $special = false;

                    $product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

                    foreach ($product_specials  as $product_special) {
                        if (($product_special['date_start'] == '0000-00-00' || $product_special['date_start'] > date('Y-m-d')) && ($product_special['date_end'] == '0000-00-00' || $product_special['date_end'] < date('Y-m-d'))) {
                            $special = $product_special['price'];

                            break;
                        }
                    }

                    $products[] = array(
                        'product_id' => $result['product_id'],
                        'name'       => $result['name'],
                        'model'      => $result['model'],
                        'price'      => $result['price'],
                        'special'    => $special,
                        'image'      => $image,
                        'quantity'   => $result['quantity'],
                        'status'     => $result['status'],
                        'status_text'=> ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'))
                    );
                }


            }
        }

        $json['total'] = $product_total;
        $json['rows'] = $products;
        $this->response->addHeader('Content-Type: application/json; charset=utf-8');
        $this->response->setOutput(json_encode($json));
    }

    public function tree() {
        $json = array();

        $operation = isset($this->request->get['operation']) ? $this->request->get['operation'] : '';
        $root = (isset($this->request->get['id']) && $this->request->get['id'] == '#');
        $node = isset($this->request->get['id']) && ctype_digit($this->request->get['id']) ? $this->request->get['id'] : 0;
        $this->load->model('catalog/category_mgr_lite');
        if ($operation == 'get_node') {
            if ($root) {
                $json[] = array('data' => array('status' => 1 ), 'text' => '', 'children' => true,  'id' => "0", 'icon' => 'jstree-folder');
            }
            else {
                $cats = $this->model_catalog_category_mgr_lite->getChildren($node);
                foreach ($cats as $cat) {
                    $json[]=array('data'=>array('status'=>$cat['status']),'text'=>$cat['name'],'children'=>$cat['children']>0,'id'=>$cat['category_id'],'icon'=>'jstree-folder','a_attr'=>array('ico-disabled'=>!(bool)$cat['status']));
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json; charset=utf-8');
        $this->response->setOutput(json_encode($json));
    }

    public function index() {

        $this->load->language('catalog/category_mgr_lite');
        $this->load->model('setting/setting');

        $this->document->setTitle($this->language->get('category_mgr_heading_title'));
        $this->document->addLink('view/javascript/category_mgr/libs/jquery-ui.css', 'stylesheet');

        $this->document->addScript('view/javascript/category_mgr/libs/jquery-1.11.2.min.js');
        $this->document->addScript('view/javascript/category_mgr/libs/jquery-ui.min.js');

        $this->document->addLink('view/javascript/category_mgr/libs/jstree/themes/default/style.min.css', 'stylesheet');
        $this->document->addLink('view/javascript/category_mgr/libs/bootstrap-table/bootstrap-table.min.css', 'stylesheet');
        $this->document->addLink('view/javascript/category_mgr/category_mgr.css', 'stylesheet');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $base='view/javascript/category_mgr/libs/';

        $this->document->addScript($base.'jstree/jstree.js');
        $this->document->addScript($base.'bootstrap-table/bootstrap-table.min.js');

        $interface_lang = $this->config->get('config_admin_language');
        if($interface_lang == 'ru')
            $this->document->addScript('view/javascript/category_mgr/libs/bootstrap-table/locale/bootstrap-table-ru-RU.min.js');
        else
            $this->document->addScript('view/javascript/category_mgr/libs/bootstrap-table/locale/bootstrap-table-en-US.min.js');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
        );
        
        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('category_mgr_heading_title'),
            'href'      => $this->url->link('catalog/category_mgr_lite', 'token=' . $this->session->data['token']. '', 'SSL'),
        );


        $data['category_mgr_heading_title'] = $this->language->get('category_mgr_heading_title');
        $data['heading_title'] = $data['category_mgr_heading_title'];

        $data['action'] = $this->url->link('module/category_mgr_lite', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
        $data['token'] = $this->session->data['token'];

        $data['button_category_add'] = $this->language->get('button_category_add');
        $data['button_category_insert'] = $this->language->get('button_category_insert');
        $data['button_category_edit'] = $this->language->get('button_category_edit');
        $data['button_category_expand'] = $this->language->get('button_category_expand');
        $data['button_category_collapse'] = $this->language->get('button_category_collapse');
        $data['button_category_enable'] = $this->language->get('button_category_enable');
        $data['button_category_disable'] = $this->language->get('button_category_disable');
        $data['button_category_delete'] = $this->language->get('button_category_delete');

        $data['button_product_edit'] = $this->language->get('button_product_edit');
        $data['button_product_add'] = $this->language->get('button_product_add');
        $data['button_product_delete'] = $this->language->get('button_product_delete');
        $data['button_product_enable'] = $this->language->get('button_product_enable');
        $data['button_product_disable'] = $this->language->get('button_product_disable');

        $data['text_confirm_delete_category'] = $this->language->get('text_confirm_delete_category');
        $data['text_confirm_delete_product'] = $this->language->get('text_confirm_delete_product');
        $data['text_confirm_delete_products'] = $this->language->get('text_confirm_delete_products');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');


        $data['text_category'] = $this->language->get('text_category');
        $data['text_selected_products'] = $this->language->get('text_selected_products');
        $data['text_operation_move'] = $this->language->get('text_operation_move');
        $data['text_operation_copy'] = $this->language->get('text_operation_copy');

        $data['column_image'] = $this->language->get('column_image');
        $data['column_name'] = $this->language->get('column_name');
        $data['column_model'] = $this->language->get('column_model');
        $data['column_price'] = $this->language->get('column_price');
        $data['column_quantity'] = $this->language->get('column_quantity');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_action'] = $this->language->get('column_action');

        $data['token'] = $this->session->data['token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('catalog/category_mgr_lite.tpl', $data));

    }

}
?>