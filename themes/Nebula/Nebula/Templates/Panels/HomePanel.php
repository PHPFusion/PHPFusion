<?php
namespace Nebula\Templates\Panels;

/**
 * Class HomePanel
 * @package Nebula\Templates\Panels
 */
class HomePanel extends \HomePanel {

    private static $headerData = array();
    private static $list_limit = 4;
    private static $content = array();

    public static function display_page($info) {

        self::$headerData = array(
            'popular' => self::$popular_content,
            'latest' => self::$latest_content,
            'featured' => self::$featured_content,
        );
        ?>
        <div class="row">
            <?php
            $keys = array_keys(self::$headerData);
            foreach ($keys as $key) : ?>
                <div class="col-xs-12 col-sm-4 text-left">
                    <?php self::display_header($key); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($info)) : ?>
            <div class="row">
                <?php foreach ($info as self::$content) :
                    //echo floor(12 / count($info))
                    ?>
                    <div class="col-xs-12 col-sm-6 col-md-4">
                        <?php self::display_content(); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
        endif;
    }

    /**
     * @param $mode - latest, popular, featured
     */
    private static function display_header($mode) {

        $label = array(
            'latest' => "Latest",
            'popular' => "Popular",
            'featured' => "Featured",
        );
        $data = self::$headerData[$mode][0];
        ?>
        <div class="panel panel-home">
            <figure>
                <?php if (!empty($data['image'])) : ?>
                    <img class="center-x" src="<?php echo $data['image'] ?>" alt="<?php echo $data['title'] ?>"/>
                <?php endif; ?>
            </figure>

            <div class="panel-body">
                <h2><?php echo $label[$mode] ?></h2>

                <p>
                    <a href="<?php echo $data['url'] ?>" title="<?php echo $data['title'] ?>">
                        <?php echo trim_text($data['title'], 70); ?>
                    </a>
                </p>

                <p>
                    <?php echo trim_text($data['content'], 500); ?>
                </p>
                <?php echo $data['meta']; ?>
            </div>
            <div class="panel-footer">
                <a href="">See All <span class="fa fa-caret-right pull-right"></span></a>
            </div>
        </div>
        <?php
    }

    private static function display_content() {
        ?>
        <div class="panel panel-home">
            <div class="panel-heading">
                <?php echo self::$content['blockTitle'] ?>
            </div>
            <?php if (!empty(self::$content['data'])) : ?>
                <ul class="panel-body">
                    <?php foreach (self::$content['data'] as $data) : ?>
                        <li>
                            <?php if ($data['image']) : ?>
                                <figure>
                                    <a href="<?php echo $data['url'] ?>" title="<?php echo $data['title'] ?>">
                                        <img class="center-x center-y" src="<?php echo $data['image'] ?>" alt="<?php echo $data['title'] ?>"/>
                                    </a>
                                </figure>
                            <?php endif; ?>
                            <div class="list-body">
                                <a href="<?php echo $data['url'] ?>" title="<?php echo $data['title'] ?>">
                                    <div>
                                        <?php echo trim_text($data['content'], 50) ?>
                                    </div>
                                </a>
                            </div>
                        </li>
                        <?php
                        self::$list_limit--;
                        if (self::$list_limit === 0) {
                            break;
                        }
                    endforeach;
                    ?>
                </ul>
            <?php else: ?>

                <div class="panel-body"><?php echo self::$content['norecord'] ?></div>

            <?php endif; ?>

            <div class="panel-footer">
                <a href="">See All <span class="fa fa-caret-right pull-right"></span></a>
            </div>
        </div>
        <?php
    }

}