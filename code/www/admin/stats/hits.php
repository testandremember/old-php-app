<?php
/**
 * Lets the admin view site stats
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: hits.php 1479 2004-11-30 21:08:14Z elijah $
 * @package NiftyCMS
 * @subpackage admin_stats
*/
require_once '../../testprep/site/inc/Page.class.php';
require_once '../../testprep/site/inc/Stats.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$output .= '<p>Choose a page type:</p>'."\n";
$output .= '<ul>'."\n";

$select = array('name', 'title');
$result = DB::select('page_types', $select, '', 0);
while ($page_type = DB::fetchAssoc($result)) {
    $output .= '<li><a href="hits.php?type='.$page_type['name'].'">'.$page_type['title'].'</a></li>'."\n";
}
$output .= '</ul>'."\n";

if (FALSE == empty($_GET['type'])) {
    $page_type = DB::makeSafe($_GET['type']);
    $query = 'SELECT id FROM '.$page_type.'_hit_log';
    $result = DB::query($query);
    $num_rows = DB::numRows($result);
    $prev_next = Stats::showPrevNextLinks($settings['admin_uri'].'/stats/hits.php?type='.$page_type.'&', $num_rows);
    $output .= $prev_next;
if (FALSE == isset($_GET['offset']) || 0 == $_GET['offset']) {
    $offset = $num_rows - 100;
} else {
    $get_offset = (int) $_GET['offset'];
    // echo $num_rows.'<br />';
    $offset = ($num_rows - 100) - $get_offset;
    // echo $offset.'<br />';
    if (0 > $offset) {
        $offset = 0;
    }
}

    if ($num_rows > 100) {
        // $num_rows = $num_rows - 100;
        // $query="SELECT * FROM cp_event_log LIMIT $num_rows,100";
        $query = 'SELECT id, date, uri, ip, host, session_id FROM '.$page_type.'_hit_log ORDER BY id ASC LIMIT '.$offset.', 100';
        $result = DB::query($query);
    } else {
        $query = 'SELECT id, date, uri, ip, host, session_id FROM '.$page_type.'_hit_log ORDER BY id ASC';
        $result = DB::query($query);
    }
    // $output .= '<ul>';
    $output .= '<table class="simple"><thead><tr>';
    $output .= '<th>Id</th>';
    $output .= '<th>Date</th>';
    // $output .= '<td>Event</td>';
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
            $output .= '<td><a href="hit-info.php?type='.$page_type.'&id='.$event['id'].'">'.$event['id'].'</a></td>';
            $output .= '<td>'.$date.'</td>';
            // $output .= '<td>'.$event_info[$event['event_id']].'</td>';
            $uri = wordwrap($event['uri'], 40, '<br />', 1);
            $output .= '<td><a href="'.$settings['site_uri'].$event['uri'].'">'.$uri.'</a></td>';
            // $event['referer'] = str_replace($settings['site_uri'], '', $event['referer']);
            // $output .= '<td>'.$event['referer'].'</td>';
            // $output .= '<td>'.$event['ua'].'</td>';
            $output .= '<td>'.$event['host'].'</td>';
            $output .= '<td>'.$event['ip'].'</td></tr>';
        } else {
            $time_after = Stats::CalcTimeDiff($lastdate, $event['date']);
            $output .= '<tr>';
            $output .= '<td><a href="hit-info.php?type='.$page_type.'&id='.$event['id'].'">'.$event['id'].'</a></td>';
            $output .= '<td>'.$time_after.' later</td>';
            // $output .= '<td>'.$event_info[$event['event_id']].'</td>';
            $uri = wordwrap($event['uri'], 40, '<br />', 1);
            $output .= '<td><a href="'.$settings['site_uri'].$event['uri'].'">'.$uri.'</a></td>';
            // $event['referer'] = str_replace($settings['site_uri'], '', $event['referer']);
            // $output .= '<td>'.$event['referer'].'</td>';
            // $output .= '<td>'.$event['ua'].'</td>';
            // $output .= '<td>'.$event['host'].'</td>';
            // $output .= '<td>'.$event['ip'].'</td></tr>';
            $output .= '<td style="text-align:center">"</td>';
            $output .= '<td style="text-align:center">"</td></tr>';

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

        // $output .= '</li>';
        $last_session_id = $event['session_id'];
        $lastip = $event['ip'];
        $lastdate = $event['date'];
    }
    $output .= '</table>';
    $output .= $prev_next;
    // $output .= Stats::showPrevNextLinks($settings['admin_uri'].'/stats/hits.php?type='.$page_type.'&', $num_rows);
}
$output .=  $Page->showFooter();

echo $output;
?>