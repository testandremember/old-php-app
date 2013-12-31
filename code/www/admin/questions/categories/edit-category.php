<?php
/**
 * Lets the admin edit a question category
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: edit-category.php 1673 2005-01-03 16:13:49Z elijah $
 * @package NiftyCMS
 * @subpackage admin_question_categories
 *
 */
require_once '../../../testprep/testprep/site/inc/Page.class.php';
require_once '../../../testprep/testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$category_id = (int) $_GET['id'];

$select = array('title');
$where = 'WHERE id="'.$category_id.'"';
$result = DB::select('prep_question_categories', $select, $where);
$category = DB::fetchAssoc($result);

// If the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $redirect_uri = $settings['admin_uri'].'/questions/';
    Form::redirectOnCancel($redirect_uri);
    // Sanitize posted form data data
    $posted = Form::sanitizeData($_POST);

    // If delete_category button was pressed then delete the question category
    if (FALSE != array_key_exists('delete_category', $_POST)) {
        // Delete question category from the database
        DB::delete('prep_question_categories', 'id="'.$category_id.'"');
        DB::close();
        header('Location: '.$redirect_uri);
        exit;
    }

    // Update the database with the updated question info
    $update = array('title' => $posted['title']);
    $where = 'id="'.$category_id.'"';
    DB::update('prep_question_categories', $update, $where);

    DB::close();

    header('Location: '.$redirect_uri);
    exit;
}

$Form = new Form($_POST, $category);

$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Edit Category"'.$category['title'].'"');
$output .= $Form->showTextInput('Title', 'title', 255);
$more_buttons = array(1 =>
                      array('label' => 'Delete Category',
                            'name' => 'delete_category',
                            'description' => 'You can delete this question category from the database.',
                            'confirm_message' => 'Are you sure you want to delete question category?'));
$output .= $Form->showSubmitButton('Save Changes', TRUE, $more_buttons);
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>