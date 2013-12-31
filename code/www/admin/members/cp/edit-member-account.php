<?php
/**
 * Allows the admin to edit the members account
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: edit-member-account.php 1489 2004-12-01 02:40:57Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

if (FALSE != isset($_GET['event'])) {
    $output .= Event::showMessage($_GET['event']);
}

$cp_member_id = (int) $_GET['id'];
$member_id = $Member->getId();

$select = array('folder_name', 'num_pages', 'num_pictures', 'account_active',
                'domain_name', 'login_name', 'duration_years', 'plan',
                'dir_listing');
$where = 'WHERE id="'.$cp_member_id.'"';
$result = DB::select('cp_members', $select, $where);
$member = DB::fetchAssoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If the cancel button was pressed then send the member to the CP index
    if (array_key_exists('cancel', $_POST)) {
        header('Location: '.$settings['admin_uri'].'/members/cp/');
        exit;
    }

    // Trim spaces, and convert quotes to html entities
    $posted = Form::sanitizeData($_POST);

    // Create a form object
    $Form = new Form($_POST, $posted);
    // Validate the form input

    // If there were not any form validation errors
    if (FALSE == $Form->hasErrors()) {
        $query = 'SELECT id, page_number FROM cp_member_pages
                  WHERE member_id="'.$cp_member_id.'" ORDER BY page_number DESC';
        $result = DB::query($query);
        $newest_member_page = DB::fetchAssoc($result);
        $last_page_number = $newest_member_page['page_number'];
        $current_num_pages = DB::numRows($result);
        $pages_to_add = $posted['num_pages'] - $current_num_pages;

        // Insert new pages if the member's page number was increased
        for ($page_number = $last_page_number + 1; $page_number <= $pages_to_add + $last_page_number; $page_number++) {
            $query2 = 'INSERT INTO cp_member_pages ( `id` , `member_id` , `page_number`)
                       VALUES ("", "'.$cp_member_id.'", "'.$page_number.'");';
            DB::query($query2);
        }

        // Update the database with the updated information
        $update = array('folder_name'    => $posted['folder_name'],
                        'login_name'     => $posted['login_name'],
                        'num_pages'      => $posted['num_pages'],
                        'num_pictures'   => $posted['num_pictures'],
                        'duration_years' => $posted['duration_years'],
                        'plan'           => $posted['plan'],
                        'domain_name'    => $posted['domain_name']);
        $where = 'id="'.$cp_member_id.'"';
        DB::update('cp_members', $update, $where);

        $member_site_uri = $Member->getSiteUri($cp_member_id);

        // Update website directory listing
        $update = array('uri' => $member_site_uri);
        $where = 'id="'.$member['dir_listing'].'"';
        DB::update('dir_pages', $update, $where);

        // Member account edited
        $output .= Event::logEvent(80, $member_id, $page_type, 'Member Id:'.$cp_member_id);

        header('Location: '.$settings['admin_uri'].'/members/cp/?event=80');
        exit;
    } else {
        // Form Input Error
        $output .= Event::showAndLog(71, $member_id, $page_type, '');
    }
} else {
    // Create a form object
    $Form = new Form($_POST,$member);
    // There was not update so there is no update result
    // $update_result='';
}

$output .= '<h2>Account Actions</h2>';
$output .= '<p class="infobox">Deactivating a members\'s account only ';
$output .= 'prevents them from logging into the Control Panel</p>';

if (1 == $member['account_active']) {
    $deactivate_target = 'deactivate-member-account.php?id='.$cp_member_id;
    $message  = 'Are you sure you want to deactivate this member\'s account? ';
    $message .= 'The member will no longer be able log in to the Control Panel!';
    $active_button = $Form->showConfirmButton($deactivate_target,
                                                 'Deactivate', $message);
} else {
    $activate_target = 'activate-member-account.php?id='.$cp_member_id;
    $activate_message = 'Are you sure you want to activate this member\'s account?';
    $active_button = $Form->showConfirmButton($activate_target, 'Activate',
                                                 $activate_message);
}

$target = 'delete-account.php?id='.$cp_member_id;
$message = 'Are you sure you want to delete this member\'s account?';
$delete_button = $Form->showConfirmButton($target, 'Delete Account', $message);

$create_folder_link = '<a href="make-member-folder.php?id='.$cp_member_id.'">Create/Recreate Member\'s Folder</a>';
$make_writable_link = '<a href="make-member-folder-writable.php?id='.$cp_member_id.'">Make Member Folder Writable</a>';
$resend_email_link  = '<a href="resend-welcome-email.php?id='.$cp_member_id.'">Resend Welcome Email</a>';

$account_actions = array(
    array('label' => 'Activate/Deactivate Account', 'value' => $active_button),
    array('label' => 'Delete Account', 'value' => $delete_button),
    array('label' => 'Create/Recreate Folder', 'value' => $create_folder_link),
    array('label' => 'Resend Welcome Email', 'value' => $resend_email_link)


);
$output .= Form::showList($account_actions);

$output .= '<h2>Edit Member Account</h2>';
// show a form that lets the member update their information
$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Edit Member Account');
$output .= $Form->showTextInput('Login Name', 'login_name', 255);
$output .= $Form->showTextInput('Number of pages', 'num_pages', 5, 5);
$output .= $Form->showTextInput('Number of pictures', 'num_pictures', 5, 5);
$output .= $Form->showTextInput('Duration Years', 'duration_years', 3, 3);
$output .= $Form->showTextInput('Plan Number', 'plan', 5, 5);
$output .= $Form->showTextInput('Folder Name, (BE VERY CAREFUL!)', 'folder_name' , 100);
$output .= $Form->showTextInput('Domain Name', 'domain_name' , 255);
$output .= $Form->showSubmitButton('Update Member Account', TRUE);
$output .= $Form->endFieldset();
$output .= $Form->endForm();
// $output .= '$Form->startFieldset('Edit Member pages');

$output .= '<h2>Edit Member\'s Pages</h2>';
$query = 'SELECT id, page_number, page_title, active FROM cp_member_pages
          WHERE member_id="'.$cp_member_id.'" ORDER BY page_number ASC';
$result = DB::query($query);
if (0 == DB::numRows($result)) {
    $output .= '<p>Member has no pages</p>';
} else {
    $info = 'Member\'s pages:<br />';
    $output .= '<table class="simple">'."\n";
    $output .= '<thead>'."\n";
    $output .= '<tr>'."\n";
    $output .= '<th>Id</th>'."\n";
    $output .= '<th>Page Number</th>'."\n";
    $output .= '<th>Title</th>'."\n";
    $output .= '<th>View Page</th>'."\n";
    $output .= '<th>Delete Page</th>'."\n";
    $output .= '<th>Activate/Deactivate Page</th>'."\n";
    $output .= '</tr>'."\n";
    $output .= '</thead>'."\n";
    $output .= '<tbody>'."\n";
    while ($member_page = DB::fetchAssoc($result)) {
        $output .= '<tr>'."\n";
        $output .= '<td>'.$member_page['id'].'</td>'."\n";
        $output .= '<td>'.$member_page['page_number'].' - <a href="edit-member-page-number.php?id='.$member_page['id'].'">Change</a></td>'."\n";
        $output .= '<td>'.$member_page['page_title'].'</td>'."\n";
        $page_uri = $settings['site_uri'].'/'.$member['folder_name'].'/'.$member_page['page_number'].'/';
        $output .= '<td><a href="'.$page_uri.'" rel="external">View Page</a></td>'."\n";
        $output .= '<td>'."\n";
        $delete_target = $settings['admin_uri'].'/members/cp/delete-member-page.php?id='.$member_page['id'].'&member_id='.$cp_member_id.'&page_number='.$member_page['page_number'];
        $delete_message = 'Are you sure you want to delete this member page? All the pictures on it will also be deleted!';
        $output .= $Form->showConfirmButton($delete_target, 'Delete', $delete_message);
        $output .= '</td>'."\n";
        $output .= '<td>'."\n";
        if (1 == $member_page['active']) {
            $deactivate_target = $settings['admin_uri'].'/members/cp/deactivate-member-page.php?id='.$member_page['id'].'&member_id='.$cp_member_id.'&page_number='.$member_page['page_number'];
            $deactivate_message = 'Are you sure you want to deactivate this member page? The member will no longer be able to edit or view it!';
            $output .= $Form->showConfirmButton($deactivate_target, 'Deactivate', $deactivate_message);
        } else {
            $activate_target = $settings['admin_uri'].'/members/cp/activate-member-page.php?id='.$member_page['id'].'&member_id='.$cp_member_id.'&page_number='.$member_page['page_number'];
            $activate_message = 'Are you sure you want to activate this member page?';
            $output .= $Form->showConfirmButton($activate_target, 'Activate', $activate_message);
        }
        $output .= '</td>'."\n";
        $output .= '</tr>'."\n";
    }
    $output .= '</tbody>'."\n";
    $output .= '</table>'."\n";
}
$output .= $Page->showFooter();
echo $output;
?>
