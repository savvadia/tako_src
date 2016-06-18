<?php
class ModelCatalogCategoryMgrLite extends Model {

    public function getChildren($category_id) {
        $query = $this->db->query("SELECT *, (SELECT COUNT(parent_id) FROM " . DB_PREFIX . "category WHERE parent_id = c.category_id) AS children FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.parent_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY c.sort_order, cd.name");
        return $query->rows;
    }
    public function getCategory($category_id) {
        $query = $this->db->query("SELECT * FROM ". DB_PREFIX . "category WHERE category_id = '" . (int)$category_id. "'");
        return $query->row;
    }

    public function getTotalProducts($data = array()) {
        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";

        if (!empty($data['filter_category_id'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
        }

        $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
        }

        if (!empty($data['filter_model'])) {
            $sql .= " AND LCASE(p.model) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_model'])) . "%'";
        }

        if (!empty($data['filter_price'])) {
            $sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
        }

        if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
            $sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
        }

        if (!empty($data['filter_category_id'])) {
            if ($data['filter_category_id'] == 'null') {
                $sql .= " AND p2c.category_id is null ";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
            }
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getProducts($data = array()) {
        if ($data) {
            $sql = "SELECT p.*, pd.* FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";


            if (!empty($data['filter_category_id'])) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
            }

            $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

            if (!empty($data['filter_name'])) {
                $sql .= " AND LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
            }

            if (!empty($data['filter_model'])) {
                $sql .= " AND LCASE(p.model) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_model'])) . "%'";
            }

            if (!empty($data['filter_price'])) {
                $sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
            }

            if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
                $sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
            }

            if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
                $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
            }

            if (!empty($data['filter_category_id'])) {

                if ($data['filter_category_id'] == 'null') {
                    $sql .= " AND p2c.category_id is null ";
                } else {
                    $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
                }

            }

            $sql .= " GROUP BY p.product_id";

            $sort_data = array(
                'name' => 'pd.name',
                'model' => 'p.model',
                'price' => 'p.price',
                'quantity' => 'p.quantity',
                'status_text' => 'p.status'
            );

            if (isset($data['sort']) && array_key_exists($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $sort_data[$data['sort']];
            } else {
                $sql .= " ORDER BY pd.name";
            }

            if (isset($data['order']) && (strtoupper($data['order']) == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->db->query($sql);

            return $query->rows;
        } else {
            $product_data = $this->cache->get('product.' . (int)$this->config->get('config_language_id'));

            if (!$product_data) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY pd.name ASC");

                $product_data = $query->rows;

                $this->cache->set('product.' . (int)$this->config->get('config_language_id'), $product_data);
            }

            return $product_data;
        }
    }
}
?>
