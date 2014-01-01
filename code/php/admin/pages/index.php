<?php
/**
 * Allows the admin to edit the pages of the site
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1386 2004-11-29 16:17:18Z elijah $
 * @package NiftyCMS
 * @subpackage admin_pages
 */
require_once '../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output  = $Page->showHeader();
$output .= '<h2>Edit Pages</h2>'."\n";
$output .= '<p>Choose a page type:</p>'."\n";
$output .= '<ul>'."\n";

$select = array('name', 'title');
$result = DB::select('page_types', $select, '', 0);
while ($page_type = DB::fetchAssoc($result)) {
    $output .= '<li><a href="./?type='.$page_type['name'].'">'.$page_type['title'].'</a></li>'."\n";
}
$output .= '</ul>'."\n";
if (FALSE == empty($_GET['type'])) {
    /*
    switch ($_GET['type']) {
        case 'dir':
    $output .= '<h2 id="dir">Directory Pages</h2>';
    $output .= '<p><a href="add-page.php?type=dir">Add Directory Page</a></p>';
    if (FALSE == isset($_GET['id'])) {

        $query = 'SELECT id, link_title FROM dir_pages WHERE parent_id="0" LIMIT 1';
        $result = DB::query($query);
        $parent_page = DB::fetchAssoc($result);

        $query = 'SELECT id,link_title,parent_id FROM dir_pages  WHERE parent_id="'.$parent_page['id'].'" ORDER BY link_title ASC';
        $result = DB::query($query);
        $output .= '<b>Choose State</b><ul>';
        $output .= '<li><a href="'.$settings['admin_uri'].'/pages/edit-page.php?type=dir&amp;id='.
            $parent_page['id'].'">'.$parent_page['link_title'].'</a><ul>'."\n";
        while ($page = DB::fetchAssoc($result)) {
            $output .= '<li><a href="'.$settings['admin_uri'].'/pages/?type=dir&amp;id='.
            $page['id'].'">'.$page['link_title'].'</a></li>'."\n";
        }
        $output .= '</li></ul></ul>'."\n";
    } else {
        $page_id = (int) $_GET['id'];
        $query = 'SELECT id,link_title FROM dir_pages WHERE id = "'.$page_id.'" LIMIT 1';
        $result = DB::query($query);
        $page = DB::fetchAssoc($result);
        $output .= '<ul><li>'."\n";
        $output .= '<a href="'.$settings['admin_uri'].'/pages/edit-page.php?type=dir&amp;id='
        .$page_id.'">'.$page['link_title'].'</a>'."\n";
        $output .= $Tree->showPageAdminMenu('dir', $settings['admin_uri'].'/pages/edit-page.php?type=dir&amp;id=', $page_id);
        $output .= '</li></ul>'."\n";
    }

    break;
    case 'admin':
        $Tree = new Tree('admin');
    $output .= '<h2 id="admin">Admin Pages</h2>'."\n";
    $output .= '<a href="add-page.php?type=admin">Add Admin Page</a>'."\n";
    // $output .= $Tree->showPageAdminMenu($settings['admin_uri'].'/pages/edit-page.php?type=admin&amp;id=');
        $output .= '<p>Editing: '.$Tree->showBreadcrumbs($_GET['pageid']).'</p>';
$output .=         $Tree->showAdminRelativeList('./?type=admin&pageid=', 'edit-page.php?type=admin&amp;id=', $_GET['pageid']);
    break;
    case 'cp':
    $output .= '<h2 id="cp">CP Pages</h2>'."\n";
    $output .= '<a href="add-page.php?type=cp">Add Control Panel Page</a>'."\n";
    $output .= $Tree->showPageAdminMenu('cp', $settings['admin_uri'].'/pages/edit-page.php?type=cp&amp;id=');
    break;
    case 'special';
    $output .= '<h2 id="special">Special Pages</h2>'."\n";
    $output .= '<a href="add-page.php?type=special">Add Special Page</a>'."\n";
    $output .= $Tree->showPageAdminMenu('special', $settings['admin_uri'].'/pages/edit-page.php?type=special&amp;id=');
    break;
    }
    */
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
    $output .= '<p class="infobox">Click on a Page Title to see its subpages</p>';
    $output .= $Menu->showRelativeActionList(
        './?type='.$page_type.'&page_id=',
        'edit-page.php?type='.$page_type.'&amp;id=', '(Edit)');


}
$output .= $Page->showFooter();
echo $output;
?>