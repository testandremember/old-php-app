<?php
/**
 * Lets the admin view site stats
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: hit-info.php 1415 2004-11-29 20:50:10Z elijah $
 * @package NiftyCMS
 * @subpackage admin_stats
*/
require_once '../../testprep/site/inc/Page.class.php';
require_once '../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$page_hit_type = DB::makeSafe($_GET['type']);
$hit_id = (int) $_GET['id'];
// $query="SELECT * FROM cp_hit_log LIMIT $num_rows,100";
// Get hit data
$select = array('id', 'date', 'uri', 'ip', 'host', 'referrer', 'ua',
                'session_id');
$where = 'WHERE id="'.$hit_id.'"';
$result = DB::select($page_hit_type.'_hit_log', $select, $where);

$hit = DB::fetchAssoc($result);

if (FALSE == empty($hit['session_id'])) {
    $select = array('member_id');
    $where = 'WHERE session_id="'.$hit['session_id'].'"';
    $result = DB::select($page_hit_type.'_member_sessions', $select, $where);

    $member_session = DB::fetchAssoc($result);
} else {
    $member_session['member_id'] = 0;
}

/*
$query2 = 'SELECT id, first_name, last_name, folder_name FROM cp_members WHERE id = "'.$member_session['member_id'].'" LIMIT 1';
$result2 = DB::query($query2);
$member = DB::fetchAssoc($result2);

$output .= '<table class="simple"><thead>';


$output .= '<tr><td><b>Id:</b></td><td>'.$hit['id'].'</td></tr>';
$output .= '<tr><td><b>Date:</b></td><td>'.$date.'</td></tr>';
*/
if (0 != $member_session['member_id']) {
    $select = array('has_website');
    $result = DB::select('member_types', $select, 'WHERE name="'.$page_hit_type.'"');
    $member_type_options = DB::fetchAssoc($result);

    $select = array('id', 'first_name', 'last_name');
    if (1 == $member_type_options['has_website']) {
        $select[] = 'folder_name';
    }
    $where = 'WHERE id="'.$member_session['member_id'].'"';
    $result = DB::select($page_hit_type.'_members', $select, $where);
    $member = DB::fetchAssoc($result);
/*
    $output .= '<tr><td><b>Member Name:</b></td><td>'.$member['first_name'].' '.$member['last_name'].'</td></tr>';
    $output .= '<tr><td><b>Member\'s folder:</b></td>';
    $output .= '<td><a href="'.$settings['site_uri'].'/'.$member['folder_name'].'/">';
    $output .= '/'.$member['folder_name'].'/</a></td></tr>';
    */
}
/*
$output .= '<tr><td><b>URI:</b></td><td><a href="'.$settings['site_uri'].$hit['uri'].'">'.$hit['uri'].'</a></td></tr>';
$output .= '<tr><td><b>Referrer:</b></td><td>'.$hit['referrer'].'</td></tr>';
$output .= '<tr><td><b>User Agent:</b></td><td>'.$hit['ua'].'</td></tr>';
$output .= '<tr><td><b>Hostname:</b></td><td>'.$hit['host'].'</td></tr>';
$output .= '<tr><td><b>IP Address:</b></td><td>'.$hit['ip'].'</td></tr>';
$output .= '</tbody></table>';
*/
$uri = '<a href="'.$settings['site_uri'].$hit['uri'].'">'.$hit['uri'].'</a>';
$date = date ("F j, Y g:i:s A", $hit['date']);

$event_info = array(
    array('label' => 'Id', 'value' => $hit['id']),
    array('label' => 'Date', 'value' => $date),
    array('label' => 'URI', 'value' => $uri),
    array('label' => 'Referrer', 'value' => $hit['referrer']),
    array('label' => 'User Agent', 'value' => $hit['ua']),
    array('label' => 'Hostname', 'value' => $hit['host']),
    array('label' => 'IP Address', 'value' => $hit['ip']),
    array('label' => 'Session Id', 'value' => $hit['session_id']));
if (0 != $member_session['member_id']) {
    $member_name = $member['first_name'].' '.$member['last_name'];
    $event_info[] = array('label' => 'Member Name', 'value' => $member_name);
}
if (FALSE == empty($member_type_options['has_website'])) {
    $folder_name  = '<a href="'.$settings['site_uri'].'/'.$member['folder_name'].'/">';
    $folder_name .= '/'.$member['folder_name'].'/</a>';
    $event_info[] = array('label' => 'Folder Name', 'value' => $folder_name);
}
$output .= Form::showList($event_info);



$output .=  $Page->showFooter();
echo $output;
?>