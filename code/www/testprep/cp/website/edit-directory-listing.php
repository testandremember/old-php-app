<?php
/**
 * Lets the member add themselve to the contractor directory
 *
 * @version $Id: edit-directory-listing.php 1544 2004-12-02 04:21:07Z elijah $
 * @author Elijah Lofgren <elijah@truthland.com>
 * @package NiftyCMS
 * @subpackage cp_website
 */
require_once '../../site/inc/Page.class.php';
require_once '../../site/inc/Form.class.php';
$page_type = 'cp';
$Page   = new Page($page_type, $settings, $Member);
$Member = new Member($page_type, $settings, $Member);
$output = $Page->showHeader();
$member_id   = $Member->getId();
$member_folder = $Member->getAttribute('folder_name');

if (FALSE == isset($_GET['step'])) {
    $current_step = 1;
} else {
    $current_step = (int) $_GET['step'];
}
if (FALSE == isset($_GET['parent_id'])) {
    $parent_id = 1;
} else {
    $parent_id = (int) $_GET['parent_id'];
}

// Get member's information
$select = array('dir_listing');
$where = 'WHERE id="'.$member_id.'"';
$result = DB::select('cp_members', $select, $where);
$member = DB::fetchAssoc($result);

// Get member website listing information
$select = array('link_title', 'description', 'uri', 'parent_id');
$where = 'WHERE id="'.$member['dir_listing'].'"';
$result = DB::select('dir_pages', $select, $where);
$listing = DB::fetchAssoc($result);

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    $redirect_uri = $settings['cp_uri'].'/';
    // If the cancel button was pressed then send the member to the CP index
    Form::redirectOnCancel($redirect_uri.'#website');

    // If delete_listing button was pressed then delete the member's listing
    if (FALSE != array_key_exists('delete_listing', $_POST)) {
        // Delete the directory listing page from the database
        DB::delete('dir_pages', 'id="'.$member['dir_listing'].'"');

        // Update the member's account to show that they no longer have
        // a directory listing
        $update = array('dir_listing' => 0);
        $where = 'id="'.$member_id.'"';
        DB::update('cp_members', $update, $where);

        Event::logEvent(70, $member_id, $page_type, '');
        DB::close();
        header('Location: '.$redirect_uri.'?website=1&event=70#website');
        exit;
    }

    // Sanitize posted form data
    $posted = Form::sanitizeData($_POST);

    $Form = new Form($posted, $posted, $page_type);

    $Form->isFilledIn('link_title');

    if (FALSE == $Form->hasErrors()) {
        $member_site_uri = $Member->getSiteUri();

        $update = array('link_title' => $posted['link_title'],
                        'description' => $posted['description'],
                        'uri' => $member_site_uri);
        $where = 'id="'.$member['dir_listing'].'"';
        DB::update('dir_pages', $update, $where);

        $event = 69; // Updated listing
        Event::logEvent($event, $member_id, $page_type, $posted['link_title']);

        // Send the member to the Control Panel index
        header('Location: '.$redirect_uri.'?event='.$event.'&website=1#website');
        exit;
    } else {
        // Form Input Error
        $output .= Event::showAndLog(71, $member_id, $page_type, '');
    }
} else {
    $Form = new Form(array(), $listing, $page_type);
}
$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Directory Listing');
$output .= $Form->showInfo('Choose a title and description for your listing');
$output .= $Form->showTextInput('Title', 'link_title', 255);
$output .= $Form->showTextInput('Description', 'description', 255);
$more_buttons = array(1 =>
                      array('label' => 'Delete Directory Listing',
                            'name' => 'delete_listing',
                            'description' => 'To change the category your site is listed in, you must first delete your directory listing.<br />Then you can create it in a different category.',
                            'confirm_message' => 'Are you sure you want to delete your directory listing?'));
$output .= $Form->showSubmitButton('Save Changes', TRUE, $more_buttons);
$output .= $Form->endFieldSet();
$output .= $Form->endForm();

$output .= '<h2>Current Directory Listing</h2>';
// Show the breadcrumbs for the member's directory listing.

$select = array('id', 'parent_id', 'link_title');
$where = 'WHERE id="'.$listing['parent_id'].'"';
$result = DB::select('dir_pages', $select, $where);
$job_page = DB::fetchAssoc($result);

$Menu = new Menu('dir', $member['dir_listing']);

$category = $Menu->showBreadCrumbs($listing['parent_id']);
$title  = '<a href="'.$listing['uri'].'" rel="external">';
$title .= $listing['link_title'].'</a>';
$uri_link  = '<a href="'.$listing['uri'].'" rel="external">';
$uri_link .= $listing['uri'].'</a>';

$listing_info = array(
    array('label' => 'Category', 'value' => $category),
    array('label' => 'Title', 'value' => $title),
    array('label' => 'Description', 'value' => $listing['description']),
    array('label' => 'Website URL', 'value' => $uri_link),
);
$output .= Form::showList($listing_info);

$output .= $Page->showFooter();
echo $output;
?>
