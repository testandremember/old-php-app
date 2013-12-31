<?php
/**
 * The test prep homepage
 * @version $Id: index.php 2265 2007-03-20 13:44:28Z elijahlofgren $
 * @package NiftyCMS
 * @subpackage www
 */
require_once 'site/inc/Page.class.php';
require_once 'site/inc/Form.class.php';
$page_type = 'www';
$Member = new Member('cp', $settings);
$Page   = new Page($page_type, $settings, $Member);
$output  = $Page->showHeader();
echo "welcome";
if (FALSE != isset($_GET['question'])) {
    $question_id = (int) $_GET['question'];
} else {
    if (FALSE == empty($_GET['category']) AND 'POST' != $_SERVER['REQUEST_METHOD']) {
        $category_id = (int) $_GET['category'];
        $select = array('id', 'question', 'answer', 'category');
        $where = 'WHERE category="'.$category_id.'"';
        $result = DB::select('prep_questions', $select, $where, 0);

        while ($question = DB::fetchAssoc($result)) {
            $question_answers_ordered[] = $question;
        }

        // Randomize order of questions
        // print_r($question_answers_ordered);
        $rand_index = array_rand($question_answers_ordered, count($question_answers_ordered));
        // $question_answers2 = shuffle($question_answers);
        // foreach ($rand_index as $key => $value) {
        // $question_answers[] = $question_answers_ordered[$value];
        // }
        //   print_r($rand_index);
        //    echo serialize($rand_index);
        //    print_r(unserialize(serialize($rand_index)));
        setcookie('rand_question_order', serialize($rand_index), NULL, '/');
        $rand_question_order_cookie = serialize($rand_index);
    }
    $question_id = 0;
}


// Start: list questions addition
if (FALSE == empty($_GET['showquestions'])) {

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
    $output .= '<li>('.$category['id'].') '.$category['title'].'';
    if (FALSE == empty($questions_array)) {
        if (array_key_exists($category['id'], $questions_array)) {
            $output .= '<ul>';
            foreach($questions_array[$category['id']] as $key => $title) {
                $questions_array[$category['id']][$key]['shown'] = TRUE;
                $output .= '<li>('.$key.') ';
                $output .= $title['question'].'';
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
                $output .= '<li>('.$key2.') ';
                $output .= $title2['question'].'';
            }
        }
    }
}
$output .= '</ul>';
}
// End list questions addition

if (FALSE != empty($_GET['category'])) {
    $output .= '<p>Note: Each time you go through a category, questions are shown in a different random order.</p><ul><li><a href="/admin/questions/">Add or edit questions</a></li>';
	 $output .= '<li><a href="?showquestions=1">View list of questions</a></li></ul>';
    $output .= '<p><b>Choose a question category to start at:</b></p>';

    $output .= '<ul>';
    $query='SELECT id,title FROM prep_question_categories ORDER by id DESC';
    $result=DB::query($query);
    while ($title=DB::fetchAssoc($result))
    {
        $output .= '<li><a href="?category='.$title['id'].'">('.$title['id'].') '.$title['title'].'</a>';
        // $output .= ' - <a href="multiple-choice.php?category='.$title['id'].'">(multiple choice)</a></li>';
        $output .= '</li>';
    }
    $output .= '</ul>';

} else {
    if (FALSE == isset($rand_question_order_cookie)) {
        $rand_question_order = (unserialize($_COOKIE['rand_question_order']));
    } else {
        $rand_question_order = (unserialize($rand_question_order_cookie));
    }
    $category_id = (int) $_GET['category'];
    $select = array('id', 'question', 'answer', 'category');
    $where = 'WHERE category="'.$category_id.'"';
    $result = DB::select('prep_questions', $select, $where, 0);

    while ($question = DB::fetchAssoc($result)) {
        $question_answers_ordered[] = $question;
    }

    // Randomize order of questions
    // print_r($question_answers_ordered);
    // $rand_index = array_rand($question_answers_ordered, count($question_answers_ordered));
    // $question_answers2 = shuffle($question_answers);
    foreach ($rand_question_order as $key => $value) {
        $question_answers[] = $question_answers_ordered[$value];
    }
    // echo '<hr>';
    // print_r($question_answers);

    $output .= '<p style="float:left"><a href="?category='.$category_id.'">Start over</a></p>';
    $output .= '<p style="float:right"><a href="/">Choose different category</a></p>';
    $output .= '<p style="clear:both"></p>';

    if ('POST' == $_SERVER['REQUEST_METHOD']) {
        // Sanitize posted form data data
        // $posted = Form::SanitizeData($_POST);
        // $real_answer = html_entity_decode($question_answers[$question_id]['answer']);
        $real_answer = str_replace('&#039;', "'", $question_answers[$question_id]['answer']);
        // $real_answer = str_replace('  ', ' ', $real_answer);
        $posted_answer = trim($_POST['answer']);
	$posted_answer = DB::stripSlashes($posted_answer);

	// Remove periods for real and posted answers so that missing periods won't be counted wrong.
        $real_answer_length = strlen($real_answer);	
        $posted_answer_length = strlen($posted_answer);	
        $real_answer_replace_pos = $real_answer_length - 1;
        $posted_answer_replace_pos = $posted_answer_length - 1;
        if ("." == $real_answer[$real_answer_replace_pos]) {
            $real_answer = substr_replace($real_answer, '', $real_answer_replace_pos, 1);           
            // $real_answer = substr($real_answer, 0, $real_answer_length - 1);
	}
	if (FALSE == empty($posted_answer) && "." == $posted_answer[$posted_answer_replace_pos]) {
            $posted_answer = substr_replace($posted_answer, '', $posted_answer_replace_pos, 1);           
            // $posted_answer = substr($posted_answer, $real_answer_replace_pos);
	}

	$real_answer = rtrim($real_answer);
	$d_real_answer = strtoupper($real_answer);
	$d_posted_answer = strtoupper($posted_answer);
	$to_replace = array(':', ',', '.', 'the', ' a ', '?');
	$with = array('', '', '', '', '', '');
	$d_posted_answer = str_replace($to_replace, $with, $d_posted_answer);
	$d_real_answer = str_replace($to_replace, $with, $d_real_answer);
								   

        if ($d_real_answer == $d_posted_answer) {
            /*
            $output .= '<p class="success">Correct!</p>';
            $output .= '<p><a href="?category='.$category_id.'&amp;question='.$next_question.'" id="continue">Continue</a></p>';
            $output .= '<script type="text/javascript">
                var main = document.getElementById(\'continue\');
            main.focus();
        </script>';
            $output .= '<p>Tip: Press "Enter" to continue again.</p>';
            */
            $next_question = $question_id + 1;
            $next_uri = '/?category='.$category_id.'&question='.$next_question;
            DB::close();
            if (FALSE != array_key_exists($next_question, $question_answers)) {
                header('Location: '.$settings['site_uri'].$next_uri);
            } else {
                header('Location: '.$settings['site_uri'].'/');
            }
            exit;
        } else {
            $length = strlen($posted_answer);
            $posted_user_answer = $posted_answer;
            // Create an array of the positions of letters that where wrong.
            for ($i = 0; $i <= $length; $i++) {
                $real_answer_letter = substr($real_answer, $i, 1);
                $user_answer_letter = substr($posted_answer, $i, 1);
                if ($real_answer_letter != $user_answer_letter) {
                    // echo $real_answer_letter.' vs '.$user_answer_letter.'<br />';
                    // $posted_user_answer = str
                    $to_highlight_real[] = array('replace_pos' => $i, 'highlight_letter' => $real_answer_letter);
                    $to_highlight_user[] = array('replace_pos' => $i, 'highlight_letter' => $user_answer_letter);
                    // $replace = '<span style="background-color:red">'.$user_answer_letter.'</span>';
                    //$posted_user_answer = substr_replace($posted_user_answer, $replace, $i, 1);
                //    break;
                }

                // echo $real_answer;
                // echo $user_answer;
            }
            // Loop through twice
            for ($i = 0; $i <= 1; $i++) {
            // echo $i;    
            // Highlight real the first time through
            if ($i == 0) {
                $to_highlight = $to_highlight_real;
                $color = 'green';
            // Highlight user the 2nd and last time through
            } else {
                $to_highlight = $to_highlight_user;
                $color = 'red';
            }
                // Highlight exactly which characters were differnt from the answer.
                $offset = 0;
                foreach ($to_highlight as $key => $value) {
                    if (' ' == $value['highlight_letter']) {
                        $value['highlight_letter'] = '&nbsp;';
                    }
                    $replace = '<span style="background-color:'.$color.'">'.$value['highlight_letter'].'</span>';
                    // echo $posted_user_answer;
                    // echo '<hr />';
                    $replace_pos = $value['replace_pos'] + $offset;
                    // echo '<p>'.$replace_pos.'</p>';
                    // echo '<p>'.substr($posted_user_answer, $replace_pos, 1).'</p>';
                    // Highlight real the first time through
                    if ($i == 0) {
                        $real_answer = substr_replace($real_answer, $replace, $replace_pos, 1);
                    // Highlight user the 2nd and last time through
                    } else {
                        $posted_user_answer = substr_replace($posted_user_answer, $replace, $replace_pos, 1);
                    }
                    $offset = $offset + strlen($replace) - 1;
                    //                   echo $offset.'<br />';
                    // echo $posted_user_answer;
                    // echo '<hr />';
                    //  if ($offset > 100) {
                    //        break; break;
                    //    }
                }
        }



            $output .= '<fieldset>';
            $output .= '<legend>Incorrect</legend><table style="margin-bottom:124px">';
            $question_answers[$question_id]['question'] = html_entity_decode($question_answers[$question_id]['question']);
            $output .= '<tr><td colspan="2" class="infobox">'.$question_answers[$question_id]['question'].'</td></tr>';
            $output .= '<tr>';
            $output .= '<td>'.$real_answer.'</td><td style="padding-left:4em">is the correct answer</td</tr>';
            $output .= '<tr>';
            $output .= '<td>'.$posted_user_answer.'</td><td style="padding-left:4em">is what you answered</td></tr>';
            $output .= '</table>';
            $output .= '<p style="text-align:right"><a href="?category='.$category_id.'&amp;question='.$question_id.'" id="tryagain">Try Again</a></p>';
            $output .= '</table></fieldset>';
            $output .= '<script type="text/javascript">
                var main = document.getElementById(\'tryagain\');
            main.focus();
        </script>';
        $output .= '<p>Tip: Press "Enter" to try again.</p>';
        }
    } else {
        $output .= showForm($question_answers, $question_id);

    }
    $previous_question = $question_id - 1;
    if (FALSE != array_key_exists($previous_question, $question_answers)) {
        $output .= '<p style="float:left"><a href="?category='.$category_id.'&amp;question='.$previous_question.'" accesskey="p">&laquo; Previous Question</a></p>';
    }
    $next_question = $question_id + 1;
    if (FALSE != array_key_exists($next_question, $question_answers)) {
        $output .= '<p style="float:right"><a href="?category='.$category_id.'&amp;question='.$next_question.'" accesskey="n">Next Question &raquo;</a></p>';
    }
}
$output .= '<p style="text-align:center">Tip: Press "Enter" or "Alt+S" to submit the form.</p>';
$output .= $Page->showFooter();
echo $output;
function showForm($questionAnswers, $questionId)
{
    // Create form object to output form
    $Form = new Form(array(), array(), 'guest');
    // Create the form
    $output  = $Form->startForm($_SERVER['REQUEST_URI'], 'post');
    $output .= $Form->startFieldset('Answer Question');
    $questionAnswers[$questionId]['question'] = html_entity_decode($questionAnswers[$questionId]['question']);
    $output .= $Form->showInfo($questionAnswers[$questionId]['question']);
    $output .= $Form->showTextArea('Answer', 'answer', 6, FALSE, '', TRUE);
    $output .= $Form->showSubmitButton('Answer!');
    $output .= $Form->endFieldset();
    $output .= $Form->endForm();
    $output .= '<script type="text/javascript">
        var main = document.getElementById(\'answer\');
    main.focus();
    </script>';
    return $output;
}
?>
