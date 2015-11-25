<?php

$settings['entypo'] = FALSE;

/**
 * Semantic UI PHP-Fusion 9 Theme.
 * Please turn off Entypo.
 * @param bool $license
 */
function render_page($license = FALSE) {

    /**
     * Copy source HTML - view-source:http://semantic-ui.com/examples/fixed.html
     */
    ?>

    <!--- copied HTML from Semantic Menu --->
    <div class="ui fixed inverted menu">
        <div class="ui container">
            <a class='header item' href='<?php echo BASEDIR."index.php" ?>'>
                <img src="<?php echo IMAGES."php-fusion-icon.png" ?>"
                     alt="<?php echo fusion_get_settings("sitename") ?>"/>
                <span class="display-inline-block m-l-10"><?php echo fusion_get_settings("sitename") ?></span>
            </a>
            <a href="#" class="item">Home</a>

            <div class="ui simple dropdown item">
                Dropdown <i class="dropdown icon"></i>

                <div class="menu">
                    <a class="item" href="#">Link Item</a>
                    <a class="item" href="#">Link Item</a>

                    <div class="divider"></div>
                    <div class="header">Header Item</div>
                    <div class="item">
                        <i class="dropdown icon"></i>
                        Sub Menu
                        <div class="menu">
                            <a class="item" href="#">Link Item</a>
                            <a class="item" href="#">Link Item</a>
                        </div>
                    </div>
                    <a class="item" href="#">Link Item</a>
                </div>
            </div>
        </div>
    </div>
    <!---end copy--->

    <!--- copy HTML from Semantic for Body Section --->
    <div class="ui main text container" style="margin-top:80px;margin-bottom:80px;">
        <h1 class="ui header">Semantic UI Fixed Template</h1>

        <p>This is a basic fixed menu template using fixed size containers.</p>

        <p>A text container is used for the main container, which is useful for single column layouts</p>

        <!--- this is from PHP-Fusion API --->
        <?php
        echo CONTENT;
        ?>


    </div>
    <!---- end copy---->

    <!----- copy HTML from Senmantic for Footer Section --->
    <div class="ui inverted vertical footer segment">
        <div class="ui center aligned container">
            <div class="ui stackable inverted divided grid">
                <div class="three wide column">
                    <h4 class="ui inverted header">Group 1</h4>

                    <div class="ui inverted link list">
                        <a href="#" class="item">Link One</a>
                        <a href="#" class="item">Link Two</a>
                        <a href="#" class="item">Link Three</a>
                        <a href="#" class="item">Link Four</a>
                    </div>
                </div>
                <div class="three wide column">
                    <h4 class="ui inverted header">Group 2</h4>

                    <div class="ui inverted link list">
                        <a href="#" class="item">Link One</a>
                        <a href="#" class="item">Link Two</a>
                        <a href="#" class="item">Link Three</a>
                        <a href="#" class="item">Link Four</a>
                    </div>
                </div>
                <div class="three wide column">
                    <h4 class="ui inverted header">Group 3</h4>

                    <div class="ui inverted link list">
                        <a href="#" class="item">Link One</a>
                        <a href="#" class="item">Link Two</a>
                        <a href="#" class="item">Link Three</a>
                        <a href="#" class="item">Link Four</a>
                    </div>
                </div>
                <div class="seven wide column">
                    <h4 class="ui inverted header">Footer Header</h4>

                    <p>Extra space for a call to action inside the footer that could help re-engage users.</p>
                </div>
            </div>
            <div class="ui inverted section divider"></div>
            <img src="<?php echo IMAGES."php-fusion-logo.png" ?>" class="ui centered image">

            <div class="ui horizontal inverted small divided link list">
                <a class="item" href="#">Site Map</a>
                <a class="item" href="#">Contact Us</a>
                <a class="item" href="#">Terms and Conditions</a>
                <a class="item" href="#">Privacy Policy</a>
            </div>
        </div>
    </div>
    <!---- end copy---->
<?php
}


function check_panel_status() {

}

function opentable() {

}

function closetable() {

}

function openside() {

}

function closeside() {

}

function opensidex() {

}

function closesidex() {

}