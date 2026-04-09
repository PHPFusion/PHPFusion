<?php
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';

function form($id = 0, $action = '') {
    set_title('Directory Categories');

    $aidlink = fusion_get_aidlink();

    $cat_id = 0;
    $cat_name = '';
    $cat_parent = 0;
    $cat_slug = '';
    $cat_description = '';
    $cat_meta_title = '';
    $cat_meta_keywords = '';
    $cat_meta_description = '';

    if ($action == 'edit') {

        if ($data = load_category($id)) {

            $cat_id = $data['cat_id'];
            $cat_name = $data['cat_name'];
            $cat_parent = $data['cat_parent'];
            $cat_slug = $data['cat_slug'];
            $cat_description = $data['cat_description'];
            $cat_meta_title = $data['cat_meta_title'];
            $cat_meta_keywords = $data['cat_meta_keywords'];
            $cat_meta_description = $data['cat_meta_description'];
        }
    }

    if ($action == 'add') {
        $cat_parent = $id;
    }

    if (check_post('save')) {
        $data = [
            'cat_id'               => sanitizer('id', '', 'id'),
            'cat_name'             => sanitizer('name', '', 'name'),
            'cat_slug'             => sanitizer('slug', '', 'slug'),
            'cat_parent'           => sanitizer('parent', '', 'parent'),
            'cat_description'      => sanitizer('description', '', 'description'),
            'cat_meta_title'       => sanitizer('title', '', 'title'),
            'cat_meta_keywords'    => sanitizer('keywords', '', 'keywords'),
            'cat_meta_description' => sanitizer('meta', '', 'meta'),
        ];

        $cat_id = $data['cat_id'];
        $cat_name = $data['cat_name'];
        $cat_description = $data['cat_description'];
        $cat_slug = $data['cat_slug'];
        $cat_meta_title = $data['cat_meta_title'];
        $cat_meta_keywords = $data['cat_meta_keywords'];
        $cat_meta_description = $data['cat_meta_description'];
        $cat_parent = $data['cat_parent'];

        if (fusion_safe()) {

            if ($data['cat_id']) {
                $mode = 'update';
            } else {
                $mode = 'save';
                $data['cat_order'] = (int)dbcount("(cat_id)", DB_CATEGORY, "cat_parent=:pid", [':pid' => $cat_parent]) + 1;
            }

            $cat_id = dbquery_insert(DB_CATEGORY, $data, $mode) ?: $cat_id;

            redirect(ADMIN.'category.php'.$aidlink.($cat_id ? '&cat='.$cat_parent : ''));
        }
    }

    add_to_jquery("
    $('#slug_enabled').on('change', function() {
        
        if ($(this).is(':checked')) {
            $('.slug-url').show();
        } else {
            $('.slug-url').hide();
        }
    });
    ");

    $cat_index = dbquery_tree(DB_CATEGORY, 'cat_id', 'cat_parent');
    $cat_childs = get_child($cat_index, $cat_id) ?: [];
    $disabled_opts = array_merge_recursive([$cat_id], $cat_childs);

    opentable('Directory Categories');

    echo openform('inputfrm', 'POST');

    $tab['title'] = ['Configuration', 'Meta Tags & Title'];
    $tab['id'] = ['cfg', 'cfm'];

    $tab_active = tab_active($tab, 0);

    echo opentab($tab, $tab_active, 'dirFrm');

    echo opentabbody($tab['title'][0], $tab['id'][0], $tab_active);

    echo form_text('name', 'Name', $cat_name, [
            'required' => TRUE,
            'inline'   => TRUE,
        ])
        .form_checkbox('slug_enabled', 'Manually enter Friendly URL slug?', (empty($cat_slug) ? 0 : 1), ['toggle' => TRUE, 'inline' => TRUE, 'reverse_label' => FALSE]).

        '<div class="slug-url" '.(empty($cat_slug) ? 'style="display:none;"' : '').'>'
        .form_text('slug', 'Slug URL', $cat_slug, ['inline' => TRUE])
        .'</div>'

        .form_hidden('id', '', $cat_id)

        .form_textarea('description', 'Description', $cat_description, [
            'type'        => 'tinymce',
            'inline'      => TRUE,
            'path'        => IMAGES.'cats/',
            'placeholder' => 'Add a description'])

        .form_select('parent', 'Parent', $cat_parent, [
            'db'           => DB_CATEGORY,
            'id_col'       => 'cat_id',
            'cat_col'      => 'cat_parent',
            'title_col'    => 'cat_name',
            'inline'       => TRUE,
            'disable_opts' => $disabled_opts,
            'stacked'      => '<div class="flex m-t-5"><span class="m-r-10">or</span> '.form_checkbox('cat_none', 'No Parent', (!$cat_parent ? 1 : 0), ['toggle' => TRUE]).'</div>']);

    echo closetabbody();

    echo opentabbody($tab['title'][1], $tab['id'][1], $tab_active);

    echo form_text('title', 'Category Title', $cat_meta_title, ['required' => FALSE, 'inline' => TRUE])
        .form_select('keywords', 'Meta Keywords', $cat_meta_keywords, ['tags' => TRUE, 'multiple' => TRUE, 'inline' => TRUE])
        .form_textarea('meta', 'Meta Description', $cat_meta_description, ['type'=>'tinymce', 'inline' => TRUE]);

    echo closetabbody();
    echo closetab();

    echo '<div class="btn-wrapper">';
    echo form_button('save', 'Save', 'save', ['class' => 'btn-primary btn-md']);
    echo '</div>';
    echo closeform();
    closetable();
}

function load_category($id = 0) {
    $aidlink = fusion_get_aidlink();
    $data = [];

    if ($id) {

        $result = dbquery("SELECT * FROM ".DB_CATEGORY." WHERE cat_id=:id", [':id' => $id]);

        if (dbrows($result)) {
            return dbarray($result);

        } else {
            redirect(ADMIN.'category'.$aidlink);
            die();
        }

    } else {

        $result = dbquery("SELECT * FROM ".DB_CATEGORY." ORDER BY cat_order");
        if (dbrows($result)) {
            while ($rows = dbarray($result)) {
                $data[] = $rows;
            }
        }

        return $data;
    }

}

function listing() {

    $aidlink = fusion_get_aidlink();

    $add_link = ADMIN.'category.php'.$aidlink.'&action=add'.(get('cat',FILTER_VALIDATE_INT)?'&id='.get('cat', FILTER_VALIDATE_INT) : '');

    opentable('Directory Categories <a href="'.$add_link.'" class="btn btn-default btn-sm">Add Category</a>');

    $index = (int)get('cat', FILTER_VALIDATE_INT) ?: 0;

    if ($index) :
        $sql = "SELECT c.cat_parent, IF(cr.cat_name, cr.cat_name, 'Categories') 'cat_name' 
        FROM ".DB_CATEGORY." c 
        LEFT JOIN ".DB_CATEGORY." cr on cr.cat_id=c.cat_parent
        WHERE c.cat_id=$index";

        $parent_query = dbquery($sql);

        if (dbrows($parent_query)) :
            $pdata = dbarray($parent_query);
            ?>
            <div class="list-group-item m-b-20">
                <a href="<?php
                echo ADMIN.'category.php'.$aidlink.'&cat='.$pdata['cat_parent'] ?>" class="btn btn-default btn-md">
                    <i class="far fa-angle-left fa-fw"></i>
                    <?php
                    echo $pdata['cat_name']
                    ?>
                </a>
            </div>
        <?php
        endif;
        ?>
    <?php

    endif;
    ?>

    <div class="list-group-item">
        <ol class="fscTree">
            <?php
            if ($data = dbquery_tree_full(DB_CATEGORY, 'cat_id', 'cat_parent')) :
                foreach ($data[$index] as $value) : ?>
                    <li class="fscBranch">
                        <div class="flex r-center">
                            <div class="m-r-10"><a href="#"><i class="far fa-bars"></i></a></div>
                            <div>
                                <?php
                                if (isset($data[$value['cat_id']])) : ?>
                                    <a class="strong" href="<?php
                                    echo ADMIN.'category.php'.$aidlink.'&cat='.$value['cat_id'] ?>"><?php
                                        echo $value['cat_name'] ?></a>

                                <?php
                                else:
                                    echo $value['cat_name'];
                                endif;
                                ?>
                            </div>
                            <div class="flex ml-auto ">
                                <a href="<?php
                                echo ADMIN.'category.php'.$aidlink.'&action=add&id='.$value['cat_id'] ?>" class="btn btn-default btn-xs m-r-5"><i class="far fa-plus-circle"></i></a>
                                <a href="<?php
                                echo ADMIN.'category.php'.$aidlink.'&action=edit&id='.$value['cat_id'] ?>" class="btn btn-default btn-xs m-r-5"><i class="far fa-edit"></i></a>
                                <div class="dropdown">
                                    <a href="#" data-toggle="dropdown" class="btn btn-default btn-xs"><i class="far fa-angle-down"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li>
                                            <a href="<?php
                                            echo ADMIN.'category.php'.$aidlink.'&action=copy&id='.$value['cat_id'] ?>"><i class="far fa-copy fa-fw m-r-5"></i>Copy</a>
                                            <a href="<?php
                                            echo ADMIN.'category.php'.$aidlink.'&action=move&id='.$value['cat_id'] ?>"><i class="far fa-arrow-right fa-fw m-r-5"></i>Move Content</a>
                                            <a href="<?php
                                            echo ADMIN.'category.php'.$aidlink.'&action=del&id='.$value['cat_id'] ?>"><i class="far fa-times-circle fa-fw m-r-5"></i>Delete</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php
                endforeach;
            else:
                ?>
                <li class="text-center">
                    There are no category defined
                </li>
            <?php
            endif;
            ?>
        </ol>
    </div>
    <?php
    closetable();
}

function del($cat_id) {
    $aidlink = fusion_get_aidlink();

    if (isnum($cat_id)) {
        // if have child.. need to modal it.
        $cat_index = dbquery_tree(DB_CATEGORY, 'cat_id', 'cat_parent');

        $cat_childs = get_child($cat_index, $cat_id) ?: [];

        $disabled_opts = array_merge_recursive([$cat_id], $cat_childs);

        $res = dbquery("SELECT cat_id FROM ".DB_CATEGORY." WHERE cat_parent=:cid", [':cid' => $cat_id]);
        if (dbrows($res)) {

            if (check_post('del')) {

                if (post('del_child', FILTER_VALIDATE_INT)) {
                    $child_in = "'".implode("','", $disabled_opts)."'";

                    dbquery("DELETE FROM ".DB_CATEGORY." WHERE cat_id IN ($child_in)");
                    add_notice('success', 'All categories has been deleted');

                } else if ($move_to = post('move', FILTER_VALIDATE_INT)) {

                    $child_in = "'".implode("','", $cat_childs)."'";

                    dbquery("UPDATE ".DB_CATEGORY." SET cat_parent=:moveto WHERE cat_id IN ($child_in)", [':moveto' => $move_to]);
                    dbquery("DELETE FROM ".DB_CATEGORY." WHERE cat_id=:cid", [':cid' => $cat_id]);
                    add_notice('success', 'Categories has been deleted and child category has been moved');
                }

                redirect(ADMIN.'category.php'.$aidlink);
            }

            echo openmodal('del', '<h4>Delete</h4>');
            echo openform('moveFrm', 'POST');
            echo form_select('move', 'Move child categories to', '', [
                'db'           => DB_CATEGORY,
                'id_col'       => 'cat_id',
                'cat_col'      => 'cat_parent',
                'title_col'    => 'cat_name',
                'disable_opts' => $disabled_opts,
                'no_root'      => TRUE,
                'stacked'      => '<div class="flex m-t-5"><span class="m-r-10">or</span> '.form_checkbox('del_child', 'Delete children', 1).'</div>'
            ]);
            echo modalfooter(form_button('del', 'Delete', 'del', ['class' => 'btn-primary btn-md']));
            echo closeform();
            echo closemodal();

        } else {

            dbquery("DELETE FROM ".DB_CATEGORY." WHERE cat_id=:cid", [':cid' => $cat_id]);

            add_notice('success', 'Category has been deleted');

            redirect(ADMIN.'category.php'.$aidlink);
        }
    }

}

// Control Script

if ($action = get('action')) {

    if ($action == 'add') {
        if ($id = get('id', FILTER_VALIDATE_INT)) {
            form($id, $action);
        } else {
            form();
        }
    } else if ($action == 'edit') {

        if ($id = get('id', FILTER_VALIDATE_INT)) {

            form($id, $action);

        } else {

            redirect(clean_request('', ['action', 'id'], FALSE));
        }

    } else if ($action == 'del') {

        if ($id = get('id', FILTER_VALIDATE_INT)) {
            del($id);
        } else {
            redirect(clean_request('', ['action', 'id'], FALSE));
        }

    } else {

        add_notice('danger', 'Unable to pefrom the action.');
        redirect(clean_request('', ['action'], FALSE));
    }

} else {

    listing();
}

require_once THEMES.'templates/footer.php';
