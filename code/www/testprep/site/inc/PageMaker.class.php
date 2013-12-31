<?php
/**
 * Generates member's pages
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: PageMaker.class.php 1564 2004-12-08 01:11:16Z elijah $
 * @package NiftyCMS
 */
class PageMaker {

    // The page number being generated
    var $mPageNumber;
    // An array of information about the member
    var $mMember = 0;
    // The data of the page being created
    var $mPageData = array();
    // The name of the member's folder
    var $mMemberFolder = '';

    function PageMaker($settings, $memberFolderName)
    {
        $this->mSiteUri = $settings['site_uri'];
        $this->mMemberFolder = $memberFolderName;
    }

    /**
     * Get's the id of the member page being viewed
     *
     */
    function showPage()
    {
        $request_uri = $_SERVER['REQUEST_URI'];
        // $found = ereg("/([_a-zA-Z0-9-]*)/", $request_uri, $matches);
        // $this->mMemberFolder = $matches[1];

        // Get the member's id and dir_listing id
        $select = array('id', 'dir_listing');
        $where = 'WHERE folder_name="'.$this->mMemberFolder.'"';
        $result = DB::select('cp_members', $select, $where);
        $member = DB::fetchAssoc($result);

        $this->mMember = $member;

        // Get the page number of the page being edited
        $found2 = ereg("/([0-9]*)/", $request_uri, $matches2);
        if (FALSE != $found2) {
            if ('' == $matches2[1]) {
                $this->mPageNumber = 1;
            } else {
                // echo $matches2[1];
                $this->mPageNumber = $matches2[1];
            }
        } else {
            $this->mPageNumber = 1;
        }

        $found3 = ereg("/picture-([0-9]*)/", $request_uri, $matches3);
        if (FALSE != $found3) {
            // print_r($matches3);
            $picture_id = $matches3[1];
            // $this->getPageData()
            return $this->showPicturePage($picture_id);
        }

        return $this->showMemberPage();
    }

    function getPageData($memberId, $pageNumber)
    {
        $select = array('id', 'page_title', 'page_number', 'header_text',
                        'link_title', 'body_one', 'body_two', 'template',
                        'text_color', 'link_color', 'visited_link_color',
                        'background_color', 'background_image', 'logo',
                        'show_counter', 'hits', 'active');
        $where = 'WHERE member_id="'.$memberId.'" AND page_number="'.$pageNumber.'"';
        $result = DB::select('cp_member_pages', $select, $where);

        if (0 == DB::numRows($result)) {
            // Unable to retrieve page data
            $error = Event::showAndLog(60, 0, 'guest', $pageNumber);
            die ($error);
            $page_data = array();
        } else {
            $page_data = DB::fetchAssoc($result);
        }
        if (1 != $page_data['active']) {
            // Page is inactive
            $error = Event::showAndLog(61, 0, 'guest', $pageNumber);
            die ($error);
            $page_data = array();
        }
        return $page_data;
    }

    /**
     * Shows a member's page
     *
     */
    function showMemberPage()
    {
        $this->mPageData = $this->getPageData($this->mMember['id'], $this->mPageNumber);

        // Get the folder the where the template is stored
        $template_id = (int) $this->mPageData['template'];
        $select = array('folder');
        $where = 'WHERE id="'.$template_id.'"';
        $result = DB::select('cp_templates', $select, $where);
        $template = DB::fetchAssoc($result);
        $this->mTemplateSettings = $this->getTemplateSettings($template['folder']);

        $this->updateCounter();
        if (1 == $this->mPageData['show_counter']) {
            $num_hits = $this->mPageData['hits'] + 1;
            $hit_counter = $num_hits.' visits';
        } else {
            $hit_counter = '';
        }

        // Convert line breaks to html line breaks
        //        $this->mPageData['body1']= nl2br($this->mPageData['body1']);
        //        $this->mPageData['body2']= nl2br($this->mPageData['body2']);

        // $this->mFolderUri = $this->mSiteUri.'/'.$this->mMemberFolder.'/';
        // Only show the header text if the member has not uploaded a logo
        if (FALSE == empty($this->mPageData['logo'])) {
            // $header = '<img src="'.$this->mFolderUri.$this->mPageData['logo'].'" alt="'.$this->mPageData['header_text'].'">';
            $page_uri = $this->getUri(1);
            $header = '<img src="'.$page_uri.$this->mPageData['logo'].'" alt="'.$this->mPageData['header_text'].'">';
        } else {
            $header = $this->mPageData['header_text'];
        }

        $dir = $this->mSiteUri.'/site/templates/'.$template['folder'];
        $menu = $this->getMenu();


        $breadcrumbs = $this->getBreadcrumbs(FALSE);

        // $body_one  = $this->mPageData['body_one'];
        // $body_one  = str_replace("'", '&#039;', $this->mPageData['body_one']);
        // $body_two = str_replace("'", '&#039;', $this->mPageData['body_two']);

        $body_one  = $this->SanitizeText($this->mPageData['body_one']);
        $body_two  = $this->SanitizeText($this->mPageData['body_two']);

        $replace = array(
            'page_title' => $this->mPageData['page_title'],
            'header' => $header,
            'text_color' => ' color:'.$this->mPageData['text_color'].'; ',
            'link_color' => ' a { color:'.$this->mPageData['link_color'].'; } ',
            'visited_link_color' => ' a:visited { color:'.$this->mPageData['visited_link_color'].'; }',
            'background_color' => ' background-color:'.$this->mPageData['background_color'].'; ',
            'body_one' => $body_one,
        //    'pictures' => $pictures,
            'menu'     => $menu,
            'hit_counter' => $hit_counter,
            'body_two' => $body_two,
            'dir' => $dir,
            'breadcrumbs' => $breadcrumbs
        );
        $this->getPictures($template['folder'], $dir);

        for ($i = 1; $i <= $this->mTemplateSettings['picture_tables']; $i++) {
            $pictures = $this->getPictureTable($template['folder'], $dir, $i);
            if (1 == $i) {
                $replace['pictures'] = $pictures;
            } else {
                $picture_index = 'pictures'.$i;
                $replace[$picture_index] = $pictures;
            }
        }
        // print_r($replace);
        $Template = new Template($replace);
        $template_file = dirname(__FILE__).'/../../site/templates/'.$template['folder'].'/'.$template['folder'].'.tpl';
        $finished_page = $Template->createFromFile($template_file);
        return $finished_page;
    }

    /**
     * Shows a picture page
     *
     */
    function showPicturePage($pictureId)
    {
        $query = 'SELECT member_id, file_name, page_number, short_description,
                         long_description FROM cp_member_pictures
                  WHERE id="'.(int) $pictureId.'" LIMIT 1';
        $result = DB::query($query);
        $picture = DB::fetchAssoc($result);
        $this->mPageData = $this->getPageData($picture['member_id'], $picture['page_number']);

        $back_link = $this->showBackLink($picture['page_number'], $this->mMemberFolder, $this->mSiteUri);

        $output = '<html>'."\n";
        $output .= '<head>'."\n";
        $output .= '<title>Picture: '.$picture['short_description'].'</title>'."\n";
        $output .= '<style type="text/css">'."\n";
        $output .= 'html, body {'."\n";
        $output .= ' text-align:center;'."\n";
        // $output .= ' border:0;'."\n";
        // $output .= ' margin:0;'."\n";
        $output .= ' color:'.$this->mPageData['text_color'].';'."\n";
        $output .= ' background-color:'.$this->mPageData['background_color'].';'."\n";
        $output .= '}'."\n";
        $output .= 'a {'."\n";
        $output .= ' color:'.$this->mPageData['link_color'].';'."\n";
        $output .= '}'."\n";
        $output .= 'a:visited {'."\n";
        $output .= ' color:'.$this->mPageData['visited_link_color'].';'."\n";
        $output .= '}'."\n";
        $output .= '</style>'."\n";
        $output .= '</head>'."\n";
        $output .= '<body>'."\n";
        $output .= '<p style="text-align:left">'.$this->getBreadcrumbs(TRUE);
        if (FALSE == empty($picture['short_description'])) {
            $link_title = $picture['short_description'];
        } else {
            $link_title = 'Picture '.$pictureId;
        }
        $output .= ' &raquo; '.$link_title;
        $output .= '</p>';
        $output .= '<h1>'.$picture['short_description'].'</h1>'."\n";
        $output .= $back_link;
        $index_uri = $this->getUri(1);
        $output .= '<img src="'.$index_uri.$picture['file_name'].'" alt="['.$picture['file_name'].']">'."\n";
        // $output .= '<img src="'.$this->mSiteUri.'/'.$this->mMemberFolder.'/'.$picture['file_name'].'" alt="['.$picture['file_name'].']">'."\n";
        $output .= '</a>'."\n";
        $output .= '<p><b>'.$picture['short_description'].'</b>:'."\n";
        $output .= '<br />'.$picture['long_description'].'</p>'."\n";
        $output .= $back_link;
        $output .= '</body>'."\n";
        $output .= '</html>'."\n";
        return $output;
    }

    /**
     * Shows a link on picture pages to go back to the members page
     *
     */
    function showBackLink($pageNumber, $memberFolder, $siteUri)
    {
        $back_uri = $this->getUri($pageNumber);
        // $back_uri = $siteUri.'/'.$memberFolder.$page_name;
        // $back_link = '<p><a href="'.$back_uri.'">&lt;&lt; Back</a></p>'."\n";
        $back_link = '<p><a href="'.$back_uri.'">&laquo; Go Back</a></p>'."\n";
        return $back_link;
    }

    /**
     * Returns the uri of a page
     *
     */
    function getUri($pageNumber)
    {
        $pos = strpos($_SERVER['REQUEST_URI'], $this->mMemberFolder);
        if (1 == $pageNumber) {
            // If the member folder was not requested
            if ($pos === FALSE) {
                $uri = '/';
            } else {
                $uri = '/'.$this->mMemberFolder.'/';
            }
         } else {
             // If the member folder was not requested
             if ($pos === false) {
                 $uri = '/'.$pageNumber.'/';
             } else {
                 $uri = '/'.$this->mMemberFolder.'/'.$pageNumber.'/';
             }
         }
         return $uri;
    }

    /**
     * Updates the page hit counter by one
     *
     */
    function updateCounter()
    {
        $num_hits = $this->mPageData['hits'] + 1;
        // Update database with the updated page options
        $query = 'UPDATE cp_member_pages SET hits="'.$num_hits.'" WHERE id="'.$this->mPageData['id'].'" LIMIT 1';
        DB::query($query);
    }

    /**
     * Returns a menu with links to the other pages
     *
     */
    function getMenu()
    {
        $query = 'SELECT link_title, page_number FROM cp_member_pages
                  WHERE member_id="'.$this->mMember['id'].'" AND active="1"
                  ORDER BY page_number ASC';
        $result = DB::query($query);
        if (DB::numRows($result) > 1) {
            $output = '<ul>';
            while ($page = DB::fetchAssoc($result)) {
                 if ('' == $page['link_title']) {
                     $link_title = 'Page '.$page['page_number'];
                 } else {
                     $link_title = $page['link_title'];
                 }
                if ($this->mPageData['page_number'] == $page['page_number']) {
                    $output .= '<li>'.$link_title.'</li>';
                } else {
                    $uri = $this->getUri($page['page_number']);
                    $output .= '<li><a href="'.$uri.'">'.$link_title.'</a></li>';
                    // $output .= '<li><a href="'.$this->mSiteUri.'/'.$this->mMemberFolder.$filename.'">'.$link_title.'</a></li>';
                    // $output .= '<li><a href="'.$filename.'">'.$link_title.'</a></li>';
                }
            }
            $output .= '</ul>';
        } else {
            $output = '';
        }
        return $output;
    }

    /**
     * Gets template specific settings and copies them to a member attribute
     * array variable.
     *
     */
    function getTemplateSettings($templateFolderName) {
        $template_settings_file = dirname(__FILE__).'/../../site/templates/'.$templateFolderName.'/template_settings.php';
        if (FAlSE != is_file($template_settings_file)) {
            include $template_settings_file;
        }
        if (FALSE == is_array($template_settings)) {
            $template_settings = array();
        }
        if (FALSE != empty($template_settings['before_picture'])) {
            $template_settings['before_picture'] = '';
        }
        if (FALSE != empty($template_settings['after_picture'])) {
            $template_settings['after_picture'] = '<br />{short_description}';
        }
        if (FALSE != empty($template_settings['picture_columns'])) {
            $template_settings['picture_columns'] = 2;
        }
        if (FALSE != empty($template_settings['picture_tables'])) {
            $template_settings['picture_tables'] = 1;
        }
        // Return the template settings
        return $template_settings;
    }

    /**
     * Fetches the pages pictures from the database and returns them in a table
     *
     */
    function getPictures($templateFolder, $dir)
    {
        // Get the page's pictures from the database
        $query = 'SELECT id, file_name, thumbnail_name, short_description
                  FROM cp_member_pictures WHERE member_id="'.$this->mMember['id'].'"
                  AND page_number="'.$this->mPageData['page_number'].'"
                  ORDER BY id ASC';
        $result = DB::query($query);
        $num_rows = DB::numRows($result);
        $table_num = 1;
        $pic_per_table = $num_rows / $this->mTemplateSettings['picture_tables'];
        $pictures_per_table = round($pic_per_table);
        $row_num = 1;
        while ($member_image = DB::fetchAssoc($result)) {
            //   echo '<p>'.$i.'</p>';
            // echo $pictures_per_table;
            $this->mPictureTables[$table_num][] = $member_image;
            //  echo '<p>added pic '.$row_num.' to table '.$table_num.'</p>';
            if ($row_num == $pictures_per_table) {
                $table_num++;
                //  echo '<p>'.$row_num.' going to table'.$table_num.'</p>';
                $row_num = 1;
            } else {
                $row_num++;
            }
            //   $this->mPictureTables[2][] = $member_image;
        }
        //  print_r($this->mPictureTables);
    }

    function getPictureTable($templateFolder, $dir, $tableNumber) {
        $output = '<table cellpadding="10" class="pictures">';
        $row_ended = 0;
        // for ($i = 1; $member_image = DB::fetchAssoc($result); $i++) {
        $i = 0;
        //    echo '<div><pre>';
        //    print_r($this->mPictureTables);
        //    echo '</pre></div>';
        if (FALSE != empty($this->mPictureTables[$tableNumber])) {
            return '&nbsp;';
        }
        foreach ($this->mPictureTables[$tableNumber] AS $key => $member_image) {
            //    echo '<div><pre>';
            //    print_r($member_image);
            //    echo '</pre></div>';
            $i++;
            $image_file = '../../'.$this->mMemberFolder.'/'.$member_image['file_name'];
            if (FALSE != is_file($image_file)) {
                // $size = getimagesize($this->mFolderUri.$member_image['file_name']);
                $size = getimagesize($image_file);
                $image_width = $size[0] + 20; // Index 0
                $image_height = $size[1] + 100; // Index 1
            } else {
                $image_width = 0;
                $image_height = 0;
            }
            if ($i == 1) {
                $output .= '<tr>';
            }
            $output .= '<td>';
            $picture = $this->mTemplateSettings['before_picture'];
            // $output .= '<a href="'.$this->mSiteUri.'/site/pages/show-picture.php?picture='.$member_image['id'].'" title="View Full Size Image"><img src="'.$this->mFolderUri.'thumb_'.$member_image['file_name'].'" alt="'.$member_image['short_description'].'" /><br />';
            // $output .= '<a href="'.$this->mFolderUri.'pictures/'.$member_image['id'].'/" title="View Full Size Image">';
            $picture .= '<a href="picture-'.$member_image['id'].'/" title="View Full Size Image">';
            // $output .= '<a href="'.$this->mSiteUri.'/site/pages/show-picture.php?picture='.$member_image['id'].'" title="View Full Size Image">';
            // $output .= '<img src="'.$member_image['thumbnail_name'].'" alt="['.$member_image['thumbnail_name'].']" /><br />';
            // $output .= '<img src="'.$this->mFolderUri.$member_image['thumbnail_name'].'" alt="['.$member_image['thumbnail_name'].']" /><br />';
            $page_uri = $this->getUri(1);
            $picture .= '<img src="'.$page_uri.$member_image['thumbnail_name'].'" alt="['.$member_image['thumbnail_name'].']" />';
            //  $output .= '<a href="'.$this->mSiteUri.'/site/pages/show-picture.php?id='.$member_image['id'].'" onclick="javascript:show_picture('.$member_image['id'].', '.$image_width.', '.$image_height.');return FALSE;" title="View Full Size Image"><img src="'.$this->mFolderUri.'thumb_'.$member_image['file_name'].'" alt="'.$member_image['short_description'].'" /><br />';
            // $picture .= '</a><br />'.$member_image['short_description'];
            $picture .= '</a>';
            $picture .= $this->mTemplateSettings['after_picture'];

            $replace = array(
                'short_description' => $member_image['short_description'],
                'dir' => $dir
            );
            $Template = new Template($replace);
            // $template_file = dirname(__FILE__).'/../../site/templates/'.$template['folder'].'/'.$template['folder'].'.tpl';
            $output .= $Template->parseTemplate($picture);

            if ($i == $this->mTemplateSettings['picture_columns']) {
                $output .= '</td></tr>'."\n";
                $i = 0;
                $row_ended = 1;
            } else {
                $row_ended = 0;
                $output .= '</td>'."\n";
            }

        }
        if (1 != $row_ended) {
            $output .= '</tr>';
        }
        $output .= '</table>';
        return $output;
    }

    /**
     * Gets the breadcrumbs trail for a member's page
     *
     */

    function getBreadcrumbs($picturePage)
    {

        if (FALSE == empty($this->mMember['dir_listing'])) {
            $Menu = new Menu('dir', $this->mMember['dir_listing']);
            $output = $Menu->showBreadCrumbs($this->mMember['dir_listing'], $this->mSiteUri);
        } else {
            /*
            $select = array('link_title');
            $where = 'WHERE page_subtypes.name="home" AND dir_pages.subtype=page_subtypes.id';
            $result = DB::select('dir_pages, page_subtypes', $select, $where);
            $dir_page = DB::fetchAssoc($result);
            $output = '<a href="'.$this->mSiteUri.'/">'.$dir_page['link_title'].'</a> &raquo; ';
            */
            $output = '<a href="'.$this->mSiteUri.'/">Home</a> &raquo; ';


        }
       /*
        if (1 != $this->mPageNumber) {
      //      $uri = $this->getUri(1);
     //       $output .= '<a href="'.$uri.'">'.$listing['title'].'</a> &raquo; ';
            // $uri = $this->getUri($this->mPageNumber);
            if ('' == $this->mPageData['link_title']) {
                $link_title = 'Page '.$this->mPageNumber;
            } else {
                $link_title = $this->mPageData['link_title'];
            }
            // $output .= '<a href="'.$uri.'">'.$link_title.'</a>';
            if (FALSE == $picturePage) {
                $output .= $link_title;
            } else {
                $uri = $this->getUri($this->mPageNumber);
                $output .= '<a href="'.$uri.'">'.$link_title.'</a>';
            }

        } else {
        */
        if ('' == $this->mPageData['link_title']) {
            $link_title = 'Page '.$this->mPageNumber;
        } else {
            $link_title = $this->mPageData['link_title'];
        }
        if (FALSE == $picturePage) {
            $output .= $link_title;
        } else {
            $uri = $this->getUri($this->mPageNumber);
            $output .= '<a href="'.$uri.'">'.$link_title.'</a>';
        }




        return $output;
        /*
        $query = 'SELECT title, state, county, job FROM dir_contractors
                  WHERE member_id="'.$this->mMemberId.'" LIMIT 1';
        $result = DB::query($query);

            $listing = DB::fetchAssoc($result);

            $query2 = 'SELECT link_title, uri, type FROM dir_pages
                WHERE type = "home" OR id = "'.$listing['state'].'"
            OR id = "'.$listing['county'].'"
            OR id = "'.$listing['job'].'" LIMIT 4';
            $result2 = DB::query($query2);

            while ($page = DB::fetchAssoc($result2)) {
                $$page['type'] = array(
                    'link_title' => $page['link_title'],
                    'uri'   => $page['uri']
                );
            }
            $output  = '<a href="'.$this->mSiteUri.$home['uri'].'">'.$home['link_title'].'</a> &gt; '."\n";
           if (1 == DB::numRows($result)) {
            $output .= '<a href="'.$this->mSiteUri.$state['uri'].'">'.$state['link_title'].'</a> &gt; '."\n";
            $output .= '<a href="'.$this->mSiteUri.$county['uri'].'">'.$county['link_title'].'</a> &gt; '."\n";
            $output .= '<a href="'.$this->mSiteUri.$job['uri'].'">'.$job['link_title'].'</a> &gt; '."\n";

               if (1 != $this->mPageNumber) {
                   $uri = $this->getUri(1);
                   $output .= '<a href="'.$uri.'">'.$listing['title'].'</a> &gt; ';
                   // $uri = $this->getUri($this->mPageNumber);
                   if ('' == $this->mPageData['link_title']) {
                       $link_title = 'Page '.$this->mPageNumber;
                   } else {
                       $link_title = $this->mPageData['link_title'];
                   }
                   // $output .= '<a href="'.$uri.'">'.$link_title.'</a>';
                   if (FALSE == $picturePage) {
                       $output .= $link_title;
                   } else {
                       $uri = $this->getUri($this->mPageNumber);
                       $output .= '<a href="'.$uri.'">'.$link_title.'</a>';
                   }

               } else {
                   $output .= $listing['title']."\n";
               }

           } else {
               if ('' == $this->mPageData['link_title']) {
                   $link_title = 'Page '.$this->mPageNumber;
               } else {
                   $link_title = $this->mPageData['link_title'];
               }

               $output .= $link_title;
           }
        return $output;
        */
    }


    /**
    * Sanitizes text by removing <script> tags
    *
    */
    function sanitizeText($text)
    {
        // $text  = str_replace("'", '&#039;', $text);

        // remove all <script> and </script> tags
        $search = "'<script[^>]*?>.*?</script>'si";
        $text = preg_replace($search, '', $text);
        $text = str_replace('<script>', '', $text);
        $text = str_replace('</script>', '', $text);
        return $text;
    }
}
?>
