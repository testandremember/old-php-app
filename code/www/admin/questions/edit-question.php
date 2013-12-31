<?php
/**
 * Lets the admin edit or delete a question.
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: edit-question.php 2206 2006-01-17 23:28:37Z elijahlofgren $
 * @package NiftyCMS
 * @subpackage admin_questions
*/
require_once '../../testprep/testprep/site/inc/Page.class.php';
require_once '../../testprep/testprep/site/inc/Form.class.php';
require_once '../../testprep/testprep/site/inc/Image.class.php';
require_once '../../testprep/testprep/site/inc/Thumbnail.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$upload_file_name = 'image';
$question_id = (int) $_GET['id'];
$select = array('id', 'question', 'answer', 'category');
$result = DB::select('prep_questions', $select, 'WHERE id="'.$question_id.'"');

$question = DB::fetchAssoc($result);

// If the form was submitted
if ('POST' == $_SERVER['REQUEST_METHOD']) {
    $redirect_uri = $settings['admin_uri'].'/questions/';

    // Redirect if the cancel button was pressed
    Form::redirectOnCancel($redirect_uri);

    // Sanitize posted form data data
    $posted = DB::addSlashes($_POST);

    // If delete_question button was pressed then delete the question
    if (FALSE != array_key_exists('delete_question', $_POST)) {
        // Delete a page from the database
        DB::delete('prep_questions', 'id="'.$question_id.'"');
        DB::close();
        header('Location: '.$redirect_uri.'?event=73');
        exit;
    }

    // Update the database with the updated question info
    $update = array('question' => $posted['question'],
                    'answer' => $posted['answer'],
                    'category' => $posted['category']);
    DB::update('prep_questions', $update, 'id = "'.$question_id.'"');

    DB::close();
    header('Location: '.$redirect_uri.'?event=74');
    exit;
}

// Create a new Form with default values from the database
$Form = new Form(array(), $question, $page_type);

// Create the form
$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Add Question');
$output .= $Form->showInfo('Use the form below to add a question to the database');
$query = 'SELECT id,title FROM prep_question_categories';
$query_result = DB::query($query);
$category_title = array();
while ($category = DB::fetchAssoc($query_result)) {
    $category_title[$category['id']] = $category['title'];
}
$output .= $Form->showDropDown('Category','category',$category_title);
$output .= $Form->showTextInput('Question', 'question', 255);
$output .= $Form->showTextArea('Answer', 'answer', 10);
$more_buttons = array(1 =>
                      array('label' => 'Delete Question',
                            'name' => 'delete_question',
                            'description' => 'You can delete this question from the database.',
                            'confirm_message' => 'Are you sure you want to delete this question?'));
$output .= $Form->showSubmitButton('Update Question', TRUE, $more_buttons);
$output .= $Form->endFieldset();
$output .= $Form->endForm();

echo $output;
?>
