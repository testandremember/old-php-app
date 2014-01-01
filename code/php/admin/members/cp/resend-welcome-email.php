<?php
/**
 * Resends the account registration welcome email
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: resend-welcome-email.php 1425 2004-11-29 22:33:04Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Account.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();
$Account = new Account($settings);

$member_id = (int) $_GET['id'];

// Get the member information from the database
$select = array('id', 'email', 'first_name', 'folder_name', 'num_pages',
                'login_name');
$where = 'WHERE id="'.$member_id.'"';
$result = DB::select('cp_members', $select, $where);
$member = DB::fetchAssoc($result);

// Send the member a email with a welcome and instructions
$Account->sendWelcomeEmail($member);

// Welcome email re-sent
$output .= Event::logEvent(86, $member_id, $page_type, '');

DB::close();

$uri = $settings['admin_uri'].'/members/cp/edit-member-account.php?id='.$member_id.'&event=86';
header('Location: '.$uri);
?>
