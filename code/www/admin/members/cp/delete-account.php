<?php
/**
 * Deletes member's account
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: delete-account.php 1519 2004-12-01 06:55:05Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Account.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$cp_member_id = (int) $_GET['id'];
$member_id = $Member->getId();

if (FALSE == empty($_GET['delete_account'])) {
    // Delete the member's account from the database
    DB::delete('cp_members', 'id="'.$cp_member_id.'"', 0);

    // Member account deleted
    $output .= Event::logEvent(84, $member_id, $page_type,
                               'CP Member id:'.$cp_member_id);

    DB::close();

    $redirect_uri = $settings['admin_uri'].'/members/cp/';
    header('Location: '.$redirect_uri.'?event=84');
    exit();
} else {
    $Account = new Account($settings);
    // Delete the members account
    $output .= $Account->delete($cp_member_id);
}
$target = 'delete-account.php?id='.$cp_member_id.'&amp;delete_account=1';
$message = 'Are you sure you want to delete this member\'s account from this database?';
$output .= Form::showConfirmButton($target, 'Delete Account From Database', $message);

$output .= $Page->showFooter();
echo $output;
// DB::close();

//  $redirect_uri = $settings['admin_uri'].'/members/cp/';
// header('Location: '.$redirect_uri);
?>