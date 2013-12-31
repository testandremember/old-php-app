<?php
/**
 * Lets the admin add page subtypes
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: add-subtype.php 1225 2004-10-27 00:15:06Z elijah $
 * @package NiftyCMS
 * @subpackage admin_pages_subtypes
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$Form = new Form($_POST,array());

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $posted = Form::sanitizeData($_POST);
    $insert = array('id' => '',
                    'subtype_action' => $posted['subtype_action'],
                    'title' => $posted['title'],
                    'description' => $posted['description'],
                    'menu_title' => $posted['menu_title']);
    DB::insert('page_subtypes', $insert);
    DB::close();
    header('Location: '.$settings['admin_uri'].'/pages/subtypes/');
    exit;
}

$output .= $Form->startForm($_SERVER['PHP_SELF'], 'post');
$output .= $Form->startFieldset('Add new page subtype');
$subtype_actions = DB::getColumnOptions('page_subtypes', 'subtype_action');
$output .= $Form->showDropDown('Subtype Action', 'subtype_action',
                               $subtype_actions);
$output .= $Form->showTextInput('Title', 'title', 255);
$output .= $Form->showTextInput('Description', 'description', 255);
$output .= $Form->showTextInput('Menu Title', 'menu_title', 255);
$output .= $Form->showSubmitButton('Add new page type');
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
