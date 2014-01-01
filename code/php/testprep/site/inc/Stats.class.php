<?php
/**
 * Performs Statistical related functions
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: Stats.class.php 1102 2004-10-15 04:07:22Z elijah $
 * @package NiftyCMS
 */
class Stats {

    function CalcTimeDiff($old_time, $new_time)
    {
        // calculate elapsed time (in seconds!)

        $diff = $new_time-$old_time;
        $days_diff = floor($diff/60/60/24);
        $diff -= $days_diff*60*60*24;
        $hrs_diff = floor($diff/60/60);
        $diff -= $hrs_diff*60*60;
        $mins_diff = floor($diff/60);
        $diff -= $mins_diff*60;
        $secs_diff = $diff;

		$output = '';
		if ($days_diff) { $output .= $days_diff.'days '; }
		if ($hrs_diff) { $output .= $hrs_diff.'hrs '; }
		if ($mins_diff) { $output .= $mins_diff.'mins '; }
		$output .= $secs_diff.'secs ';
        return $output;
    }

    /**
     * Outputs next and previous links for lists of stats
     *
     */
    function showPrevNextLinks($pageUri, $numRows) {
        if ((FALSE == isset($_GET['offset']) || 0 == $_GET['offset']) && $numRows > 100) {
            $output = '<a href="'.$pageUri.'offset=100">&lt;&lt; Previous</a>';
        } else {
            if (FALSE != isset($_GET['offset'])) {
                $prev_offset = $_GET['offset'] + 100;
                //echo $numRows.'<br />';
                //echo $prev_offset;
                if ($numRows > $prev_offset) {
                    $output  = '<div class="back">';
                    $output .= '<a href="'.$pageUri.'offset='.$prev_offset.'">&lt;&lt; Previous</a>';
                    $output .= '</div>';
                } else {
                    $output = '';
                }

                $next_events = $_GET['offset'] - 100;
                $output .= '<div class="forward">';
                $output .= '<a href="'.$pageUri.'offset='.$next_events.'">Next &gt;&gt;</a>';
                $output .= '</div>';
            } else {
                $output = '';
            }
        }
        return $output;
    }
}
?>
