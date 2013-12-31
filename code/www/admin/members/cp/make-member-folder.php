<?php
/**
 * Allows the admin to manually create the member's folder
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: make-member-folder.php 1477 2004-11-30 20:53:05Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
require_once '../../../testprep/site/inc/Account.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$cp_member_id = (int) $_GET['id'];
$member_id = $Member->getId();

$select = array('folder_name');
$where = 'WHERE id="'.$cp_member_id.'"';
$result = DB::select('cp_members', $select, $where);
$member_data = DB::fetchAssoc($result);

$Account = new Account($settings);
$Account->createMemberFolder($member_data['folder_name']);

// Member folder created
$output .= Event::logEvent(87, $member_id, $page_type,
                           $member_data['folder_name']);

header('Location: '.$settings['admin_uri'].'/members/cp/edit-member-account.php?id='.$cp_member_id.'&event=87');
exit;

?>
