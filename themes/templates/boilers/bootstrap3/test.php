<?php
require_once __DIR__.'/../../../../maincore.php';
require_once THEMES.'templates/header.php';
require_once INCLUDES.'theme_functions_include.php';

// Start Testing Documentation
echo "<div style='background:#fff;'>\n";
echo "<div class='container'>\n";
echo "<h1>Bootstrap 3 Boilerplate Components</h1>";
echo '
<h1 class="text-light">Modal</h1>
<h3 class="text-light">Use Bootstrap’s JavaScript modal plugin to add dialogs to your site for lightboxes, user notifications, or completely custom content.</h3>
<h2 id="how-it-works"><div>How it works<a class="anchorjs-link " href="#how-it-works" aria-label="Anchor" data-anchorjs-icon="#" style="padding-left: 0.375em;"></a></div></h2>
<p>PHP-Fusion Dynamics Boilerplate Bridge</p>
<ul class="list">
  <li>Modals are built with HTML, CSS, and JavaScript. They’re positioned over everything else in the document and remove scroll from the <code class="highlighter-rouge">&lt;body&gt;</code> so that modal content scrolls instead.</li>
  <li>Clicking on the modal “backdrop” will automatically close the modal.</li>
  <li>Bootstrap only supports one modal window at a time. Nested modals aren’t supported as we believe them to be poor user experiences.</li>
  <li>Modals use <code class="highlighter-rouge">position: fixed</code>, which can sometimes be a bit particular about its rendering. Whenever possible, place your modal HTML in a top-level position to avoid potential interference from other elements. You’ll likely run into issues when nesting a <code class="highlighter-rouge">.modal</code> within another fixed element.</li>
  <li>Once again, due to <code class="highlighter-rouge">position: fixed</code>, there are some caveats with using modals on mobile devices. <a href="/docs/4.0/getting-started/browsers-devices/#modals-and-dropdowns-on-mobile">See our browser support docs</a> for details.</li>
  <li>Due to how HTML5 defines its semantics, <a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-autofocus">the <code class="highlighter-rouge">autofocus</code> HTML attribute</a> has no effect in Bootstrap modals. To achieve the same effect, use some custom JavaScript:</li>
</ul>
';
echo '<h3>The codes</h3>
<div class="list-group-item">
$modal = openmodal(\'test\', \'This is modal title\', [\'button_id\'=>\'test-modal\']);<br/>
$modal .= lorem_ipsum(300);<br/>
$modal .= modalfooter(form_button(\'save_changes\', "Save Changes", \'save_changes\', [\'class\'=>\'btn-primary \', \'icon\'=>\'fas fa-edit\']));<br/>
$modal .= closemodal();<br/>
<code>Note: Use add_to_footer function to ensure z-index is highest value.</code><br/>
add_to_footer($modal);<br/> 
</div>';

// ID for template object - `modal`.
echo "<a href='#' class='btn btn-primary btn-md spacer-md m-b-50' id='test-modal'>Trigger Modal</a>\n";
$modal = openmodal('test', 'This is modal title', ['button_id'=>'test-modal']);
$modal .= lorem_ipsum(300);
$modal .= modalfooter(form_button('save_changes', "Save Changes", 'save_changes', ['class'=>'btn-primary ', 'icon'=>'fas fa-edit']));
$modal .= closemodal();
add_to_footer($modal);

echo '<h1 class="text-light">Badges</h1>
<h3 class="text-light">Documentation and examples for badges, our small count and labeling component.</h3>';
echo "<div class='spacer-md'>".badge('Label', array("icon"=>"fas fa-users m-r-10"))."</div>";
echo '<h3>The codes</h3>
<div class="list-group-item m-b-50">
echo badge(\'Label\', array("icon"=>"fas fa-users");<br/> 
</div>';


echo '<h1 class="text-light">Label</h1>
<h3 class="text-light">Documentation and examples for labels.</h3>';
echo "<div class='spacer-md'>".label('Label', array("icon"=>"fas fa-users m-r-10", "class"=>"label-primary"))."</div>";
echo '<h3>The codes</h3>
<div class="list-group-item m-b-50">
echo label(\'Label\', array("icon"=>"fas fa-users m-r-10", "class"=>"label-primary");<br/> 
</div>';


echo '<h1 class="text-light">Progress Bar</h1>
<h3 class="text-light">Provide up-to-date feedback on the progress of a workflow or action with simple yet flexible progress bars.</h3>
<h4>There are two methods of using the progress bar. For multiple values, use array as the number, and array as the title</h4>';
echo '<div class="list-group-item">';
echo "<div class='spacer-md'>".progress_bar([33,57, 82, 98.5], ['Some label', 'Some label 2', 'Some label 3', 'Some label 4'])."</div>";
echo '</div>';
echo '<h3>The codes</h3>
<div class="list-group-item m-b-50">
echo progress_bar( [33,57, 82, 98.5] , [\'Some label\', \'Some label 2\', \'Some label 3\', \'Some label 4\'] );<br/> 
</div>';

echo '<div class="list-group-item">';
echo "<div class='spacer-md'>".progress_bar(80, "Single Progress Bar")."</div>";
echo '</div>';
echo '<h3>The codes</h3>
<div class="list-group-item m-b-50">
echo progress_bar(70, \'Some label\');<br/> 
</div>';

echo '<h1 class="text-light">Alert Bar</h1>
<h3 class="text-light">Provide contextual feedback messages for typical user actions with the handful of available and flexible alert messages.</h3>';
echo '<div class="list-group-item">';
echo "<div class='spacer-md'>".alert("This is an alert message", ['class'=>'alert-success'])."</div>";
echo '</div>';
echo '<h3>The codes</h3>
<div class="list-group-item m-b-50">
echo alert("This is an alert message", [\'class\'=>\'alert-success\']);<br/> 
</div>';

echo '<div>These functions are still under translations to support multiple boilers</div>';
echo '<h1 class="text-light">Collapse / Accordion</h1>';
echo '<h1 class="text-light">Navigation Tabs</h1>';
echo '<h1 class="text-light">Paginations</h1>';
echo '<h1 class="text-light">Dropdowns</h1>';
echo '<h1 class="text-light">Dynamic Components - Input Fields, Selects, Checkboxes, Radios, etc.</h1>';

echo "</div>\n</div>\n";

require_once THEMES.'templates/footer.php';