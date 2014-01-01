<?php
/**
 * Lets the admin add template categories.
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: add-template.php 1282 2004-10-30 13:20:55Z elijah $
 * @package NiftyCMS
 * @subpackage admin_page_templates
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$Form = new Form($_POST,array());

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $posted = DB::addSlashes($_POST);
    $insert = array('id' => '', 'name' => $posted['name'],
                    'description' => $posted['description'],
                    'header' => $posted['header'],
                    'footer' => $posted['footer']);
    DB::insert('page_templates', $insert);
    DB::close();
    header('Location: '.$settings['admin_uri'].'/pages/templates/');
    exit;
}

$output .= $Form->startForm($_SERVER['PHP_SELF'], 'post');
$output .= $Form->startFieldset('Add new template');
$output .= $Form->showTextInput('Name', 'name', 255);
$output .= $Form->showTextInput('Description', 'description', 255);
$output .= $Form->showTextarea('Header','header');
$output .= $Form->showTextarea('Footer','footer');
$output .= $Form->showSubmitButton('Add new template');
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>