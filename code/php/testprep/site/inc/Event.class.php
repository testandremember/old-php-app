<?php
/**
 * Methods to Get event messages and log events
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: Event.class.php 1342 2004-10-31 00:08:07Z elijah $
 * @package NiftyCMS
 */
class Event {

    /**
     * Fetches a message from the database and returns it
     *
     * @param $eventId int - the id of the event to show
     * return $output string - the event's message
     */
    function showMessage ($eventId, $moreMessage = '')
    {
        // Convert the event id to an integer
        $eventId = (int) $eventId;

        // Get the event message out of the database
        $query = 'SELECT type, message FROM event_messages WHERE id="'.$eventId.'" LIMIT 1';
        $result = DB::query($query);

        $output = '<div class="infobox">';
        // If the event was not found in the database
        if (1 != DB::numRows($result)) {
            // show an error
            $output .= '<p class="error">';
            $output .= $eventId.' is not a valid event id.';
            $output .= '</p>';
        } else {
            // Output the message
            $event = DB::fetchAssoc($result);
            $output .= '<span class="'.$event['type'].'">';
            $output .= $event['message'];
            if (FALSE == empty($moreMessage)) {
                $output .= '<br />'.$moreMessage;
            }
            $output .= '</span>';
        }
        $output .= '</div>';
        return $output;
    }

    function getMessage ($eventId)
    {
        // Convert the event id to an integer
        $eventId = (int) $eventId;

        // Get the event message out of the database
        $query = 'SELECT type, message FROM event_messages WHERE id="'.$eventId.'" LIMIT 1';
        $result = DB::query($query);

        // If the event was not found in the database
        if (1 != DB::numRows($result)) {
            // show an error
            $output  = '<p class="error">';
            $output .= $eventId.' is not a valid event id.';
            $output .= '</p>';
        } else {
            // Output the message
            $event = DB::fetchAssoc($result);
            // $output  = '<div class="'.$event['type'].'">';
            $output = $event['message'];
            // $output .= '</div>';
        }
        return $output;
    }

    /**
     * Logs an event and returns it's message.
     *
     * @param $eventId int - the event's id
     * @param $memberId int - id of the member who is logged in (if available)
     * @param $moreInfo string - More information if given (such a an sql query)
     * @returns $message string - The event message
     */
    function showAndLog ($eventId, $memberId, $memberType, $moreInfo = '', $moreMessage = '')
    {
        // Log the event to the database
        Event::logEvent($eventId, $memberId, $memberType, $moreInfo);
        // Get the event message out of the database
        $message = Event::showMessage($eventId, $moreMessage);
        // Return the message
        return $message;
    }

    /**
     * Logs an event to the database with so enviroment variables
     *
     * @param $eventId  int    - the event's id
     * @param $memberId int    - the id of the logged in member (if available)
     * @param $moreInfo string - more information such as a database query
     */
    static function logEvent($eventId, $memberId, $memberType, $moreInfo='')
    {
        $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        //   $findme   = 'hawkcommunications.com';
        //  $pos = strpos($hostname, $findme);

        // If the visitor is not me, my search engine, or a validator, then log it.
        //  if ($pos === FALSE AND $hostname != 'index.atomz.com' AND $hostname != 'lovejoy.w3.org' AND $hostname != 'w3c5.inria.fr' AND $hostname != 'bobby.watchfire.com') {
        if (FALSE != isset($_SERVER["HTTP_REFERER"])) {
            $referrer = DB::makeSafe($_SERVER["HTTP_REFERER"]);
        } else {
            $referrer = '';
        }
        if (FALSE != isset($_SERVER["HTTP_USER_AGENT"])) {
            $user_agent = DB::makeSafe($_SERVER["HTTP_USER_AGENT"]);
        } else {
            $user_agent = '';
        }
        if (FALSE != isset($_COOKIE[$memberType.'_session_id'])) {
            $session_id = DB::makeSafe($_COOKIE[$memberType.'_session_id']);
        } else {
            $session_id = '';
        }
        $more_info = DB::makeSafe($moreInfo);
        $uri       = DB::makeSafe($_SERVER['REQUEST_URI']);
        $member_id = (int) $memberId;
        // Log the event into the database
        $insert = array('id' => '', 'event_id' => $eventId, 'date' => time(),
                        'uri' => $uri, 'referrer' => $referrer,
                        'ip' => $_SERVER['REMOTE_ADDR'], 'host' => $hostname,
                        'ua' => $user_agent, 'member_id' => $member_id,
                        'session_id' => $session_id, 'more_info' => $more_info);
        DB::insert($memberType.'_event_log', $insert);
    }

    /**
     * Logs a hit to the database
     *
     * @param string $pageType The type of page being logged.
     */
    static function logHit($pageType)
    {
        if (FALSE != isset($_SERVER["REMOTE_ADDR"])) {
            $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        } else {
            $hostname = '';
        }
        //   $findme   = 'hawkcommunications.com';
        //  $pos = strpos($hostname, $findme);

        // If the visitor is not me, my search engine, or a validator, then log it.
        //  if ($pos === FALSE AND $hostname != 'index.atomz.com' AND $hostname != 'lovejoy.w3.org' AND $hostname != 'w3c5.inria.fr' AND $hostname != 'bobby.watchfire.com') {
        if (FALSE != isset($_SERVER["HTTP_REFERER"])) {
            $referrer = DB::makeSafe($_SERVER["HTTP_REFERER"]);
        } else {
            $referrer = '';
        }
        if (FALSE != isset($_SERVER["HTTP_USER_AGENT"])) {
            $user_agent = DB::makeSafe($_SERVER["HTTP_USER_AGENT"]);
        } else {
            $user_agent = '';
        }
        if (FALSE != isset($_COOKIE[$pageType.'_session_id'])) {
            $session_id = DB::makeSafe($_COOKIE[$pageType.'_session_id']);
        } else {
            $session_id = '';
        }
        if (FALSE != isset($_SERVER['REQUEST_URI'])) {
            $uri = DB::makeSafe($_SERVER['REQUEST_URI']);
        } else {
            $uri = '';
        }
        if (FALSE != isset($_SERVER["REMOTE_ADDR"])) {
            $ip = DB::makeSafe($_SERVER["REMOTE_ADDR"]);
        } else {
            $ip = '';
        }
        // Log the hit into the database
        $query = 'INSERT INTO '.$pageType.'_hit_log (id, date, uri, referrer, ip, host, ua, session_id) VALUES ("", "'.time().'", "'.$uri.'", "'.$referrer.'", "'.$ip.'", "'.$hostname.'", "'.$user_agent.'", "'.$session_id.'")';
        DB::query($query);
    }
}
?>