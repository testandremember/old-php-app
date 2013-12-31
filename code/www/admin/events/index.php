<?php
/**
 * The Events index. Add, Edit, and delete events.
 *
 * @version $Id: index.php 1240 2004-10-27 02:22:42Z elijah $
 * @author Elijah Lofgren <elijah@truthland.com>
 * @package NiftyCMS
 * @subpackage admin_events
 */
require_once '../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

if (FALSE != isset($_GET['event'])) {
    $output .= Event::showMessage($_GET['event']);
}

$query  = 'SELECT id, title, message, type FROM event_messages';
$result = DB::query($query);
$output .= '<ul>';
while ($event = DB::fetchAssoc($result)) {
    $output .= '<li>';
    $output .= '<a href="edit-event.php?id='.$event['id'].'">';
    $output .= $event['id'].' - '.$event['title'].'</a> - ';
    $output .= '<span class="'.$event['type'].'">'.$event['message'].'</span>';
    $output .= '</li>';
}
$output .=  '</ul>';
$output .= $Page->showFooter();
echo $output;
?>
