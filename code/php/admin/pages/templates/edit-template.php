<?php
/**
 * Lets the admin edit a template
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: edit-template.php 1297 2004-10-30 14:12:25Z elijah $
 * @package NiftyCMS
 * @subpackage admin_template_categories
 *
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$template_id = (int) $_GET['id'];

$select = array('id', 'name', 'description', 'header', 'footer');
$where = 'WHERE id="'.$template_id.'"';
$result   = DB::select('page_templates', $select, $where);
$template = DB::fetchAssoc($result);

// If the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $posted = DB::addSlashes($_POST);
    $update = array('name' => $posted['name'],
                    'description' => $posted['description'],
                    'header' => $posted['header'],
                    'footer' => $posted['footer']);
    DB::update('page_templates', $update, 'id = "'.$template_id.'"');
    DB::close();

    header('Location: '.$settings['admin_uri'].'/pages/templates/');
    exit;
}

$Form = new Form($_POST, $template);

$delete_target = 'delete-template.php?id='.$template_id;
$message = 'Are you sure you want to delete this template?';
$output .= $Form->showConfirmButton($delete_target, 'Delete Template', $message);

$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Add new template');
$output .= $Form->showTextInput('Name', 'name', 255);
$output .= $Form->showTextInput('Description', 'description', 255);
$output .= $Form->showTextarea('Header','header');
$output .= $Form->showTextarea('Footer','footer');
$output .= $Form->showSubmitButton('Update template');
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>