<?php

class ModelModuleSpecialLabel extends Model {

    public function getLabel($language_id) {
        $q = $this->db->query("SELECT label FROM `" . DB_PREFIX . "special_label` WHERE language_id = $language_id");
        return $q->row['label'];
    }

    /*public function getSpecial($data, $special) {

        if (isset($data['has_option']) && $data['has_option'] == 1) {
            $product_id = $data['product_id'];
            $price = ($data['price']);
            $q = $this->db->query("SELECT price FROM `" . DB_PREFIX . "product_option_variant` WHERE product_id = $product_id AND active = 1");
            $prices = array();
            foreach ($q->rows as $row) {
                if ((($row['price']) > 0)) {
                    $prices[] = ($row['price']);
                }
            }
            $this->log->write($data);
            if (count($prices) < 1) {
                return $special;
            }
            $min_prices = min($prices);
            $this->log->write("min prices: $min_prices");
            return ($min_prices < $price) ? $min_prices : $special;
        } else {
            return $special;
        }
    }*/

}
