<?php
/**
 * Lets the admin view site stats
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: event-info.php 1385 2004-11-29 16:16:49Z elijah $
 * @package NiftyCMS
 * @subpackage admin_stats
*/
require_once '../../testprep/site/inc/Page.class.php';
require_once '../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$member_type = DB::makeSafe($_GET['type']);

$event_id = (int) $_GET['id'];

$select = array('id', 'event_id', 'date', 'uri', 'ip', 'host', 'referrer', 'ua',
                'member_id', 'session_id', 'more_info');
$where = 'WHERE id="'.$event_id.'"';
$result = DB::select($member_type.'_event_log', $select, $where);

$event  = DB::fetchAssoc($result);

// Get event data
$query2 = 'SELECT id, type, title, message FROM event_messages WHERE id = "'.$event['event_id'].'" LIMIT 1';
$result2 = DB::query($query2);
$event_info = DB::fetchAssoc($result2);


$select = array('has_website', 'can_login');
$result = DB::select('member_types', $select, 'WHERE name="'.$member_type.'"');
$member_type_options = DB::fetchAssoc($result);

$event_title  = '<span class="'.$event_info['type'].'">';
$event_title .= $event_info['title'];
$event_title .= '</span>';

$event_message  = '<span class="'.$event_info['type'].'">';
$event_message .= $event_info['message'];
$event_message .= '</span>';

$uri = '<a href="'.$settings['site_uri'].$event['uri'].'">'.$event['uri'].'</a>';
$date = date ("F j, Y g:i:s A", $event['date']);

$event_info = array(
    array('label' => 'Id', 'value' => $event['id']),
    array('label' => 'Date', 'value' => $date),
    array('label' => 'Event Title', 'value' => $event_title),
    array('label' => 'Event Message', 'value' => $event_message),
    array('label' => 'URI', 'value' => $uri),
    array('label' => 'Referrer', 'value' => $event['referrer']),
    array('label' => 'User Agent', 'value' => $event['ua']),
    array('label' => 'Hostname', 'value' => $event['host']),
    array('label' => 'IP Address', 'value' => $event['ip']),
    array('label' => 'Member Id', 'value' => $event['member_id']),
    array('label' => 'Session Id', 'value' => $event['session_id']),
    array('label' => 'More Info', 'value' => $event['more_info']));
$event_member_id = $event['member_id'];
if (0 == $event_member_id  && 1 == $member_type_options['can_login']) {
    $select = array('member_id');
    $where = 'WHERE session_id="'.$event['session_id'].'"';
    $result = DB::select($member_type.'_member_sessions', $select, $where);

    $member_session = DB::fetchAssoc($result);
    $event_member_id = $member_session['member_id'];
}
if (0 != $event_member_id) {
    $select = array('id', 'first_name', 'last_name');
    if (1 == $member_type_options['has_website']) {
        $select[] = 'folder_name';
    }
    $where = 'WHERE id="'.$event_member_id.'"';
    $result = DB::select($member_type.'_members', $select, $where);
    $member = DB::fetchAssoc($result);

    $member_name = $member['first_name'].' '.$member['last_name'];
    $event_info[] = array('label' => 'Member Name', 'value' => $member_name);
    if (1 == $member_type_options['has_website']) {
        $folder_name  = '<a href="'.$settings['site_uri'].'/'.$member['folder_name'].'/">';
        $folder_name .= '/'.$member['folder_name'].'/</a>';
        $event_info[] = array('label' => 'Folder Name', 'value' => $folder_name);
    }
}
$output .= Form::showList($event_info);


$output .=  $Page->showFooter();
echo $output;
?>