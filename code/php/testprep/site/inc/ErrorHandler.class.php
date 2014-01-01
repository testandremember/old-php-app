<?php
/**
 * Handles php errors.
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: ErrorHandler.class.php 1939 2005-05-24 22:31:19Z elijah $
 */
class ErrorHandler {
    // An array of variables to exclude from the enviroment variable list
    private $excludes = array();
    // Number of lines of code to include in the code context
    private $source_context_options = array('lines' => 5);
    // Settings that include email_from and error_email
    private $mSettings = array();
    // Whether a notice has been shown saying that we are having tech problems
    private $mTechProblemsshown = FALSE;

    function ErrorHandler($settings)
    {
        $this->mSettings = $settings;
    }

    function startHandler()
    {
        // Same as error_reporting(E_ALL);
        // ini_set('error_reporting', E_ALL);

        // hack to work around the inability to use a method as a
        // callback for the set_error_handler function prior to PHP 4.3
        // NOTE: if you forGet the '&', then all of the settings
        // to class properties after this execution point will be lost

        $GLOBALS['_ERROR_HANDLER_OBJECT'] = & $this;
        $GLOBALS['_ERROR_HANDLER_METHOD'] = 'handleError';

        //  inside function to handle redirection to a class method
        function eh($errNo, $errStr, $file, $line, $context)
        {
            call_user_func(
                           array(
                                 &$GLOBALS['_ERROR_HANDLER_OBJECT'],
                                 $GLOBALS['_ERROR_HANDLER_METHOD']
                                 ),
                           $errNo, $errStr, $file, $line, $context
                           );
        }
        //  start handling errors
        set_error_handler('eh');
    }

    function setExcludes($excludes)
    {
        $this->excludes = $excludes;
    }

    function _excludeVars($context)
    {
        foreach ($this->excludes as $name) {
            if (isset($context[$name])) {
                unset($context[$name]);
            }
        }
        return $context;
    }

    function handleError($errorNumber, $errorMessage, $file, $line, $context)
    {
        switch ($errorNumber) {
        case 1:
            $type = 'Error';
            break;
        case 2:
            $type = 'Warning';
            break;
        case 8:
            $type = 'Notice';
            break;
        case 256:
            $type = 'User Error';
            break;
        default:
            $type = $errorNumber;
            break;
        }
        if (FALSE != $this->mSettings['debug']) {
       //     $error_text = '<div style="color:black;padding:5px;background-color:white;border:2px dotted red;">'.$type.': '.$errorMessage.' in <b>'.$file.'</b> on line '.$line.'</div>';
           // if (1 == $errorNumber or 256 == $errorNumber) {
             //       die($error_text);
               // } else {
                    if ($errorNumber != 2048) {
         //              echo ($error_text);
                   }
               // }
        } else {
           // $this->SendErrorEmail($type, $errorMessage, $file, $line, $context);
            if (FALSE == $this->mTechProblemsshown) {
             //   echo '<div style="color:black;padding:5px;background-color:white;border:2px dotted red;">We are currently experiencing technical difficulties. We will try to fix the problem as soon as possible. Sorry for the inconvenience.</div>';
                $this->mTechProblemsshown = TRUE;
            }
        }
    }


    function sendErrorEmail($type, $errorMessage, $file, $line, $context)
    {
        $from    =  $this->mSettings['email_from'];
        $subject =  $type.': '.$errorMessage;

        //  remove vars from context output
        $context = $this->_excludeVars($context);

        // timestamp for the error entry
        $timestamp = date("Y-m-d H:i:s (T)");

        $message = '<html><body><b>Message:</b> '.$errorMessage."<br>\n"
            .'<b>File:</b> '.$file."<br>\n"
            .'<b>Line:</b> '.$line."<br>\n"
            .'<b>Date:</b> '.$timestamp."<br>\n"
            .'<hr>'
            .'<b>Source Context:</b><br>'."\n"
            .$this->_getSourceContext($file, $line)
            .'<hr>'
            .'<b>Variable Context:</b><br>'."\n"
            .'<pre>';
        ob_start();
        print_r($context);
        $var_context = ob_get_contents();
        ob_end_clean();
        $var_context = htmlspecialchars($var_context, ENT_QUOTES);
        $message .= $var_context;
        $message .= '</pre></body></html>';

        $headers  =  "From: $from\r\n";
        $headers .= 'Content-Type: text/html; charset=UTF-8'."\r\n";
        $headers .=  "Reply-To: $from\r\n";
        $headers .=  "X-Mailer: PHP/".phpversion()."\r\n";

        // Send the error to the $settings['error_email'] email address
        mail($this->mSettings['error_email'], $subject, $message, $headers );
    }

    /**
     * Gets the lines of code where the error occurred.
     *
     */
    function _getSourceContext($file, $line)
    {
        if (FALSE == file_exists($file)) {
            //  check that file exists
            return "Context cannot be shown - ($file) does not exist";
        } elseif ((FALSE == is_int($line)) || ($line <= 0)) {
            //  check if line number is valid
            return "Context cannot be shown - ($line) is an invalid line number";
        } else {
            //  Get the source
            $source = highlight_file($file, TRUE);
            $lines = split('<br />', $source);
            $num_lines = count($lines); // Get the number of lines in the file
            //  Get line numbers
            $start = $line - $this->source_context_options['lines'] - 1;
            $finish = $line + $this->source_context_options['lines'];
            //  Get lines
            if ($start < 0) {
                $start = 0;
            }
            if ($start >= count($lines)) {
                $start = count($lines) -1;
            }
            for ($i = $start; $i < $finish; $i++) {
                //  highlight line in question
                if ($i == ($line -1)) {
                    $context_lines[] = '<div style="color:white; font-weight:bold;background-color:black;">' . ($i + 1) .
                        "\t" . strip_tags($lines[$line -1]) . '</div>';
                } else {
                    // Only try to show the line if there are really that
                    // many lines in the file
                    if ($i < $num_lines) {
                        $context_lines[] = '<b>' . ($i + 1) .
                            "</b>\t" . $lines[$i];
                    }
                }
            }
            return trim(join("<br>\n", $context_lines)) . "<br>\n";
        }
    }
}

// print '<h2>Error demo - start</h2>';
// $foo = & new ErrorHandler();
/* $foo->set_excludes(array(
    'HTTP_POST_VARS',
    'HTTP_GET_VARS',
    'HTTP_SERVER_VARS',
    'HTTP_COOKIE_VARS',
    'HTTP_ENV_VARS',
    'HTTP_POST_FILES',
    '_POST',
    '_GET',
    '_SERVER',
    '_COOKIE',
    '_ENV',
    '_FILES',
    '_REQUEST',
    'foo',
    ));
*/

// $foo->start_handler();
/*
$edsfe = 'sdfewer';
$fp = 'not_a_file_handle';
array (serewwdsfewer3);
print $sdfwewr;
$row = fgets($fp, 1024);
*/
// print '<h2>Error demo - finish</h2>';
?>