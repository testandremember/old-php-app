<?php
/**
 * The Questions index. Add, Edit, and delete test prep questions
 *
 * @version $Id: index.php 2205 2006-01-17 23:14:55Z elijahlofgren $
 * @author Elijah Lofgren <elijah@truthland.com>
 * @package NiftyCMS
 * @subpackage admin_questions
 */
require_once '../../testprep/testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

if (FALSE != isset($_GET['event'])) {
        $output .= Event::showMessage($_GET['event']);
}

$query = 'SELECT id,question,category FROM prep_questions';
$result = DB::query($query);
$output .= '<ul>';
while ($question = DB::fetchAssoc($result)) {
    $questions_array[$question['category']][$question['id']]['id'] = $question['id'];
    $questions_array[$question['category']][$question['id']]['question'] = $question['question'];
    $questions_array[$question['category']][$question['id']]['shown'] = FALSE;
}

$output .= 'Questions and their categories';
$query = 'SELECT id, title FROM prep_question_categories ORDER by id DESC';
$result = DB::query($query);
$output .= '<ul>';
while ($category = DB::fetchAssoc($result)) {
    $output .= '<li><a href="categories/edit-category.php?id='.$category['id'].'">('.$category['id'].') '.$category['title'].'</a>';
    if (FALSE == empty($questions_array)) {
        if (array_key_exists($category['id'], $questions_array)) {
            $output .= '<ul>';
            foreach($questions_array[$category['id']] as $key => $title) {
                $questions_array[$category['id']][$key]['shown'] = TRUE;
                $output .= '<li><a href="edit-question.php?id='.$key.'">('.$key.') ';
                $output .= $title['question'].'</a>';
                $output .= '</li>';
            }
            $output .= '</ul>';
        }
    }
    $output .= '</li>';
}
$output .= '</ul>';

$output .= '<hr />Orphan Questions (not in any category):';
$output .= '<ul>';
if (FALSE == empty($questions_array)) {
    foreach($questions_array as $key => $title) {
        foreach($title as $key2 => $title2) {
            if (FALSE == $title2['shown']) {
                $output .= '<li><a href="edit-question.php?id='.$key2.'">('.$key2.') ';
                $output .= $title2['question'].'</a>';
            }
        }
    }
}
$output .= '</ul>';
$output .= $Page->showFooter();
echo $output;
?>
