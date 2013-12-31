<?php
/**
 * Lets the admin edit a page subtype
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: edit-subtype.php 1297 2004-10-30 14:12:25Z elijah $
 * @package NiftyCMS
 * @subpackage admin_pages_subtypes
 *
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$subtype_id = (int) $_GET['id'];

$select = array('id', 'subtype_action', 'name', 'title', 'description',
                'menu_title');
$result = DB::select('page_subtypes', $select, 'WHERE id="'.$subtype_id.'"');
$type = DB::fetchAssoc($result);
// If the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $posted = Form::sanitizeData($_POST);
    $update = array('subtype_action' => $posted['subtype_action'],
                    'title' => $posted['title'],
                    'name' => $posted['name'],
                    'description' => $posted['description'],
                    'menu_title' => $posted['menu_title']);

    DB::update('page_subtypes', $update, 'id = "'.$subtype_id.'"');

    DB::close();
    header('Location: '.$settings['admin_uri'].'/pages/subtypes/');
    exit;
}

$Form = new Form($_POST, $type);

$target = 'delete-subtype.php?id='.$subtype_id;
$message = 'Are you sure you want to delete this page subtype?';
$output .= $Form->showConfirmButton($target, 'Delete Page Subtype', $message);

$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Add new page subtype');
$subtype_actions = DB::getColumnOptions('page_subtypes', 'subtype_action');
$output .= $Form->showDropDown('Subtype Action', 'subtype_action',
                               $subtype_actions);
$output .= $Form->showTextInput('Name', 'name', 255);
$output .= $Form->showTextInput('Title', 'title', 255);
$output .= $Form->showTextInput('Description', 'description', 255);
$output .= $Form->showTextInput('Menu Title', 'menu_title', 255);
$output .= $Form->showSubmitButton('Update Page Subtype');
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>