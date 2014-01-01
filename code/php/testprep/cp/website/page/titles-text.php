<?php
/**
 * Allows members to edit page titles and text
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: titles-text.php 1462 2004-11-30 15:24:38Z elijah $
 * @package NiftyCMS
 * @subpackage cp_page
 */
require_once '../../../site/inc/Page.class.php';
require_once '../../../site/inc/Form.class.php';
$page_type = 'cp';
$Page   = new Page($page_type, $settings, $Member);
$Member = new Member($page_type, $settings, $Member);
// $header_stuff = '<script type="text/javascript" src="richtext.js"></script>';
/*
$header_stuff = '
    <script type="text/javascript" src="'.$settings['inc_uri'].'/FCKeditor/fckeditor.js"></script>

    <script type="text/javascript">
      window.onload = function()
      {
        var oFCKeditor = new FCKeditor( \'body_one\' ) ;
 oFCKeditor.BasePath = "'.$settings['inc_uri'].'/FCKeditor/" ;
oFCKeditor.Height = 400 ; // 400 pixels
oFCKeditor.ReplaceTextarea() ;
          var oFCKeditor = new FCKeditor( \'body_two\' ) ;
oFCKeditor.BasePath = "'.$settings['inc_uri'].'/FCKeditor/" ;
oFCKeditor.Height = 400 ; // 400 pixels
oFCKeditor.ReplaceTextarea() ;


      }
</script>';
$output = $Page->showHeader($header_stuff);
*/
$output = $Page->showHeader();

// Get the members id
$member_id   = $Member->getId();
$page_number = (int)$_GET['page'];

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    // If cancel button was pressed then send them to the CP index
    Form::redirectOnCancel($settings['cp_uri'].'/#page'.$page_number);
    // Sanitize posted form data
    $posted = Form::sanitizeData($_POST);
    $body_one = DB::addSlashes($_POST['body_one']);
    $body_two = DB::addSlashes($_POST['body_two']);
    // Update database with the update text and title data
    $query = 'UPDATE cp_member_pages SET
    page_title="'.$posted['page_title'].'",
    link_title="'.$posted['link_title'].'",
    header_text="'.$posted['header_text'].'",
    body_one="'.$body_one.'",
    body_two="'.$body_two.'" WHERE
    member_id="'.$member_id.'" AND page_number="'.$page_number.'"';
    DB::query($query);

    // Log that the member updated their pages titles and text
    Event::logEvent(1, $member_id, $page_type, '');

    // Send the member to the CP index
    header('Location: '.$settings['cp_uri'].'/?event=1&page='.$page_number.'#page'.$page_number);
    exit;
}
$query = 'SELECT page_title, link_title, header_text, body_one, body_two
          FROM cp_member_pages WHERE member_id="'.$member_id.'"
          AND page_number="'.$page_number.'"';
$result = DB::query($query);
if (0 >= DB::numRows($result)) {
    // If their page was not found in the database.
    $output .= Event::showAndLog(12, $member_id, $page_type, $query);
}
$member_page = DB::fetchAssoc($result);

$Form = new Form($_POST, $member_page, $page_type, $settings);

$output .= $Form->startForm($_SERVER['REQUEST_URI']);
$output .= $Form->startFieldset('Page Titles');
$output .= $Form->showInfo('Choose a title for your web page');
$output .= $Form->showTextInput('Page Title (e.g. Company Name)', 'page_title', 255);
$output .= $Form->showTextInput('Page Headline (e.g. Company Name)', 'header_text', 255);
$output .= $Form->showTextInput('Link Title (e.g. Home, More Pictures, or About Us)', 'link_title', 255);
$output .= $Form->endFieldSet();
$output .= $Form->startFieldset('Page Text');
// $output .= $Form->showInfo('To create a link, first select the text that you want to make into a link. Then click the "Insert Web Link" button.');
$select = array('background_color', 'text_color');
$where = 'WHERE member_id="'.$member_id.'" AND page_number="'.$page_number.'"';
$result = DB::select('cp_member_pages', $select, $where);
$member_page = DB::fetchAssoc($result);
$page_style = '';
if (FALSE == empty($member_page['background_color'])) {
    $page_style .= 'body { background-color: '.$member_page['background_color'].';}';
}
if (FALSE == empty($member_page['text_color'])) {
    $page_style .= 'body { color: '.$member_page['text_color'].';}';
}
$output .= $Form->showTextarea('Body One','body_one', 30, TRUE, $page_style);
$output .= $Form->showTextarea('Body Two','body_two', 30, TRUE, $page_style);

$output .= $Form->showSubmitButton('Save Changes', TRUE);
$output .= $Form->endFieldSet();

$output .= $Form->endForm();

$output .= $Page->showFooter();

echo $output;


?>