<?php
/**
 * Deletes a members's page from the database
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: delete-member-page.php 1411 2004-11-29 20:07:36Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members
 */
require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

// Delete member's page
$query = 'DELETE FROM cp_member_pages WHERE id='.$_GET['id'].' LIMIT 1';
DB::query($query);

// Get a list of all the pictures on the member's page
$query2 = 'SELECT id, file_name, thumbnail_name FROM cp_member_pictures WHERE member_id="'.$_GET['member_id'].'" AND page_number="'.$_GET['page_number'].'"';
$result2 = DB::query($query2);

while ($member_picture = DB::fetchAssoc($result2)) {
    $query3 = 'DELETE FROM cp_member_pictures WHERE id="'.$member_picture['id'].'" LIMIT 1';
    DB::query($query3);
    $CpMember = new Member('cp', $settings, $_GET['member_id']);
    $CpMember->deleteImage($member_picture['file_name']);
    $CpMember->deleteImage($member_picture['thumbnail_name']);

}

DB::close();

$uri = $settings['admin_uri'].'/members/cp/edit-member-account.php?id='.$_GET['member_id'];
// echo $member_page['member_id'];
// echo $uri;
header('Location: '.$uri);
?>