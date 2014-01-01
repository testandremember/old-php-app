<?php
/**
 * Methods to output hierarchical trees from the database.
 *
 * Can output menu trees that use the <ul> and <li> tag or use &nbsp; to indent
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: Menu.class.php 1939 2005-05-24 22:31:19Z elijah $
 * @package NiftyCMS
 */
class Menu {
    // Where page data will be stored. id, title, parent_id
    private $mPageData = array();
    // Where page child ids will be stored
    private $mPageChildIds = array();
    // URL of page being viewed
    private $mPageBeingViewed = '';
    // Type of page being shown
    private $mPageType = '';
    // Whether or not the data has been fetched from the Database
    private $mDataFetched = FALSE;
    // Whether or not to show page descriptions under the links
    private $mShowDescriptions = FALSE;
    private $mShowAll = FALSE;
    // Site URI
   // var $mSiteUri = '';
    // Array of site setttings
   // var $mSettings = array();
    /**
     * Initializes the class
     *
     */
    // function Tree ($settings)
    function Menu ($pageType, $currentPageId, $showAll = FALSE,
                   $showDescriptions = FALSE)
    {
        $this->mCurrentPageId = $currentPageId;
        $this->mShowAll = $showAll;
        $this->mShowDescriptions = $showDescriptions;
        $this->mPageType = $pageType;
        // $this->mSettings = $settings;
        if (FALSE != isset($_SERVER['REQUEST_URI'])) {
            // Get the uri of the page being viewed without the query string
            $this->mPageBeingViewed = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
            // Remove all # signs and everything that follows them.
            $this->mPageBeingViewed = preg_replace('/\#[a-zA-Z0-9_-]*/', '', $this->mPageBeingViewed);
        } else {
            $this->mPageBeingViewed = '';
        }
    }
    /*
function getMenuArray($pageType) {
// $query='SELECT id,label,parent_id FROM category';
// $query='SELECT id,title AS label,parent_id FROM admin_pages ORDER BY label';
        // $query='SELECT id,title AS label,parent_id FROM main_pages ORDER BY label';
        $query='SELECT id,title AS label,parent_id FROM '.$pageType.'_pages ORDER BY label';
        $result=DB::query($query);
        while ($link=DB::fetchAssoc($result)) {
            // echo 'Id: '.$link['id'].' Label: '.$link['label'].' Parent_id: '.$link['parent_id']."<br />\n";
        $this->menu_links_array[] = $link;
        }
        $output = $this->showbranch(0, 0);
        return $output;
    }

    // showbranch(0, $links_array, 0);
    function showbranch($parent_id, $level) {
        $output = '';
        foreach ($this->menu_links_array as $key => $value) {
            if ($value['parent_id'] == $parent_id) {
                $indent = '';
                for ($i=0; $i < $level; $i++) {
                    $indent .= '&nbsp;&nbsp;';
            }
                //            echo $indent.'Key: '.$key.' Id: '.$value['id'].' Label: '.$value['label']."<br />\n";
                $output .= $indent.'('.$value['id'].') '.$value['label']."<br />\n";
               $output .= $this->showbranch($value['id'], $level+1);
            }
        }
        return $output;
    }
    */
    function showChildList($parentId)
    {
        // $this->mSiteUri = $siteUri;
     //   $this->mPageData = array();
      //  $this->mPageChildIds = array();

        // $query='SELECT id,label,parent_id FROM category';
        // $query='SELECT id,title AS label,parent_id FROM admin_pages ORDER BY label';
        // $query='SELECT id,title AS label,parent_id FROM main_pages ORDER BY label';
        //        $query='SELECT id,link_title, uri FROM '.$pageType."_pages WHERE menu_parent_id='".$parentId."' ORDER BY link_title";
/*
        $query = 'SELECT id,link_title,parent_id, uri FROM '.$pageType.'_pages ORDER BY link_title';
        $result = DB::query($query);
        */
/*
        $select = array('id', 'link_title', 'parent_id', 'uri');
        $options = 'WHERE parent_id="'.$parentId.'" ORDER BY link_title';
        $result = DB::select($this->mPageType.'_pages', $select, $options, 0);
        */
        $this->fetchData();
        //    while ($page=DB::fetchAssoc($result)) {
        //        $this->mPageData[$page['id']] = $page;
        //       $this->mPageChildIds[$page['menu_parent_id']][]=$page['id'];
        //   }
        // $output = '<ul>'."\n";
    /*
        if (0 != DB::numRows($result)) {
            while ($page = DB::fetchAssoc($result)) {
                $this->mPageData[$page['id']] = $page;
                $this->mPageChildIds[$page['parent_id']][]=$page['id'];
            }

            // while ($pagelink=DB::fetchAssoc($result)) {
            //           $output .= '<li><a href="'.$link['uri'].'">'.$link['link_title']."</a></li>\n";
            ///    }
            $output = $this->makeChildListBranch($this->mPageChildIds[$parentId]);
            // $output .= '</ul>'."\n";
        } else {
            $output = '';
        }
        */
        if (FALSE != array_key_exists($parentId, $this->mPageChildIds)) {
            $output = $this->makeChildListBranch($this->mPageChildIds[$parentId]);
        } else {
            $output = '';
        }
        return $output;
    }

    function makeChildListBranch($pageIdsToshow)
    {
        // Start the unordered list
        $output = '<ul>'."\n";
        // Cycle through the page ids to show
        foreach ($pageIdsToshow as $key => $page_id) {
            // Only show a link to the page if it does not contain a query string
            if ('' == $this->mPageData[$page_id]['query_string']) {
                // Output each link as we cycle through the one's to show
                // $output .= '<li>';
                   // Output each link as we cycle through the one's to show
                if (FALSE == empty($this->mPageChildIds[$page_id])) {
                    if (FALSE == empty($this->mParentIds[$page_id])) {
                        if ($this->mParentIds[1] == $this->mParentIds[$page_id]) {
                            $output .= '<li class="folderhome">';
                        } else {
                            $output .= '<li class="folderopen">';

                        }
                    } else {
                        $output .= '<li class="folder">';
                    }
                } else {
                    if (FALSE == empty($this->mParentIds[$page_id])) {
                        $output .= '<li class="current">';
                    } else {
                        $output .= '<li class="article">';
                    }
                }
                if ($_SERVER['REQUEST_URI'] != $this->mPageData[$page_id]['uri']) {
                    $output .= '<a href="'.$this->mPageData[$page_id]['uri'].'">'.$this->mPageData[$page_id]['link_title'].'</a>';
                } else {
                    $output .= $this->mPageData[$page_id]['link_title'];
                }
                if ($this->mShowDescriptions != FALSE) {
                    $output .= '<br />';
                    $output .= $this->mPageData[$page_id]['description'];
                }
                $output .= '</li>'."\n";
            }
        }
        $output .= '</ul>'."\n";
        return $output;
    }

    /**
     * Fetches menu from the database and returns it a <ul> indented menu
     *
     */
    function showListMenu()
    {
        // $this->mSiteUri = $siteUri;
     //   $this->mPageData = array();
    //    $this->mPageChildIds = array();

        // $query='SELECT id,label,parent_id FROM category';
        // $query='SELECT id,title AS label,parent_id FROM admin_pages ORDER BY label';
        // $query='SELECT id,title AS label,parent_id FROM main_pages ORDER BY label';
        $this->fetchData();
        /*
        $select = array('id', 'link_title', 'parent_id', 'uri');
        $options = 'WHERE show_on_menu = "1" ORDER BY link_title';
        $result = DB::select($this->mPageType.'_pages', $select, $options, 0);
        while ($page = DB::fetchAssoc($result)) {
            $this->mPageData[$page['id']] = $page;
            $this->mPageChildIds[$page['parent_id']][] = $page['id'];
        }
        */
        $output = $this->makeListBranch($this->mPageChildIds[0]);
        return $output;
    }

    /**
     * Makes a branch of the list menu.
     *
     */
    function makeListBranch($pageIdsToshow)
    {
        // Start the unordered list
        $output = '<ul>'."\n";
        // Cycle through the page ids to show
        foreach ($pageIdsToshow as $key => $page_id) {

            // Output each link as we cycle through the one's to show
            // show the page's id
            // if (FALSE == isset($this->mPageData[$page_id]['menu_show']) OR $this->mPageData[$page_id]['menu_show'] == TRUE) {
            //    $output .= '<li>';
            if (FALSE == empty($this->mPageChildIds[$page_id])) {
                    if (FALSE != empty($this->mPageData[$page_id]['parent_id'])) {
                        $output .= '<li class="folderhome">';
                    } else {
                        $output .= '<li class="folderopen">';
                    }
            } else {
                if (FALSE == empty($this->mParentIds[$page_id])) {
                    $output .= '<li class="current">';
                } else {
                    $output .= '<li class="article">';
                }
            }
            //           $output .= '<li>('.$this->mPageData[$page_id]['id'].') ';
                // show the page linked if it's not the one being viewed
                //            if ($this->mPageBeingViewed == $this->mPageData[$page_id]['uri']) {
                // show unlinked page title
                //                $output .= $this->mPageData[$page_id]['link_title'];
                //            } else {
                // show link to page
                if ($this->mPageData[$page_id]['link_title'] == '') {
                    $this->mPageData[$page_id]['link_title'] = '('.$this->mPageData[$page_id]['id'].')';
                }

                if ($this->mPageBeingViewed == $this->mPageData[$page_id]['uri']) {
                    $output .= $this->mPageData[$page_id]['link_title'];
                } else {
                    // $output .= '<a href="'.$this->mSiteUri.$this->mPageData[$page_id]['uri'].'">'.$this->mPageData[$page_id]['link_title'].'</a>';
                    $output .= '<a href="'.$this->mPageData[$page_id]['uri'].'">'.$this->mPageData[$page_id]['link_title'].'</a>';
                }
                if ($this->mShowDescriptions != FALSE) {
                    $output .= '<br />';
                    $output .= $this->mPageData[$page_id]['description'];
                }
                //  }
                // If the link we are showing has children then make a branch
                //     showing it's children.
                if (array_key_exists($page_id, $this->mPageChildIds)) {
                    //   if (isset($this->mPageChildIds[$page_id])) {
                    // Start a branch showing all the children of $current_page_id
                    $output .= $this->makeListBranch($this->mPageChildIds[$page_id]);
                }
                $output .= '</li>'."\n";
           // }
        }
        $output .= '</ul>'."\n";
        return $output;
    }


    /**
     * Fetches menu from the database and returns it a <ul> indented menu
     *
     */
    function showRelativeList()
    {
        // $this->mSiteUri = $siteUri;
    //    $this->mPageData = array();
      //  $this->mPageChildIds = array();

        // $query='SELECT id,label,parent_id FROM category';
        // $query='SELECT id,title AS label,parent_id FROM admin_pages ORDER BY label';
        // $query='SELECT id,title AS label,parent_id FROM main_pages ORDER BY label';

     //   $select = array('id', 'link_title', 'parent_id', 'uri');
       // $options = 'WHERE show_on_menu=1 ORDER BY link_title';
      //  $result = DB::select($this->mPageType.'_pages', $select, $options, 0);
        $this->fetchData();
        $this->getParentIds($this->mCurrentPageId);
        /*
        while ($page = DB::fetchAssoc($result)) {
        //    echo '<pre style="color:white; text-align:left">';
         //   print_r($page);
            $this->mPageData[$page['id']] = $page;
            $this->mPageChildIds[$page['parent_id']][] = $page['id'];
        }
        */
        // print_r($this->mPageChildIds);
        $output = $this->makeRelativeListBranch($this->mPageChildIds[0]);
        return $output;
    }

    /**
     * Makes a branch of the list menu.
     *
     */
    function makeRelativeListBranch($pageIdsToshow)
    {
        // Start the unordered list
        $output = '<ul>'."\n";
        // Cycle through the page ids to show
        foreach ($pageIdsToshow as $key => $page_id) {
            // Only show a link to the page if it does not contain a query string
            if ('' == $this->mPageData[$page_id]['query_string']) {
                // Output each link as we cycle through the one's to show
                if (FALSE == empty($this->mPageChildIds[$page_id])) {
                    if (FALSE == empty($this->mParentIds[$page_id])) {
                        if ($this->mParentIds[1] == $this->mParentIds[$page_id]) {
                            $output .= '<li class="folderhome">';
                        } else {
                            $output .= '<li class="folderopen">';

                        }
                    } else {
                        $output .= '<li class="folder">';
                    }
                } else {
                    if (FALSE == empty($this->mParentIds[$page_id])) {
                        $output .= '<li class="current">';
                    } else {
                        $output .= '<li class="article">';
                    }
                }
                if ($this->mPageData[$page_id]['link_title'] == '') {
                    $this->mPageData[$page_id]['link_title'] = '('.$this->mPageData[$page_id]['id'].')';
                }

                if ($this->mPageBeingViewed == $this->mPageData[$page_id]['uri']) {
                    $output .= $this->mPageData[$page_id]['link_title'];
                } else {
                    // $output .= '<a href="'.$this->mSiteUri.$this->mPageData[$page_id]['uri'].'">'.$this->mPageData[$page_id]['link_title'].'</a>';
                    $output .= '<a href="'.$this->mPageData[$page_id]['uri'].'">'.$this->mPageData[$page_id]['link_title'].'</a>';
                }
                if (FALSE != array_key_exists($page_id, $this->mPageChildIds)
                    && FALSE != array_key_exists($page_id, $this->mParentIds)) {
                    // Start a branch showing all the children of $current_page_id
                    $output .= $this->makeRelativeListBranch($this->mPageChildIds[$page_id]);
                }
                $output .= '</li>'."\n";
            }
            }
        $output .= '</ul>'."\n";
        return $output;
    }


    /**
     * Fetches menu from the database and returns it a <ul> indented menu
     *
     */
    function showRelativeActionList($uriPrefix, $actionPrefix, $action)
    {

        // $this->mSiteUri = $siteUri;
        //    $this->mPageData = array();
        //  $this->mPageChildIds = array();

        // $query='SELECT id,label,parent_id FROM category';
        // $query='SELECT id,title AS label,parent_id FROM admin_pages ORDER BY label';
        // $query='SELECT id,title AS label,parent_id FROM main_pages ORDER BY label';

        //   $select = array('id', 'link_title', 'parent_id', 'uri');
        // $options = 'WHERE show_on_menu=1 ORDER BY link_title';
        //  $result = DB::select($this->mPageType.'_pages', $select, $options, 0);
        $this->fetchData();
        $this->getParentIds($this->mCurrentPageId);
        /*
        while ($page = DB::fetchAssoc($result)) {
        //    echo '<pre style="color:white; text-align:left">';
        //   print_r($page);
        $this->mPageData[$page['id']] = $page;
        $this->mPageChildIds[$page['parent_id']][] = $page['id'];
        }
        */
        // print_r($this->mPageChildIds);
        if (FALSE != isset($this->mPageChildIds[0])) {
            $output = $this->makeRelativeActionListBranch(
                $this->mPageChildIds[0], $uriPrefix, $actionPrefix, $action);
        } else {
            $output = '';
        }
        return $output;
    }

    /**
     * Makes a branch of the list menu.
     *
     */
    function makeRelativeActionListBranch($pageIdsToshow, $uriPrefix,
                                          $actionPrefix, $action)
    {
        // Start the unordered list
        $output = '<ul>'."\n";
        // Cycle through the page ids to show
        foreach ($pageIdsToshow as $key => $page_id) {

            // Output each link as we cycle through the one's to show
            // show the page's id
            // if (FALSE == isset($this->mPageData[$page_id]['menu_show']) OR $this->mPageData[$page_id]['menu_show'] == TRUE) {
            $output .= '<li>';
            //           $output .= '<li>('.$this->mPageData[$page_id]['id'].') ';
            // show the page linked if it's not the one being viewed
            //            if ($this->mPageBeingViewed == $this->mPageData[$page_id]['uri']) {
            // show unlinked page title
            //                $output .= $this->mPageData[$page_id]['link_title'];
            //            } else {
            // show link to page
            if ($this->mPageData[$page_id]['link_title'] == '') {
                $this->mPageData[$page_id]['link_title'] = '('.$this->mPageData[$page_id]['id'].')';
            }

            if ($this->mCurrentPageId == $this->mPageData[$page_id]['id'] || FALSE == isset($this->mNumChildren[$page_id])) {
                $output .= $this->mPageData[$page_id]['link_title'];
            } else {
                // $output .= '<a href="'.$this->mSiteUri.$this->mPageData[$page_id]['uri'].'">'.$this->mPageData[$page_id]['link_title'].'</a>';
                $output .= '<a href="'.$uriPrefix.$this->mPageData[$page_id]['id'].'">'.$this->mPageData[$page_id]['link_title'].' ('.$this->mNumChildren[$page_id].')</a>';

            }
            $output .= ' - <a href="'.$actionPrefix.$this->mPageData[$page_id]['id'].'">'.$action.'</a>';
            //  }
            // If the link we are showing has children then make a branch
            //     showing it's children.
            //   print_r($this->mPageChildIds);
            //  print_r($this->mParentIds);
            if (FALSE != array_key_exists($page_id, $this->mPageChildIds)) {
                //   if (isset($this->mPageChildIds[$page_id])) {
                if (FALSE != array_key_exists($page_id, $this->mParentIds)) {
                    // Start a branch showing all the children of $current_page_id
                    $output .= $this->makeRelativeActionListBranch(
                        $this->mPageChildIds[$page_id], $uriPrefix, $actionPrefix,
                        $action
                    );
                }
            }
            $output .= '</li>'."\n";
            // }
        }
        $output .= '</ul>'."\n";
        return $output;
    }

    /**
     * Fetches a menu and returns an array with the titles indented.
     *
     */
    function getMenuArray()
    {
        /*
        $this->mPageData = array();
        $this->mPageChildIds = array();
        // $query='SELECT id,label,parent_id FROM category';
        // $query='SELECT id,title AS label,parent_id FROM admin_pages ORDER BY label';
        // $query='SELECT id,title AS label,parent_id FROM main_pages ORDER BY label';

        $select = array('id', 'link_title', 'parent_id');
        $options = 'ORDER BY link_title';
        $result = DB::select($this->mPageType.'_pages', $select, $options, 0);

        while ($page = DB::fetchAssoc($result)) {
            $this->mPageData[$page['id']] = $page;
            // The array that contains the child ids of each page
            $this->mPageChildIds[$page['parent_id']][]=$page['id'];
        }
        */
        $this->fetchData();
        if (FALSE != isset($this->mPageChildIds[0])) {
        $output_array = $this->MakeMenuArrayBranch($this->mPageChildIds[0],'');
        } else {
            $output_array = array();
        }
        return $output_array;
    }

    /**
     * Makes a branch of the Array Menu
     *
     */
    function makeMenuArrayBranch($pageIdsToshow, $indent)
    {
        // Start the unordered list
        //  $output_array = array();
        // Cycle through the page ids to show
        foreach ($pageIdsToshow as $key => $page_id) {

            // Output each link as we cycle through the one's to show
            // show the page id
            //            $output .= $indent.'('.$this->array_links_array[$current_page_id]['id'].') ';
            // show the link title
            //            $output .= $this->array_links_array[$current_page_id]['label']."<br />\n";

            $page_id = $this->mPageData[$page_id]['id'];
            $title = $indent.$this->mPageData[$page_id]['link_title'];
            $output_array[$page_id] = $title;

            // If the link we are showing has children then make a branch
            //     showing it's children.

            if (array_key_exists($page_id, $this->mPageChildIds)) {

                //            if (isset($this->mPageChildIds[$current_page_id])) {

                // Start a branch showing all the children of $current_page_id
                $branch_array = $this->makeMenuArrayBranch($this->mPageChildIds[$page_id], $indent.'&nbsp;&nbsp;');
                $output_array = $output_array + $branch_array;
            }
            //            $output .= '</li>';
        }
        //  $output .= "</ul>";
        return $output_array;
    }

    /**
     * Gets the data from the database
     *
     */
    function fetchData()
    {
        if (FALSE == $this->mDataFetched) {
            $select = array('id', 'link_title', 'parent_id', 'uri',
                            'query_string');
            if ($this->mShowDescriptions != FALSE) {
                $select[] = 'description';
            }
            if ($this->mShowAll == FALSE) {
                $options = 'WHERE id ="'.$this->mCurrentPageId.'" OR show_on_menu = 1 ';
            } else {
                $options = '';
            }
            $options .= 'ORDER BY link_title';
            $result = DB::select($this->mPageType.'_pages', $select, $options, 0);

            while ($page = DB::fetchAssoc($result)) {
                $this->mPageData[$page['id']] = $page;
                // The array that contains the child ids of each page
                $this->mPageData[$page['id']] = $page;

                $this->mPageChildIds[$page['parent_id']][] = $page['id'];
                // print_r($this->mPageChildIds);

                if (FALSE != isset($this->mNumChildren[$page['parent_id']])) {
                    $this->mNumChildren[$page['parent_id']]++;
                } else {
                    $this->mNumChildren[$page['parent_id']] = 1;
                }
            }
            $this->mDataFetched = TRUE;
        }
        // return $this->mResult;
    }

    /**
     * Returns a breadcrumbs trail that is fetched from the database
     *
     */
    function showBreadCrumbs($pageId, $uriPrefix = '')
    {
        // Get need data from database
        $this->fetchData();
        // Start making the breadcrumbs trail
        $output = $this->breadCrumbsUpOneLevel($pageId, $uriPrefix);
        // Only show the link_title of the current page if it has parents.
        if ('' != $output) {
            $output .= $this->mPageData[$this->mCurrentPageId]['link_title'];
        } else {
            $output .= $this->mPageData[$this->mCurrentPageId]['link_title'];
            $output .= ' &raquo; Home';
        }
        return $output;
    }

    /**
     * Goes up one breadcrumb level
     *
     */
    function breadCrumbsUpOneLevel($pageId, $uriPrefix = '')
    {
       // $this->mParentIds[$pageId] = $pageId;
        $output = '';
        if ($pageId != 0) {
     //       if ($this->mPageBeingViewed != $this->mPageData[$pageId]['uri']) {
                // show link to parent page
                // $output = '<a href="'.$this->mSettings['site_uri'].$this->mPageData[$pageId]['uri'].'">'.$this->mPageData[$pageId]['link_title'].'</a> &raquo; '."\n";

                $link_title = $this->mPageData[$pageId]['link_title'];
                $query_string = $this->mPageData[$pageId]['query_string'];
                if ('' != $this->mPageData[$pageId]['query_string']) {
                    // replaced all instances of {word} with the value of $_GET['word']
                    $query_string = preg_replace("/\{(\w+)\}/e", "\$_GET['\\1']", $query_string);
                    $link_title = preg_replace("/\{(\w+)\}/e", "\$_GET['\\1']", $link_title);
          }
                $findme  = 'http://';
                $pos = strpos($this->mPageData[$pageId]['uri'], $findme);

                // Note our use of ===.  Simply == would not work as expected
                // because the position of 'a' was the 0th (first) character.
                if ($pos === FALSE) {
                    $uri = $uriPrefix;
                    // echo "The string '$findme' was not found in the string '$mystring'";
                } else {
                    $uri = '';
                    // echo "The string '$findme' was found in the string '$mystring'";
                    // echo " and exists at position $pos";
                }
                $uri .= $this->mPageData[$pageId]['uri'].$query_string;
                $output = '<a href="'.$uri.'">'.$link_title.'</a> &raquo; '."\n";
                //   }
            /*
            if ($this->mPageBeingViewed == $this->mPageData[$pageId]['uri']) {
                // show unlinked page title
                $output = $this->mPageData[$pageId]['link_title'];
            } else {
                // show link to page
                $output = '<a href="'.$this->mSettings['site_uri'].$this->mPageData[$pageId]['uri'].'">'.$this->mPageData[$pageId]['link_title'].'</a> &gt; '."\n";
            }
            */
            $output = $this->breadCrumbsUpOneLevel($this->mPageData[$pageId]['parent_id'], $uriPrefix).$output;
        }
        return $output;
    }

    /**
     * Gets a list of the pages parentids
     *
     */
    function getParentIds($pageId) {
        $this->mParentIds[$pageId] = $pageId;
        if ($pageId != 0) {
            $this->getParentIds($this->mPageData[$pageId]['parent_id']);
        }
    }

    /**
     * Shows a link to the previous page
     *
     */
    function showBackLink($pageId) {
        $query_string = $this->mPageData[$pageId]['query_string'];
        if ('' != $this->mPageData[$pageId]['query_string']) {
            // replaced all instances of {word} with the value of $_GET['word']
            $query_string = preg_replace("/\{(\w+)\}/e", "\$_GET['\\1']", $query_string);
        }
        // echo $msg;
        $output = '<a href="'.$this->mPageData[$pageId]['uri'].$query_string.'">&laquo; Go Back</a>'."\n";
        return $output;
    }
}
?>