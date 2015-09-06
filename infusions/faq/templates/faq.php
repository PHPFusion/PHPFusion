<?php

function render_faq_main($info) {
	opentable($info['faq_title']);
	echo "<!--pre_faq_idx-->\n";
	if (!empty($info['items']) && count($info['items'])) {
		echo "<div class='list-group'>\n";
		foreach($info['items'] as $data) {
			echo "<div class='list-group-item'>\n";
			echo "<h4 style='width:100%'><a href='".$data['faq_link']."'>".$data['faq_cat_name']."</a><span class='badge pull-right'>".$data['faq_count']."</span></h4>\n";
			if ($data['faq_cat_description']) {
				echo $data['faq_cat_description'];
			}
			echo "</div>\n";
		}
		echo "</div>\n";
	} else {
		echo "<div class='well text-center'>".$info['nofaqs']."</div>\n";
	}
	closetable();
}
