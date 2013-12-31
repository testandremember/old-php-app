<?php
/**
 * Lets members upload a logo for their website.
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1272 2004-10-30 01:22:18Z elijah $
 * @package NiftyCMS
 * @subpackage cp_page_logo
 */
require_once '../../../../site/inc/Page.class.php';
require_once '../../../../site/inc/Form.class.php';
require_once '../../../../site/inc/Image.class.php';
$page_type = 'cp';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$Form   = new Form($_POST, array(), $page_type);

$output           = $Page->showHeader();
$member_id        = $Member->getId(); // Get the members id
$member_folder    = $Member->getAttribute('folder_name');     // The folder to upload to
$page_number      = (int) $_GET['page'];
$upload_file_name = 'logo'; // The name of the file field in your form.

// The path to the directory where you want to save the uploaded files.
$path = '../../../../'.$member_folder.'/';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If the cancel button was pressed then send the member to the admin index
    Form::redirectOnCancel($settings['cp_uri'].'/#page'.$page_number);

    // If delete_logo button was pressed then delete the logo
    if (FALSE != array_key_exists('delete_logo', $_POST)) {
        // Get current logo info out of DB
        $select = array('logo');
        $where = 'WHERE member_id="'.$member_id.'" AND page_number="'.$page_number.'"';
        $result = DB::select('cp_member_pages', $select, $where);
        $member_page = DB::fetchAssoc($result);
        // Tell the database that the logo no longer exists
        $update = array('logo' => '');
        $where = 'member_id="'.$member_id.'" AND page_number="'.$page_number.'"';
        DB::update('cp_member_pages', $update, $where);

        // Delete the old logo if it is not used elsewhere
        $Member->deleteImage($member_page['logo']);

        // Log that the member deleted their logo
        Event::logEvent(30, $member_id, $page_type, $member_page['logo']);
        // Tell the member their logo was successfully deleted.
        $output .= Event::showMessage(30);
    } else {
        // Upload the member's logo
        $Image = new Image($image_settings);
        if (FALSE != $Image->upload($upload_file_name)) {
            if (FALSE != $Image->saveFile($path)) {
                $file_name = $Image->getFinalName();
            }
        }

        // If there were errors
        if (FALSE != $Image->hasError()) {
            $upload_error = $Image->getUploadError();
            $output .= Event::showAndLog(46, $member_id, $page_type, $upload_error);
            $output .= '<div class="error">Error: '.$upload_error."</div>\n";
        } else {
            $output .= Event::showAndLog(47, $member_id, $page_type, $file_name);
            // Show the member's current logo
            $query  = 'SELECT logo FROM cp_member_pages WHERE member_id="'.$member_id.'"
                AND page_number="'.$page_number.'" LIMIT 1';
            $result = DB::query($query);
            $old_member_page = DB::fetchAssoc($result);
            // Update the database with the new logo.
            $query = 'UPDATE cp_member_pages SET logo="'.$file_name.'"
                WHERE member_id="'.$member_id.'"
            AND page_number="'.$page_number.'" LIMIT 1';
            DB::query($query);

            // Delete the member's old logo
            $Member->deleteImage($old_member_page['logo']);
        }
    }
}

// Show any event messages (such as a logo deletion)
if (FALSE != isset($_GET['event'])) {
    $output .= Event::showMessage($_GET['event']);
}

// Show the member's current logo
$query  = 'SELECT logo FROM cp_member_pages WHERE member_id="'.$member_id.'"
           AND page_number="'.$page_number.'" LIMIT 1';
$result = DB::query($query);
$member_page = DB::fetchAssoc($result);

if ('' != $member_page['logo'] && TRUE == is_file($path.$member_page['logo'])) {
    $current_logo_exists = TRUE;
} else {
    $current_logo_exists = FALSE;
}


// Show a form that lets the member upload a logo
// $form_target = $settings['cp_uri'].'/page/logo.php?page='.$page_number;
$form_target = './?page='.$page_number;
$output .= $Form->startForm($form_target, 'post');
$output .= $Form->startFieldset('Upload Logo');
$info  = '<b>Logo Upload</b> - You can upload an image file from your computer';
$info .= ' to use as the logo of your web page.';
$output .= $Form->showInfo($info);
$output .= $Form->showFileInput('Upload this file', $upload_file_name);
if (FALSE != $current_logo_exists) {
    $more_buttons = array(1 =>
                          array('label' => 'Delete Logo',
                                'name' => 'delete_logo',
                                'description' => 'You can delete this logo off your page.',
                                'confirm_message' => 'Are you sure you want to delete this logo?'));
    $output .= $Form->showSubmitButton('Upload Logo', TRUE, $more_buttons);
} else {
    $output .= $Form->showSubmitButton('Upload logo', TRUE);
}
$output .= $Form->endFieldset();
$output .= $Form->endForm();

if (FALSE != $current_logo_exists) {
    $output .= '<b>Current Logo:</b> <br />';
    $output .= Image::showResized($member_page['logo'], $member_folder, $settings['site_uri']);
}


$output .= $Page->showFooter();
echo $output;
?>
