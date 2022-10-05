<?php
require_once __DIR__."/../maincore.php";
require_once FUSION_HEADER;


echo openmodal("test", "Modal for Test", ["centered"=>TRUE]);
echo lorem_ipsum(300);
echo modalfooter(form_button("test_upload", "Test", "test"));
echo closemodal();

require_once FUSION_FOOTER;
