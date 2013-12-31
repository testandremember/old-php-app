<?php
/**
 * Allows members to edit the options of their page
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: options.php 1272 2004-10-30 01:22:18Z elijah $
 * @package NiftyCMS
 * @subpackage cp_page
 */
require_once '../../../site/inc/Page.class.php';
require_once '../../../site/inc/Form.class.php';
$page_type = 'cp';
$Page   = new Page($page_type, $settings, $Member);
$Member = new Member($page_type, $settings, $Member);

$output = $Page->showHeader();

$member_id = $Member->getId();
$page_number = (int) $_GET['page'];

// Get the current pages options data out of the database
$select = array('show_counter');
$where = 'WHERE member_id="'.$member_id.'" AND page_number="'.$page_number.'"';
$result = DB::select('cp_member_pages', $select, $where);
$member_page = DB::fetchAssoc($result);

$Form = new Form($_POST, $member_page);

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    // If the cancel button was pressed then send the member to the CP index
    $Form->redirectOnCancel($settings['cp_uri'].'/#page'.$page_number);

    // Sanitize posted form data
    $posted = Form::sanitizeData($_POST);

    if (FALSE == empty($posted['show_counter'])) {
        $posted['show_counter'] = 1;
    } else {
        $posted['show_counter'] = 0;
    }

    // Update database with the updated page options
    $update = array('show_counter' => $posted['show_counter']);
    $where = 'member_id="'.$member_id.'" AND page_number="'.$page_number.'"';
    DB::update('cp_member_pages', $update, $where);

    Event::logEvent(59, $member_id, $page_type, '');

    // Send the member to the Control Panel index
    header('Location: '.$settings['cp_uri'].'/?event=59&page='.$page_number.'#page'.$page_number);
    exit;
}
$output .= $Form->startForm($_SERVER['REQUEST_URI']);
$output .= $Form->startFieldset('Page Titles');
$output .= $Form->showCheckbox('Show Page Hit Counter', 'show_counter', 1);
$output .= $Form->showSubmitButton('Save Changes', TRUE);
$output .= $Form->endFieldSet();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
