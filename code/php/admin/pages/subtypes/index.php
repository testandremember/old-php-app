<?php
/**
 * Lets the admin edit and add page subtypes
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1103 2004-10-15 04:22:54Z elijah $
 * @package NiftyCMS
 * @subpackage admin_pages_subtypes
*/
require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$output .= '<h2>Current Page Subtypes:</h2>';
$output .= '<ul>';
$select = array('id', 'subtype_action', 'title', 'description');
$result = DB::select('page_subtypes', $select, '', 0);
while ($type = DB::fetchAssoc($result))
{
    $output .= '<li><a href="edit-subtype.php?id='.$type['id'].'">';
    $output .= ''.$type['id'].': '.$type['title'];
    $output .= ' (Action: '.$type['subtype_action'].')</a><br />';
    $output .= $type['description'].'</li>';
}
$output .= '</ul>';
$output .= $Page->showFooter();
echo $output;
?>