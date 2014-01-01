<?php
/**
 * Lets the admin edit and add member types
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1181 2004-10-25 00:58:46Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members_types
*/
require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$output .= '<h2>Current Member Types:</h2>';
$output .= '<ul>';
$select = array('id', 'name', 'title', 'description');
$result = DB::select('member_types', $select, '', 0);
while ($type = DB::fetchAssoc($result))
{
    $output .= '<li><a href="edit-type.php?id='.$type['id'].'">';
    $output .= ''.$type['id'].': '.$type['title'].' ('.$type['name'].')</a><br />';
    $output .= $type['description'].'</li>';
}
$output .= '</ul>';
$output .= $Page->showFooter();
echo $output;
?>