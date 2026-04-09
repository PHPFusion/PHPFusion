<?php

/**
 * Hooks
 *
 * @return array
 */
function faq_infusion() {
    $submissions = fusion_get_submissions("F");
    $comments = fusion_get_comments("F");
    $ratings = fusion_get_ratings("F");
    $icon = get_image("ac_F");
    $icon_src = substr_replace($icon, "", strpos($icon, INFUSIONS), strlen(INFUSIONS));

    return [
        "id"        => "faq",
        "dashboard" => [
            "summary_panel" => [
                "count"       => (int)dbcount("(faq_id)", DB_FAQS),
                "submissions" => (int)($submissions ? count($submissions) : "0"),
                "comments"    => (int)($comments ? count($comments) : "0"),
                "ratings"     => (int)($ratings ? count($ratings) : "0"),
                "title"       => "FAQs",
                "image"       => "<img src='$icon_src' alt=''>",
                "icon"        => feather_icon("help-circle"),
            ]
        ]
    ];
}

function remove_user_faq($data) {
    $user_id = $data["user_id"];
    dbquery("DELETE FROM ".DB_FAQS." WHERE faq_user=:uid", [':uid' => $user_id]);
}
