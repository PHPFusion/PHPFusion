<?php
namespace PHPFusion\Pro\Classes;

use PHPFusion\Admins;
use PHPFusion\BreadCrumbs;

/**
 * Admin helper class
 */
class AdminHelper extends Admins {

    public function __construct() {
        $this->setAdmin();
    }

    /* Admin pages */
    public function viewThemeAdminPages(): array {
        //$locale = fusion_get_locale();

        //
        //if (!empty($admin_pages)) {
        //    foreach ($admin_pages as $keys => $pages) {
        //        foreach ($pages as $index => $apage) {
        //            if (checkrights($apage['admin_rights'])) {
        //                if ($index != 5) {
        //                    $apage['admin_title'] = $locale[$apage['admin_rights']] ?? $apage['admin_title'];
        //                }
        //            }
        //        }
        //    }
        //}

        return $this->getAdminPages();
    }

    /* Admin sections */
    public function viewThemeAdminSections(): array {
        $admin_sections = $this->getAdminSections();
        if (!empty($admin_sections)) {
            foreach ($admin_sections as $index => $section) {
                $admin_sections[$index] = [
                    'title' => $section,
                    'icon'  => $this->admin_section_icons[$index]
                ];
            }
        }

        unset($admin_sections[0]);
        unset($admin_sections[1]);

        return $admin_sections;
    }

    public function getSettingsURI() {
        return $this->settings_uri;
    }

    public function getDashboardURI() {
        return $this->dashboard_uri;
    }

    /**
     * Format breadcrumbs
     *
     * @return array
     */
    public function getAdminBreadcrumbs(): array {
        $breadcrumbs = BreadCrumbs::getInstance();
        $arr = $breadcrumbs->toArray();
        unset($arr[0]);
        unset($arr[1]);

        return $arr;
    }

    /* Notices */
    public function getAdminNotices(): string {
        return $this->renderNotices(getNotices());
    }

    private function renderNotices($notices) {

        $messages = '';

        foreach ( $notices as $status => $notice ) {

            if ($status !='success') {
                $icon = 'lightbulb';
                if ( $status == 'warning' ) {
                    $icon = 'bell';
                }
                else if ( $status == 'danger' ) {
                    $icon = 'lightbulb-exclamation';
                }

                $messages .= "<div class='admin-message alert alert-" . $status . " alert-dismissible' role='alert'>";

                $messages .= "<button type='button' class='close' data-dismiss='alert'><span aria-hidden='true'>&times;</span></button>";
                $messages .= '<div class="flex gap-15 ac"><i class="fal fa-' . $icon . ' fa-lg"></i><div>';
                $messages .= implode('<br/>', $notice);
                $messages .= "</div>\n</div>\n";
                $messages .= '</div>';

            }

        }

        return $messages;
    }

}
