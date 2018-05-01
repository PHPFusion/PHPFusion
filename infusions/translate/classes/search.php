<?php
namespace Translate;

class Search {

    private $keywords = '';
    private static $locale = [];

    public function __construct() {
        self::$locale = fusion_get_locale('', TRANSLATE_LOCALE);
    }

    public function set_search_keywords($value) {
        $this->keywords = $value;
    }

    public function get_search_keywords($pairs) {
        $array = [];
        for($i =1; $i<=$pairs; $i++) {
            $array[':keywords_0'.$i] = $this->keywords;
        }
        return $array;
    }

    public function display_search_result() {
        $sql_1 = "SELECT *, MATCH (package_name) AGAINST (:keywords_01 IN NATURAL LANGUAGE MODE) 'score'  
        FROM ".DB_TRANSLATE_PACKAGE." WHERE MATCH (package_name) AGAINST (:keywords_02 IN NATURAL LANGUAGE MODE)
        GROUP BY package_id ORDER BY score DESC
        ";
        $sql_2 = "SELECT * FROM ".DB_TRANSLATE_FILES." WHERE file_name=:keywords_01";

        $sql_3 = "SELECT tr.*, f.file_id, f.file_package 
        FROM ".DB_TRANSLATE." tr 
        INNER JOIN ".DB_TRANSLATE_FILES." f ON tr.translate_file_id=f.file_id
        WHERE tr.translate_locale_key=:keywords_01 ORDER BY f.file_id
        ";

        $sql_4 = "SELECT *, MATCH (translate_locale_value) AGAINST (:keywords_01 IN NATURAL LANGUAGE MODE) 'score'  
        FROM ".DB_TRANSLATE." WHERE MATCH (translate_locale_value) AGAINST (:keywords_02 IN NATURAL LANGUAGE MODE) 
        GROUP BY translate_id ORDER BY score DESC
        ";

        $result_1 = dbquery($sql_1, $this->get_search_keywords(2));
        $rows_1 = dbrows($result_1);
        $result_2 = dbquery($sql_2, $this->get_search_keywords(1));
        $rows_2 = dbrows($result_2);
        $result_3 = dbquery($sql_3, $this->get_search_keywords(1));
        $rows_3 = dbrows($result_3);
        //$result_4 = dbquery($sql_4, $this->get_search_keywords(2));
        //$rows_4 = dbrows($result_4);
        $rows_4 = 0;

        $total_search_results = $rows_1 + $rows_2 + $rows_3 + $rows_4;

        opentable(self::$locale['translate_0100']);
        if ($total_search_results) {
            if ($rows_1) {
                while ($data = dbarray($result_1)) {
                    echo "<div class='list-group-item'>\n";
                    echo "<h4><a href='".Translate_URI::get_link('view_package', $data['package_id'])."'>".$data['package_name']."</a>\n</h4>";
                    echo "<p>".$data['score']."</p>\n";
                    echo "</div>\n";
                }
            }
            if ($rows_2) {
                while ($data = dbarray($result_2)) {
                    echo "<div class='list-group-item'>\n";
                    echo "<h4><a href='".Translate_URI::get_link('view_translations', $data['file_package'], $data['file_id'])."'>".$data['file_name']."</a>\n</h4>";
                    echo "</div>\n";
                }
            }
            if ($rows_3) {
                while ($data = dbarray($result_3)) {
                    echo "<div class='list-group-item'>\n";
                    echo "<h4><a href='".Translate_URI::get_link('view_translations', $data['file_package'], $data['file_id'])."'>".$data['translate_locale_key']."</a>\n</h4>";
                    echo "</div>\n";
                }
            }
        } else {
            echo "<div class='text-center'>Your keywords <strong>".$this->keywords."</strong> did not match any records.</div>";
        }
        closetable();



    }
}