<?php
/**
 * Lets members edit or delete a picture
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: edit-picture.php 1283 2004-10-30 13:32:27Z elijah $
 * @package NiftyCMS
 * @subpackage cp_page
 */
require_once '../../../../site/inc/Page.class.php';
require_once '../../../../site/inc/Form.class.php';
require_once '../../../../site/inc/Image.class.php';
$page_type = 'cp';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$member_id     = $Member->getId();   // Get the members id
$member_folder = $Member->getAttribute('folder_name'); // Name of folder to upload files
$page_number   = (int) $_GET['page'];      // Id of page being edited
$edit_id       = (int) $_GET['id'];        // Id of image being edited

$upload_file_name = 'picture1';
$show_upload_form = TRUE;

$query = 'SELECT id, file_name, thumbnail_name, short_description,
                 long_description
          FROM cp_member_pictures WHERE id="'.$edit_id.'" LIMIT 1';
$result   = DB::query($query);
$num_rows = DB::numRows($result);

$posted = Form::SanitizeData($_POST);

if (1 == $num_rows) {
    // Get an associative array containing the picture's information
    $member_image = DB::fetchAssoc($result);

    // show a form that lets the member upload an image
    if ('POST' == $_SERVER['REQUEST_METHOD']) {
        $Form = new Form($_POST, $posted);
    } else {
        $Form = new Form($_POST, $member_image);
    }
}

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    $redirect_uri = $settings['cp_uri'].'/website/page/pictures/?page='.$page_number;
    // If the cancel button was pressed then send the member to the admin index
    Form::redirectOnCancel($redirect_uri);

    // If delete_picture button was pressed then delete the picture
    if (FALSE != array_key_exists('delete_picture', $_POST)) {
        // Log that the member deleted a picture
        Event::logEvent(43, $member_id, $page_type, $edit_id.': '.$member_image['file_name']);

        // Delete the picture from the database
        $where = 'id="'.$edit_id.'" AND  member_id="'.$member_id.'"';
        DB::delete('cp_member_pictures', $where);

        // Delete the picture and it's thumbnail if they are not in use elsewhere
        $Member->deleteImage($member_image['file_name']);
        $Member->deleteImage($member_image['thumbnail_name']);

        DB::close();
        header ('Location: '.$redirect_uri.'&event=44');
        exit();
    }


    // Only try to upload the file if the file field was filled in
    if ('' != $_FILES[$upload_file_name]['name']) {
        // The path to the directory where you want to save the uploaded files.
        $path = '../../../../'.$member_folder.'/';

        // Create a new instance of the ImageUploader class
        $Image = new Image($image_settings);

        // Upload the image
        if (FALSE != $Image->upload($upload_file_name)) {
            if (FALSE != $Image->saveFile($path)) {
                $file_name = $Image->getFinalName();
            } else {
               $file_name = '';
            }
        } else {
            $file_name = '';
        }

        // If there were image upload errors errors
        if (FALSE != $Image->HasError()) {
            // Upload failed
            $upload_success = FALSE;
            // Log that the file upload failed
            $upload_error = Event::showAndLog(4, $member_id, $Image->getUploadError());
            $upload_error .= '<div class="error">';
            $upload_error .= $Image->getUploadError();
            $upload_error .= '</div>';
        } else {
            $upload_success = TRUE;

            // Get the old image name out of the database
            $query = 'SELECT file_name, thumbnail_name FROM cp_member_pictures
                      WHERE id="'.$edit_id.'" LIMIT 1';
            $result = DB::query($query);
            $old_member_picture = DB::fetchAssoc($result);

            // Create the new thumbnail
            $Thumbnail = new Thumbnail($path, $file_name, $image_settings);
            $Thumbnail->SetHeight($image_settings['thumbnail_height']);
            $Thumbnail->SetWidth($image_settings['thumbnail_width']);
            $thumbnail_name = $Thumbnail->Save($path, 'thumb_'.$file_name);

            if ('' != $file_name and TRUE == is_file($path.$file_name)) {
                // Update the database with the new picture information
                $query='UPDATE cp_member_pictures SET
                        file_name="'.$file_name.'",
                        thumbnail_name="'.$thumbnail_name.'",
                        short_description="'.$posted['short_description'].'",
                        long_description="'.$posted['long_description'].'"
                        WHERE id="'.$edit_id.'" AND member_id="'.$member_id.'" LIMIT 1';
                DB::query($query);

                // Delete the previous picture and it's thumbnail if they are not in use anymore
                $Member->deleteImage($old_member_picture['file_name']);
                $Member->deleteImage($old_member_picture['thumbnail_name']);

            } else {
                $uploaded_image = '<div class="error">Error: Uploaded image does not exist.</div>';
            }

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
        }

        if (FALSE == $upload_success) {
            $output .= $upload_error;
        } else {
            if (FALSE == $thumbnail_success) {
                $output .= $thumbnail_error;
            //    $output .= $uploaded_image;
            } else {
                $output .= Event::showAndLog(2, $member_id, $page_type);
       //         $output .= $uploaded_image;
            }
        }

    } else {
        // echo 'NO FILE CHOSEN!';
        // Update the database with the new picture information
        $query = 'UPDATE cp_member_pictures SET
                  short_description="'.$posted['short_description'].'",
                  long_description="'.$posted['long_description'].'"
                  WHERE id="'.$edit_id.'" AND member_id="'.$member_id.'" LIMIT 1';
        DB::query($query);

        $member_image['short_description'] = $posted['short_description'];
        $member_image['long_description'] = $posted['long_description'];

        // Picture description successfully updated
        $output .= Event::showAndLog(41, $member_id, $page_type);

        $upload_success = FALSE;
    }
}

if (1 == $num_rows) {
    $output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
    $output .= $Form->startFieldset('Edit Picture');
    $output .= $Form->showInfo('<b>Edit Picture</b> - You can upload an image file from your computer to replace this picture. <br />If you only want to change the picture description just leave the "Upload this file" field blank.');
    $output .= $Form->showFileInput('Upload this file', $upload_file_name);
    $output .= $Form->showTextInput('Short Description', 'short_description', 255);
    $output .= $Form->showTextarea('Long Description', 'long_description', 4);
    $more_buttons = array(1 =>
                          array('label' => 'Delete Picture',
                                'name' => 'delete_picture',
                                'description' => 'Click \'Delete Picture\' to delete this picture from your page.',
                                'confirm_message' => 'Are you sure you want to delete this picture?'));
    $output .= $Form->showSubmitButton('Submit', TRUE, $more_buttons);
    $output .= $Form->endFieldset();
    $output .= $Form->endForm();

    // Show the thumbnail, descriptions and full size image.
    $select = array('file_name', 'short_description', 'long_description', 'thumbnail_name');
    $where = 'WHERE id="'.$edit_id.'"';
    $result2 = DB::select('cp_member_pictures', $select, $where);
    $member_image2 = DB::fetchAssoc($result2);

    $output .= 'Thumbnail:';
    $output .= Image::showResized($member_image2['thumbnail_name'], $member_folder, $settings['site_uri']);
    $output .= 'Description:';
    $output .= '<p class="description"><b>'.$member_image2['short_description'].'</b>: ';
    $output .= $member_image2['long_description'].'</p>';

$output .= 'Full Size Image:';
    $output .= Image::showResized($member_image2['file_name'], $member_folder, $settings['site_uri']);
} else {
    $output .= '<div class="error">Error: picture was not found in database.</div>';
}
$output .= $Page->showFooter();
echo $output;
?>
