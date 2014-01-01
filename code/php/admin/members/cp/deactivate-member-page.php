<?php
/**
 * Deactivates a members's page in the database
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: deactivate-member-page.php 1478 2004-11-30 20:53:35Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members
 */
require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$page_id = (int) $_GET['id'];
$cp_member_id =(int) $_GET['member_id'];
$member_id = $Member->getId();

// Delete member's page
$query = 'UPDATE cp_member_pages SET active="0" WHERE id="'.$page_id.'" LIMIT 1';
DB::query($query);

// Member page activated
$output .= Event::logEvent(92, $member_id, $page_type, 'Page id:'.$page_id);

DB::close();

$uri = $settings['admin_uri'].'/members/cp/edit-member-account.php?id='.$cp_member_id.'&event=92';
// echo $member_page['member_id'];
// echo $uri;
header('Location: '.$uri);
?>