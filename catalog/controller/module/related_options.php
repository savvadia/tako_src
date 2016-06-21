<?php
//  Related Options / Связанные опции
//  Support: support@liveopencart.com / Подержка: help@liveopencart.ru
?>
<?php  
class ControllerModuleRelatedOptions extends Controller {
	
  protected function index($setting) {
		
		
		
		
	}
  
  public function get_to_free_quantity() {
    
    
    if ($this->request->server['REQUEST_METHOD'] == 'GET' && isset($this->request->get['roid'])) {
      
      $this->load->model('module/related_options');
      
      $quantity = $this->model_module_related_options->get_ro_free_quantity((int)$this->request->get['roid']);
      
      echo $quantity;
      exit;
      
    }
    
    
    
  }
  
  
}
?>