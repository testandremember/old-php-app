<?php
/**
 * Lists the parts of the Control Panel.
 *
 * Shows a list of the different parts of the Control Panel with a description
 * of each.
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1455 2004-11-30 06:18:55Z elijah $
 * @package NiftyCMS
 * @subpackage cp
 */
require_once '../site/inc/Page.class.php';
require_once '../site/inc/Form.class.php';
$page_type = 'cp';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$header_stuff  = '<style type="text/css">'."\n";
$header_stuff .= '.backlink { display: none; }'."\n";
$header_stuff .= '</style>'."\n";
$output = $Page->showHeader($header_stuff);
$member_folder = $Member->getAttribute('folder_name');
$member_id     = $Member->getId();

// Get member's information
$select = array('login_name', 'num_pictures', 'plan', 'num_pages',
                'duration_years', 'email', 'creation_date', 'dir_listing');
$where = 'WHERE id="'.$member_id.'"';
$result = DB::select('cp_members', $select, $where);
$member = DB::fetchAssoc($result);

if (FALSE != isset($_GET['event']) && FALSE == isset($_GET['page'])
    && FALSE == isset($_GET['account']) && FALSE == isset($_GET['website'])) {
    $output .= Event::showMessage($_GET['event']);
}
$output .= '<h2>Page Contents</h2>'."\n";
$output .= '<ul>'."\n";
$output .= '<li><a href="#website">Your Website</a>: Edit and configure your website';
$output .= '<ul>'."\n";
$query = 'SELECT page_number FROM cp_member_pages
          WHERE member_id="'.$member_id.'" AND active="1"
          ORDER BY page_number ASC';
$result = DB::query($query);
$page_menu = '';
while ($member_page = DB::fetchAssoc($result)) {
    $page_menu .= showPageMenu($member_page['page_number'], $settings, $member_folder);
    $output    .= showPageList($member_page['page_number']);
}

$output .= '</ul>'."\n";
$output .= '</li>'."\n";
$output .= '<li><a href="#account">Your Account</a>: Edit your account information such as your address or password.</li>'."\n";
$output .= '<li><a href="#support">Support</a>: Get help with a problem you are having or just ask a question.</li>'."\n";
$output .= '</ul>'."\n";

$output .= '<h2 id="website">Your Website</h2>'."\n";
if (FALSE != isset($_GET['event']) && FALSE != isset($_GET['website'])) {
    $output .= Event::showMessage($_GET['event']);
}
$output .= '<h3>Website Information &amp; Options</h3>'."\n";
$main_site = $Member->getSiteUri();
$main_site_link = '<a href="'.$main_site.'" rel="external">'.$main_site.'</a>';
$subdomain = 'http://'.$member_folder.'.'.$settings['domain'];
$subdomain_link = '<a href="'.$subdomain.'" rel="external">'.$subdomain.'</a>';
$subfolder = $settings['site_uri'].'/'.$member_folder.'/';
$subfolder_link = '<a href="'.$subfolder.'" rel="external">'.$subfolder.'</a>';
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
    $dir_listing = '<a href="'.$settings['cp_uri'].'/website/add-directory-listing.php">List your site in the directory</a>'."\n";
}

$website_info = array(
    array('label' => 'Main Website URL', 'value' => $main_site_link),
    array('label' => 'Subdomain', 'value' => $subdomain_link),
    array('label' => 'Subfolder', 'value' => $subfolder_link),
    array('label' => 'Directory Listing', 'value' => $dir_listing),
    array('label' => 'Number of Pages', 'value' => $member['num_pages'].' pages'),
    array('label' => 'Max Number of Pictures', 'value' => $member['num_pictures'].' pictures'),
);
$output .= Form::showList($website_info);

$output .= $page_menu;

$output .= '<h2 id="account">Your Account</h2>';
if (FALSE != isset($_GET['event']) && FALSE != isset($_GET['account'])) {
    $output .= Event::showMessage($_GET['event']);
    }
$output .= '<h3>Account Information</h3>';

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
$confirm = array(
    array('label' => 'Login Name', 'value' => $member['login_name']),
    array('label' => 'Email Address', 'value' => $member['email']),
    array('label' => 'Creation Date', 'value' => $creation_date),
    array('label' => 'Plan Number', 'value' => 'Plan Number '.$member['plan']),
    array('label' => 'Duration', 'value' => $duration_years),
    array('label' => 'Plan Price', 'value' => $price));
$output .= Form::showList($confirm);

$output .= '<h3>Edit Account</h3>';
$output .= '<ul class="cpmenu">'."\n";
$output .= '<li class="address">'."\n";
$output .= '<a href="'.$settings['cp_uri'].'/account/address.php">Update Address Information</a><br />'."\n";
$output .= 'Update your contact information.'."\n";
$output .= '</li>'."\n";
$output .= '<li class="password">'."\n";
$output .= '<a href="'.$settings['cp_uri'].'/account/password.php">Change Password</a><br />'."\n";
$output .= 'Choose a new login password.'."\n";
$output .= '</li>'."\n";
$output .= '<li class="email">'."\n";
$output .= '<a href="'.$settings['cp_uri'].'/account/email.php">Change Email Address</a><br />'."\n";
$output .= 'Change your email address.'."\n";
$output .= '</li>'."\n";
$output .= '</ul>'."\n";
$output .= '<p class="toplink"><a href="#top">Back to Top</a></p>';

$output .= '<h2 id="support">Support</h2>';
if (FALSE != isset($_GET['event']) && FALSE != isset($_GET['support'])) {
    $output .= Event::showMessage($_GET['event']);
}
$output .= '<ul class="cpmenu">'."\n";
$output .= '<li class="support">'."\n";
$output .= '<a href="'.$settings['cp_uri'].'/support/">Get Support</a><br />'."\n";
$output .= 'Ask questions, get answers. Get support for any problem you have.'."\n";
$output .= '</li>'."\n";
$output .= '</ul>'."\n";

$output .= '<p class="toplink"><a href="#top">Back to Top</a></p>';
$output .= $Page->showFooter();

echo $output;

function showPageMenu($pageNumber, $settings, $member_folder) {
    $output  = '<h3 id="page'.$pageNumber.'">Web Page Number '.$pageNumber.'</h3>'."\n";
    if (FALSE != isset($_GET['event']) AND FALSE != isset($_GET['page']) AND $_GET['page'] == $pageNumber) {
        $output .= Event::showMessage($_GET['event']);
    }
    if (1 == $pageNumber) {
        $output .= '<div style="text-align:right"><a href="http://'.$member_folder.'.'.$settings['domain'].'/" rel="external">View Web Page Number 1</a></div>'."\n";
        // $output .= '<div style="text-align:right"><a href="'.$settings['site_uri'].'/'.$member_folder.'/" rel="external">View Web Page Number 1</a></div>';
    } else {
        $output .= '<div style="text-align:right"><a href="http://'.$member_folder.'.'.$settings['domain'].'/'.$pageNumber.'/" rel="external">View Web Page Number '.$pageNumber.'</a></div>'."\n";
    }
    $output .= '<ul class="cpmenu">'."\n";
    $output .= '<li class="titlestext">'."\n";
    $output .= '<a href="'.$settings['cp_uri'].'/website/page/titles-text.php?page='.$pageNumber.'">Titles and Text</a><br />'."\n";
    $output .= 'The first thing you need to do is to choose the titles and text that will go on your web page'."\n";
    $output .= '</li>'."\n";
    $output .= '<li class="template">'."\n";
    $output .= '<a href="'.$settings['cp_uri'].'/website/page/template/?page='.$pageNumber.'">Page Template</a><br />'."\n";
    $output .= 'Optionally choose a page template. We have created several templates for different jobs.';
    $output .= '</li>'."\n";
    $output .= '<li class="colors">'."\n";
    $output .= '<a href="'.$settings['cp_uri'].'/website/page/colors.php?page='.$pageNumber.'">Page Colors</a><br />'."\n";
    $output .= 'Customize the colors that appear on your web page.'."\n";
    $output .= '</li>'."\n";
    $output .= '<li class="logo">'."\n";
    $output .= '<a href="'.$settings['cp_uri'].'/website/page/logo/?page='.$pageNumber.'">Upload Logo</a><br />'."\n";
    $output .= 'If you have a company logo, you can upload it from your computer and use it on your web page.'."\n";
    $output .= '</li>'."\n";
    $output .= '<li class="pictures">'."\n";
    $output .= '<a href="'.$settings['cp_uri'].'/website/page/pictures/?page='.$pageNumber.'">Upload Pictures</a><br />'."\n";
    $output .= 'Upload pictures from your computer to use on your web page.'."\n";
    $output .= '</li>'."\n";
    $output .= '<li class="options">'."\n";
    $output .= '<a href="'.$settings['cp_uri'].'/website/page/options.php?page='.$pageNumber.'">Page Options</a><br />'."\n";
    $output .= 'Optionally configure page options such as showing a hit counter.'."\n";
    $output .= '</li>'."\n";
    $output .= '</ul>'."\n";
    $output .= '<p class="toplink"><a href="#top">Back to Top</a></p>';
    return $output;
}
/**
 * Shows a <li> with a link to a Page Section of the Control Panel
 *
 */
function showPageList($pageNumber) {
    $output = '<li><a href="#page'.$pageNumber.'">Page '.$pageNumber.'</a></li>'."\n";
    return $output;
}
?>
