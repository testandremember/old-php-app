<?php
/**
 * Parses templates.
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: Template.class.php 1939 2005-05-24 22:31:19Z elijah $
 * @package NiftyCMS
 */

class Template {

    // An array of what to replace with what.
    private $mReplace;

    /**
     * Intializes the class
     *
     */
    function Template ($replace)
    {
        $this->mReplace = $replace;
    }

    /**
     * Parses the template and returns it with the variables replaced
     *
     */
    function parseTemplate($template_data)
    {
        foreach ($this->mReplace as $key => $value ) {
            $template_data = str_replace('{'.$key.'}', $value, $template_data);
        }
        return $template_data;
    }

    /**
     * Creates a page using a given template file.
     *
     */
    function createFromFile($filename)
    {
        $handle = fopen($filename, "r");
        $template_data = fread($handle, filesize($filename));
        fclose($handle);
        $finished_page = $this->parseTemplate($template_data);
        return $finished_page;
    }
}
