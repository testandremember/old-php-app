<?php
/**
 * Lets the admin edit a template
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: edit-type.php 1598 2004-12-20 02:39:28Z elijah $
 * @package NiftyCMS
 * @subpackage admin_pages_types
 *
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$type_id = (int) $_GET['id'];

$select = array('id', 'name', 'title', 'description', 'template',
                'force_login', 'prevent_caching', 'has_subtypes',
                'left_menu_type', 'right_menu_type', 'right_menu_parameter',
                'show_back_links', 'use_creation_info');
$result = DB::select('page_types', $select, 'WHERE id="'.$type_id.'"');
$type = DB::fetchAssoc($result);
// If the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $posted = Form::sanitizeData($_POST);
    if (FALSE == empty($posted['force_login'])) {
        $posted['force_login'] = 1;
    } else {
        $posted['force_login'] = 0;
    }
    if (FALSE == empty($posted['prevent_caching'])) {
        $posted['prevent_caching'] = 1;
    } else {
        $posted['prevent_caching'] = 0;
    }
    if (FALSE == empty($posted['has_subtypes'])) {
        $posted['has_subtypes'] = 1;
    } else {
        $posted['has_subtypes'] = 0;
    }
    if (FALSE == empty($posted['show_back_links'])) {
        $posted['show_back_links'] = 1;
    } else {
        $posted['show_back_links'] = 0;
    }
    if (FALSE == empty($posted['use_creation_info'])) {
        $posted['use_creation_info'] = 1;
    } else {
        $posted['use_creation_info'] = 0;
    }

    $update = array('name' => $posted['name'],
                    'title' => $posted['title'],
                    'description' => $posted['description'],
                    'template' => $posted['template'],
                    'force_login' => $posted['force_login'],
                    'prevent_caching' => $posted['prevent_caching'],
                    'has_subtypes' => $posted['has_subtypes'],
                    'left_menu_type' => $posted['left_menu_type'],
                    'right_menu_type' => $posted['right_menu_type'],
                    'right_menu_parameter' => $posted['right_menu_parameter'],
                    'show_back_links' => $posted['show_back_links'],
                    'use_creation_info' => $posted['use_creation_info']);
    DB::update('page_types', $update, 'id = "'.$type_id.'"');

    DB::close();
    header('Location: '.$settings['admin_uri'].'/pages/types/');
    exit;
}

$Form = new Form($_POST, $type);

$target = 'delete-type.php?id='.$type_id;
$message = 'Are you sure you want to delete this page type?';
$output .= $Form->showConfirmButton($target, 'Delete Page Type', $message);

$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Edit Page Type');
$output .= $Form->showTextInput('Name', 'name', 255);
$output .= $Form->showTextInput('Title', 'title', 255);
$output .= $Form->showTextInput('Description', 'description', 255);
$select = array('id', 'name');
$result = DB::select('page_templates', $select, '', 0);
$template_title = array();
while ($template = DB::fetchAssoc($result)) {
    $template_title[$template['id']] = $template['name'];
}
$output .= $Form->showDropDown('Template', 'template', $template_title);
$menu_types = DB::getColumnOptions('page_types', 'left_menu_type');
$output .= $Form->showDropDown('Left Menu Type', 'left_menu_type', $menu_types);
$menu_types = DB::getColumnOptions('page_types', 'right_menu_type');
$output .= $Form->showDropDown('Right Menu Type', 'right_menu_type', $menu_types);
$output .= $Form->showTextInput('Right Menu Parameter', 'right_menu_parameter', 255);
$output .= $Form->showCheckbox('Force Login', 'force_login', 1);
$output .= $Form->showCheckbox('Prevent Page Caching', 'prevent_caching', 1);
$output .= $Form->showCheckbox('Has Subtypes', 'has_subtypes', 1);
$output .= $Form->showCheckbox('Show Back Links', 'show_back_links', 1);
$label = 'Use Creation Info <br />({author}, {last_modified}, {creation_date})';
$output .= $Form->showCheckbox($label, 'use_creation_info', 1);
$output .= $Form->showSubmitButton('Update page type');
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>