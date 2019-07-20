<?php
namespace PHPFusion\Infusions\Forum\Classes\Profile;

use PHPFusion\Infusions\Forum\Classes\Forum_Profile;
use PHPFusion\Template;

/**
 * Class Reputation
 *
 * @package PHPFusion\Infusions\Forum\Classes\Profile
 */
class Reputation  {

    private $profile_url = '';

    private $user_data = [];

    private $locale = [];

    private $class = NULL;

    private $nav_tabs = [];

    private $nav_active = 'post';

    private $nav_sql = 'reputation-post';

    private $self_noun = '';

    /**
     * Summary constructor.
     * Lock implementation method
     *
     * @param Forum_Profile $obj
     */
    public function __construct(Forum_Profile $obj) {

        $this->profile_url = $obj->getProfileUrl().'ref=reputation&amp;';
        $type = get('type');
        $this->nav_tabs = [
            'chart' => [
                'link' => $this->profile_url.'type=chart',
                'title' => 'Graph',
                'sql' => 'reputation-chart' // by graph
            ],
            'post' => [
                'link' => $this->profile_url.'type=post',
                'title' => 'Post',
                'sql' => 'reputation-post' // by id
            ],
        ];

        if ($type && isset($this->nav_tabs[$type])) {
            $this->nav_active = $type;
            $this->nav_sql = $this->nav_tabs[$type]['sql'];
        }

        $this->profile_url = $this->profile_url.'type='.$this->nav_active.'&amp;';
        $this->user_data = $obj->getUserData();
        $this->locale = $obj->getLocale();
        $this->class = $obj;
        $this->self_noun = $obj->self_noun;
    }


    public function displayProfile() {
        $locale = fusion_get_locale();

        $ctpl = Template::getInstance('uf-forum-summary');
        $ctpl->set_template(__DIR__.'/../../templates/profile/reputation.html');

        $limit = 24;
        $this->class->setSQLResults($limit);

        $sql = $this->class->getSQL($this->nav_sql); // all types of posts.

        $rowstart = get('rowstart', FILTER_VALIDATE_INT);

        $max_count = dbrows(dbquery(str_replace('LIMIT 0,'.$limit, '', $sql)));

        $rowstart = $rowstart && $rowstart <= $max_count ? $rowstart : 0;

        $this->class->setSQLRowstart($rowstart);

        $sql = $this->class->getSQL($this->nav_sql);

        $i = 0;
        foreach($this->nav_tabs as $key => $tabs) {
            $tabs['class'] = $this->nav_active == $key ? ' class="active"' : '';
            $ctpl->set_block('nav_tabs', $tabs);
            $i++;
        }

        $count_title = format_word(number_format($this->user_data['user_reputation'],0), 'point|points');
        $ctpl->set_tag('row_count', $count_title ); // this one need the total reputation of the month

        $result = dbquery($sql);

        if ($this->nav_active == 'chart') {

            $reputation_arr = [];
            if ($row_count = dbrows($result)) {
                while ($rdata = dbarray($result)) {
                    $reputation_arr[$rdata['rep_day']][$rdata['rep_id']] = $rdata;
                }
            }

            // show the chart
            require_once INCLUDES.'charts/charts_include.php';
            $chart = new \Charts('bar');
            $current_month_days = days_current_month();
            $x_axis = range(1, $current_month_days);

            if (!empty($reputation_arr)) {
                foreach($reputation_arr as $day => $day_events) {
                    if (!empty($day_events)) {
                        foreach($day_events as $rep_id => $rep_data) {
                            $rep_chart_arr[$day] = $rep_data['points_gain'] + (!empty($rep_chart_arr[$day]) ? $rep_chart_arr[$day] : 0);
                        }
                    }
                }
            }

            $chart_data = [];
            foreach($x_axis as $day) {
                $chart_data[] = !empty($rep_chart_arr[$day]) ? $rep_chart_arr[$day] : 0;
            }

            $chart->set_categories($x_axis);
            $chart->set_data("Reputation per day", $chart_data, ["backgroundColor" => "rgba(10,149,109,0.5)", "borderColor"=>"rgba(10,149,109,1)", "fill"=>"origin" ]);

            $chart_output = $chart->display_chart('xy_charts',  [
                "display_title" => "true",
                "title" => "Reputation changes for ".showdate('%b %Y', TIME),
                "stacked" => false,
                'height' => '350px'
            ]);

            $ctpl->set_block('chart_output', ['content' => $chart_output]);

        } else {

            // Listing

            if ($row_count = dbrows($result)) {
                $reputation_arr = [];
                // grouping by day
                while ($data = dbarray($result)) {
                    $reputation_arr [$data['rep_year']][$data['rep_month']][$data['rep_day']][$data['rep_id']] = $data;
                }
                if (!empty($reputation_arr)) {
                    $rep_html = opencollapse('rep-changes');
                    foreach($reputation_arr as $year => $month_events) {
                        if (!empty($month_events)) {
                            foreach($month_events as $month => $day_events) {
                                if (!empty($day_events)) {
                                    $i = 0;
                                    foreach($day_events as $day => $daily_events) {
                                        if (!empty($daily_events)) {
                                            $rep_count = 0;
                                            $daily_rep ='';
                                            foreach($daily_events as $rep_id => $rep_data) {
                                                $rep_count = $rep_count + $rep_data['points_gain'];
                                                $rep_time = showdate('%I:%M%p', $rep_data['datestamp']);
                                                // the item format.
                                                $daily_rep .= '<tr><td class="min"><span class="label label-success strong display-inline-block">'.format_word($rep_data['points_gain'], 'point|points').'</span></td>';
                                                $daily_rep .= '<td class="min text-lighter">'.$rep_time.'</td>';
                                                $daily_rep .= '<td class="min text-lighter">'.$rep_data['rep_type'].'</td>';
                                                $daily_rep .= '<td><a title="'.$rep_data['thread_subject'].'" href="'.FORUM.'viewthread.php?thread_id='.$rep_data['thread_id'].'&amp;pid='.$rep_data['post_id'].'#post_'.$rep_data['post_id'].'">'.$rep_data['thread_subject'].'</a></td>';
                                                $daily_rep .= '</tr>';
                                            }
                                            $date = new \DateTime($year.'-'.$month.'-'.$day);
                                            $timestamp = $date->getTimestamp();

                                            $rep_count_title = '<h3 class="text-success display-inline">'.number_format($rep_count).'</h3> <span class="label p-5"><i class="fas fa-caret-up text-lighter"></i></span>';
                                            if ($rep_count < 0) {
                                                $rep_count_title = '<h3 class="display-inline">'.number_format($rep_count).'</h3>  <span class="label p-5"><i class="fas fa-caret-down text-lighter"></i></span>';
                                            }

                                            $title = $rep_count_title.' <span class="text-dark">'.showdate('%d %b\'', $timestamp).'</span>';

                                            $rep_html .= opencollapsebody($title, 'rep-changes-'.$month.'-'.$day, 'no-grouping', $i == 0 ? TRUE : FALSE);
                                            $rep_html .= "<table class='table'>";
                                            $rep_html .= $daily_rep;
                                            $rep_html .= "</table>";
                                            $rep_html .= closecollapsebody();

                                            $i++;
                                        }
                                    }
                                }

                            }
                        }
                    }
                    $rep_html .= closecollapse();

                    $ctpl->set_block('reputation_item', ['content'=>$rep_html]);
                }
                if ($max_count > $row_count) {
                    // this needs to be chunked and recounted.
                    //$ctpl->set_block('page_nav',['nav'=> makepagenav($rowstart, $limit, $max_count, 3, $this->profile_url) ]);
                }
            } else {
                $ctpl->set_block('no_thread_item', ['message' => $this->self_noun.' have no reputation changes.']);
            }
        }


        return $ctpl->get_output();
    }


}