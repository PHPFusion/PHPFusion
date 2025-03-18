<?php

use PHPFusion\Panels;

Panels::getInstance()->hideAll();

function display_loginform($info)
{

    fusion_load_script(TEMPLATES . 'phpfusion/assets/styles/login.css', 'css');
?>
    <div class="login-wrapper spacer-lg">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-md-6 col-md-offset-3">
                    <h4>Sign in to PHPFusion</h4>
                    <div class="cardx">
                        <?php
                        echo $info['open_form'];
                        echo $info['user_name'];
                        echo $info['user_pass'];
                        echo $info['remember_me'];

                        echo form_button('login', 'Sign In', 'login', ['class' => 'btn-block btn-primary btn-lg']);
                        ?>
                        <div class="m-t-10">
                            Don't have an account ? <a href="<?php echo BASEDIR ?>register.php">Sign up</a>
                        </div>
                        <div class="m-t-10"><?php echo $info['forgot_password_link'] ?></div>
                        <?php
                        echo $info['close_form'];
                        ?>

                        <div class="or-wrapper">
                            <div class="line-separator"></div>
                            <div class="or-label">or</div>
                            <div class="line-separator"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php



}
