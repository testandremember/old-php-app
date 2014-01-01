<?php
/**
 * Lets the admin view site stats
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: events.php 1482 2004-11-30 21:40:50Z elijah $
 * @package NiftyCMS
 * @subpackage admin_stats
*/
require_once '../../testprep/site/inc/Page.class.php';
require_once '../../testprep/site/inc/Stats.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();
$output .= '<p>Choose a member type:</p>'."\n";
$output .= '<ul>'."\n";

$select = array('name', 'title');
$result = DB::select('member_types', $select, '', 0);
while ($member_type = DB::fetchAssoc($result)) {
    $output .= '<li><a href="?type='.$member_type['name'].'">'.$member_type['title'].'</a></li>'."\n";
}
$output .= '</ul>'."\n";

if (FALSE == empty($_GET['type'])) {
    $member_type = DB::makeSafe($_GET['type']);
    $output .= '<h2>\''.$member_type.'\' Member Events</h2>';
    // Get event data
    $query = 'SELECT id, type, title, message FROM event_messages';
    $result = DB::query($query);
    while ($the_event = DB::fetchAssoc($result)) {
        $event_info[$the_event['id']] = $the_event;
    }
    /*
    $query = 'SELECT id, first_name, last_name FROM cp_members';
    $result = DB::query($query);
    while ($member = DB::fetchAssoc($result)) {
    $cp_member_array[$member['id']] = $member;
    }

    $query = 'SELECT id, first_name, last_name FROM admin_members';
    $result = DB::query($query);
    while ($member = DB::fetchAssoc($result)) {
    $admin_member_array[$member['id']] = $member;
    }
    */
    $query = 'SELECT id FROM '.$member_type.'_event_log';
    $result = DB::query($query);
    $num_rows = DB::numRows($result);
    $prev_next = Stats::showPrevNextLinks($settings['admin_uri'].'/stats/events.php?', $num_rows);
    $output .= $prev_next;
    if (FALSE == isset($_GET['offset']) || 0 == $_GET['offset']) {
        $offset = $num_rows - 100;
        if (0 > $offset) {
            $offset = 0;
        }
    } else {
        $get_offset = (int) $_GET['offset'];
        $offset = ($num_rows - 100) - $get_offset;
        if (0 > $offset) {
            $offset = 0;
        }
    }
    // $query="SELECT * FROM cp_event_log LIMIT $num_rows,100";
    // $query = 'SELECT id, event_id, date, uri, ip, host, member_id, session_id FROM event_log ORDER BY id ASC LIMIT '.$offset.', 100';
    // $result = DB::query($query);
    $select = array('id', 'event_id', 'date', 'uri', 'ip', 'host', 'member_id',
                    'session_id');
    $options = 'ORDER BY id ASC LIMIT '.$offset.', 100';
    $result = DB::select($member_type.'_event_log', $select, $options, 0);
    // $output .= '<ul>';
    $output .= '<table class="simple"><thead><tr>';
    $output .= '<th>Id</th>';
    $output .= '<th>Date</th>';
    $output .= '<th>Member</th>';
    $output .= '<th>Event</th>';
    $output .= '<th>URI</th>';
    // $output .= '<td>Referrer</td>';
    // $output .= '<td>User Agent</td>';
    $output .= '<th>Host</th>';
    $output .= '<th>IP Address</th>';
    $output .= '</tr></thead><tbody>';
    $last_session_id='';
    $lastip='';
    $lastdate='';
    while ($event = DB::fetchAssoc($result))
    {
        $date = date ("F j, Y g:i:s A", $event['date']);
        if ($event['ip'] != $lastip OR $event['session_id'] != $last_session_id) {
            $output .= '<tr>';
            $output .= '<td><a href="event-info.php?type='.$member_type.'&amp;id='.$event['id'].'">'.$event['id'].'</a></td>';
            $output .= '<td>'.$date.'</td>';
            if (FALSE == empty($event['member_id'])) {
                $member_name = $Member->getName($event['member_id'], $member_type);
                $output .= '<td>'.$member_name.'</td>';
            } else {
                $output .= '<td>Guest</td>';
            }
            $output .= '<td class="'.$event_info[$event['event_id']]['type'].'">'.$event_info[$event['event_id']]['title'].'</td>';
            $uri = wordwrap($event['uri'], 40, '<br />', 1);
            $output .= '<td><a href="'.$settings['site_uri'].$event['uri'].'">'.$uri.'</a></td>';
            // $event['referer'] = str_replace($settings['site_uri'], '', $event['referer']);
            // $output .= '<td>'.$event['referer'].'</td>';
            // $output .= '<td>'.$event['ua'].'</td>';
            $output .= '<td>'.$event['host'].'</td>';
            $output .= '<td>'.$event['ip'].'</td></tr>';

        } else {
            $time_after = Stats::CalcTimeDiff($lastdate,$event['date']);
            $output .= '<tr>';
            $output .= '<td><a href="event-info.php?type='.$member_type.'&amp;id='.$event['id'].'">'.$event['id'].'</a></td>';
            $output .= '<td>'.$time_after.' later</td>';
            if (FALSE == empty($event['member_id'])) {
                $member_name = $Member->getName($event['member_id'], $member_type);
                $output .= '<td>'.$member_name.'</td>';
            } else {
                $output .= '<td>Guest</td>';
            }
            $output .= '<td class="'.$event_info[$event['event_id']]['type'].'">'.$event_info[$event['event_id']]['title'].'</td>';
            $uri = wordwrap($event['uri'], 40, '<br />', 1);
            $output .= '<td><a href="'.$settings['site_uri'].$event['uri'].'">'.$uri.'</a></td>';
            // $event['referer'] = str_replace($settings['site_uri'], '', $event['referer']);
            // $output .= '<td>'.$event['referer'].'</td>';
            // $output .= '<td>'.$event['ua'].'</td>';
            // $output .= '<td>'.$event['host'].'</td>';
            // $output .= '<td>'.$event['ip'].'</td></tr>';
            $output .= '<td>"</td>';
            $output .= '<td>"</td></tr>';

            /*
            $output .= '<div style="margin-left:12px;">Then '.$time_after;
            $event['referer'] = str_replace($settings['site_uri'], '', $event['referer']);
            $output .= ' later. Came from: '.$event['referer'];
            $output .= ' And visited: <a  href="'.$settings['site_uri'].$event['uri'].'">'.$event['uri'].'</a>';
            $output .= '<br />And did: ';
            $output .= $event[$event['event_id']];
            $output .= '<br /></div>';
            */
        }

        $output .= '</li>';
        $last_session_id = $event['session_id'];
        $lastip = $event['ip'];
        $lastdate = $event['date'];
    }
    $output .= '</tbody></table>';
    $output .= $prev_next;
}
$output .=  $Page->showFooter();
echo $output;
?>