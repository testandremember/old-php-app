<?php
/**
 * Provides a way to interact with the database.
 *
 * Using a database class will keep us from having to change large amounts of
 * code if we ever switch database software.
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: DB.class.php 2206 2006-01-17 23:28:37Z elijahlofgren $
 * @package NiftyCMS
 */

class DB {

    /**
     * Connects to and selects the database.
     *
     * @param $settings array - An array of connect settings
     */
    static function connect($settings)
    {
        $link = mysql_connect($settings['db_server'], $settings['db_username'], $settings['db_password']);
        if (FALSE == $link) {
            trigger_error(mysql_errno().': '.mysql_error(), E_USER_ERROR);
        }
        $db_selected = mysql_select_db($settings['db_name']);
        if (FALSE == $db_selected) {
            trigger_error(mysql_errno().': '.mysql_error(), E_USER_ERROR);
        }
    }

    /**
     * Queries database.
     *
     * @param $query string - Query to query
     */
    static function query($query)
    {
        $query_result = mysql_query($query);
        if (FALSE == $query_result) {
            // echo $query;
            trigger_error(mysql_errno().': '.mysql_error().'<br />'."\n".'Query: '.$query, E_USER_ERROR);
        }
        return $query_result;
    }

    /**
     * Fetches and associative array of the query result
     *
     * @param $result - The database result to fetch the associative array from
     */
    static function fetchAssoc($result)
    {
        return mysql_fetch_assoc($result);
    }

    /**
     * Returns the number of rows in a result
     *
     * @param $result - The database result to fetch the number of
     * returned rows from
     */
    static function numRows($result)
    {
        return mysql_num_rows($result);
    }

    /**
     * Closes the database connection
     *
     */
    static function close()
    {
        return mysql_close();
    }

    /**
     * Makes a string or an array safe to put in a database query by
     * converting quotes to html entities
     *
     * $thing mixed - A string or array of strings to make safe to put in
     * a database query
     */
     static function makeSafe($thing)
     {
         $thing = DB::stripSlashes($thing);
         if (FALSE != is_array($thing)) {
            $escaped = array();

            foreach ($thing as $key => $value) {
                $escaped[$key] = DB::makeSafe($value);
            }
            return $escaped;
        }
        $thing = htmlspecialchars($thing, ENT_QUOTES);
        $thing = addslashes($thing);
        return $thing;

    }

    /**
     * Removes slashes from string data if neccessary so that the data is not backslashed
     *
     */
    static function stripSlashes($value)
    {
        if (FALSE != get_magic_quotes_gpc()) {
            if (FALSE != is_array($value)) {
                foreach ($value as $index => $val) {
                    $value[$index] = DB::stripSlashes($val);
                }
                return $value;
            } else {
                return stripslashes($value);
            }
        } else {
            return $value;
        }
    }

    
    /**
     * Adds slashes to string data if neccessary so that the data is safe to put
     * in a database query.
     *
     */
    static function addSlashes($value)
    {
        if (FALSE == get_magic_quotes_gpc()) {
            if (FALSE != is_array($value)) {
                foreach ($value as $index => $val) {
                    $value[$index] = DB::addSlashes($val);
                }
                return $value;
            } else {
                return addslashes($value);
            }
        } else {
            return $value;
        }
    }

    /**
     * Builds an insert query from an array of values and inserts the data.
     *
     */
    static function insert($table, $data)
    {
        // $data = DB::makeSafe($data);
        $query = 'INSERT INTO '.$table. ' (';
        $first_time = TRUE;
        foreach ($data as $key => $value ) {
            if (FALSE == $first_time) {
                $query .= ', ';
            } else {
                $first_time = FALSE;
            }
            $query .= $key;
        }
        $query .= ') VALUES (';
        $first_time = TRUE;
        foreach ($data as $key => $value ) {
            if (FALSE == $first_time) {
                $query .= ', ';
            } else {
                $first_time = FALSE;
            }
            $query .= '"'.$value.'"';
        }
        $query .= ');';
        return DB::query($query);
    }

    /**
     * Builds an update query from an array of values and updates the DB
     *
     */
    static function update($table, $data, $where, $limit = 1)
    {
        // $data = DB::makeSafe($data);
        $query = 'UPDATE '.$table. ' SET ';
        $first_time = TRUE;
        foreach ($data as $key => $value ) {
            if (FALSE == $first_time) {
                $query .= ', ';
            } else {
                $first_time = FALSE;
            }
            // If values from a multi-select box
            if (is_array($value)) {
                $new_value = '';
                $first_time2 = TRUE;
                foreach ($value as $key2 => $value2 ) {
                    if (FALSE == $first_time2) {
                        $new_value .= ',';
                    } else {
                        $first_time2 = FALSE;
                    }
                    $new_value .= $value2;
                }
                $value = $new_value;
            }

            $query .= '`'.$key.'`="'.$value.'"';
        }
        $query .= ' WHERE '.$where.' LIMIT '.$limit.';';
        // echo $query;
        return DB::query($query);
    }

    /**
     * Builds a select query from an array and selects data from the DB
     *
     */
    static function select($table, $columns, $options = '', $limit = 1)
    {
        $query = 'SELECT ';
        $first_time = TRUE;
        foreach ($columns as $key => $value) {
            if (FALSE == $first_time) {
                $query .= ', ';
            } else {
                $first_time = FALSE;
            }
            $query .= $value;
        }
        $query .= ' FROM '.$table;
        if ('' != $options) {
            $query .= ' '.$options;
        }
        if (0 != $limit) {
            $query .= ' LIMIT '.$limit.';';
        }
        return DB::query($query);
    }

    /**
     * Builds a DELETE query from an array and deletes data from the DB
     *
     */
    static function delete($table, $where, $limit = 1)
    {
        $query = 'DELETE FROM '.$table.' WHERE '.$where;
        if (0 != $limit) {
            $query .= ' LIMIT '.$limit.';';
        }
        return DB::query($query);
    }

    /**
     * Returns an array with a list of possible values for an ENUM or SET
     * column type.
     *
     */
    static function getColumnOptions($table, $column)
    {
        $query = 'SHOW COLUMNS FROM '.$table.' LIKE "'.$column.'"';
        $result = DB::query($query);
        $row = mysql_fetch_row($result);
        $options = explode(
            "','",
            preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $row[1])
        );
        foreach ($options AS $key => $value) {
            $title = str_replace('_', ' ', $value);
            $title = ucwords($title);
            $new_options[$value] = $title;
        }
        return $new_options;
    }

    /**
     * Gets the ID generated from the previous INSERT operation.
     *
     */
    static function insertId() {
        return mysql_insert_id();
    }

    /* Database Backup Utility 1.0 By Eric Rosebrock, http://www.phpfreaks.com
    Written: July 7th, 2002 12:59 AM

    If running from shell, put this above the <?php  "#! /usr/bin/php -q"  without the quotes!!!

    This script is dedicated to "Salk". You know who you are :)

    This script runs a backup of your database that you define below. It then gzips the .sql file
    and emails it to you or ftp's the file to a location of your choice.

    It is highly recommended that you leave gzip on to reduce the file size.

    You must chown the directory this script resides in to the same user or group your webserver runs
    in, or CHMOD it to writable. I do not recommend chmod 777 but it's a quick solution. If you can setup
    a cron, you can probably chown your directory!

    IMPORTANT!!! I recommend that you run this outside of your
    web directory, unless you manually want to run this script. If you do upload it inside your web
    directory source Tree($settings), I would at least apply Apache access control on that directory. You don't
    want people downloading your raw databases!

    This script is meant to be setup on a crontab and run on a weekly basis
    You will have to contact your system administrator to setup a cron tab for this script
    Here's an example crontab:

    0 0-23 * * * php /path/to/thisdirectory/dbsender.php > dev/null

    */
    static function backup($settings)
    {
        // Optional Options You May Optionally Configure

        $use_gzip = TRUE;  // Set to FALSE if you don't want the files sent in .gz format
        $remove_sql_file = TRUE; // Set this to TRUE if you want to remove the .sql file after gzipping. Yes is recommended.
        $remove_gzip_file = FALSE; // Set this to TRUE if you want to delete the gzip file also. I recommend leaving it to "no"

        // Full path to the backup directory. Do not use trailing slash!
        $savepath = dirname(__FILE__).'/../backups';
        $send_email = TRUE;  // Do you want this database backup sent to your email? Fill out the next 2 lines

        // $senddate = date("j F Y");
        $senddate = date('Y-m-d-Hi');

        // Subject in the email to be sent.
        $subject = 'MySQL DB Backup - '.$senddate;
        $message = 'Your MySQL database has been backed up and is attached to this email'; // Brief Message.

/*
        $use_ftp = "no"; // Do you want this database backup uploaded to an ftp server? Fill out the next 4 lines
        $ftp_server = ""; // FTP hostname
        $ftp_user_name = ""; // FTP username
        $ftp_user_pass = ""; // FTP password
        $ftp_path = "/public_html/backups/"; // This is the path to upload on your ftp server!
*/
        // Do not Modify below this line! It will void your warranty!

        $date = date('Y-m-d-Hi');
        $filename = $savepath.'/'.$settings['db_name'].'-'.$date.'.sql';
        // passthru("mysqldump --opt -h$dbhost -u$dbuser -p$dbpass $dbname >$filename");
        // passthru("mysqldump --opt -h$dbhost -u$dbuser -p$dbpass $dbname >$filename");
        // $passthru = "mysqldump -h $server -u $username -p$password --add-drop-table --all --quick --lock-tables --disable-keys --extended-insert -d $database >$filename";
        $passthru = 'mysqldump --opt'
                             .' -h '.$settings['db_server']
                             .' -u '.$settings['db_username']
                      // There must not be a space between -p and the password
                             .' -p'.$settings['db_password']
                      // There MUST be a space between the password and DB name
                             .' '.$settings['db_name'].' > '.$filename;
                             // echo $passthru;
        //  $passthru = "mysqldump -h $server -u $username -p$password --opt --tables -d $database >$filename";
        // echo $passthru;
        passthru($passthru);

        if (FALSE != $use_gzip) {
            $real_path = realpath($savepath);
            // echo '<br />df '.$real_path.'</br>';
            $zipline = "tar -czf ".$real_path.'/'.$settings['db_name'].'-'.$date.'_sql.tar.gz '.$real_path.'/'.$settings['db_name'].'-'.$date.'.sql';
            // $zipline = "tar -czf --directory=".$real_path.'  '.$database."-".$date."_sql.tar.gz "."$database-$date.sql";
            //   echo '<br />'.$zipline.'<br />';
            shell_exec($zipline);
        }
        // Remove the SQL file if needed
        if (FALSE != $remove_sql_file) {
            exec('rm -r -f '.$filename);
        }
        // If supposed to gzip the file
        if (FALSE != $use_gzip) {
            $filename2 = $savepath.'/'.$settings['db_name'].'-'.$date.'_sql.tar.gz';
        } else {
            $filename2 = $savepath.'/'.$settings['db_name'].'-'.$date.'.sql';
        }
        // If backing up to email address
        if (FALSE != $send_email) {
            $fileatt_type = filetype($filename2);
            //  echo $filename2;
            $fileatt_name = "".$settings['db_name']."-".$date."_sql.tar.gz";
            $headers = 'From: '.$settings['email_from'];
            // Read the file to be attached ('rb' = read binary)
            $file = fopen($filename2, 'rb');
            $data = fread($file, filesize($filename2));
            fclose($file);

            // Generate a boundary string
            $semi_rand = md5(time());
            $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

            // Add the headers for a file attachment
            $headers .= "\nMIME-Version: 1.0\n" ."Content-Type: multipart/mixed;\n" ." boundary=\"{$mime_boundary}\"";

            // Add a multipart boundary above the plain message
            $message = "This is a multi-part message in MIME format.\n\n" ."--{$mime_boundary}\n" ."Content-Type: text/plain; charset=\"iso-8859-1\"\n" ."Content-Transfer-Encoding: 7bit\n\n" .
            $message . "\n\n";

            // Base64 encode the file data
            $data = chunk_split(base64_encode($data));

            // Add file attachment to the message
            $message .= "--{$mime_boundary}\n" ."Content-Type: {$fileatt_type};\n" ." name=\"{$fileatt_name}\"\n" ."Content-Disposition: attachment;\n" ." filename=\"{$fileatt_name}\"\n" ."Content-Transfer-Encoding: base64\n\n" .
            $data . "\n\n" ."--{$mime_boundary}--\n";

            // Send the message
            $ok = mail($settings['backup_email'], $subject, $message, $headers);
            if (FALSE != $ok) {
                $output  = '<p>Database backup created and sent! ';
                $output .= 'File name '.$filename2.'</p>';
            } else {
                $output = '<p>Mail could not be sent. Sorry!</p>';
            }
        }
        /*
        if($use_ftp == "yes"){
            $ftpconnect = "ncftpput -u $ftp_user_name -p $ftp_user_pass -d debsender_ftplog.log -e dbsender_ftplog2.log -a -E -V $ftp_server $ftp_path $filename2";
            shell_exec($ftpconnect);
            echo "<h4><center>$filename2 Was created and uploaded to your FTP server!</center></h4>";

        }
        */
        if ('yes' == $remove_gzip_file) {
            exec("rm -r -f $filename2");
        }
        return $output;
    }
}
?>