<?php
/**
 * Lets an admin edit a member page's page number.
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: edit-member-page-number.php 1411 2004-11-29 20:07:36Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$edit_id= (int) $_GET['id'];

$query  = 'SELECT page_number, member_id FROM cp_member_pages
           WHERE id="'.$edit_id.'" LIMIT 1';
$result = DB::query($query);
$member_page = DB::fetchAssoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If the cancel button was pressed then send the user back to the previous page
    if (array_key_exists('cancel', $_POST)) {
        header('Location: '.$settings['admin_uri'].'/members/cp/edit-member-account.php?id='.$member_page['member_id']);
        exit;
    }
    // Trim spaces, and convert quotes to html entities
    $posted = Form::SanitizeData($_POST);

    $Form = new Form($_POST, $posted);

    // Make sure a page number was chosen

    $Form->isFilledIn('page_number');
    $Form->isInteger('page_number');

    if (FALSE == $Form->HasErrors()) {
        $page_number = (int) $_POST['page_number'];
        $query  = 'SELECT id FROM cp_member_pages
                   WHERE page_number="'.$page_number.'"
                   AND member_id="'.$member_page['member_id'].'" LIMIT 1';
        $result = DB::query($query);
        if (0 != DB::numRows($result)) {
            $output .= '<p class="error">That page number is already use. ';
            $output .= 'Please choose a different number.</p>';
        } else {
            $query2 = 'UPDATE cp_member_pages SET page_number="'.$page_number.'"
                      WHERE id="'.$edit_id.'" LIMIT 1';
            DB::query($query2);
            DB::close();
            header('Location: '.$settings['admin_uri'].'/members/cp/edit-member-account?id='.$member_page['member_id']);
            exit;
        }
    }
} else {
$Form = new Form($_POST, $member_page);
}

$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Edit Job');
$output .= $Form->showTextInput('Page Number', 'page_number', 255);
$output .= $Form->showSubmitButton('Change Page Number', TRUE);
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>