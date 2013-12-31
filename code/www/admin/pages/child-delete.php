<?php
/**
 * Allows the admin to quickly delete all the children of a page.
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: child-delete.php 1357 2004-11-01 03:14:05Z elijah $
 * @package NiftyCMS
 * @subpackage admin_pages
 */
require_once '../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();
$output .= '<h2>Delete Child Pages</h2>'."\n";
$output .= '<p>Choose a page type:</p>'."\n";
$output .= '<ul>'."\n";

if (FALSE != isset($_GET['event'])) {
    $output .= Event::showMessage($_GET['event']);
}

$select = array('name', 'title');
$result = DB::select('page_types', $select, '', 0);
while ($page_type = DB::fetchAssoc($result)) {
    $output .= '<li><a href="?type='.$page_type['name'].'">'.$page_type['title'].'</a></li>'."\n";
}
$output .= '</ul>'."\n";
if (FALSE != isset($_GET['type'])) {

//    $Tree = new Tree($settings);

$output .= '<p style="color:red; font-size:1.5em">WARNING! Pages are deleted without confirmation. Be careful!</p>';
    $page_type = DB::makeSafe($_GET['type']);
    if (FALSE != isset($_GET['page_id'])) {
        $page_id = (int) $_GET['page_id'];
    } else {
        $page_id = 0;
    }
    $Menu = new Menu($page_type, $page_id, TRUE);
    $output .= '<h2 id="'.$page_type.'">"'.$page_type.'" Pages</h2>'."\n";
    $output .= '<a href="add-page.php?type='.$page_type.'">Add "'.$page_type.'" Page</a>'."\n";
    // $output .= $Tree->showPageAdminMenu($settings[''.$page_type.'_uri'].'/pages/edit-page.php?type='.$page_type.'&amp;id=');
    // $output .= '<p>Editing: '.$Tree->showBreadcrumbs($page_id).'</p>';
    $output .= $Menu->showRelativeActionList(
        '?type='.$page_type.'&page_id=',
        'delete-children.php?type='.$page_type.'&amp;id=', '(Delete Children)');

    /*
    $output .= '<h2 id="dir">Directory Pages</h2>';
$output .= $Tree->showPageAdminMenu('dir', $settings['admin_uri'].'/pages/delete-children.php?type=dir&amp;id=');
$output .= '<h2 id="admin">Admin Pages</h2>';
$output .= $Tree->showPageAdminMenu('admin', $settings['admin_uri'].'/pages/delete-children.php?type=admin&amp;id=');
$output .= '<h2 id="cp">CP Pages</h2>';
$output .= $Tree->showPageAdminMenu('cp', $settings['admin_uri'].'/pages/delete-children.php?type=cp&amp;id=');
$output .= '<h2 id="special">Special Pages</h2>';
$output .= $Tree->showPageAdminMenu('special', $settings['admin_uri'].'/pages/delete-childrenpage.php?type=special&amp;id=');
*/
}
$output .= $Page->showFooter();
echo $output;
?>
