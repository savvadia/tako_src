<?php

class ModelModuleSpecialLabel extends Model {

    public function getLabels() {
        $q = $this->db->query("SELECT b.*,a.label FROM `" . DB_PREFIX . "special_label` as a LEFT JOIN `" . DB_PREFIX . "language` as b ON a.language_id = b.language_id");
        foreach ($q->rows as $key => $row) {
            $img = "language/".$row['code']."/".$row['code'].".png";
            if (!file_exists(DIR_APPLICATION .$img)) {
                $img = "view/image/flags/".$row['image'];
            }
            $q->rows[$key]['image'] = $img;    
        }
        return $q->rows;
    }

    public function createSpecialLabelTable() {
        $this->db->query("CREATE TABLE `" . DB_PREFIX . "special_label` (
				`language_id` INT(11) NOT NULL UNIQUE,
				`label` varchar(255) NOT NULL,
				PRIMARY KEY (`language_id`),
                                FOREIGN KEY (language_id) REFERENCES " . DB_PREFIX . "language(language_id) ON DELETE CASCADE
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

        $q = $this->db->query("SELECT language_id FROM `" . DB_PREFIX . "language`");

        foreach ($q->rows as $row) {
            $language_id = $row['language_id'];
            $this->db->query("INSERT INTO " . DB_PREFIX . "special_label SET language_id = '" . (int) $language_id . "', label = 'Special!'");
        }
    }

    public function deleteSpecialLabelTable() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "special_label`");
    }

    public function updateLabels($labels) {
        foreach ($labels as $_label) {
            $label = $_label['label'];
            $language_id = $_label['language_id'];
            $this->db->query("INSERT INTO `" . DB_PREFIX . "special_label` (language_id,label) "
                    . "VALUES ('$language_id','$label') ON DUPLICATE KEY UPDATE label=VALUES(label)");
        }
    }

}
