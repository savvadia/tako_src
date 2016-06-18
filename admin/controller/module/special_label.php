<?php

class ControllerModuleSpecialLabel extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('module/special_label');
        $this->load->model('module/special_label');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $post = array();
            $post['special_label_enabled'] = $this->request->post['special_label_enabled'];
            $post['special_label_style'] = $this->request->post['special_label_style'];
            $post['special_label_default_style'] = $this->config->get('special_label_default_style');
            $post['special_label_show_percentage'] = $this->request->post['special_label_show_percentage'];
            $post['special_label_show_sign'] = $this->request->post['special_label_show_sign'];

            $this->model_setting_setting->editSetting('special_label', $post);

            $this->model_module_special_label->updateLabels($this->request->post['special_label_label']);

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data = array();
        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_special_label'] = $this->language->get('entry_special_label');
        $data['entry_style'] = $this->language->get('entry_style');
        $data['entry_css_nodes'] = $this->language->get('entry_css_nodes');
        $data['entry_css_nodes_content'] = $this->language->get('entry_css_nodes_content');
        $data['entry_load_default_style'] = $this->language->get('entry_load_default_style');
        $data['entry_show_sign'] = $this->language->get('entry_show_sign');
        $data['entry_show_percentage'] = $this->language->get('entry_show_percentage');
        $data['entry_placeholder'] = $this->language->get('entry_placeholder');
        $data['entry_confirm_default_style_loading'] = $this->language->get('entry_confirm_default_style_loading');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');



        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['label'])) {
            $data['error_label'] = $this->error['label'];
        } else {
            $data['error_label'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/special_label', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['action'] = $this->url->link('module/special_label', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

        // status
        if (isset($this->request->post['special_label_enabled'])) {
            $data['special_label_enabled'] = $this->request->post['special_label_enabled'];
        } else {
            $data['special_label_enabled'] = $this->config->get('special_label_enabled');
        }

        // label
        if (isset($this->request->post['special_label_label'])) {
            $data['special_label_label'] = $this->request->post['special_label_label'];
        } else {
            $data['special_label_label'] = $this->model_module_special_label->getLabels();
        }

        // style
        if (isset($this->request->post['special_label_style'])) {
            $data['special_label_style'] = $this->request->post['special_label_style'];
        } else {
            $data['special_label_style'] = $this->config->get('special_label_style');
        }

        // show_percentage
        if (isset($this->request->post['special_label_show_percentage'])) {
            $data['special_label_show_percentage'] = $this->request->post['special_label_show_percentage'];
        } else {
            $data['special_label_show_percentage'] = $this->config->get('special_label_show_percentage');
        }

        // show_sign
        if (isset($this->request->post['special_label_show_sign'])) {
            $data['special_label_show_sign'] = $this->request->post['special_label_show_sign'];
        } else {
            $data['special_label_show_sign'] = $this->config->get('special_label_show_sign');
        }

        // default_style
        $data['special_label_default_style'] = $this->config->get('special_label_default_style');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('module/special_label.tpl', $data));
    }

    protected function validate() {
        foreach ($this->request->post['special_label_label'] as $key => $label) {
            if (empty($label['label'])) {
                $this->error['label'][$key] = $this->language->get('error_empty_line');
            }
        }
        if (!$this->user->hasPermission('modify', 'module/special_label')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }


        return !$this->error;
    }

    public function install() {
        $this->load->model('setting/setting');
        $post = array();
        $post['special_label_enabled'] = "1";
        $post['special_label_show_sign'] = "0";
        $post['special_label_show_percentage'] = "1";
        $post['special_label_enabled'] = "1";
        $post['special_label_style'] = $post['special_label_default_style'] = ".product-layout{
    overflow: hidden;
}

.product-discount{
    color: white;
    position: absolute;
    text-transform: uppercase;
    text-align: center;
    border: 3px white double;
    width: 200px;
    background-color: #23a1d1;
    margin-left: -74px;
    margin-top: 15px;
    -ms-transform: rotate(-45deg); 
    -webkit-transform: rotate(-45deg); 
    transform: rotate(-45deg);
    z-index: 2;
    height: 27px;
}

.product-discount p{
    padding-top: 1px;
    width: 120px;
    margin: auto;
    font-size: 11px;
    margin-left: 30px;
}

 .product-discount:before{
    -ms-transform: rotate(45deg); 
    -webkit-transform: rotate(45deg);
    transform: rotate(45deg);
    content:'*';
    color: transparent;
    position: absolute;
    z-index: -1;
    border-left: 15px solid transparent;
    border-bottom: 5px solid transparent;
    border-top: 15px solid transparent;
    border-right: 15px solid #176B8C;
    height: 0;
    width: 0;
    margin-left: -82px;
    margin-top: 11px;
}";

        $this->model_setting_setting->editSetting('special_label', $post);
        $this->load->model('module/special_label');
        $this->model_module_special_label->createSpecialLabelTable();
    }

    public function uninstall() {
        $this->load->model('module/special_label');
        $this->model_module_special_label->deleteSpecialLabelTable();
    }

}
