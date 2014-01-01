<?php
/**
 * Lets the admin add question categories.
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: add-category.php 1671 2005-01-03 16:10:52Z elijah $
 * @package NiftyCMS
 * @subpackage admin_question_categories
 */
require_once '../../../testprep/testprep/site/inc/Page.class.php';
require_once '../../../testprep/testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$Form = new Form($_POST,array());

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $query = "INSERT INTO prep_question_categories VALUES ('', '".$_POST['title']."')";
    DB::query($query);
    DB::close();
    header('Location: '.$settings['admin_uri'].'/questions/');
    exit;
}
echo $output;

echo $Form->startForm($_SERVER['PHP_SELF'], 'post');
echo $Form->startFieldset('Add new question category');
echo $Form->showTextInput('Title', 'title', 255);
echo $Form->showSubmitButton('Add new question category');
echo $Form->endFieldset();
echo $Form->endForm();

echo $Page->showFooter();
?>