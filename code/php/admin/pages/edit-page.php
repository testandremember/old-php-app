<?php
/**
 * Lets the admin edit or delete a page.
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: edit-page.php 1626 2004-12-24 23:13:23Z elijah $
 * @package NiftyCMS
 * @subpackage admin_pages
 */
require_once '../../testprep/site/inc/Page.class.php';
require_once '../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();
$page_id = (int) $_GET['id'];
$member_id = $Member->getId();
$edit_page_type = DB::makeSafe($_GET['type']);

$select = array('has_subtypes', 'left_menu_type', 'use_creation_info');
$result = DB::select('page_types', $select, 'WHERE name="'.$edit_page_type.'"');
$page_type_options = DB::fetchAssoc($result);
$select = array('id', 'uri', 'query_string', 'parent_id', 'title', 'heading',
                'content', 'link_title', 'show_on_menu', 'description');
if ('custom' == $page_type_options['left_menu_type']) {
    $select[] = 'left_menu';
}
if (1 == $page_type_options['has_subtypes']) {
    $select[] = 'subtype';
    $select[] = 'menu_parent_id';
}
if (1 == $page_type_options['use_creation_info']) {
    $select[] = 'author';
    $select[] = 'creation_date';
}

$result = DB::select($edit_page_type.'_pages', $select, 'WHERE id="'.$page_id.'"');
$page = DB::fetchAssoc($result);

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    $posted = DB::addSlashes($_POST);
    $redirect_uri = $settings['admin_uri'].'/pages/?type='.$edit_page_type.'&page_id='.$posted['parent_id'].'#'.$_GET['type'];
    Form::redirectOnCancel($redirect_uri);

    // If delete_page button was pressed then delete the page
    if (FALSE != array_key_exists('delete_page', $_POST)) {
        // Delete a page from the database
        DB::delete($edit_page_type.'_pages', 'id="'.$page_id.'"');
        DB::close();
        header('Location: '.$redirect_uri);
        exit;
    }

    // Create a form object
    $Form = new Form($_POST, $_POST);
    if (1 == $page_type_options['has_subtypes']) {
     //   $Form->hasPickedOption('menu_parent_id');
        $Form->hasPickedOption('subtype');
    }

    if (1 == $page_type_options['use_creation_info']) {
        $Form->hasPickedOption('author');
    }

    if (FALSE == empty($posted['show_on_menu'])) {
        $posted['show_on_menu'] = 1;
    } else {
        $posted['show_on_menu'] = 0;
    }

    $Form->isFilledIn('uri');
    // $Form->isFilledIn('title');
    // $Form->isFilledIn('heading');
    $Form->isFilledIn('link_title');
    // $Form->HasPickedOption('parent_id');

    // If there were not any form validation error
    if (FALSE == $Form->hasErrors()) {
        $update = array('uri' => $posted['uri'],
                        'query_string' => $posted['query_string'],
                        'parent_id' => $posted['parent_id'],
                        'title' => $posted['title'],
                        'description' => $posted['description'],
                        'heading' => $posted['heading'],
                        'content' => $posted['content'],
                        'link_title' => $posted['link_title'],
                        'show_on_menu' => $posted['show_on_menu'],
        );
        if (1 == $page_type_options['has_subtypes']) {
            $update['subtype'] = $posted['subtype'];
            $update['menu_parent_id'] = $posted['menu_parent_id'];
        }
        if (1 == $page_type_options['use_creation_info']) {
            $update['creation_date'] = $posted['creation_date'];
            $update['last_modified'] = time();
            $update['author'] = $posted['author'];
         }
        if ('custom' == $page_type_options['left_menu_type']) {
            $update['left_menu'] = $posted['left_menu'];
        }
        $where = 'id="'.$page_id.'"';
        DB::update($edit_page_type.'_pages', $update, $where);

        header('Location: '.$redirect_uri);
        exit;
    } else {
        // Form Input Error
        $output .= Event::showAndLog(71, $member_id, $page_type, '');
    }
} else {
    $Form = new Form($_POST, $page);
}

$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Edit Page "'.$page['link_title'].'"');
$output .= $Form->showTextInput('URI', 'uri', 255);
$output .= $Form->showTextInput('Query String', 'query_string', 255);
$output .= $Form->showTextInput('Title', 'title', 255);
$output .= $Form->showTextInput('Heading', 'heading', 255);
$output .= $Form->showTextInput('Link Title', 'link_title', 255);
$Menu = new Menu($_GET['type'], 0, TRUE);
$options_array = $Menu->getMenuArray($_GET['type']);

$output .= $Form->showDropDown('Parent Page', 'parent_id', $options_array);
if (1 == $page_type_options['has_subtypes']) {
    $output .= $Form->showDropDown('Menu - show Children of', 'menu_parent_id',
                                   $options_array);
    $select = array('id', 'title');
    $result = DB::select('page_subtypes', $select, '', 0);
    while ($subtype = DB::fetchAssoc($result)) {
        $page_subtypes[$subtype['id']] = $subtype['title'];
    }
    $output .= $Form->showDropDown('Subtype', 'subtype', $page_subtypes);
}
if (1 == $page_type_options['use_creation_info']) {
    $select = array('id', 'name');
    $result = DB::select('page_authors', $select, '', 0);
    while ($author = DB::fetchAssoc($result)) {
        $page_authors[$author['id']] = $author['name'];
    }
    $output .= $Form->showDropDown('Author', 'author', $page_authors);
    $output .= $Form->showTextInput('Creation Date', 'creation_date', 255, 0);
}
$output .= $Form->showTextInput('Description', 'description', 255, 0);
$output .= $Form->showCheckbox('Show On Menu', 'show_on_menu', 1);
$output .= $Form->showTextarea('Page Content','content');
if ('custom' == $page_type_options['left_menu_type']) {
    $output .= $Form->showTextarea('Left Menu','left_menu');
}

$more_buttons = array(1 =>
                      array('label' => 'Delete Page',
                            'name' => 'delete_page',
                            'description' => 'You can delete this page from the database.',
                            'confirm_message' => 'Are you sure you want to delete this page?'));
$output .= $Form->showSubmitButton('Update Page', TRUE, $more_buttons);
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
