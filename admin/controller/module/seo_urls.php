<?php
/**
 * @total-module	Seo Friendly Urls
 * @author-name 	◘ Dotbox Creative
 * @copyright		Copyright (C) 2015 ◘ Dotbox Creative www.dotbox.eu
 */
class ControllerModuleSeoUrls extends Controller {
	private $error = array(); 
	
	public function index() {   
		$this->load->language('module/seo_urls');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('seo_urls', $this->request->post);		
			/// CRUCIAL	
			foreach ($this->request->post['route'] as $route) {
				$seo_url_route = 'route='.trim($route['route']);
				$seo_url_url = trim($route['url']);	
				$this->db->query("INSERT INTO ". DB_PREFIX ."url_alias SET query = '". $this->db->escape($seo_url_route) ."', keyword = '". $this->db->escape($seo_url_url) ."'");
			}	
			///// 
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$language_info = array(
		'heading_title', 'button_save',	'button_cancel', 'button_delete', 'tab_general', 'tab_info', 'entry_status', 'entry_des', 'entry_url', 'entry_route', 'text_edit', 'text_enabled', 'text_disabled','button_add','entry_examples','entry_examples_title'
		);

		foreach ($language_info as $language) {
			$data[$language] = $this->language->get($language); 
		}

		$data['token'] = $this->session->data['token'];
    
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
 		if (isset($this->error['folder'])) {
			$data['error_folder'] = $this->error['folder'];
		} else {
			$data['error_folder'] = '';
		}    
		
		$data['breadcrumbs'] = array();

 		$data['breadcrumbs'][] = array(
     		'text'      => $this->language->get('text_home'),
     		'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
    		'separator' => false
 		);

 		$data['breadcrumbs'][] = array(
     		'text'      => $this->language->get('text_module'),
     		'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
    		'separator' => ' :: '
 		);
	
 		$data['breadcrumbs'][] = array(
     		'text'      => $this->language->get('heading_title'),
     		'href'      => $this->url->link('module/seo_urls', 'token=' . $this->session->data['token'], 'SSL'),
    		'separator' => ' :: '
 		);
		
		$data['action'] = $this->url->link('module/seo_urls', 'token=' . $this->session->data['token'], 'SSL');
		
		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

		$seo_urls_db = $this->db->query("SELECT * FROM ". DB_PREFIX ."url_alias WHERE query LIKE 'route=%'");
		
		$data['seo_urls'] = array();

		foreach ($seo_urls_db->rows as $seo_url) {
			$url_route = explode('route=',$seo_url['query']);

			if (isset($url_route[1])) {
				$url_route = $url_route[1];
			} else {
				$url_route = $url_route[0];
			}

			$url_delete = '&ulr_id=' . $seo_url['url_alias_id'];
			$url_delete = $this->url->link('module/seo_urls/delete', 'token=' . $this->session->data['token'] . $url_delete, 'SSL');

			$data['seo_urls'][] = array(
					'route' => $url_route,
					'keyword' => $seo_url['keyword'],
					'delete' => $url_delete
				);
		}

		// RENDER
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/seo_urls.tpl', $data));
	}

	public function delete() {
		$this->load->language('module/seo_urls');
		if (isset($this->request->get['ulr_id']) && $this->validateDelete()) {
			$query = $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE url_alias_id = '" . (int)$this->request->get['ulr_id'] . "'");
			$this->session->data['success'] = $this->language->get('text_success_delete');
			$this->response->redirect($this->url->link('module/seo_urls', 'token=' . $this->session->data['token'], 'SSL'));
		}
		$this->index();
	}

	/* OLD 
	private function validate() {
		if (!$this->user->hasPermission('modify', 'module/seo_urls')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
	*/

	private function validate() {
		if (!$this->user->hasPermission('modify', 'module/seo_urls')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		foreach ($this->request->post['route'] as $route) {
			if (isset($route['route'])) {	
				if (empty($route['route'])) {
				$this->error['warning'] = $this->language->get('error_url_route');
				}
			}
			if (isset($route['url'])) { 
				if (empty($route['url'])) {
				$this->error['warning'] = $this->language->get('error_url_route');
				}
			}
			if (isset($route['route'])) {
				$query = $this->db->query("SELECT * FROM ".DB_PREFIX."url_alias WHERE query = 'route=".$this->db->escape($route['route'])."'");
				if($query->num_rows) {
					$this->error['warning'] = $this->language->get('error_route');
				}
			}
			if (isset($route['url'])) {
				$query = $this->db->query("SELECT * FROM ".DB_PREFIX."url_alias WHERE query = 'keyword=".$this->db->escape($route['url'])."'");
				if($query->num_rows) {
					$this->error['warning'] = $this->language->get('error_keyword');
				}
			}
		}
		return !$this->error;
	}

	private function validateDelete() {
		if (!$this->user->hasPermission('modify', 'module/seo_urls')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
}
?>