<?php
/**
 * Allows the admin to view more information about a cp member
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: view-info.php 1411 2004-11-29 20:07:36Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$output .= '<p>Click on the members name to edit their account.</p>'."\n";

$cp_member_id = (int) $_GET['id'];
// $query = 'SELECT id, first_name, last_name, folder_name, email, num_pages, account_active FROM cp_members WHERE id = "'.$member_id.'" LIMIT 1';
// $result = DB::query($query);
$select = array('id', 'first_name', 'last_name', 'login_name', 'num_pictures',
                'plan', 'num_pages', 'duration_years', 'email', 'creation_date',
                'dir_listing', 'folder_name', 'account_active', 'first_name',
                'last_name', 'address1', 'address2', 'city', 'state', 'zip',
                'night_phone', 'day_phone');
$where = 'WHERE id="'.$cp_member_id.'"';
$result = DB::select('cp_members', $select, $where);
$member = DB::fetchAssoc($result);

if (1 == $member['account_active']) {
    $login_link = '<a href="login-to-members-cp.php?id='.$member['id'].'">Login to CP</a>'."\n";
} else {
    $login_link = '<a href="activate-account.php?id='.$member['id'].'">Activate Account</a>'."\n";
}

$edit_link = '<a href="edit-member-account.php?id='.$member['id'].'">Edit Account</a>';

$duration_years = $member['duration_years'].' ';
if (1 == $member['duration_years']) {
    $duration_years .= 'year';
} else {
    $duration_years .= 'years';
}
if (FALSE == empty($member['duration_years'])) {
    $price = '$'.$plan_settings[$member['plan']]['year_prices'][$member['duration_years']];
} else {
    $price = '0';
}

$creation_date = date("F j, Y, g:i a", $member['creation_date']);

if (0 != $member['dir_listing']) {
    $select = array('parent_id', 'link_title', 'description');
    $where = 'WHERE id="'.$member['dir_listing'].'"';
    $result = DB::select('dir_pages', $select, $where);
    $member_page_listing = DB::fetchAssoc($result);

    $select = array('uri');
    $where = 'WHERE id="'.$member_page_listing['parent_id'].'"';
    $result = DB::select('dir_pages', $select, $where);
    $job_page = DB::fetchAssoc($result);


    $dir_listing_uri = $settings['site_uri'].$job_page['uri'];
    $listing_link = '<a href="'.$dir_listing_uri.'" rel="external">'.$dir_listing_uri.'</a>'."\n";
    $edit_link = '<a href="'.$settings['cp_uri'].'/website/edit-directory-listing.php">Edit or Delete Listing</a>'."\n";

    $dir_listing_info = array(
        array('label' => 'Company Name', 'value' => $member_page_listing['link_title']),
        array('label' => 'Description', 'value' => $member_page_listing['description']),
        array('label' => 'Listing URL', 'value' => $listing_link),
        array('label' => 'Modify', 'value' => $edit_link),
    );
    $dir_listing = Form::showList($dir_listing_info);

} else {
    $dir_listing = 'Not Listed';
}

$main_site = $Member->getSiteUri($cp_member_id);
$main_site_link = '<a href="'.$main_site.'" rel="external">'.$main_site.'</a>';
$subdomain = 'http://'.$member['folder_name'].'.'.$settings['domain'];
$subdomain_link = '<a href="'.$subdomain.'" rel="external">'.$subdomain.'</a>';
$subfolder = $settings['site_uri'].'/'.$member['folder_name'].'/';
$subfolder_link = '<a href="'.$subfolder.'" rel="external">'.$subfolder.'</a>';

$address = $member['first_name'].' ';
$address .= $member['last_name'].'<br />'."\n";
$address .= $member['address1'].'<br />'."\n";
if ('' != $member['address2']) {
    $address .= $member['address2'].'<br />'."\n";
}
$address .= $member['city'].', ';
$address .= $member['state'].' ';
$address .= $member['zip']."\n";

$member_info = array(
    array('label' => 'Id', 'value' => $member['id']),
    array('label' => 'Login Name', 'value' => $member['login_name']),
    array('label' => 'Name', 'value' => $member['first_name'].' '.$member['last_name']),
    array('label' => 'Email Address', 'value' => $member['email']),
    array('label' => 'Main Website URL', 'value' => $main_site_link),
    array('label' => 'Subdomain', 'value' => $subdomain_link),
    array('label' => 'Subfolder', 'value' => $subfolder_link),
    array('label' => 'Directory Listing', 'value' => $dir_listing),
    array('label' => 'Number of Pages', 'value' => $member['num_pages'].' pages'),
    array('label' => 'Max Number of Pictures', 'value' => $member['num_pictures'].' pictures'),
    array('label' => 'Edit Member Account', 'value' => $edit_link),
    array('label' => 'Login to Member\'s Account', 'value' => $login_link),
    array('label' => 'Account Creation Date', 'value' => $creation_date),
    array('label' => 'Plan Number', 'value' => 'Plan Number '.$member['plan']),
    array('label' => 'Duration', 'value' => $duration_years),
    array('label' => 'Plan Price', 'value' => $price),
    array('label' => 'Address', 'value' => $address),
    array('label' => 'Night Phone', 'value' => $member['night_phone']),
    array('label' => 'Day Phone', 'value' => $member['day_phone']),
);
$output .= Form::showList($member_info);

$output .= $Page->showFooter();
echo $output;
?>
