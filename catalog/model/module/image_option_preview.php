<?php

class ModelModuleImageOptionPreview extends Model {

    public function getOptionName($option_id, $language_id) {
        $q = $this->db->query("SELECT name FROM `" . DB_PREFIX . "option_description` "
                . "WHERE language_id = $language_id AND option_id = $option_id");
        return $q->row['name'];
    }

    public function getImageOptionFor($product_id, $option_id, $language_id, $show_zero) {

        $product_option_id = $this->getProductOptionId($product_id, $option_id);
        if ($product_option_id) {
            $sql = "SELECT c.*,d.name,d.language_id "
                    . "FROM (SELECT a.product_option_value_id,a.quantity,a.option_value_id,b.image "
                    . "FROM (SELECT product_option_value_id,quantity,option_value_id "
                    . "FROM `" . DB_PREFIX . "product_option_value` WHERE product_option_id = $product_option_id) as a "
                    . "LEFT JOIN `" . DB_PREFIX . "option_value` as b on a.option_value_id = b.option_value_id) as c "
                    . "LEFT JOIN `" . DB_PREFIX . "option_value_description` as d "
                    . "ON c.option_value_id = d.option_value_id WHERE language_id = $language_id";

            if ($show_zero == "0") {
                $sql.= " AND quantity > 0";
            }
            $q = $this->db->query($sql);
            return array("data" => $q->rows);
        }

    }

    private function getProductOptionId($product_id, $option_id) {
        $sql = "SELECT product_option_id "
                . "FROM `" . DB_PREFIX . "product_option` as a "
                . "LEFT JOIN `" . DB_PREFIX . "option` as b "
                . "ON a.option_id = b.option_id "
                . "WHERE b.option_id = $option_id "
                . "AND a.product_id = $product_id LIMIT 1";
        $q = $this->db->query($sql);
        return (isset($q->row['product_option_id'])) ? $q->row['product_option_id'] : false;
    }

}
