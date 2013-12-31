<?php
/**
 * Lets the admin view site stats
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1501 2004-12-01 05:20:53Z elijah $
 * @package NiftyCMS
 * @subpackage admin_stats
 */
require_once '../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$select = array('name', 'can_login');
$result = DB::select('member_types', $select, '', 0);
while ($type = DB::fetchAssoc($result))
{
    if (1 == $type['can_login']) {
        $output .= show_active_members($settings['admin_uri'], $type['name']);
    }
}

echo $output;


/**
 * Shows a list of members active in the last hour
 *
 */
function show_active_members($adminUri, $memberType) {
    $Member = new Member($memberType, array(), 0);
    $one_hour_ago = mktime(date("H") - 1, date("i"), date("s"), date("m"), date("d"), date("Y"));
    $output = '<p><b>\''.$memberType.'\' Members active in the last hour:</b></p>';

  //  $query = 'SELECT id, first_name, last_name FROM '.$memberType.'_members';
  //  $result = DB::query($query);
 //   while ($member = DB::fetchAssoc($result)) {
 //       $member_array[$member['id']] = $member;
//    }
    $query4 = 'SELECT id, title FROM event_messages';
    $result4 = DB::query($query4);
    while ($the_event = DB::fetchAssoc($result4)) {
        $event_array[$the_event['id']] = $the_event;
    }
    $select2 = array('member_id', 'last_action', 'session_id');
    $where = 'WHERE last_action >= "'.$one_hour_ago.'"';
    $result2 = DB::select($memberType.'_member_sessions', $select2, $where, 0);

    // $query2 = 'SELECT member_id, user_agent, last_action, session_id FROM '.$memberType.'_member_sessions WHERE last_action >= "'.$one_hour_ago.'"';
    // $result2 = DB::query($query2);

    $output .= '<table class="simple"><thead><tr>';
    $output .= '<th>Member Name</th>';
    // $output .= '<th>Member\'s Folder Name</th>';
    $output .= '<th>Last Active</th>';
    $output .= '<th>Last Action</th>';
    $output .= '<th>Session Id</th>';
    $output .= '</tr></thead><tbody>';
    while ($member_session = DB::fetchAssoc($result2)) {
        $output .= '<tr>';
        $output .= '<td>';
        $output .= '<a href="'.$adminUri.'/members/cp/view-info.php?id='.$member_session['member_id'].'">';
        $output .= $Member->getName($member_session['member_id'], $memberType);
        // $output .= $member_array[$member_session['member_id']]['first_name'].' ';
        // $output .= $member_array[$member_session['member_id']]['last_name'];
        $output .= '</a></td>';
        // $output .= '<td>';
        // $output .= '/'.$member_array[$member_session['member_id']]['folder_name'].'/';
        // $output .= '</td>';
        $last_action = date ("F j, Y g:i:s A", $member_session['last_action']);
        $output .= '<td>'.$last_action.'</td>';
        $query3 = 'SELECT id, event_id FROM '.$memberType.'_event_log WHERE member_id ="'.$member_session['member_id'].'" ORDER BY id DESC LIMIT 1';
        $result3 = DB::query($query3);
        $event = DB::fetchAssoc($result3);
        // $member_array[$member['id']] = $member;
        // $output .= '<td>'.$event_array[$event['event_id']]['title'].'</td>';
        $uri = $adminUri.'/stats/event-info.php?type='.$memberType.'&amp;id='.$event['id'];
        $output .= '<td><a href="'.$uri.'">';
        $output .= $event_array[$event['event_id']]['title'].'</td>';
        $output .= '<td>';
        $output .= $member_session['session_id'];
        $output .= '</td>';
        $output .= '</tr>';
    }
    $output .= '</tbody></table>';

    return $output;
}
?>