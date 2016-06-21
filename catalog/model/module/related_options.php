<?php
//  Related Options / Связанные опции
//  Support: support@liveopencart.com / Подержка: help@liveopencart.ru
?>
<?php

class ModelModuleRelatedOptions extends Model {

	
	// << orders editing 
	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}
	
	// возвращает количество из заказа в остаток (при удалении заказа)
	public function set_ro_quantity_back($product_id, $product_options, $quantity) {
		
		if (!$this->installed() || !$product_options || !is_array($product_options)) {
			return;
		}
		
		$query = $this->db->query("SELECT subtract FROM `".DB_PREFIX."product` WHERE `product_id` = ".(int)$product_id." " );
		if ($query->num_rows && $query->row['subtract'] && $this->get_product_related_options_use((int)$product_id)) {
			
			$options = array();
			foreach ($product_options as $product_option) {
				$options[$product_option['product_option_id']] = $product_option['product_option_value_id'];
			}
			
			$ro = $this->get_related_options_set_by_poids($product_id, $options);
			if ($ro && is_array($ro)) {
				$this->db->query("UPDATE `".DB_PREFIX."relatedoptions` SET quantity=(quantity+".(int)$quantity.") WHERE `relatedoptions_id` = ".(int)$ro['relatedoptions_id']." " );
			}
			
		}
		
	}
	// >> orders editing
	
	public function getThemeName() {
    if ( VERSION >= '2.2.0.0' ) {
      if ($this->config->get('config_theme') == 'theme_default') {
        return $this->config->get('theme_default_directory');
      } else {
        return substr($this->config->get('config_theme'), 0, 6) == 'theme_' ? substr($this->config->get('config_theme'), 6) : $this->config->get('config_theme') ;
      }
      //return substr($this->config->get('config_theme'), 0, 6) == 'theme_' ? substr($this->config->get('config_theme'), 6) : $this->config->get('config_theme') ;
    } else {  
      return $this->config->get('config_template');
    }
  }

	// returns only switched-on additional fields (sku, upc, location)
	public function getAdditionalFields() {
		
		$fields = array();
		
		if ($this->installed()) {
			$ro_settings = $this->config->get('related_options');
			$std_fields = array('sku', 'upc', 'ean', 'location');
			foreach ($std_fields as $field) {
				if ( isset($ro_settings['spec_'.$field]) && $ro_settings['spec_'.$field] ) {
					$fields[] = $field;
				}
			}
		}
		
		return $fields;
	}
	
	public function updateOrderProductAdditionalFields($product, $order_product_id) {
		
		if ($this->installed()) {
			$this->check_order_product_table();
			$ro_settings = $this->config->get('related_options');
			
			$ro_options = array();
			foreach ($product['option'] as $option) {
				if (isset($option['product_option_value_id'])) {
					$ro_options[$option['product_option_id']] = $option['product_option_value_id'];
				}
			}
			
			$product_ro = $this->get_related_options_set_by_poids($product['product_id'], $ro_options);
			
			if ($product_ro) {
				if (isset($ro_settings['spec_model']) && $ro_settings['spec_model'] && isset($product_ro['model']) && trim($product_ro['model']) != "") {
					$this->db->query("UPDATE " . DB_PREFIX . "order_product SET `model`='".$this->db->escape($product_ro['model'])."' WHERE order_product_id = " . (int)$order_product_id . "");
				}
				
				$current_product_info = $this->getProductSimple($product['product_id']);
				if (isset($ro_settings['spec_sku']) && $ro_settings['spec_sku']) {
					$current_value = (isset($product_ro['sku']) && trim($product_ro['sku']) != "") ? $product_ro['sku'] : $current_product_info['sku'];
					$this->db->query("UPDATE " . DB_PREFIX . "order_product SET `sku`='".$this->db->escape($current_value)."' WHERE order_product_id = " . (int)$order_product_id . "");
				}
				if (isset($ro_settings['spec_upc']) && $ro_settings['spec_upc']) {
					$current_value = (isset($product_ro['upc']) && trim($product_ro['upc']) != "") ? $product_ro['upc'] : $current_product_info['upc'];
					$this->db->query("UPDATE " . DB_PREFIX . "order_product SET `upc`='".$this->db->escape($current_value)."' WHERE order_product_id = " . (int)$order_product_id . "");
				}
				if (isset($ro_settings['spec_ean']) && $ro_settings['spec_ean']) {
					$current_value = (isset($product_ro['ean']) && trim($product_ro['ean']) != "") ? $product_ro['ean'] : $current_product_info['ean'];
					$this->db->query("UPDATE " . DB_PREFIX . "order_product SET `ean`='".$this->db->escape($current_value)."' WHERE order_product_id = " . (int)$order_product_id . "");
				}
				if (isset($ro_settings['spec_location']) && $ro_settings['spec_location']) {
					$current_value = (isset($product_ro['location']) && trim($product_ro['location']) != "") ? $product_ro['location'] : $current_product_info['location'];
					$this->db->query("UPDATE " . DB_PREFIX . "order_product SET `location`='".$this->db->escape($current_value)."' WHERE order_product_id = " . (int)$order_product_id . "");
				}
			}
				
		}
		
	}
	

  // массив значений опций
	
  public function getJournal2Price($product_id) {
		
		if ($this->installed()) {
			
			$special=false;
			$ro_price_prefix = '';
			$ro_stock = 0;
			
			$ro_settings = $this->config->get('related_options');
			if ($ro_settings && is_array($ro_settings) && isset($ro_settings['spec_price']) && $ro_settings['spec_price']) {
				
				if ( !$this->model_catalog_product ) {
					$this->load->model('catalog/product');
				}
				$product_options = $this->model_catalog_product->getProductOptions($product_id);
				$options = array();
				foreach ($product_options as $option) {
					if (!in_array($option['type'], array('select', 'radio', 'image'))) continue;
								
					$option_ids = Journal2Utils::getProperty($this->request->post, 'option.' . $option['product_option_id'], array());
					
					if (is_scalar($option_ids)) {
						$options[$option['product_option_id']] = $option_ids;
					} elseif (is_array($option_ids) && count($option_ids) > 0) {
						$options[$option['product_option_id']] = $option_ids[0];
					}
					
				}
				
				if (count($options) > 0 ) {
					$ro = $this->get_related_options_set_by_poids($product_id, $options, true);
					
					$ro_price_prefix = (isset($ro_settings['spec_price_prefix']) && $ro_settings['spec_price_prefix'] && $ro['price_prefix']) ? $ro['price_prefix'] : '=' ;
					
					if (isset($ro['current_customer_group_special_price']) && $ro['current_customer_group_special_price']) {
						$special = $ro['current_customer_group_special_price'];
					}
					
					if (isset($ro_settings['spec_ofs']) && $ro_settings['spec_ofs']) {
						$ro_prices = $this->get_ro_prices($product_id);
						if ($ro_prices && isset($ro_prices[$ro['relatedoptions_id']])) {
							$ro_stock = $ro_prices[$ro['relatedoptions_id']]['stock'];
						}
					}
					
					if ($ro != false && is_array($ro) && $ro['price'] != 0) {
						return array('price'=>$ro['price'], 'special'=>$special, 'price_prefix'=>$ro_price_prefix, 'stock'=>$ro_stock);
						//return $ro['price'];
					}
				}
			}	
		}
		
		return false;
	}
	
	
	// проверяет достаточно ли количества по связанным опциям по всем товарам корзины
	public function cart_ckeckout_stock($products) {
		
		if ($this->installed()) {
			if (is_array($products)) {
				foreach ($products as &$product) {
					if ($this->get_product_related_options_use($product['product_id'])) {
						if ($product['stock']) {
							if (isset($product['option'])&&is_array($product['option'])) {
								$poids = array();
								foreach ($product['option'] as $option) {
									$poids[$option['product_option_id']] = (int)$option['product_option_value_id'];
								}
								
								if (count($poids) > 0) {
									$product['stock'] = $this->cart_stock($product['product_id'], $poids, $product['quantity']);
								}
								
							}
						}
					}
				}
				unset($product);
			}
		}
		return $products;
		
	}
	
	public function get_ro_free_quantity($roid) {
		
		$query = $this->db->query("	SELECT RO.quantity, RO.product_id
																FROM 	" . DB_PREFIX . "relatedoptions RO
																WHERE RO.relatedoptions_id = ".$roid."
																");
		$quantity = 0;
		$product_id = 0;
		if ($query->num_rows) {
			$quantity = $query->row['quantity'];
			$product_id = $query->row['product_id'];
		}
		
		if ($product_id==0 || $quantity==0 ) return 0;
		
		
		
		$products = $this->cart->getProducts();
		foreach ($products as $product) {
			if ($product['product_id'] == $product_id) {
				$options = array();
				foreach ($product['option'] as $option) {
					$options[$option['product_option_id']] = $option['product_option_value_id'];
				}
				$ro = $this->get_related_options_set_by_poids($product_id, $options);
				if ($ro !== FALSE && $ro['relatedoptions_id'] == $roid) {
					
					return MAX(0, $quantity-$product['quantity']);
				}
			}
		}
		
		return $quantity;
		
	}
	
	
	// проверяет достаточно ли количества по связанным опциям
	public function cart_stock($product_id, $options, $quantity) {
		
		$ro = $this->get_related_options_set_by_poids($product_id, $options);
		if ($ro === FALSE || !is_array($ro) || !isset($ro['quantity'])) {
			return FALSE;
		} else {
			return ($quantity <= $ro['quantity']);
		}
		
	}
	
	private function getProductSimple($product_id) {
		
		$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'meta_title'       => $query->row['meta_title'],
				'meta_description' => $query->row['meta_description'],
				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'model'            => $query->row['model'],
				'sku'              => $query->row['sku'],
				'upc'              => $query->row['upc'],
				'ean'              => $query->row['ean'],
				'jan'              => $query->row['jan'],
				'isbn'             => $query->row['isbn'],
				'mpn'              => $query->row['mpn'],
				'location'         => $query->row['location'],
				'quantity'         => $query->row['quantity'],
				'stock_status'     => $query->row['stock_status'],
				'image'            => $query->row['image'],
				'manufacturer_id'  => $query->row['manufacturer_id'],
				'manufacturer'     => $query->row['manufacturer'],
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'special'          => $query->row['special'],
				'reward'           => $query->row['reward'],
				'points'           => $query->row['points'],
				'tax_class_id'     => $query->row['tax_class_id'],
				'date_available'   => $query->row['date_available'],
				'weight'           => $query->row['weight'],
				'weight_class_id'  => $query->row['weight_class_id'],
				'length'           => $query->row['length'],
				'width'            => $query->row['width'],
				'height'           => $query->row['height'],
				'length_class_id'  => $query->row['length_class_id'],
				'subtract'         => $query->row['subtract'],
				'rating'           => round($query->row['rating']),
				'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
				'minimum'          => $query->row['minimum'],
				'sort_order'       => $query->row['sort_order'],
				'status'           => $query->row['status'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],
				'viewed'           => $query->row['viewed']
			);
		} else {
			return false;
		}
		
	}
	
	
	// возвращает информацию по всем походящим наборам связанных опций
	// акции и скидки берутся для текущего покупателя
	// если цена, акции или скидки связанных опций отсутствуют, берутся обычные данные из товара
	public function get_related_options_sets_by_poids($product_id, $options) {
		
		if (!is_array($options) || count($options)==0 || !$this->get_product_related_options_use($product_id)) {
			return FALSE;
		}
		//if ($this->customer->isLogged()) {
		//	$customer_group_id = $this->customer->getCustomerGroupId();
		//} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		//}	
		
		$str_opts = "";
		foreach ($options as $product_option_id => $option_value) {
			$str_opts .= ",".$product_option_id;
		}
		$str_opts = substr($str_opts, 1);
		
		// проверяем только опции участвующие в связанных опциях
		$pvo = $this->get_product_variant_options($product_id);
		
		if (count($pvo)>0 && count($options)>0) {
		
			$query = $this->db->query("	SELECT PO.product_option_id, PO.option_id
																	FROM 	" . DB_PREFIX . "product_option PO
																	WHERE PO.product_id = ".(int)$product_id."
																		AND PO.product_option_id IN (".$str_opts.")
																		AND PO.option_id IN (".join(",",$pvo).")
																	");
			$sql_from = "";
			$sql_where = "";
			$sql_cnt = 0;
			foreach ($query->rows as $row) {
				
				if (in_array($row['option_id'], $pvo)) { 
					$sql_cnt++;
					
					$sql_from .= ", ".DB_PREFIX."relatedoptions_option ROO".$sql_cnt;
					$sql_from .= ", ".DB_PREFIX."product_option_value POV".$sql_cnt;
					
					// только подходящие опции
					$sql_where .= " AND ROO".$sql_cnt.".relatedoptions_id = RO.relatedoptions_id ";
					$sql_where .= " AND ROO".$sql_cnt.".option_id = ".$row['option_id']." ";
					
					// только подходящие значения
					$sql_where .= " AND ROO".$sql_cnt.".option_value_id = POV".$sql_cnt.".option_value_id";
					$sql_where .= " AND POV".$sql_cnt.".product_option_value_id = ".(int)$options[$row['product_option_id']]."";
				}
			}
			
			if ($sql_from!="") {
				
				$product_info = $this->getProductSimple($product_id);
				
				$query = $this->db->query("	SELECT RO.*
																					,(SELECT price
																						FROM ".DB_PREFIX."relatedoptions_special ROS
																						WHERE ROS.relatedoptions_id = RO.relatedoptions_id
																							AND ROS.customer_group_id = ".(int)$customer_group_id."
																						ORDER BY ROS.priority ASC, ROS.price ASC LIMIT 1) special
																					,(SELECT price
																						FROM ".DB_PREFIX."relatedoptions_discount ROD
																						WHERE ROD.relatedoptions_id = RO.relatedoptions_id
																							AND ROD.customer_group_id = ".(int)$customer_group_id."
																							AND ROD.quantity = '1'
																						ORDER BY ROD.priority ASC, ROD.price ASC LIMIT 1) discount	
																		FROM 	".DB_PREFIX."relatedoptions RO
																					".$sql_from."
																		WHERE RO.product_id = ".(int)$product_id."
																					".$sql_where."
																		");
				
				if ($query->num_rows) {
					$ro_data = array();
					foreach ($query->rows as $row) {
						
						// если скидка есть на 1 штуку
						$row['price'] = $row['discount'] ? $row['discount'] : $row['price'];
						
						// may be should be removed
						// цены и акции ставим из информации о товаре
						if (!$row['price'] || !isset($ro_settings['spec_price']) || !$ro_settings['spec_price']) {
							$row['price'] = $product_info['price'];
						}
						
						if (!$row['special'] || !isset($ro_settings['spec_price']) || !$ro_settings['spec_price'] || !isset($ro_settings['spec_price_special']) || !$ro_settings['spec_price_special']) {
							$row['special'] = $product_info['special'];
						}

						$ro_data[] = $row;
					}
					
					return $ro_data;
				}
				
			}
		}
		
		return FALSE;
		
	}
	
	// возвращает информацию по одному походящему набору связанных опций
	public function get_related_options_set_by_poids($product_id, $options_param, $full_equal=true) {
		
		if (!$this->installed() || !is_array($options_param) || count($options_param)==0 || !$this->get_product_related_options_use($product_id)) {
			return FALSE;
		}
		
		// keys strings to ints
		$options = array();
		foreach ($options_param as $product_option_id => $option_value) {
			$options[(int)$product_option_id] = $option_value;
		}
		
		$str_opts = "";
		foreach ($options as $product_option_id => $option_value) {
			$str_opts .= ",".$product_option_id;
		}
		$str_opts = substr($str_opts, 1);
		
		
		// проверяем только опции участвующие в связанных опциях
		$pvo = $this->get_product_variant_options($product_id);
		
		if (count($pvo)>0 && count($options)>0) {
		
			$query = $this->db->query("	SELECT PO.product_option_id, PO.option_id
																	FROM 	" . DB_PREFIX . "product_option PO
																	WHERE PO.product_id = ".(int)$product_id."
																		AND PO.product_option_id IN (".$str_opts.")
																		AND PO.option_id IN (".join(",",$pvo).")
																	");
			
			$sql_from = "";
			$sql_where = "";
			$sql_cnt = 0;
			foreach ($query->rows as $row) {
				
				if (in_array($row['option_id'], $pvo)) { 
					$sql_cnt++;
					
					$sql_from .= ", ".DB_PREFIX."relatedoptions_option ROO".$sql_cnt;
					$sql_from .= ", ".DB_PREFIX."product_option_value POV".$sql_cnt;
					
					// только подходящие опции
					$sql_where .= " AND ROO".$sql_cnt.".relatedoptions_id = RO.relatedoptions_id ";
					$sql_where .= " AND ROO".$sql_cnt.".option_id = ".$row['option_id']." ";
					
					// только подходящие значения
					$sql_where .= " AND ROO".$sql_cnt.".option_value_id = POV".$sql_cnt.".option_value_id";
					$sql_where .= " AND POV".$sql_cnt.".product_option_value_id = ".(int)$options[$row['product_option_id']]."";
					
					
				}
				
			}
			
			if ($full_equal) {
				// только полная комбинация связанных опций - все опции должны быть указаны
				if ($sql_cnt != count($pvo)) {
					return false;
				}
			}
			
			if ($sql_from!="") {
				
				$query = $this->db->query("	SELECT RO.*
																		FROM 	".DB_PREFIX."relatedoptions RO
																					".$sql_from."
																		WHERE RO.product_id = ".(int)$product_id."
																					".$sql_where."
																		");
				if ($query->num_rows) {
					$price_data = $query->row;
					$price_data['price'] = (float)$price_data['price'];
					
					$price_data['discounts'] = array();
					$price_data['specials'] = array();
					
					// добавим акцию если надо
					$ro_settings = $this->config->get('related_options');
					
					$customer_group_id = (int)$this->config->get('config_customer_group_id');
					
					if (isset($ro_settings['spec_price']) && $ro_settings['spec_price'] && isset($ro_settings['spec_price_discount']) && $ro_settings['spec_price_discount']) {
						//if ($this->customer->isLogged()) {
						//	$customer_group_id = $this->customer->getCustomerGroupId();
						//} else {
						//	$customer_group_id = (int)$this->config->get('config_customer_group_id');
						//}
						
						//$price_data['discounts'] = array();
						$query = $this->db->query("	SELECT *
																				FROM 	".DB_PREFIX."relatedoptions_discount
																				WHERE relatedoptions_id = ".(int)$price_data['relatedoptions_id']."
																					AND customer_group_id = ".(int)$customer_group_id."
																				ORDER BY quantity ASC, priority ASC, price ASC
																				");
						/*
						$query = $this->db->query("	SELECT *
																				FROM 	".DB_PREFIX."relatedoptions_discount
																				WHERE relatedoptions_id = ".(int)$price_data['relatedoptions_id']."
																					AND customer_group_id = ".(int)$customer_group_id."
																				ORDER BY priority ASC, price ASC
																				LIMIT 1
																				");
						*/
						
						if ($query->num_rows) {
							$price_data['discounts'] = $query->rows;
						}
					}
					
					if (isset($ro_settings['spec_price']) && $ro_settings['spec_price'] && isset($ro_settings['spec_price_special']) && $ro_settings['spec_price_special']) {
						
						$price_data['current_customer_group_special_price'] = false;
						$query = $this->db->query("	SELECT *
																				FROM 	".DB_PREFIX."relatedoptions_special
																				WHERE relatedoptions_id = ".(int)$price_data['relatedoptions_id']."
																					AND customer_group_id = ".(int)$customer_group_id."
																				ORDER BY priority ASC, price ASC
																				LIMIT 1
																				");
						if ($query->num_rows) {
							$price_data['specials'] = $query->rows;
							$price_data['current_customer_group_special_price'] = $query->row['price'];
							
						}
						
					}
					return $price_data;
				}
				
			}
		}
		
		return FALSE;
		
	}
	
	
	public function get_product_related_options_use($product_id) {
		
		if (!$this->installed()) return 0;
		
		$query = $this->db->query("	SELECT VP.relatedoptions_use
																FROM 	`".DB_PREFIX."relatedoptions_variant_product` VP
																WHERE	VP.product_id = ".(int)$product_id."
																");
		
		if ($query->num_rows) {
			return $query->row['relatedoptions_use'];
		} else {
			return 0;
		}
		
	}
	
	
  public function update_related_options_quantity_by_order($product_id, $quantity, $options) {
      
      if (!$this->installed()) {
        return;
      }
      
      if ( count($options) > 0 ) {
        
        // только если по товару надо минусовать количество
        $query = $this->db->query("SELECT subtract FROM " . DB_PREFIX . "product WHERE product_id = ".(int)$product_id." ");
				// и для товара используются связанные опции
        if ($query->num_rows && $query->row['subtract'] && $this->get_product_related_options_use($product_id)) {
          
          $product_options = $this->get_variant_product_options($product_id);
					
          // найдем набор связанных опций
          
          $sql_from = "";
          $sql_where = "";
          $ro_cnt = 0;
          foreach ($options as $option) {
            //if ($option['type'] == 'select' || $option['type'] == 'radio') {
            if (in_array($option['product_option_id'], $product_options)) {
              $sql_from .= ", ".DB_PREFIX . "relatedoptions_option RO".$ro_cnt.", ".DB_PREFIX . "product_option_value POV".$ro_cnt;
              $sql_where .= "
                              AND RO".$ro_cnt.".relatedoptions_id = R.relatedoptions_id
                              AND RO".$ro_cnt.".option_id = POV".$ro_cnt.".option_id
                              AND POV".$ro_cnt.".product_option_value_id = ".$option['product_option_value_id']."
                              AND POV".$ro_cnt.".option_value_id = RO".$ro_cnt.".option_value_id ";
              $ro_cnt++;
            }
          }
          
          $query = $this->db->query("SELECT R.* FROM " . DB_PREFIX . "relatedoptions R ".$sql_from."
                                      WHERE R.product_id = ".(int)$product_id." ".$sql_where);
          
          
          if ($query->num_rows) {
						
            $new_quantity = MAX(0, $query->row['quantity']-$quantity);
            $query = $this->db->query("UPDATE " . DB_PREFIX . "relatedoptions SET quantity = ".$new_quantity." WHERE relatedoptions_id = ".$query->row['relatedoptions_id']." ");
          }
          
        }
      }
      
  }
  
  
  public function get_option_types() {
		return "'select', 'radio', 'image', 'block'";
	}
  
  public function get_compatible_options() {
		
		if (!$this->installed()) {
			return array();
		}
		
		$lang_id = $this->getLanguageId($this->config->get('config_language'));
		
		$query = $this->db->query("SELECT O.option_id, OD.name FROM `".DB_PREFIX."option` O, `".DB_PREFIX."option_description` OD
															WHERE O.option_id = OD.option_id
																AND OD.language_id = ".$lang_id."
																AND O.type IN (".$this->get_option_types().")
															ORDER BY O.sort_order
															");
		
		$opts = array();
		foreach ($query->rows as $row) {
			$opts[$row['option_id']] = $row['name'];
		}
		
		return $opts;
		
	}
  
  public function get_compatible_options_values() {
		
		if (!$this->installed()) {
			return array();
		}
		
		$lang_id = $this->getLanguageId($this->config->get('config_language'));
		
		$optsv = array();
		$compatible_options = $this->get_compatible_options();
		$str_opt = "";
		foreach ($compatible_options as $option_id => $option_name) {
			$optsv[$option_id] = array('name'=>$option_name, 'values'=>array());
			$str_opt .= ",".$option_id;
		}
		if ($str_opt!="") {
			$str_opt = substr($str_opt, 1);
			$query = $this->db->query("	SELECT OV.option_id, OVD.name, OVD.option_value_id
																	FROM `".DB_PREFIX."option_value` OV, `".DB_PREFIX."option_value_description` OVD 
																	WHERE OV.option_id IN (".$str_opt.")
																		AND OVD.language_id = ".$lang_id."
																		AND OV.option_value_id = OVD.option_value_id
																	ORDER BY OV.sort_order
																	");
			foreach ($query->rows as $row) {
				$optsv[$row['option_id']]['values'][$row['option_value_id']] = $row['name'];
			}
		}
		
		return $optsv;
		
	}
  
  public function get_options_for_variant($relatedoptions_variant_id) {
		
		$options = array();
		if ($relatedoptions_variant_id == 0) {
			$copts = $this->get_compatible_options();
			$options = array_keys($copts);
		} else {
			$options = array();
			$query = $this->db->query("	SELECT VO.option_id
																	FROM `".DB_PREFIX."relatedoptions_variant_option` VO
																	WHERE relatedoptions_variant_id = ".$relatedoptions_variant_id."
																	");
			foreach ($query->rows as $row) {
				$options[] = $row['option_id'];
			}
		}
		
		return $options;
		
	}
  
  
  public function getLanguageId($lang) {
		$query = $this->db->query('SELECT `language_id` FROM `' . DB_PREFIX . 'language` WHERE `code` = "'.$lang.'"');
		return $query->row['language_id'];
	}
  
  // option_id
  public function get_product_variant_options($product_id) {
		
		$options = array();
		
		$ro_variant_id = 0;
		$query = $this->db->query("	SELECT VP.relatedoptions_variant_id
																FROM 	" . DB_PREFIX . "relatedoptions_variant_product VP
																WHERE VP.product_id = ".(int)$product_id."
																");
		if ($query->num_rows) {
			$ro_variant_id = $query->row['relatedoptions_variant_id'];
		}
		
		$options = $this->get_options_for_variant($ro_variant_id);
		return $options;
		
	}
  
  // product_option_id
  public function get_variant_product_options($product_id) {
    
    $product_options = array();
    
    if ($this->installed() && $this->get_product_related_options_use($product_id)) {
    
      $options = $this->get_product_variant_options($product_id);
      
      if (count($options) != 0) {
        $query = $this->db->query("SELECT PO.product_option_id
                                    FROM  " . DB_PREFIX . "product_option PO
                                    WHERE PO.option_id IN (".join(",",$options).")
                                  ");
        foreach($query->rows as $row) {
          $product_options[] = $row['product_option_id'];
        }
      }
    }
    
    
    return $product_options;
    
  }
	
	function check_order_product_table() {
		
		if (!$this->installed()) return;
		
		$ro_settings = $this->config->get('related_options');
		
		if (isset($ro_settings['spec_sku']) && $ro_settings['spec_sku']) {
			$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order_product` WHERE field='sku' ");
			if (!$query->num_rows) {
				$this->db->query("ALTER TABLE `".DB_PREFIX."order_product` ADD COLUMN `sku` varchar(64) NOT NULL " );
			}
		}
		
		if (isset($ro_settings['spec_upc']) && $ro_settings['spec_upc']) {
			$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order_product` WHERE field='upc' ");
			if (!$query->num_rows) {
				$this->db->query("ALTER TABLE `".DB_PREFIX."order_product` ADD COLUMN `upc` varchar(12) NOT NULL " );
			}
		}
		
		if (isset($ro_settings['spec_ean']) && $ro_settings['spec_ean']) {
			$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order_product` WHERE field='ean' ");
			if (!$query->num_rows) {
				$this->db->query("ALTER TABLE `".DB_PREFIX."order_product` ADD COLUMN `ean` varchar(14) NOT NULL " );
			}
		}
		
		if (isset($ro_settings['spec_location']) && $ro_settings['spec_location']) {
			$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order_product` WHERE field='location' ");
			if (!$query->num_rows) {
				$this->db->query("ALTER TABLE `".DB_PREFIX."order_product` ADD COLUMN `location` varchar(128) NOT NULL " );
			}
		}
		
		
	}
	
	// for pov_id if possible
	public function get_default_ro_set($product_id, $pov_id=false) {
		if (!$this->installed() || !$this->get_product_related_options_use($product_id)) {
			return FALSE;
		}
		
		$ro_settings = $this->config->get('related_options');
		if ($ro_settings && is_array($ro_settings) && isset($ro_settings['select_first']) && $ro_settings['select_first'] == 1) {
		
			if ($pov_id) {
				$query = $this->db->query("SELECT option_value_id FROM ".DB_PREFIX."product_option_value WHERE product_option_value_id = ".(int)$pov_id." ");
				if ($query->num_rows) {
					$ov_id = $query->row['option_value_id'];
				}
			}
			
		
			$query = $this->db->query("SELECT relatedoptions_id FROM " . DB_PREFIX . "relatedoptions
																	WHERE product_id = ".(int)$product_id."
																		AND (quantity > 0 OR ".( (isset($ro_settings['allow_zero_select']) ? (int)$ro_settings['allow_zero_select'] : 0) )." )
																		AND defaultselect = 1
																	ORDER BY	".(!empty($ov_id)
																							 ? "(relatedoptions_id IN (	SELECT relatedoptions_id
																																					FROM ".DB_PREFIX."relatedoptions_option
																																					WHERE product_id = ".(int)$product_id."
																																						AND option_value_id = ".(int)$ov_id." ))
																									DESC, "
																							 : "")."
																						defaultselectpriority ASC LIMIT 1 ");
			if ($query->num_rows) {
				return $query->row['relatedoptions_id'];
			}
			
			$query = $this->db->query("SELECT relatedoptions_id FROM " . DB_PREFIX . "relatedoptions
																	WHERE product_id = ".(int)$product_id."
																		AND (quantity > 0 OR ".( (isset($ro_settings['allow_zero_select']) ? (int)$ro_settings['allow_zero_select'] : 0) )." )
																	ORDER BY	".(!empty($ov_id)
																							 ? "(relatedoptions_id IN (	SELECT relatedoptions_id
																																					FROM ".DB_PREFIX."relatedoptions_option
																																					WHERE product_id = ".(int)$product_id."
																																						AND option_value_id = ".(int)$ov_id." ))
																									DESC, "
																							 : "")."
																						relatedoptions_id LIMIT 1 ");
			if ($query->num_rows) {
				return $query->row['relatedoptions_id'];
			}
		}
		
		return FALSE;
		
	}
  
  public function get_options_array($product_id, $only_zero=false) {
    
    if (!$this->installed() || !$this->get_product_related_options_use($product_id)) {
      return array();
    }
		
		$ro_settings = $this->config->get('related_options');
    
    $query = $this->db->query("SELECT RO.relatedoptions_id, PO.product_option_id, POV.product_option_value_id
                                FROM " . DB_PREFIX . "relatedoptions RO
                                    ," . DB_PREFIX . "relatedoptions_option ROO
                                    ," . DB_PREFIX . "product_option PO
                                    ," . DB_PREFIX . "product_option_value POV
                                WHERE RO.product_id = ".(int)$product_id."
																	".
                                  ( $only_zero ? "AND RO.quantity = 0" : ((isset($ro_settings['allow_zero_select']) && $ro_settings['allow_zero_select'])? "" : "AND RO.quantity > 0" ) )
																	."
                                  AND PO.product_id = ".(int)$product_id."
                                  AND POV.product_id = ".(int)$product_id."
                                  AND RO.relatedoptions_id = ROO.relatedoptions_id
                                  AND ROO.option_id = PO.option_id
                                  AND ROO.option_value_id = POV.option_value_id
                                  ");
    
    $ro_array = array();
    foreach ($query->rows as $row) {
      
      if ( !isset($ro_array[$row['relatedoptions_id']]) ) {
        $ro_array[$row['relatedoptions_id']] = array();
      }
      
      $ro_array[$row['relatedoptions_id']][$row['product_option_id']] = $row['product_option_value_id'];
      
    }
    
    return $ro_array;
    
  }
	
	public function get_ro_prices($product_id) {
		
		if (!$this->get_product_related_options_use($product_id)) {
			return FALSE;
		}
		
		$this->load->language('product/product');
		
		//if ($this->customer->isLogged()) {
		//	$customer_group_id = $this->customer->getCustomerGroupId();
		//} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		//}
		
		$ro_prices = array();
		
		$lang_id = $this->getLanguageId($this->config->get('config_language'));
		$ro_settings = $this->config->get('related_options');
		
		$query = $this->db->query("SELECT RO.relatedoptions_id
																		, RO.price ro_price
																		, RO.quantity ro_quantity
																		, RO.price_prefix ro_price_prefix
																		, ROD.price
																		, ROD.quantity
																		, RO.model
																		, RO.ean
																		, SS.name stock_status
																		, PS.name product_stock_status
															FROM ".DB_PREFIX."relatedoptions RO
															LEFT JOIN ".DB_PREFIX."relatedoptions_discount ROD ON (RO.relatedoptions_id = ROD.relatedoptions_id && ROD.customer_group_id = ".(int)$customer_group_id." )
															LEFT JOIN ".DB_PREFIX."stock_status SS ON (SS.stock_status_id = RO.stock_status_id && SS.language_id = ".(int)$lang_id." )
															, ".DB_PREFIX."product P
															LEFT JOIN ".DB_PREFIX."stock_status PS ON (PS.stock_status_id = P.stock_status_id && PS.language_id = ".(int)$lang_id." )
															WHERE RO.product_id = ".(int)$product_id."
																AND P.product_id = RO.product_id
																");
		foreach ($query->rows as $row) {
			if (!isset($ro_prices[$row['relatedoptions_id']])) {
				
				$stock = '';
				$in_stock = false;
				if (isset($ro_settings['spec_ofs'])&& $ro_settings['spec_ofs']) {
					$in_stock = true;
					if ($row['ro_quantity'] <= 0) {
						$stock = ($row['stock_status']) ? $row['stock_status'] : $row['product_stock_status'] ;
						$in_stock = false;
					} elseif ($this->config->get('config_stock_display')) {
						$stock = $row['ro_quantity'];
					} else {
						$stock = $this->language->get('text_instock');
					}
				}
				
				$ro_prices[$row['relatedoptions_id']] = array(	'price'=>$row['ro_price']
																											, 'price_prefix'=>$row['ro_price_prefix']
																											, 'model'=>$row['model']
																											, 'ean'=>$row['ean']
																											, 'stock'=>$stock
																											, 'in_stock'=>$in_stock
																											, 'discounts'=>array()
																											, 'specials'=>array()
																											, 'special'=>false);
				
				
			}
			if ($row['price']) {
				$ro_prices[$row['relatedoptions_id']]['discounts'][] = array('quantity'=>$row['quantity'], 'price'=>$row['price']);
			}
		}
		$query = $this->db->query("SELECT ROD.*
															FROM ".DB_PREFIX."relatedoptions_special ROD, ".DB_PREFIX."relatedoptions RO
															WHERE RO.product_id = ".(int)$product_id."
																AND RO.relatedoptions_id = ROD.relatedoptions_id
																AND ROD.customer_group_id = ".(int)$customer_group_id."
															ORDER BY ROD.priority ASC, ROD.price ASC	
																");
		foreach ($query->rows as $row) {
			if (isset($ro_prices[$row['relatedoptions_id']])) {
				
				$ro_prices[$row['relatedoptions_id']]['specials'][] = array('price'=>$row['price']);
				
				if ($ro_prices[$row['relatedoptions_id']]['special'] === false) {
					$ro_prices[$row['relatedoptions_id']]['special'] = $row['price'];
				}
			}
		}
		
		return $ro_prices;
		
	}
	
	public function getMinMaxRO_Price($product_id) {
		
		if ($this->installed()) {
			$product_price = 0;
			$query = $query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product WHERE product_id = ".(int)$product_id." ");
			if ($query->num_rows) {
				$product_price = $query->row['price'];
			}
			
			$prices = $this->get_ro_prices($product_id);
			
			if ($prices) {

				$min_price = false;
				$max_price = false;

				$min_discount = false;
				$max_discount = false;
				
				$min_special = false;
				$max_special = false;
				
				$ro_cnt = 0;
				$special_cnt = 0;
				
				foreach ($prices as $ro_id => $price) {
					
					$ro_cnt++;
					
					if ($min_price === false) {
						$min_price = $price['price'];
					} else {
						$min_price = min($min_price, $price['price']);
					}
					if ($max_price === false) {
						$max_price = $price['price'];
					} else {
						$max_price = max($max_price, $price['price']);
					}
					
					// для скидок надо учитывать группы покупателей, пока пропустим
					
					if (isset($price['discounts']) && is_array($price['discounts'])) {
						foreach ($price['discounts'] as $discount) {
							if ($min_discount === false) {
								$min_discount = $discount['price'];
							} else {
								$min_discount = min($min_discount, $discount['price']);
							}
							if ($max_discount === false) {
								$max_discount = $discount['price'];
							} else {
								$max_discount = max($max_discount, $discount['price']);
							}
						}
					}
					
					if (isset($price['special']) && $price['special'] !== false) {
						$special_cnt++;
						if ($min_special === false) {
							$min_special = $price['special'];
						} else {
							$min_special = min($min_special, $price['special']);
						}
						if ($max_special === false) {
							$max_special = $price['special'];
						} else {
							$max_special = max($max_special, $price['special']);
						}
					}
					/*
					if (isset($price['specials']) && is_array($price['specials'])) {
						foreach ($price['specials'] as $special) {
							if ($min_special === false) {
								$min_special = $special['price'];
							} else {
								$min_special = min($min_special, $special['price']);
							}
							if ($max_special === false) {
								$max_special = $special['price'];
							} else {
								$max_special = max($max_special, $special['price']);
							}
						}
					}
					*/
					
				}
				return array(		'product_price'=>$product_price
											, 'min'=>$min_price
											, 'max'=>$max_price
											, 'min_delta'=>$min_price-$product_price
											, 'max_delta'=>$max_price-$product_price
											, 'min_discount'=> $min_discount
											, 'max_discount'=> $max_discount
											, 'min_special'=> $min_special
											, 'max_special'=> $max_special
											, 'all_ro_have_specials'=> ($ro_cnt == $special_cnt && $ro_cnt!=0)
											);
			}
		}
		return false;
	}
  
  public function installed() {
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = 'module' AND `code` = 'related_options'");
		return $query->num_rows;
		
		return false;
		
	}


}


