<?php
/**
 * Lets the member add themselve to the contractor directory
 *
 * @version $Id: add-directory-listing.php 1346 2004-10-31 01:00:47Z elijah $
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

// Get member's information
$select = array('dir_listing');
$where = 'WHERE id="'.$member_id.'"';
$result = DB::select('cp_members', $select, $where);
$member = DB::fetchAssoc($result);

if (0 != $member['dir_listing']) {
    $output .= Event::showAndlog(81, $member_id, $page_type, '');
    $output .= $Page->showFooter();
    echo $output;
    exit(81);
    }

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

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    $redirect_uri = $settings['cp_uri'].'/#website';
    // If the cancel button was pressed then send the member to the CP index
    Form::redirectOnCancel($redirect_uri);

    // Sanitize posted form data
    $posted = Form::sanitizeData($_POST);

    $Form = new Form($posted, $posted);

    $Form->isFilledIn('link_title');

    if (FALSE == $Form->hasErrors()) {
        $member_site_uri = $Member->getSiteUri();
        // die($member_site_uri);

        $select = array('id');
        $where = 'WHERE name="membersite"';
        $result = DB::select('page_subtypes', $select, $where);
        $subtype = DB::fetchAssoc($result);


        // Add a link to the member's page to the directory
        $insert = array('id' => '', 'uri' => $member_site_uri,
                        'query_string' => '', 'parent_id' => $parent_id,
                        'title' => '',
                        'description' => $posted['description'],
                        'heading' => '', 'content' => '',
                        'link_title' => $posted['link_title'],
                        'show_on_menu' => 1,
                        'subtype' => $subtype['id'],
                        'menu_parent_id' => '');
        DB::insert('dir_pages', $insert);

        // Get the id of the page we just inserted
        $last_insert_id = DB::insertId();

        // Update the member's account with id of the directory listing page
        $update = array('dir_listing' => $last_insert_id);
        $where = 'id="'.$member_id.'"';
        DB::update('cp_members', $update, $where);

        $event = 68; // Added listing

        Event::logEvent($event, $member_id, $page_type, $posted['link_title']);

        // Send the member to the Control Panel index
        header('Location: '.$settings['cp_uri'].'/?event='.$event.'&website=1#website');
        exit;
    } else {
        // Form Input Error
        $output .= Event::showAndLog(71, $member_id, $page_type, '');
    }
}

$Form = new Form(array(), array(), $page_type, $settings);
$steps_array = array(1 => 'Choose State', 2 => 'Choose County',
                     3 => 'Choose Job', 4 => 'Enter Title and Description'
);
$output .= $Form->showSteps($steps_array, $current_step);

switch ($current_step) {
    case 1:
        $output .= showList('state', $current_step, $parent_id);
        break;
    case 2:
        $output .= showList('county', $current_step, $parent_id);
        break;
    case 3:
        $output .= showList('job', $current_step, $parent_id);
        break;
    case 4:
        $output .= showForm($Form);
}
$output .= $Page->showFooter();
echo $output;

/**
 * Shows a form allowing the member to edit their directory listing.
 *
 */
function showForm($formObject)
{
    $output  = $formObject->startForm($_SERVER['REQUEST_URI'], 'post');
    $output .= $formObject->startFieldset('Directory Listing');
    $output .= $formObject->showInfo('Choose a title and description for your listing');
    $output .= $formObject->showTextInput('Title', 'link_title', 255);
    $output .= $formObject->showTextInput('Description', 'description', 255);
    $output .= $formObject->showSubmitButton('Add Directory Listing', TRUE);
    $output .= $formObject->endFieldSet();
    $output .= $formObject->endForm();
    return $output;
}

/**
 * Shows a list of either states, counties or jobs.
 *
 */
function showList($type, $currentStep, $parentId)
{
    $next_step = $currentStep + 1;
    $output = '<ul>';

    // Get pages
    $select = array('dir_pages.id AS id', 'link_title');
    $where = 'WHERE page_subtypes.name="'.$type.'" AND dir_pages.subtype=page_subtypes.id';
    if ('state' != $type) {
        $where .= ' AND parent_id="'.$parentId.'"';
    }
    $result = DB::select('dir_pages, page_subtypes', $select, $where, 0);

    $title = ucfirst($type);
    $output .= '<b>Choose '.$title.'</b><ul>';
    while ($page = DB::fetchAssoc($result)) {
        $output .= '<li><a href="add-directory-listing.php?step='.$next_step.'&amp;parent_id='.
            $page['id'].'">'.$page['link_title'].'</a></li>'."\n";
    }
    $output .= '</ul>';
    return $output;
}
?>
