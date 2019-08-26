<?php
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';



echo openform('some_form', 'post');

echo form_button('submit', 'Post', 'post');

echo closeform();


require_once THEMES.'templates/footer.php';