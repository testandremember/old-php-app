<?php
/**
 * Lets members upload multiple pictures.
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: upload-multiple-pictures.php 1272 2004-10-30 01:22:18Z elijah $
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

$member_id     = $Member->getId();   // Get the member's id
$page_number   = (int) $_GET['page'];      // Page number being edited
$member_folder = $Member->getAttribute('folder_name'); // Get the member's folder
// $upload_success = FALSE;
$upload_success = array();
$upload_error = array();
$Form = new Form($_POST, array());

$number_left = 0;
$can_upload = $Member->canUploadMoreImages($member_id, $number_left);

if (FALSE != isset($_GET['event']) AND FALSE != isset($_GET['page'])) {
        $output .= Event::showMessage($_GET['event']);
    }

$show_upload_form = TRUE;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $posted = Form::SanitizeData($_POST);
    $Form = new Form($posted, $posted);
    $uri = $settings['cp_uri'].'/website/page/pictures/?page='.$page_number;
    Form::redirectOnCancel($uri);

    if (FALSE != $can_upload) {
        // The path to the directory where you want to save the uploaded files.
        $path = '../../../../'.$member_folder.'/';

        $can_upload_another = TRUE;
        for ($picture_num = 1; $can_upload_another == TRUE; $picture_num++) {
            // Create a new instance of the Image class
            $Image[$picture_num] = new Image($image_settings);

            $uploaded_image[$picture_num] = '';
 $upload_success[$picture_num] = FALSE;
            // echo '<p>picnum '.$picture_num;
            // echo '<p>numleft '.$number_left;
        if ($picture_num < $image_settings['max_upload_at_once'] && $picture_num < $number_left) {
            $can_upload_another = TRUE;
        } else {
            $can_upload_another = FALSE;
        }

        $upload_file_name[$picture_num] = 'picture'.$picture_num;
            // UPLOAD the file
            if (FALSE != $Image[$picture_num]->upload($upload_file_name[$picture_num])) {
                if (FALSE != $Image[$picture_num]->saveFile($path)) {
                    $file_name[$picture_num] = $Image[$picture_num]->getFinalName();
                }
            } else {
                $file_name[$picture_num] = $Image[$picture_num]->getFinalName();
                if ('' == $file_name[$picture_num]) {

                    // echo $file_name;
              // break;
                }
            }

            // If there were errors
            if (FALSE != $Image[$picture_num]->hasError()) {
                // Upload failed
                $upload_success[$picture_num] = FALSE;
                $upload_error[$picture_num] = Event::showAndLog(4, $member_id, $page_type, $Image[$picture_num]->getUploadError());
                $upload_error[$picture_num] .= '<p class="error">';
                $upload_error[$picture_num] .= $Image[$picture_num]->getUploadError();
                $upload_error[$picture_num] .= '</p>';

                //        $upload_result = '<div class="error">File was not uploaded.<br />Error: '.$Image->getUploadError()."</div>\n";
            } else {
                // Upload was successful
                $upload_success[$picture_num] = TRUE;

                //  $upload_result = '<div class="success">'.$file_name.' was successfully uploaded.</div>'."\n";

                // Create Thunbnail
                $Thumbnail[$picture_num] = new Thumbnail($path, $file_name[$picture_num], $image_settings);

                $Thumbnail[$picture_num]->setHeight($image_settings['thumbnail_height']);
                $Thumbnail[$picture_num]->setWidth($image_settings['thumbnail_width']);

                $thumbnail_name[$picture_num] = $Thumbnail[$picture_num]->save($path, 'thumb_'.$file_name[$picture_num]);
                if (FALSE != $Thumbnail[$picture_num]->hasError()) {
                    // echo 'Thumbnail errors';
                    $thumbnail_success[$picture_num] = FALSE;
                    $thumbnail_error[$picture_num]  = '<div class="error">';
                    $thumbnail_error[$picture_num] .= $Thumbnail[$picture_num]->getError();
                    $thumbnail_error[$picture_num] .= '</div>';
                } else {
                    $thumbnail_success[$picture_num] = TRUE;
                    // echo 'No Thumbnail errors';
                }
                // If the file was successfully uploaded
                if ('' != $file_name[$picture_num] AND TRUE == is_file($path.$file_name[$picture_num])) {
                    $uploaded_image[$picture_num] .= '<b>Thumbnail:</b>';
                    $uploaded_image[$picture_num] .= Image::showResized($thumbnail_name[$picture_num], $member_folder, $settings['site_uri']);
                    $uploaded_image[$picture_num] .= '<p><b>'.$posted['short_description'.$picture_num].'</b>: ';
                    $uploaded_image[$picture_num] .= $posted['long_description'.$picture_num].'</p>';
                    $uploaded_image[$picture_num] .= '<b>Full Size Image:</b>';
                    $uploaded_image[$picture_num] .= Image::showResized($file_name[$picture_num], $member_folder, $settings['site_uri']);

                } else {
                    $uploaded_image[$picture_num] = '<div class="error">Error: Uploaded image does not exist.</div>';
                }
                // Add the image to the database
                $query = 'INSERT INTO cp_member_pictures
                (id, member_id, file_name, thumbnail_name, page_number,
                 short_description, long_description)
                VALUES ("", "'.$member_id.'", "'.$file_name[$picture_num].'",
                        "'.$thumbnail_name[$picture_num].'", "'.$page_number.'",
                        "'.$posted['short_description'.$picture_num].'",
                        "'.$posted['long_description'.$picture_num].'")';
                DB::query($query);

            //      for ($picture_num2 = 1; $picture_num2 < $picture_num; $picture_num2++) {
    //   if (FALSE == $upload_success[$picture_num]) {
       //     echo $picture_num2;
        //    $output .= Event::showAndLog(4, $member_id, $Image->getUploadError());
     //      $output .= '<p class="error">';
   //      $output .= $Image->getUploadError();
  //     $output .= '</p>';
     //   } else {
            if (FALSE == $thumbnail_success) {
                $output .= $thumbnail_error;
                $output .= $uploaded_image;
            } else {
                // Log that multiple pictures were uploaded
              //  $output .= Event::showAndLog(53, $member_id);
                  $output .= Event::showAndLog(2, $member_id, $page_type);
                $output .= $uploaded_image[$picture_num];
            }
   //     }
            // }
            }
        }
    } else {
      //  $upload_success[$picture_num] = FALSE;
        // Picture upload limit reached
    //    $upload_error[$picture_num] = Event::showAndLog(67, $member_id, '');
        $show_upload_form = FALSE;
    }

}
// print_r($upload_success);
// print_r($upload_error);
// $output .= 'You have uploaded a total of '.$num_rows.' pictures.';
$number_left = 0;
// if ($num_rows < $member['num_pictures']) {
if (FALSE != $Member->canUploadMoreImages($member_id, $number_left)) {

    $output .= 'You may upload a total of '.$number_left.' more pictures.';

    $output .= show_upload_form($Form, $image_settings['max_upload_at_once'], $number_left, $upload_success, $upload_error);
} else {
    $output .= 'Your picture upload limit has been reached. You must delete some of your current pictures in order to upload more pictures.';
}

$output .= $Page->showFooter();
echo $output;

function show_upload_form(&$form, $numUpload, $numberLeft, $upload_success, $upload_error)
{
    $output = $form->startForm($_SERVER['REQUEST_URI'], 'post');
    $can_upload_another = TRUE;
    $number_left = 0;
    for ($picture_num = 1; $can_upload_another == TRUE; $picture_num++) {

        // echo '<p>picnum '.$picture_num;
        // echo '<p>numleft '.$numberLeft;
        if ($picture_num < $numUpload && $picture_num < $numberLeft) {
            $can_upload_another = TRUE;
        } else {
            $can_upload_another = FALSE;
        }

        // The name of the file field in your form.
        $upload_file_name = 'picture'.$picture_num;

        // show a form that lets the member upload a picture
        $output .= $form->startFieldset('Upload New Picture');
        if (FALSE != isset($upload_success[$picture_num]) AND FALSE == $upload_success[$picture_num]) {
            if (FALSE != isset($upload_error[$picture_num])) {
                $output .= $form->showInfo($upload_error[$picture_num]);
            }
            //    echo '*'.$output.'*';
        }


        $output .= $form->showInfo('<b>Picture Upload</b> - You can upload an image file from your computer to use as a picture on your web page.');
        $output .= $form->showFileInput('Upload this file', $upload_file_name);
        $output .= $form->showTextInput('Short Description', 'short_description'.$picture_num, 255);
        $output .= $form->showTextarea('Long Description', 'long_description'.$picture_num, 4);
        $output .= $form->endFieldset();

    }
    $output .= $form->startFieldset('Upload Pictures');
    $output .= $form->showSubmitButton('Upload pictures', TRUE);
    $output .= $form->endFieldset();
    $output .= $form->endForm();
    return $output;
}
?>
