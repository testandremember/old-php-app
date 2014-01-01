<?php
/**
 * Lets the admin add a new page to the database
 *
 * @version $Id: add-page.php 1626 2004-12-24 23:13:23Z elijah $
 * @author Elijah Lofgren <elijah@truthland.com>
 * @package NiftyCMS
 * @subpackage admin_pages
 */
require_once '../../testprep/site/inc/Page.class.php';
require_once '../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$new_page_type = DB::makeSafe($_GET['type']);
$member_id = $Member->getId();
$select = array('has_subtypes', 'left_menu_type', 'use_creation_info');
$result = DB::select('page_types', $select, 'WHERE name="'.$new_page_type.'"');
$page_type_options = DB::fetchAssoc($result);


if ('POST' == $_SERVER['REQUEST_METHOD']) {
    // $posted = Form::SanitizeData($_POST);
    $posted = DB::addSlashes($_POST);

    // Create a form object
    $Form = new Form($_POST,$_POST);
    if (1 == $page_type_options['has_subtypes']) {
       //  $Form->hasPickedOption('menu_parent_id');
        $Form->hasPickedOption('subtype');
    }
    $Form->isFilledIn('uri');
    // $Form->isFilledIn('title');
    // $Form->isFilledIn('heading');
    $Form->isFilledIn('link_title');
    // $Form->hasPickedOption('parent_id');

    // If there were not any form validation errors
    if (FALSE == $Form->hasErrors()) {

        if (FALSE == empty($posted['show_on_menu'])) {
            $posted['show_on_menu'] = 1;
        } else {
            $posted['show_on_menu'] = 0;
        }

        $insert = array('id' => '',
                        'uri' => $posted['uri'],
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
            $insert['subtype'] = $posted['subtype'];
            $insert['menu_parent_id'] = $posted['menu_parent_id'];
        }
        if ('custom' == $page_type_options['left_menu_type']) {
            $insert['left_menu'] = $posted['left_menu'];
        }
        if (1 == $page_type_options['use_creation_info']) {
            $insert['creation_date'] = $posted['creation_date'];
            $insert['last_modified'] = time();
            $insert['author'] = $posted['author'];
         }
        DB::insert($new_page_type.'_pages', $insert);

        header('Location: '.$settings['admin_uri'].'/pages/?type='.$new_page_type.'&page_id='.$posted['parent_id']);
        exit;
    } else {
        // Form Input Error
        $output .= Event::showAndLog(71, $member_id, $page_type, '');
    }
} else {
    $Form = new Form($_POST, array());
}

$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'POST');
$output .= $Form->startFieldset('Add new '.$new_page_type.' page');
$output .= $Form->showTextInput('URI', 'uri', 255, 0);
$output .= $Form->showTextInput('Query String', 'query_string', 255);
$output .= $Form->showTextInput('Title', 'title', 255, 0);
$output .= $Form->showTextInput('Heading', 'heading', 255, 0);
$output .= $Form->showTextInput('Link Title', 'link_title', 255, 0);
$output .= $Form->showCheckbox('Show On Menu', 'show_on_menu', 1);
$Menu = new Menu($new_page_type, 0, TRUE);
$options_array = $Menu->getMenuArray();
$output .= $Form->showDropDown('Parent Page','parent_id',$options_array);

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

$output .= $Form->showTextarea('Content','content');
if ('custom' == $page_type_options['left_menu_type']) {
    $output .= $Form->showTextarea('Left Menu','left_menu');
}
$output .= $Form->showSubmitButton('Add New Page');
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
