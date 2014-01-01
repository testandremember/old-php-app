<?php
/**
 * Lets members upload and edit their pictures
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1272 2004-10-30 01:22:18Z elijah $
 * @package NiftyCMS
 * @subpackage cp_page_pictures
 */
require_once '../../../../site/inc/Page.class.php';
require_once '../../../../site/inc/Form.class.php';
require_once '../../../../site/inc/Image.class.php';
$page_type = 'cp';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);

$output = $Page->showHeader();

$member_id     = $Member->getId();   // Get the member's id
$page_number   = (int) $_GET['page'];      // Page number being edited
$member_folder = $Member->getAttribute('folder_name'); // Get the member's folder

$Form = new Form($_POST, array());

if (FALSE != isset($_GET['event']) AND FALSE != isset($_GET['page'])) {
        $output .= Event::showMessage($_GET['event']);
}

// The name of the file field in your form.
$upload_file_name = 'picture1';
$show_upload_form = TRUE;
$upload_success = FALSE;
$upload_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $posted = Form::SanitizeData($_POST);

    // If the cancel button was pressed then send the member to the cp index
    Form::redirectOnCancel($settings['cp_uri'].'/#page'.$page_number);

    $number_left = 0;
    if (FALSE != $Member->canUploadMoreImages($member_id, $number_left)) {
        // The path to the directory where you want to save the uploaded files.
        $path = '../../../../'.$member_folder.'/';

        $Image = new Image($image_settings);

        // UPLOAD the file
        if (FALSE != $Image->upload($upload_file_name)) {
            if (FALSE != $Image->saveFile($path)) {
                $file_name = $Image->getFinalName();
            }
        }

        // If there were errors
        if (FALSE != $Image->hasError()) {
            // Upload failed
            $upload_success = FALSE;
            // Log that the upload failed
            $upload_error = Event::showAndLog(4, $member_id, $page_type,
                                              $Image->getUploadError(), $Image->getUploadError());
          //  $upload_error .= '<div class="error">';
           // $upload_error .= $Image->getUploadError();
            // $upload_error .= '</div>';
        } else {
            // Upload was successful
            $upload_success = TRUE;

            // Create Thunbnail
            $Thumbnail = new Thumbnail($path, $file_name, $image_settings);

            $thumbnail_name = $Thumbnail->Save($path, 'thumb_'.$file_name);
            if (FALSE != $Thumbnail->HasError()) {
                // echo 'Thumbnail errors';
                $thumbnail_success = FALSE;
                $thumbnail_error  = '<div class="error">';
                $thumbnail_error .= $Thumbnail->getError();
                $thumbnail_error .= '</div>';
            } else {
                $thumbnail_success = TRUE;
                // echo 'No Thumbnail errors';
            }

            if ('' != $file_name AND TRUE == is_file($path.$file_name)) {
                $uploaded_image = '<b>Thumbnail:</b>';
                $uploaded_image .= Image::showResized($thumbnail_name,
                                                      $member_folder,
                                                      $settings['site_uri']);
                $uploaded_image .= '<b>Description:</b><p class="description"><b>'.$posted['short_description'].'</b>: ';
                $uploaded_image .= $posted['long_description'].'</p>';
                $uploaded_image .= '<b>Full Size Image:</b>';
                $uploaded_image .= Image::showResized($file_name, $member_folder, $settings['site_uri']);
            } else {
                $uploaded_image = '<div class="error">Error: Uploaded image does not exist.</div>';
            }
            // Add the image to the database
            $query = 'INSERT INTO cp_member_pictures
                      (id, member_id, file_name, thumbnail_name, page_number,
                       short_description, long_description)
                       VALUES ("", "'.$member_id.'", "'.$file_name.'",
                               "'.$thumbnail_name.'", "'.$page_number.'",
                               "'.$posted['short_description'].'",
                               "'.$posted['long_description'].'")';
            DB::query($query);
        }
    } else {
        $upload_success = FALSE;
        // Picture upload limit reached
        $upload_error = Event::showAndLog(67, $member_id, $page_type, '');
        $show_upload_form = FALSE;
    }

    if (FALSE == $upload_success) {
        $Form = new Form($posted, $posted);
        $output .= $upload_error;
    } else {
        $Form = new Form(array(), array());
        if (FALSE == $thumbnail_success) {
            $output .= $thumbnail_error;
            $output .= $uploaded_image;
        } else {
            $output .= Event::showAndLog(2, $member_id, $page_type, '');
            $output .= $uploaded_image;
        }
    }
}

// $output .= 'You have uploaded a total of '.$num_rows.' pictures.';
$number_left = 0;
// if ($num_rows < $member['num_pictures']) {
if (FALSE != $Member->canUploadMoreImages($member_id, $number_left)) {

    $output .= 'You may upload a total of '.$number_left.' more pictures.';

    // show a form that lets the member upload a picture
    // $form_action = $settings['cp_uri'].'/page/pictures.php?page='.$page_number;
    $form_action = './?page='.$page_number;
    $output .= $Form->startForm($form_action);
    $output .= $Form->startFieldset('Upload New Picture');
    $info  = '<b>Picture Upload</b> - You can upload an image file from your ';
    $info .= 'computer to use as a picture on your web page.';
    $output .= $Form->showInfo($info);
    $output .= $Form->showFileInput('Upload this file', $upload_file_name);
    $output .= $Form->showTextInput('Short Description', 'short_description', 255);
    $output .= $Form->showTextarea('Long Description', 'long_description', 4);
    $output .= $Form->showSubmitButton('Upload picture', TRUE);
    if ($number_left > 1) {
        // $multi_upload_uri = $settings['cp_uri'].'/page/upload-multiple-pictures.php?page='.$page_number;
        $multi_upload_uri = 'upload-multiple-pictures.php?page='.$page_number;
        $output .= $Form->showLink('Upload multiple pictures', $multi_upload_uri);
    }
    $output .= $Form->endFieldset();
    $output .= $Form->endForm();
} else {
    $output .= 'Your picture upload limit has been reached. ';
    $output .= 'You must delete some of your current pictures in order to';
    $output .= ' upload more pictures.';
}

$output .= $Form->startFieldSet('Edit Pictures');
$output .= $Form->showInfo('Click on a picture to edit or delete it.');

$query = 'SELECT id, file_name, thumbnail_name FROM cp_member_pictures
          WHERE member_id="'.$member_id.'" AND page_number="'.$page_number.'"
          ORDER BY id ASC';
$result = DB::query($query);
$num_rows = DB::numRows($result);
$output .= '<b>You have '.$num_rows.' pictures on page '.$page_number.'</b>'."\n";
$output .= '<table cellpadding="5" cellspacing="0">';
$row_ended = 0;
for ($i = 1; $member_image = DB::fetchAssoc($result); $i++) {

    if (1 == $i) {
        $output .= '<tr>';
    }
    $output .= '<td>';

    // $edit_uri = $settings['cp_uri'].'/page/edit-picture.php?page='.$page_number.'&id='.$member_image['id'];
    $edit_uri = 'edit-picture.php?page='.$page_number.'&id='.$member_image['id'];
    $output .= '<a href="'.$edit_uri.'" title="Edit '.$member_image['file_name'].'">'."\n";
    $image_uri = $settings['site_uri'].'/'.$member_folder.'/'.$member_image['thumbnail_name'];
    $output .= '<img src="'.$image_uri.'" alt=""/>'."\n";
    $output .= '<br />'.$member_image['file_name'].'</a>'."\n";

    if (2 == $i) {
        $output .= '</td></tr>'."\n";
        $i = 0;
        $row_ended = 1;
    } else {
        $row_ended = 0;
        $output .= '</td>'."\n";
    }

}
if (1 != $row_ended) {
    $output .= '</tr>';
}
$output .= $Form->endFieldSet();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
