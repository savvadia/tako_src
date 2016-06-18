<?php

class ModelModuleImageOptionPreview extends Model{
    
    public function getImageOptions($language_id){
        $sql = "SELECT b.option_id,a.name "
                . "FROM `" . DB_PREFIX . "option_description` as a "
                . "LEFT JOIN `" . DB_PREFIX . "option` as b "
                . "ON a.option_id = b.option_id "
                . "WHERE b.type = 'image' AND a.language_id = $language_id";
        $q = $this->db->query($sql);
        return $q->rows;
    }
    
    
}