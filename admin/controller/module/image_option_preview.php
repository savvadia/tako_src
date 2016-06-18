<?php

class ControllerModuleImageOptionPreview extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('module/image_option_preview');
        $this->load->model('module/image_option_preview');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');


        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('image_option_preview', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_option_id'] = $this->language->get('entry_image_option');
        $data['entry_show_zero'] = $this->language->get('entry_show_zero');
        $data['entry_show_name'] = $this->language->get('entry_show_name');
        $data['entry_thumb_height'] = $this->language->get('entry_thumb_height');
        $data['entry_thumb_width'] = $this->language->get('entry_thumb_width');
        $data['entry_style'] = $this->language->get('entry_style');
        $data['entry_load_default_style'] = $this->language->get('entry_load_default_style');
       
        $data['info_no_image_option'] = sprintf($this->language->get('info_no_image_option'), $this->url->link('catalog/option', 'token=' . $this->session->data['token'], 'SSL'));

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');



        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['option_id'])) {
            $data['error_option_id'] = $this->error['option_id'];
        } else {
            $data['error_option_id'] = '';
        }

        if (isset($this->error['width'])) {
            $data['error_width'] = $this->error['width'];
        } else {
            $data['error_width'] = '';
        }

        if (isset($this->error['height'])) {
            $data['error_height'] = $this->error['height'];
        } else {
            $data['error_height'] = '';
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
            'href' => $this->url->link('module/image_option_preview', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['action'] = $this->url->link('module/image_option_preview', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

        // status
        if (isset($this->request->post['image_option_preview_enabled'])) {
            $data['image_option_preview_enabled'] = $this->request->post['image_option_preview_enabled'];
        } else {
            $data['image_option_preview_enabled'] = $this->config->get('image_option_preview_enabled');
        }
        // option_id
        if (isset($this->request->post['image_option_preview_option_id'])) {
            $data['image_option_preview_option_id'] = $this->request->post['image_option_preview_option_id'];
        } else {
            $data['image_option_preview_option_id'] = $this->config->get('image_option_preview_option_id');
        }

        // show zero quantity
        if (isset($this->request->post['image_option_preview_show_zero'])) {
            $data['image_option_preview_show_zero'] = $this->request->post['image_option_preview_show_zero'];
        } else {
            $data['image_option_preview_show_zero'] = $this->config->get('image_option_preview_show_zero');
        }

        // show option name
        if (isset($this->request->post['image_option_preview_show_option_name'])) {
            $data['image_option_preview_show_name'] = $this->request->post['image_option_preview_show_name'];
        } else {
            $data['image_option_preview_show_name'] = $this->config->get('image_option_preview_show_name');
        }

        // thumb height
        if (isset($this->request->post['image_option_preview_thumb_height'])) {
            $data['image_option_preview_thumb_height'] = $this->request->post['image_option_preview_thumb_height'];
        } else {
            $data['image_option_preview_thumb_height'] = $this->config->get('image_option_preview_thumb_height');
        }
        // thumb width
        if (isset($this->request->post['image_option_preview_thumb_width'])) {
            $data['image_option_preview_thumb_width'] = $this->request->post['image_option_preview_thumb_width'];
        } else {
            $data['image_option_preview_thumb_width'] = $this->config->get('image_option_preview_thumb_width');
        }
        // style
        if (isset($this->request->post['image_option_preview_style'])) {
            $data['image_option_preview_style'] = $this->request->post['image_option_preview_style'];
        } else {
            $data['image_option_preview_style'] = $this->config->get('image_option_preview_style');
        }
        $data['image_option_preview_default_style'] = $this->config->get('image_option_preview_default_style');

        $image_options = $this->model_module_image_option_preview->getImageOptions((int) $this->config->get('config_language_id'));
        array_unshift($image_options, array("option_id" => "-1", "name" => $this->language->get('text_please_select')));

        $data['image_options'] = $image_options;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('module/image_option_preview.tpl', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'module/google_hangouts')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if ($this->request->post['image_option_preview_option_id'] == -1) {
            $this->error['option_id'] = $this->language->get('error_no_selection');
        }
        $w = $this->request->post['image_option_preview_thumb_width'];
        if (!is_numeric($w) || $w < 16) {
            $this->error['width'] = $this->language->get('error_width');
        }
        $h = $this->request->post['image_option_preview_thumb_height'];
        if (!is_numeric($h) || $h < 16) {
            $this->error['height'] = $this->language->get('error_height');
        }

        return !$this->error;
    }

    public function install() {
        $this->load->model('setting/setting');
        $post = array();
        $post['image_option_preview_enabled'] = "0";
        $post['image_option_preview_option_id'] = "-1";
        $post['image_option_preview_show_zero'] = "0";
        $post['image_option_preview_thumb_height'] = "16";
        $post['image_option_preview_thumb_width'] = "16";
        $post['image_option_preview_style'] = ".image-option-preview{
        margin: 2px;
        padding: 0px;
    }
    .image-option-preview a{
        margin: 0px;
        padding: 2px !important;
        border-radius: 100px !important;
    }
    .image-option-preview p{
        margin: 0px;
        padding: 1px !important;
        margin-right: 5px;
        font-weight: bold;
    }
    .image-option-preview a img{
        margin: 0px;
        padding: 0px;
        border-radius: 100px;
    }";

        $post['image_option_preview_default_style'] = $post['image_option_preview_style'];

        $this->model_setting_setting->editSetting('image_option_preview', $post);
    }

}
