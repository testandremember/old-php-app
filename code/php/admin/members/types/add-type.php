<?php
/**
 * Lets the admin add member types
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: add-type.php 1331 2004-10-30 23:42:20Z elijah $
 * @package NiftyCMS
 * @subpackage admin_member_types
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
                    'name' => $posted['name'],
                    'title' => $posted['title'],
                    'description' => $posted['description'],
                    'can_login' => $posted['can_login'],
                    'has_website' => $posted['has_website']);
    DB::insert('member_types', $insert);
    DB::close();
    header('Location: '.$settings['admin_uri'].'/members/types/');
    exit;
}

$output .= $Form->startForm($_SERVER['PHP_SELF'], 'post');
$output .= $Form->startFieldset('Add new member type');
$output .= $Form->showTextInput('Name', 'name', 255);
$output .= $Form->showTextInput('Title', 'title', 255);
$output .= $Form->showTextInput('Description', 'description', 255);
$output .= $Form->showCheckbox('Can login', 'can_login', 1);
$output .= $Form->showCheckbox('Has Website', 'has_website', 1);
$output .= $Form->showSubmitButton('Add new member type');
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
