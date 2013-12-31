<?php
/**
 * Methods to display the header and footer of pages
 *
 * Lets the page headers and footers be displayed through methods.
 * Checks to make sure that Members are logged in at the admin pages.
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: Page.class.php 1939 2005-05-24 22:31:19Z elijah $
 * @package NiftyCMS
 */

require_once 'ErrorHandler.class.php';
require_once 'Event.class.php';
require_once 'settings.inc.php';
require_once 'Member.class.php';
require_once 'DB.class.php';
require_once 'Menu.class.php';
require_once 'Template.class.php';

class Page {
    // An array of page data
    private $mData = array();
    // The page type
    private $mType = '';
    // The member's name
    private $mMemberName = '';
    // The pages's left menu
    private $mLeftMenu = '';
    // Holds the Page's right menu
    private $mRightMenu = '';
    // The page's breadcrumbs trail
    private $mBreadcrumbs = '&nbsp;';
    // The Uri of the page being viewed
    private $mUri = '';
    // Whether the member is logged in or not, TRUE or FALSE
    private $mMemberLoggedIn = FALSE;
    // Stuff to go between the <head> and </head> tags.
    private $mHeaderStuff = '';
    // The title for the left menu
    private $mMenuTitle = '';
    // A link to the previous page
    private $mBackLink = '';

    /**
     * Initializes the class
     *
     * @param object $member - A member object which is passed by reference
     */
    function Page($pageType, $settings, &$member)
    {
        // Copy the $settings array into a local class variable
        $this->mSettings = $settings;

        // The error handler will email errors to $settings['error_email']
        $ErrorHandler = new ErrorHandler($this->mSettings);
        $ErrorHandler->startHandler();

        $this->mType = $pageType;
        // Copy the member object by reference
        $this->mMember = & $member;

        if (FALSE != isset($_SERVER['REQUEST_URI'])) {
            // Get the uri of the page being viewed without the query string
            $this->mUri = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
            // Remove all # signs and everything that follows them.
            $this->mUri = preg_replace('/\#[a-zA-Z0-9_-]*/', '', $this->mUri);
            // echo $this->mUri;
            // $this->mUri = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
        } else {
            $this->mUri = '';
        }
    }

    /**
     * Shows the page header
     *
     */
    function showHeader($headerStuff = '') {
        // Stuff to go between the <head> and </head> tags.
        $this->mHeaderStuff = $headerStuff;

        // Connect to the database
        DB::connect($this->mSettings);

        // Log the hit
        Event::logHit($this->mType);
        // Create the page
        $this->_createPage();
        // Return the page header
        return $this->mHeader;
    }

    /**
     * Fetches and creates header from the template in the database.
     *
     */
    function _createPage()
    {
        $select = array('header', 'footer', 'force_login', 'prevent_caching',
                        'has_subtypes', 'left_menu_type', 'right_menu_type',
                        'right_menu_parameter', 'show_back_links',
                        'use_creation_info');
        $where  = 'WHERE page_types.name = "'.$this->mType.'" AND ';
        if (FALSE == empty($_GET['template'])) {
            $template_name = DB::makeSafe($_GET['template']);
            $where .= 'page_templates.name="'.$template_name.'"';
        } else {
            $where .= 'page_templates.id=page_types.template';
        }
        $result = DB::select('page_types, page_templates, page_subtypes',
                             $select, $where);
        $this->mPage = DB::fetchAssoc($result);

        if (1 == $this->mPage['force_login']) {
// EJL: Disable to temporarily enable full access for everyone.
            $this->forceLogin(); // Make sure member is logged in
            $this->mMember->updateLastAction();
        } else {
            $reason = 0;
            // See if member is logged in
            $this->mMemberLoggedIn = $this->mMember->isLoggedIn($reason);
        }
        // Get the member's name
        $this->mMemberName = $this->mMember->getAttribute('first_name');

        $this->_getData();
        if (1 == $this->mPage['prevent_caching']) {
            $this->sendNoCacheHeaders(); // Prevent pages from being cached by the browser
         }

        $this->_createParts();

        $replace = array ('inc_uri'      => $this->mSettings['inc_uri'],
                          'title'        => $this->mData['title'],
                          'server_name'  => $_SERVER['SERVER_NAME'],
                          'page_uri'     => $this->mData['uri'],
                          'header_stuff' => $this->mHeaderStuff,
                          'top_menu'     => $this->mTopMenu,
                          'menu_title'   => $this->mMenuTitle,
                          'left_menu'    => $this->mLeftMenu,
                          'breadcrumbs'  => $this->mBreadcrumbs,
                          'heading'      => $this->mData['heading'],
                          'back_link'    => $this->mBackLink,
                          'content'      => $this->mData['content'],
                          'description'  => $this->mData['description'],
                          'right_menu'   => $this->mRightMenu);
          if (1 == $this->mPage['use_creation_info']) {
              // $replace['creation_date'] = $this->mData['creation_date'];
              if (FALSE == empty($this->mData['name'])) {
                  $replace['author'] = ' by '.$this->mData['name'];
              } else {
                  $replace['author'] = '';
              }
              if (FALSE == empty($this->mData['creation_date']) ||
                  FALSE == empty($this->mData['author'])) {
                  $replace['creation_date']  = '<ul><li class="created">';
                  $replace['creation_date'] .= $this->mData['creation_date'];
                  $replace['author'] .= '</li></ul>';
              } else {
                  $replace['creation_date'] = $this->mData['creation_date'];
              }
              $last_modified = date('Y/m/d G:i:s ', $this->mData['last_modified']);

              $replace['last_modified'] = $last_modified;


          }
          $Template = new Template($replace);
          $this->mHeader = $Template->parseTemplate($this->mPage['header']);
          $this->mFooter = $Template->parseTemplate($this->mPage['footer']);
    }

    /**
     * Gets the page data from the database and creates different parts of the page
     *
     */
    function _getData() {
        $from_tables = $this->mType.'_pages';
        $select = array($this->mType.'_pages.id', 'link_title', 'uri',
                        $this->mType.'_pages.title', 'heading', 'content',
                        'parent_id', $this->mType.'_pages.description');
        $where = 'WHERE uri="'.$this->mUri.'"';
        if (1 == $this->mPage['has_subtypes']) {
            $select[] = 'page_subtypes.menu_title';
            $select[] = 'page_subtypes.subtype_action';
            $where .= ' AND page_subtypes.id = '.$this->mType.'_pages.subtype';
            $from_tables .= ' ,page_subtypes';
        }
        if (1 == $this->mPage['use_creation_info']) {
            $select[] = $this->mType.'_pages.author';
            $select[] = $this->mType.'_pages.creation_date';
            $select[] = $this->mType.'_pages.last_modified';
            /*
            $select[] = 'page_authors.name';
            $select[] = 'page_authors.about';
            $select[] = 'page_authors.picture';
            $where .= ' AND page_authors.id = '.$this->mType.'_pages.author';
            $from_tables .= ' ,page_authors';

            $select = array('name');
            $where = 'WHERE id="'.$this->mUri.'"';
            $result = DB::select($from_tables, $select, $where);
            */
        }
        if ($this->mPage['left_menu_type'] == 'child_list') {
            $select[] = 'menu_parent_id';
        }
        if ($this->mPage['left_menu_type'] == 'custom') {
            $select[] = 'left_menu';
        }
        $result = DB::select($from_tables, $select, $where);
        // If the page was not found in the database
        if (1 != DB::numRows($result)) {
            $select2 = array('id');
            $where2 = 'WHERE uri="'.$this->mUri.'/"';
            $result2 = DB::select($this->mType.'_pages', $select2, $where2);
            // If the page is not in the database with a trailing slash
            if (1 != DB::numRows($result2)) {
                header('HTTP/1.0 404 Not Found');
                echo '404 Not Found!';
                // Log the 404 error to the database
                Event::logEvent(10, 0, 'guest', '');
                exit();
            } else {
                // Redirect the user to the correct page URI
                header('HTTP/1.1 301');
                header('Location: '.$this->mSettings['site_uri'].$this->mUri.'/');
                exit();
            }
        }
        $this->mData = DB::fetchAssoc($result);
        if (FALSE == empty($this->mData['author'])) {
            $select = array('name');
            $where = 'WHERE id="'.$this->mData['author'].'"';
            $result = DB::select('page_authors', $select, $where);
            $creation_info = DB::fetchAssoc($result);
            $this->mData['name'] = $creation_info['name'];
        }
    }

    /**
     * Creates different parts of the page
     *
     */
    function _createParts()
    {
        // Create a new tree object
        $Menu = new Menu($this->mType, $this->mData['id'], FALSE, FALSE);

        $this->mBreadcrumbs = $Menu->showBreadCrumbs($this->mData['parent_id']);

        /*
        // Only show the link_title of the current page if it has parents.
        if ('' != $this->mBreadcrumbs) {
            $this->mBreadcrumbs .= $this->mData['link_title'];
        }
        */
        // Get the top menu of the page
        $this->mTopMenu = $this->_getTopMenu();

        if (1 == $this->mPage['has_subtypes']) {
            include_once 'PageSubtype.class.php';
            $PageSubtype = new PageSubtype($this->mData['id'], $this->mType);
            switch ($this->mData['subtype_action']) {
                case 'show_child_table':
                    // show a table list of the page's children
                    $this->mData['content'] .= $PageSubtype->showChildTable();
                    break;
                case 'show_child_list':
                    // show a list of page's children
                    $Menu2 = new Menu($this->mType, $this->mData['id'], FALSE, TRUE);
                    $this->mData['content'] .= $Menu2->showChildList($this->mData['id']);
                    // $this->mData['content'] .= $PageSubtype->showChildList();
                    break;
                case 'show_description_list':
                    // show a list of page's children
                    $Menu2 = new Menu($this->mType, $this->mData['id'], TRUE, TRUE);
                    $this->mData['content'] .= $Menu2->showListMenu($this->mData['id']);
                    // $this->mData['content'] .= $PageSubtype->showChildList();
                    break;
                default:
                    // don't output anything
                    break;
            }
            // echo $this->mData['subtype_action'];
            $this->mMenuTitle = $this->mData['menu_title'];
        }

        if (1 == $this->mPage['show_back_links']) {
            $this->mBackLink = $Menu->showBackLink($this->mData['parent_id']);
        }

        // Get the left menu
        switch ($this->mPage['left_menu_type']) {
            case 'child_list':
                $this->mLeftMenu .= $Menu->showChildList($this->mData['menu_parent_id']);
                break;
            case 'full_list':
                $this->mLeftMenu .= $Menu->showListMenu();
                break;
            case 'relative_list':
                $this->mLeftMenu .= $Menu->showRelativeList();
                break;
            case 'custom':
                $this->mLeftMenu = $this->mData['left_menu'];
            default:
                // No left menu
                break;
        }
        // Get the right menu
        switch ($this->mPage['right_menu_type']) {
            case 'new_subtype_list':
                $this->mRightMenu .= $PageSubtype->showNewList(
                    $this->mPage['right_menu_parameter']
                );
                break;
            case 'child_list':
                $this->mRightMenu = $Menu->showChildList($this->mData['id']);
                break;
            default:
                // No right menu
                break;
        }

    }

    /**
     * Shows the page footer
     *
     */
    function showFooter()
    {
        // Close the database connection
        DB::Close();
        // Return the page footer
        return $this->mFooter;
    }

    /**
     * Returns the top menu
     *
     */
    function _getTopMenu()
    {
        $output = '<tr class="topmenu"><td>'."\n";
        if (FALSE == $this->mMemberLoggedIn) {
            $output .= $this->_showLink('List your company', '/hosting/')."\n";
        } else {
            $output .= 'Welcome '.$this->mMemberName."\n";
        }
        $output .= '</td><td><ul>'."\n";
        $output .= '<li>'.$this->_showLink('Home', '/').'</li>'."\n";
        if (TRUE == $this->mMemberLoggedIn) {
            $output .= '<li>'.$this->_showLink('Control Panel', '/cp/').'</li>'."\n";
        }
        $output .= '<li>'.$this->_showLink('About Us', '/about/').'</li>'."\n";
        $output .= '<li>'.$this->_showLink('Web Hosting', '/hosting/')."</li>\n";
        $output .= '<li>'.$this->_showLink('Support', '/support/').'</li>'."\n";
        $output .= '</ul></td><td style="padding-right:5px">'."\n";
        if (TRUE == $this->mMemberLoggedIn) {
            $output .= $this->_showLink('Log Out', $this->mSettings[$this->mMember->mType.'_uri'].'/log-out.php');
        } else {
            $output .= $this->_showLink('Log In', '/cp/login.php');
        }
        $output .= '</td></tr>'."\n";
        return $output;
    }

    /**
     * Outputs a link but if it is to the page being viewed then it outputs
     * just the text
     *
     */
    function _showLink($title, $uri)
    {
        if ($this->mUri == $uri) {
            return $title;
        } else {
            // return '<a href="'.$this->mSettings['site_uri'].$uri.'">'.$title.'</a>';
            return '<a href="'.$uri.'">'.$title.'</a>';
        }
    }

    /**
     * Sends headers to prevent page caching
     *
     */
    function sendNoCacheHeaders()
    {
        // Send headers to prevent page caching
        // Date in the past
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        // always modified
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        // HTTP/1.1
        header('Cache-Control: no-store, no-cache, must-revalidate', FALSE);
        header('Cache-Control: post-check=0, pre-check=0', FALSE);
        // HTTP/1.0
        header('Pragma: no-cache');
    }

    /**
     * Sends member's to the login page if they are not logged in.
     *
     */
    function forceLogin() {
        $reason = 0;
        // See if member is logged in
        $this->mMemberLoggedIn = $this->mMember->isLoggedIn($reason);
        // If the member is not logged in then send them to the login page
        if (FALSE == $this->mMemberLoggedIn) {
            // Encode REQUEST URL
            $request_uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $request_uri = urlencode($request_uri);
            header ('Location:'.$this->mSettings[$this->mType.'_uri'].'/login.php?event='.$reason.'&from='.$request_uri);
            exit('Header redirection failed!');
        }
    }
}
?>
