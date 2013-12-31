<?php
/**
 * Documentation for NiftyCMS.
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1102 2004-10-15 04:07:22Z elijah $
 * @package NiftyCMS
 * @subpackage admin_docs
 */
require_once '../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();
echo $output;
?>
<h2>PHP Code Documentation</h2>
<p>Coming Later</p>
<h2>Standards used</h2>
<ul>
<li><a href="http://pear.php.net/manual/en/standards.php">PEAR Coding Standards</a>:<br />
The most important of the two PHP standards we try follow
</li>
<li>
<a href="http://alltasks.net/code/php_coding_standard.html">PHP Coding Standard</a>:<br />
PHP coding conventions that we try to follow
</li>
<li>
<a href="http://www.w3.org/TR/xhtml1/"> XHTML&trade; 1.0 The
 Extensible HyperText Markup Language (Second Edition)</a>:
<br />This specification defines the Second Edition of XHTML 1.0, a
 reformulation of HTML 4 as an XML 1.0 application, and three DTDs
 corresponding to the ones defined by HTML 4.
</li>
<li>
<a href="http://www.w3.org/TR/REC-CSS2/">Cascading Style Sheets, level 2 - CSS2 Specification</a>: <br />
This specification defines Cascading Style Sheets, level 2 (CSS2). CSS2 is a style sheet language that allows authors and users to attach style (e.g., fonts, spacing, and aural cues) to structured documents (e.g., HTML documents and XML applications).
</li>
</ul>
<?php
echo $Page->showFooter();
?>