<?php
/**
 * Allows adding, editing, and deleting of admin accounts
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1423 2004-11-29 22:31:11Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members_admin
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

$output .= '<p>Click on the members name to edit their account.</p>'."\n";

// Get members information form database and display it in a table
$query = 'SELECT id, email, first_name, last_name, account_active
          FROM admin_members';
$result = DB::query($query);
$output .= '<table class="simple">';
$output .= '<thead>';
$output .= '<tr><th>Name</th>';
$output .= '<th>Email</th>';
$output .= '<th>Change Password</th>';
$output .= '<th>Delete Account</th>';
$output .= '<th>Activate/Deactivate Account</th>';
$output .= '</tr></thead>';
$output .= '<tbody>';

$Form = new Form(array(), array());
while ($member = DB::fetchAssoc($result)) {
    $output .= '<tr>'."\n";
    $output .= '<td><a href="edit-account.php?id='.$member['id'].'">'."\n";
    $output .= $member['first_name'].' '.$member['last_name'].'</a></td> '."\n";
    $output .= '<td><a href="email.php?id='.$member['id'].'">'."\n";
    $output .= $member['email'].'</a></td>'."\n";
    $output .= '<td><a href="password.php?id='.$member['id'].'">'."\n";
    $output .= 'Change Password</a></td>'."\n";
    $output .= '<td>';
    $delete_target = $settings['admin_uri'].'/members/admin/delete-account.php?id='.$member['id'];
    $message = 'Are you sure you want to delete this Admin Panel Account?';
    $output .= $Form->showConfirmButton($delete_target, 'Delete Account', $message);
    $output .= '</td>';
    $output .= '<td>';
    if (1 == $member['account_active']) {
        $deactivate_target = $settings['admin_uri'].'/members/admin/deactivate-admin-account.php?id='.$member['id'];
        $deactivate_message = 'Are you sure you want to deactivate this admin account? The admin member will no longer be able log in to their account!';
        $output .= $Form->showConfirmButton($deactivate_target, 'Deactivate', $deactivate_message);
    } else {
        $activate_target = $settings['admin_uri'].'/members/admin/activate-admin-account.php?id='.$member['id'];
        $activate_message = 'Are you sure you want to activate this admin account?';
        $output .= $Form->showConfirmButton($activate_target, 'Activate', $activate_message);
    }
    $output .= '</td>';
    $output .= '</tr>'."\n";
}
$output .= '</tbody></table>';
$output .= $Page->showFooter();
echo $output;
?>
