<?php
/**
 * Allows the admin to edit the members and their pages
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1411 2004-11-29 20:07:36Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$Form   = new Form(array(), array());
$output = $Page->showHeader();

if (FALSE != isset($_GET['event'])) {
    $output .= Event::showMessage($_GET['event']);
}

$output .= '<p>Click on the members name to edit their account or their pages.</p>'."\n";

// Get members information form database and display it in a table
$query = 'SELECT id, email, first_name, last_name, folder_name, num_pages,
                 account_active, account_setup FROM cp_members ORDER BY id ASC';
$result = DB::query($query);
$output .= '<table class="simple">'."\n";
$output .= '<thead>'."\n";
$output .= '<tr>'."\n";
$output .= '<th>Id</th>'."\n";
$output .= '<th>Name</th>'."\n";
$output .= '<th>Email</th>'."\n";
$output .= '<th>More Info</th>'."\n";
$output .= '<th>Site URI</th>'."\n";
$output .= '<th>Pages</th>'."\n";
$output .= '<th>Account Actions</th></tr>'."\n";
$output .= '</thead>'."\n";
$output .= '<tbody>'."\n";
while ($member = DB::fetchAssoc($result)) {
    $output .= '<tr>'."\n";
    $output .= '<td>'.$member['id'].'</td>'."\n";
    $output .= '<td><a href="edit-member-account.php?id='.$member['id'].'">'."\n";
    $output .= $member['first_name'].' '.$member['last_name'].'</a></td> '."\n";
    $output .= '<td>'.$member['email'].'</td>'."\n";
    $output .= '<td><a href="view-info.php?id='.$member['id'].'">More Info</a></td>'."\n";

    $output .= '<td><a href="'.$settings['site_uri'].'/'.$member['folder_name'].'/">/'.$member['folder_name'].'/</a></td>'."\n";
    $output .= '<td>'.$member['num_pages'].'</td>'."\n";
    $output .= '<td>';
    if (1 != $member['account_setup']) {
        $output .= '<a href="setup-account.php?id='.$member['id'].'">Setup Account</a>'."\n";
    } else {
        if (1 == $member['account_active']) {
            $output .= '<a href="login-to-members-cp.php?id='.$member['id'].'">Login to CP</a>'."\n";
        } else {
            $target = $settings['admin_uri'].'/members/cp/activate-member-account.php?id='.$member['id'];
            $message = 'Are you sure you want to activate this member\'s account?';
            $output .= $Form->showConfirmButton($target, 'Activate', $message);
        }
    }
    $output .= '</td>';
    $output .= '</tr>'."\n";
}
$output .= '</tbody></table>';
$output .= $Page->showFooter();
echo $output;
?>
