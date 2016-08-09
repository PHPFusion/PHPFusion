<?php
/**
 * Error template
 */
if (!function_exists("display_error_page")) {
    function display_error_page($data) {
        $locale = fusion_get_locale();
        $text = $data['title'];
        $image = $data['image'];
        opentable($text);
        echo "<table class='table table-responsive' width='100%' style='text-center'>";
        echo "<tr>";
        echo "<td width='30%' align='center'><img class='img-responsive' src='".$image."' alt='".$text."' border='0'></td>";
        echo "<td style='font-size:16px;color:red' align='center'>".$text."</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td colspan='2' align='center'><b><a class='button' href='".BASEDIR."index.php'>".$locale['errret']."</a></b></td>";
        echo "</tr>";
        echo "</table>";
        closetable();
    }
}
