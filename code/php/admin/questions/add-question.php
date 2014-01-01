<?php
/**
 * Lets the admin add questions.
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: add-question.php 2206 2006-01-17 23:28:37Z elijahlofgren $
 * @package NiftyCMS
 * @subpackage admin_questions
*/
require_once '../../testprep/testprep/site/inc/Page.class.php';
require_once '../../testprep/testprep/site/inc/Form.class.php';
require_once '../../testprep/testprep/site/inc/Thumbnail.class.php';
require_once '../../testprep/testprep/site/inc/Image.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();
$member_id = $Member->getId();
$upload_file_name = 'image';
$upload_result = '';
// If the form was submitted
if ('POST' == $_SERVER['REQUEST_METHOD']) {
    // Sanitize posted form data data
    $posted = DB::addSlashes($_POST);


    $insert = array('id' => '',
                    'category' => $posted['category'],
                    'question' => $posted['question'],
                    'answer' => $posted['answer'],
    );
    DB::insert('prep_questions', $insert);

    $output .= Event::showAndLog(94, $member_id, $page_type,
                                 $posted['question']);
    if (FALSE == empty($posted['remember_question'])) {
        $posted['remember_question'] = 1;
        $preset_values['remember_question'] = $posted['remember_question'];
        $preset_values['question'] = $posted['question'];
    } else {
        $posted['remember_question'] = 0;
    }

    // DB::close();
    // header('Location: '.$settings['admin_uri'].'/questions/');
    // exit;
    $preset_values['category'] = $posted['category'];
    $Form = new Form(array(), $preset_values, $page_type);

} else {
    // Create form object to output form
    $Form = new Form(array(), array(), $page_type);
}

// Create the form
$output .= $Form->startForm($_SERVER['PHP_SELF'], 'post');
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
$output .= $Form->showCheckbox('Remember Question', 'remember_question', 1);
$output .= $Form->showSubmitButton('Add New Question');
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
