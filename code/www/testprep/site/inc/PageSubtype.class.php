<?php
/**
 * Performs actions for page subtypes.
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: PageSubtype.class.php 1638 2004-12-25 04:19:33Z elijah $
 * @package NiftyCMS
 */

class PageSubtype {
    // The id of the page being viewed
     var $mPageId = 0;
    // An array of settings
    // var $mSettings = array();

    /**
     * Initializes the class
     *
     */
    function PageSubtype($pageId, $pageType)
    {
        $this->mType = $pageType;
        $this->mPageId = $pageId;
        // $this->mSettings = $settings;
    }

    /**
     * Shows a list of all the contractors jobs in a county
     *
     */
    function showChildTable()
    {
       // $output = '<p style="text-align:center">List of contractor jobs</p>';
        $query = 'SELECT id, uri, link_title, parent_id FROM '.$this->mType.'_pages
                  WHERE parent_id="'.$this->mPageId.'" ORDER BY link_title ASC';
        $result = DB::query($query);

        // Get the info we need to show how many contractors each job has
        $query2 = 'SELECT parent_id FROM '.$this->mType.'_pages';
        $result2 = DB::query($query2);

        while ($pages = DB::fetchAssoc($result2)) {
            // print_r($pages);
            if (FALSE != isset($num_children[$pages['parent_id']])) {
                $num_children[$pages['parent_id']]++;
            } else {
                $num_children[$pages['parent_id']] = 1;
            }
        }
        // print_r($num_children);
        $output = '<table cellpadding="10" cellspacing="0">';
        $row_ended = FALSE;
        for ($i = 1; $page = DB::fetchAssoc($result); $i++) {
            if (1 == $i) {
                $output .= '<tr>';
            }
            $output .= '<td>';

            if (FALSE != isset($num_children[$page['id']])) {
                // show how many children the page has
                // $output .= '<a href="'.$this->mSettings['site_uri'].$page['uri'].'"><b>';
                $output .= '<a href="'.$page['uri'].'"><b>';
                $output .= $page['link_title'];
                $output .= ' </b>('.$num_children[$page['id']].')';
                $output .= '</a>';
            } else {
                $output .= $page['link_title'];
                $output .= ' (0)';
            }

            if (2 == $i) {
                $output .= '</td></tr>'."\n";
                $i = 0;
                $row_ended = TRUE;
            } else {
                $row_ended = FALSE;
                $output .= '</td>'."\n";
            }
        }
        if (FALSE == $row_ended) {
            $output .= '</tr>';
        }
        $output .= '</table>';
        return $output;
    }

    /**
     * Shows a list of the five newest contractors
     *
     */
    function showNewList($subtype)
    {
        /*
        $output = '<p class="title">New Contractors</p>'."\n";
        */
        $output = '<ol>'."\n";

        /*
        $query = 'SELECT folder_name, title FROM dir_contractors, cp_members
                  WHERE dir_contractors.member_id=cp_members.id
                  ORDER BY dir_contractors.id DESC LIMIT 5';
        $result = DB::query($query);
        */
        $select = array('uri', 'link_title');
        $options  = 'WHERE page_subtypes.name="'.$subtype.'" ';
        $options .= 'AND '.$this->mType.'_pages.subtype=page_subtypes.id ';
        $options .= 'ORDER BY dir_pages.id DESC';
        $result = DB::select($this->mType.'_pages, page_subtypes', $select,
                             $options, 5);
        while ($page = DB::fetchAssoc($result)) {
            // $output .= '<li><a href="http://'.$listing['folder_name'].'.'.$this->mSettings['domain'].'/'.'" rel="external">';

            $output .= '<li><a href="'.$page['uri'].'">';
            $output .= $page['link_title'].'</a></li>'."\n";
        }
        $output .= '</ol>';
        return $output;
    }
}
?>