<?php
/**
 * Lets the admin edit and add page types
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1103 2004-10-15 04:22:54Z elijah $
 * @package NiftyCMS
 * @subpackage admin_pages_types
*/
require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$output .= '<h2>Current Page Types:</h2>';
$output .= '<ul>';
$select = array('id', 'name', 'title', 'description');
$result = DB::select('page_types', $select, '', 0);
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