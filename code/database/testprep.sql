-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 31, 2013 at 04:13 AM
-- Server version: 5.6.12-log
-- PHP Version: 5.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `testprep`
--
CREATE DATABASE IF NOT EXISTS `testprep` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `testprep`;

-- --------------------------------------------------------

--
-- Table structure for table `admin_event_log`
--

CREATE TABLE IF NOT EXISTS `admin_event_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `date` varchar(10) DEFAULT NULL,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `referrer` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `host` varchar(255) NOT NULL DEFAULT '',
  `ua` varchar(255) NOT NULL DEFAULT '',
  `member_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `more_info` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `admin_hit_log`
--

CREATE TABLE IF NOT EXISTS `admin_hit_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` varchar(10) DEFAULT NULL,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `referrer` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `host` varchar(255) NOT NULL DEFAULT '',
  `ua` varchar(255) NOT NULL DEFAULT '',
  `session_id` varchar(32) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `admin_members`
--

CREATE TABLE IF NOT EXISTS `admin_members` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL DEFAULT '',
  `login_name` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `account_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `first_name` varchar(255) NOT NULL DEFAULT '',
  `last_name` varchar(255) NOT NULL DEFAULT '',
  `support_notify` tinyint(1) unsigned NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=67 ;

--
-- Dumping data for table `admin_members`
--

INSERT INTO `admin_members` (`id`, `email`, `login_name`, `password`, `account_active`, `first_name`, `last_name`, `support_notify`) VALUES
(1, 'admin@example.com', 'admin', '144817074ba54ef975bf4758b98e6cec', 1, 'Admin', 'User', 1);

-- --------------------------------------------------------

--
-- Table structure for table `admin_member_sessions`
--

CREATE TABLE IF NOT EXISTS `admin_member_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `user_agent` varchar(32) NOT NULL DEFAULT '',
  `member_id` mediumint(9) NOT NULL DEFAULT '0',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `last_action` varchar(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `admin_pages`
--

CREATE TABLE IF NOT EXISTS `admin_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `query_string` varchar(255) NOT NULL DEFAULT '',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `heading` varchar(255) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `link_title` varchar(255) NOT NULL DEFAULT '',
  `show_on_menu` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`uri`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=93 ;

--
-- Dumping data for table `admin_pages`
--

INSERT INTO `admin_pages` (`id`, `uri`, `query_string`, `parent_id`, `title`, `description`, `heading`, `content`, `link_title`, `show_on_menu`) VALUES
(1, '/', '', 0, '', '', '', '', 'Home', 1),
(2, '/admin/', '', 1, 'Admin Panel', '', 'Admin Panel', '', 'Admin Panel', 1),
(3, '/admin/pages/', '', 2, 'Pages', '', 'Pages', '', 'Pages', 1),
(16, '/admin/members/cp/edit-member-account.php', '', 82, 'Edit Member Account', '', 'Edit Member Account', '', 'Edit Account', 0),
(6, '/admin/members/', '', 2, 'Member Admin', '', 'Member Admin', '', 'Members', 1),
(7, '/admin/questions/', '', 2, 'Test Prep Questions', '', 'Test Prep Questions', '', 'Test Prep Questions', 1),
(8, '/admin/questions/add-question.php', '', 7, 'Add Question', '', 'Add Question', '', 'Add Question', 1),
(11, '/admin/pages/edit-page.php', '', 71, 'Edit Page', '', 'Edit Page', '', 'Edit Page', 0),
(12, '/admin/pages/add-page.php', '', 3, 'Add Page', '', 'Add Page', '', 'Add Page', 0),
(17, '/admin/documentation/', '', 2, 'Documentation', '', 'Documentation', '', 'Documentation', 0),
(18, '/admin/events/', '', 2, 'Event Messages', '', 'Event Messages', '', 'Event Messages', 1),
(19, '/admin/stats/', '', 2, 'Statistics', '', 'Statistics', '', 'Statistics', 0),
(20, '/admin/events/add-event.php', '', 18, 'Add Event', '', 'Add Event', '', 'Add', 1),
(23, '/admin/questions/edit-question.php', '', 7, 'Edit a Question', '', 'Edit a Question', '', 'Edit a Question', 0),
(24, '/admin/questions/categories/', '', 7, 'Question Categories', '', 'Question Categories', '', 'Categories', 1),
(25, '/admin/questions/categories/edit-category.php', '', 24, 'Edit Question Category', '', 'Edit Question Category', '', 'Edit', 0),
(26, '/admin/questions/categories/add-category.php', '', 24, 'Add Question Category', '', 'Add Question Category', '', 'Add', 1),
(81, '/admin/members/cp/delete-account.php', '', 82, 'Delete Member Account', '', 'Delete Member Account', '', 'Delete Member Account', 0),
(78, '/admin/stats/events.php?type=', '{type}', 34, '', '', '', '', '''{type}'' Events', 1),
(32, '/admin/pages/child-delete.php', '', 3, 'Delete All Child Pages', '', 'Delete All Child Pages', '', 'Child Delete', 1),
(33, '/bugs/', '', 2, 'Bugs', '', 'Bugs', '', 'Bugs', 0),
(34, '/admin/stats/events.php', '', 19, 'Events', '', '100 Most Recent Events', '', 'Events', 1),
(35, '/admin/stats/referrers.php', '', 19, 'Referrers', '', 'Top Referrers', '', 'Referrers', 1),
(36, '/admin/members/cp/view-info.php', '', 82, 'View Member Info', '', 'View Member Info', '', 'View Member Info', 0),
(37, '/admin/stats/hits.php', '', 19, 'Page Views', '', '100 Most Recent Page Views', '', 'Page Views', 1),
(38, '/admin/backups/', '', 2, 'Backups', '', 'Backups', '', 'Backups', 0),
(39, '/admin/backups/backup-database.php', '', 38, 'Backup Database', '', 'Backup Database', '', 'Backup Database', 1),
(40, '/admin/stats/hit-info.php', '', 79, 'Page View Details', '', 'Page View Details', '', 'Page View Details', 0),
(41, '/admin/stats/event-info.php', '', 78, 'View Event Details', '', 'View Event Details', '', 'View Event Details', 0),
(42, '/admin/members/cp/setup-account.php', '', 82, 'Manually Setup Member Account', '', 'Manually Setup Member Account', '', 'Manually Setup Member Account', 0),
(47, '/admin/members/admin/', '', 6, 'Admin Panel Members', '', 'Admin Panel Members', '', 'Admin Panel', 1),
(48, '/admin/members/admin/add-account.php', '', 47, 'Add Admin Member', '', 'Add Admin Member', '', 'Add', 1),
(49, '/admin/members/admin/password.php', '', 47, 'Change Admin Member Password', '', 'Change Admin Member Password', '', 'Change Admin Member Password', 0),
(50, '/admin/members/admin/edit-account.php', '', 47, 'Edit Admin Member Account', '', 'Edit Admin Member Account', '', 'Edit Account', 0),
(51, '/admin/members/admin/email.php', '', 47, 'Change Admin Member Email Address', '', 'Change Admin Member Email Address', '', 'Change Admin Member Email Address', 0),
(67, '/admin/support/ticket.php', '', 54, 'View Support Ticket', '', 'View Support Ticket', '', 'View Support Ticket', 0),
(53, '/admin/members/cp/edit-member-page-number.php', '', 82, 'Edit the page number of a member''s page', '', 'Edit the page number of a member''s page', '', 'Edit page number of member page', 0),
(54, '/admin/support/', '', 2, 'Support', '', 'Support', '', 'Support', 0),
(55, '/admin/pages/templates/', '', 3, 'Page Templates', '', 'Page Templates', '', 'Templates', 1),
(56, '/admin/pages/templates/add-template.php', '', 55, 'Add Page Template', '', 'Add Page Template', '', 'Add', 1),
(57, '/admin/pages/templates/edit-template.php', '', 55, 'Edit Page Template', '', 'Edit Page Template', '', 'Edit', 0),
(58, '/admin/pages/types/', '', 3, 'Page Types', '', 'Page Types', '', 'Types', 1),
(59, '/admin/pages/types/add-type.php', '', 58, 'Add Page Type', '', 'Add Page Type', '', 'Add', 1),
(60, '/admin/pages/types/edit-type.php', '', 58, 'Edit Page Type', '', 'Edit Page Type', '', 'Edit Page Type', 0),
(61, '/admin/pages/subtypes/', '', 3, 'Page Subtypes', '', 'Page Subtypes', '', 'Subtypes', 1),
(62, '/admin/pages/subtypes/add-subtype.php', '', 61, 'Add Page Subtype', '', 'Add Page Subtype', '', 'Add', 1),
(63, '/admin/pages/subtypes/edit-subtype.php', '', 61, 'Edit Page Subtype', '', 'Edit Page Subtype', '', 'Edit Page Subtype', 0),
(64, '/admin/log-out.php', '', 2, 'Log Out', '', 'Log Out', '', 'Log Out', 0),
(79, '/admin/stats/hits.php?type=', '{type}', 37, '', '', '', '', '''{type}'' Pages', 1),
(80, '/admin/pages/delete-children.php', '', 32, '', '', '', '', 'Delete all child pages.', 0),
(69, '/admin/support/close-ticket.php', '', 54, 'Close Ticket', '', 'Close Ticket', '', 'Close Ticket', 0),
(70, '/admin/members/types/', '', 6, 'Member Types', '', 'Member Types', '', 'Types', 1),
(71, '/admin/pages/?type=', '{type}', 3, '', '', '', '', '''{type}'' Pages', 1),
(72, '/admin/members/types/add-type.php', '', 70, 'Add Member Type', '', 'Add Member Type', '', 'Add', 1),
(73, '/admin/members/types/edit-type.php', '', 70, 'Edit Member Type', '', 'Edit Member Type', '', 'Edit Member Type', 0),
(74, '/admin/members/cp/make-member-folder.php', '', 82, '', '', '', '', 'Make members folder', 0),
(75, '/admin/events/edit-event.php', '', 18, 'Edit Event', '', 'Edit Event', '', 'Edit Event', 0),
(76, '/admin/members/cp/login-to-members-cp.php', '', 82, '', '', '', '', 'Login to members cp', 0),
(77, '/admin/members/cp/resend-welcome-email.php', '', 82, '', '', '', '', 'Resend Welcome Email', 0),
(82, '/admin/members/cp/', '', 6, 'Control Panel Members', '', 'Control Panel Members', '', 'Control Panel', 1),
(83, '/admin/members/cp/deactivate-member-account.php', '', 82, '', '', '', '', 'Deactive Member Account', 0),
(84, '/admin/members/cp/activate-member-account.php', '', 82, '', '', '', '', 'Activate Member Account', 0),
(85, '/admin/members/cp/deactivate-member-page.php', '', 82, '', '', '', '', 'Deactive Member Page', 0),
(86, '/admin/members/cp/activate-member-page.php', '', 82, '', '', '', '', 'Activate Member Page', 0),
(87, '/admin/members/admin/delete-account.php', '', 47, '', '', '', '', 'Delete Admin Account', 0),
(88, '/admin/members/admin/deactivate-admin-account.php', '', 47, '', '', '', '', 'Deactive Admin Account', 0),
(89, '/admin/members/admin/activate-admin-account.php', '', 47, '', '', '', '', 'Activate Admin Account', 0),
(90, '/admin/members/cp/delete-member-page.php', '', 82, '', '', '', '', 'Delete Member Page', 0),
(91, '/admin/payments/', '', 2, 'PayPal Payments', '', 'PayPal Payments', '', 'Payments', 0),
(92, '/admin/payments/transaction-info.php', '', 91, 'View PayPal IPN Info', '', 'View PayPal IPN Info', '', 'View Info', 0);

-- --------------------------------------------------------

--
-- Table structure for table `cp_event_log`
--

CREATE TABLE IF NOT EXISTS `cp_event_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `date` varchar(10) DEFAULT NULL,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `referrer` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `host` varchar(255) NOT NULL DEFAULT '',
  `ua` varchar(255) NOT NULL DEFAULT '',
  `member_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `more_info` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cp_hit_log`
--

CREATE TABLE IF NOT EXISTS `cp_hit_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` varchar(10) DEFAULT NULL,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `referrer` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `host` varchar(255) NOT NULL DEFAULT '',
  `ua` varchar(255) NOT NULL DEFAULT '',
  `session_id` varchar(32) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;


-- --------------------------------------------------------

--
-- Table structure for table `cp_members`
--

CREATE TABLE IF NOT EXISTS `cp_members` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `folder_name` varchar(255) NOT NULL DEFAULT '',
  `domain_name` varchar(255) NOT NULL DEFAULT '',
  `plan` smallint(5) unsigned NOT NULL DEFAULT '0',
  `duration_years` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `num_pages` smallint(5) unsigned NOT NULL DEFAULT '0',
  `num_pictures` smallint(5) unsigned NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL DEFAULT '',
  `login_name` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `account_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `last_paid` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `first_name` varchar(255) NOT NULL DEFAULT '',
  `last_name` varchar(255) NOT NULL DEFAULT '',
  `address1` varchar(255) NOT NULL DEFAULT '',
  `address2` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL DEFAULT '',
  `state` varchar(255) NOT NULL DEFAULT '',
  `zip` varchar(10) NOT NULL DEFAULT '',
  `night_phone` varchar(12) NOT NULL DEFAULT '0',
  `day_phone` varchar(12) NOT NULL DEFAULT '0',
  `signup_id` varchar(32) NOT NULL DEFAULT '',
  `account_setup` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `creation_date` varchar(10) NOT NULL DEFAULT '',
  `dir_listing` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=82 ;


--
-- Table structure for table `cp_member_pages`
--

CREATE TABLE IF NOT EXISTS `cp_member_pages` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `page_number` smallint(5) unsigned NOT NULL DEFAULT '0',
  `page_title` varchar(255) NOT NULL DEFAULT '',
  `link_title` varchar(255) NOT NULL DEFAULT '',
  `header_text` varchar(255) NOT NULL DEFAULT '',
  `body_one` longtext NOT NULL,
  `body_two` longtext NOT NULL,
  `template` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `text_color` varchar(25) NOT NULL DEFAULT '',
  `link_color` varchar(25) NOT NULL DEFAULT '',
  `visited_link_color` varchar(25) NOT NULL DEFAULT '',
  `background_color` varchar(25) NOT NULL DEFAULT '',
  `background_image` varchar(255) NOT NULL DEFAULT '',
  `logo` varchar(255) NOT NULL DEFAULT '',
  `show_counter` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `hits` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `active` smallint(1) unsigned NOT NULL DEFAULT '1',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=192 ;

--
-- Table structure for table `cp_member_pictures`
--

CREATE TABLE IF NOT EXISTS `cp_member_pictures` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `thumbnail_name` varchar(255) NOT NULL DEFAULT '',
  `page_number` smallint(5) unsigned NOT NULL DEFAULT '0',
  `short_description` varchar(255) NOT NULL DEFAULT '',
  `long_description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`member_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=404 ;

-- --------------------------------------------------------

--
-- Table structure for table `cp_member_sessions`
--

CREATE TABLE IF NOT EXISTS `cp_member_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `user_agent` varchar(32) NOT NULL DEFAULT '',
  `member_id` mediumint(9) NOT NULL DEFAULT '0',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `last_action` varchar(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cp_pages`
--

CREATE TABLE IF NOT EXISTS `cp_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `query_string` varchar(255) NOT NULL DEFAULT '',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `heading` varchar(255) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `link_title` varchar(255) NOT NULL DEFAULT '',
  `show_on_menu` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`uri`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=38 ;

--
-- Dumping data for table `cp_pages`
--

INSERT INTO `cp_pages` (`id`, `uri`, `query_string`, `parent_id`, `title`, `description`, `heading`, `content`, `link_title`, `show_on_menu`) VALUES
(1, '/cp/', '', 7, 'Control Panel', '', 'Control Panel', '', 'Control Panel', 1),
(31, '/cp/support/ticket.php', '', 30, 'View Support Ticket', '', 'View Support Ticket', '', 'View Support Ticket', 1),
(28, '/cp/website/add-directory-listing.php', '', 29, 'List your website in the directory', '', 'List Website In Directory', '', 'List website in directory', 1),
(16, '/cp/website/page/logo/', '', 32, 'Upload Logo', '', 'Upload Logo', '', 'Upload Logo', 1),
(30, '/cp/support/', '', 1, 'Support', '', 'Support', '', 'Support', 1),
(7, '/', '', 0, '', '', '', '', 'Home', 1),
(10, '/cp/account/email.php', '', 15, 'Change Email Address', '', 'Change Email Address', '', 'Change Email Address', 1),
(11, '/cp/account/password.php', '', 15, 'Edit Password', '', 'Edit Password', '', 'Edit Password', 1),
(12, '/cp/account/address.php', '', 15, 'Update Address Information', '', 'Update Address Information', '', 'Update Address Information', 1),
(14, '/cp/website/page/titles-text.php', '', 32, 'Edit Web Page Titles and Text', '', 'Edit Titles and Text', '', 'Edit Titles and Text', 1),
(15, '/cp/#account', '', 1, 'Your Account', '', 'Your Account', '', 'Your Account', 1),
(17, '/cp/website/page/pictures/edit-picture.php', '', 35, 'Edit Picture', '', 'Edit Picture', '', 'Edit Picture', 1),
(35, '/cp/website/page/pictures/', '?page={page}', 32, 'Pictures', '', 'Pictures', '', 'Pictures', 1),
(20, '/cp/website/page/template/', '?page={page}', 32, 'Choose Template Category', '', 'Choose Template Category', '', 'Choose Template Category', 1),
(21, '/cp/website/page/template/category.php', '?page={page}&amp;category={category}', 20, 'Choose Template', '', 'Choose Template', '', 'Choose Template', 1),
(22, '/cp/website/page/colors.php', '', 32, 'Page Colors', '', 'Page Colors', '', 'Page Colors', 1),
(24, '/cp/website/page/pictures/upload-multiple-pictures.php', '', 35, 'Upload Multiple Pictures', '', 'Upload Multiple Pictures', '', 'Upload Multiple Pictures', 1),
(25, '/cp/website/page/template/template.php', '?page={page}', 21, 'Template Preview', '', 'Template Preview', '', 'Template Preview', 1),
(27, '/cp/website/page/options.php', '', 32, 'Page Options', '', 'Page Options', '', 'Page Options', 1),
(29, '/cp/#website', '', 1, 'Website', '', 'Website', '', 'Website', 1),
(32, '/cp/#page', '{page}', 29, 'Page', '', 'Page', '', 'Page {page}', 1),
(36, '/cp/log-out.php', '', 1, '', '', '', '', 'Log Out', 0),
(37, '/cp/website/edit-directory-listing.php', '', 29, 'Edit Directory Listing', '', 'Edit Directory Listing', '', 'Edit Directory Listing', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cp_password_resets`
--

CREATE TABLE IF NOT EXISTS `cp_password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reset_key` char(12) NOT NULL DEFAULT '',
  `member_id` mediumint(9) NOT NULL DEFAULT '0',
  `expires` char(10) DEFAULT NULL,
  PRIMARY KEY (`reset_key`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cp_templates`
--

CREATE TABLE IF NOT EXISTS `cp_templates` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `category` smallint(5) unsigned NOT NULL DEFAULT '0',
  `folder` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `thumbnail_image` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

--
-- Dumping data for table `cp_templates`
--

INSERT INTO `cp_templates` (`id`, `category`, `folder`, `title`, `image`, `thumbnail_image`) VALUES
(4, 1, 'brown_pipe', 'Plumbing', '43630008.jpg', 'thumb_43630008.jpg'),
(1, 4, 'basic', 'Basic', '43630019.jpg', 'thumb_43630019.jpg'),
(5, 2, 'bricks', 'Bricks', 'sitelogo.png', ''),
(26, 3, 'rain', 'Rain', 'binary.png', 'binary.png'),
(14, 3, 'wood', 'Wood', 'wood.jpg', 'thumb_wood.jpg'),
(25, 2, 'bricks2', 'Bricks Template', 'bricks.gif', 'bricks.gif'),
(30, 8, 'landscaping', 'Landscaping', 'demoimg_water.jpg', 'thumb_demoimg_water.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `cp_template_categories`
--

CREATE TABLE IF NOT EXISTS `cp_template_categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `cp_template_categories`
--

INSERT INTO `cp_template_categories` (`id`, `title`) VALUES
(1, 'Plumbing'),
(2, 'Masonry'),
(3, 'Construction'),
(4, 'General'),
(8, 'Landscaping');

-- --------------------------------------------------------

--
-- Table structure for table `dir_pages_jobs`
--

CREATE TABLE IF NOT EXISTS `dir_pages_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `heading` varchar(255) NOT NULL DEFAULT '',
  `link_title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`uri`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `dir_pages_jobs`
--

INSERT INTO `dir_pages_jobs` (`id`, `uri`, `title`, `heading`, `link_title`) VALUES
(1, 'drywall/', 'Drywall', 'Drywall', 'Drywall'),
(2, 'carpentry/', 'Carpentry', 'Carpentry', 'Carpentry'),
(3, 'exterminator/', 'Exterminator', 'Exterminator', 'Exterminator'),
(4, 'masonry/', 'Masonry', 'Masonry', 'Masonry'),
(5, 'remodeling/', 'Remodeling', 'Remodeling', 'Remodeling'),
(6, 'handyman/', 'Handyman', 'Handyman', 'Handyman'),
(7, 'painting/', 'Painting', 'Painting', 'Painting'),
(8, 'roofing/', 'Roofing', 'Roofing', 'Roofing'),
(9, 'electrician/', 'Electrician', 'Electrician', 'Electrician'),
(10, 'hvac/', 'Heating, Ventilation & AC', 'Heating, Ventilation & AC', 'Heating, Ventilation & AC'),
(11, 'plumbing/', 'Plumbing', 'Plumbing', 'Plumbing'),
(12, 'siding_windows/', 'Siding & Windows', 'Siding & Windows', 'Siding & Windows'),
(13, 'excavation/', 'Excavation', 'Excavation', 'Excavation'),
(14, 'pool/', 'Pools', 'Pools', 'Pools'),
(15, 'misc/', 'Miscellaneous', 'Miscellaneous', 'Miscellaneous'),
(16, 'landscaping/', 'Landscaping', 'Landscaping', 'Landscaping');

-- --------------------------------------------------------

--
-- Table structure for table `event_messages`
--

CREATE TABLE IF NOT EXISTS `event_messages` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `type` enum('success','error') NOT NULL DEFAULT 'success',
  `title` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=95 ;

--
-- Dumping data for table `event_messages`
--

INSERT INTO `event_messages` (`id`, `type`, `title`, `message`) VALUES
(1, 'success', 'Page Titles and Text Updated', 'Your page titles and text have been updated.'),
(2, 'success', 'Picture Uploaded', 'Your picture was successfully uploaded.'),
(4, 'error', 'File was not uploaded.', 'File was not uploaded.'),
(5, 'success', 'Log Out', 'You have successfully logged out.'),
(6, 'error', 'Address Update Problem', 'There were problems updating your address information. Please correct the errors below and try again.'),
(7, 'success', 'Address Info Update', 'Your address information was successfully updated.'),
(8, 'error', 'Problem changing password', 'There were problems changing your password. Please correct the errors below and try again.'),
(9, 'success', 'Password successfully changed', 'Your account password was successfully changed.'),
(10, 'error', '404 Error', 'Error 404 Not Found'),
(11, 'error', 'Unable to retrieve registration data', 'Unable to retrieve registration information from database. Please contact support.'),
(12, 'error', 'Page not found in database', 'Your web page was not found in the database.'),
(13, 'error', 'Invalid Phone Number', 'Phone Number must be in 000-000-0000 format.'),
(14, 'error', 'Manditory Form Field', 'You must fill in this field.'),
(15, 'error', 'Manditory Drop-down box', 'You must choose an option from the drop-down box.'),
(16, 'error', 'Invalid Email', 'You entered an invalid email address.'),
(17, 'error', 'Invalid zip code format', 'Zip code must be in 12345 or 12345-1234 format'),
(18, 'error', 'Equal Fields', 'This value must match the previous one.'),
(19, 'error', 'Invalid Password Format', 'Your password must not contain any punctuation or spaces.'),
(20, 'error', 'Invalid Password Length', 'Your password must be at least 6 characters long and shorter than 50 characters.'),
(21, 'error', 'Invalid Folder (subdomain) Name', 'Your subdomain name must contain only letters, numbers, and dashes.'),
(22, 'error', 'Folder (subdomain) name too long', 'Your subdomain name must not be more than 100 characters long.'),
(23, 'error', 'Email not in Database', 'That email address is not in our database.'),
(24, 'error', 'Incorrect Password', 'You entered an incorrect password.'),
(25, 'error', 'Folder already taken', 'That folder is already taken. Please choose another name.'),
(26, 'error', 'Email address taken', 'An account with that email address already exists. Please use a different email addresss.'),
(27, 'error', 'Incorrect current password', 'You did not enter your current password correctly.'),
(28, 'error', 'Email not in database - reset password', 'That email address does not exist in our database. If you continue to have problems please contact support.'),
(29, 'success', 'Email Address Change', 'Your email address was successfully changed.'),
(30, 'success', 'Logo Deletion', 'Your logo was sucessfully deleted.'),
(31, 'success', 'Password Reset Success', 'Your password was successfully reset.'),
(32, 'success', 'Session Timeout', 'Your session has timed out. Please log in again.'),
(33, 'success', 'No session cookie', 'No session cookie was found on your computer. Most likely your session has timed out. If you continue to encounter this error, please make sure that cookies are enabled in your brower settings.'),
(34, 'success', 'Page Colors Updated', 'Your web page colors have been updated.'),
(35, 'success', 'Template Chosen', 'Your page template has been updated.'),
(36, 'success', 'Successful Payment', 'Thank you for your payment. Your transaction has been completed, and a receipt for your purchase has been emailed to you. You may log into your account at <a href="https://www.paypal.com" rel="external">https://www.paypal.com</a> to view details of this transaction.'),
(37, 'error', 'Account Inactive', 'Your account is not active. Please make sure that you have completed all the registration steps. If you continue to encounter this error please contact support.'),
(38, 'success', 'Successful Login', 'You have successfully logged in.'),
(39, 'error', 'IPN Error', 'An IPN error has occured.'),
(40, 'success', 'Successful IPN', 'A successful IPN has been completed.'),
(41, 'success', 'Picture Description Updated', 'Your picture''s description was successfully updated.'),
(42, 'error', 'Invalid User Agent', 'Your browser supplied incorrect credentials. Please log in again.'),
(43, 'success', 'Picture Deleted from DB', 'Your picture was successfully deleted.'),
(44, 'success', 'Picture Deleted', 'Your picture was successfully deleted.'),
(45, 'success', 'Thumbnail deleted', 'Your picture thumbnail was deleted.'),
(46, 'error', 'Logo Upload Failed', 'Logo was not uploaded.'),
(47, 'success', 'Logo Uploaded', 'Your logo was successfully uploaded.'),
(48, 'success', 'Old Logo Deleted', 'Your old logo was deleted.'),
(49, 'error', '403 Forbidden Error', 'You don''t have permission to access this file or folder.'),
(50, 'error', 'Error sending password reset email', 'There was a problem sending the email. If you continue to have problems please contact support.'),
(51, 'error', 'Invalid password reset email', 'You did not enter a valid email address.'),
(52, 'success', 'Passord reset email sent', 'An email has been sent with instructions on how to reset your password.'),
(53, 'success', 'Multiple Pictures Uploaded', 'Your pictures were successfully uploaded.'),
(54, 'error', 'Radio Button Not Chosen', 'You must choose an option.'),
(55, 'error', 'Invalid password reset key.', 'Your password reset key is invalid or has expired.'),
(58, 'success', 'Admin Account Created', 'You have successfully created a new Admin Panel Account.'),
(59, 'success', 'Page options changed', 'You have successfully changed your page options.'),
(60, 'error', 'Unable to retrieve page data', 'Unable to retrieve page data from database.'),
(61, 'error', 'Page Inactive', 'Sorry, this page has been deactivated.'),
(62, 'error', 'Checkbox must be checked', 'This box must be checked.'),
(63, 'error', 'Invalid Number', 'You entered an invalid number.'),
(64, 'error', 'Non-Alphanumeric Input', 'This field must contain only letters and numbers.'),
(65, 'error', 'Invalid login id', 'That login name or customer number does not exist in our database.'),
(66, 'error', 'Login Name Taken', 'That login name is already in use. Please choose a different login name.'),
(67, 'error', 'Picture upload limit reached', 'Sorry, but your picture upload limit has been reached. Please go back and delete some current pictures in order to upload new ones.'),
(68, 'success', 'Directory Listing Added', 'You have succesfully added your website to the directory.'),
(69, 'success', 'Directory Listing Updated', 'You have successfully updated your directory listing.'),
(70, 'success', 'Deleted directory Listing.', 'You have successfully deleted your directory listing.'),
(71, 'error', 'Form Input Problem', 'There were problems with your form input. <br />Please correct the errors below and try again.'),
(72, 'success', 'Support Ticket Reply', 'Thank you for your response. We will try to respond to you as soon as possible.'),
(73, 'success', 'Question Deleted', 'The question was successfully removed from the database.'),
(74, 'success', 'Question Updated', 'The question was successfully updated.'),
(78, 'success', 'Event Updated', 'The event message was successfully updated.'),
(76, 'success', 'Event Deleted', 'The event was successfully deleted.'),
(77, 'success', 'Event Added', 'The event was successfully added to the database.'),
(79, 'success', 'Support Ticket Opened', 'Your support request has been successfully been submitted. We will try to respond as soon as possible.'),
(80, 'success', 'Member Account Edited', 'You have successfully updated the members account.'),
(81, 'error', 'Site Already Listed', 'Error: your site is already listed in the directory. If you continue to get this error please contact support.'),
(82, 'success', 'Child Pages Deleted', 'The child pages were successfully deleted.'),
(83, 'success', 'Support Ticket Deleted', 'The support ticket was successfully deleted.'),
(84, 'success', 'Successful CP account deletion', 'The account was successfully removed from the database.'),
(85, 'error', 'Unable to fetch ticket data', 'Unable to fetch ticket data from the database.'),
(86, 'success', 'Welcome email re-sent', 'The welcome email was successfully re-sent.'),
(87, 'success', 'Member folder created', 'The members folder was successfully created.'),
(88, 'success', 'Account Activated', 'The account was succesfully activated.'),
(89, 'success', 'Account  de-activated', 'The account was successfully de-activated.'),
(90, 'success', 'Admin account deleted', 'The Admin account was successfully deleted.'),
(91, 'success', 'Member page activated', 'The member page was successfully activated.'),
(92, 'success', 'Member page de-activated', 'The member page was successfully de-activated.'),
(93, 'error', 'Tried to delete nonexistant file', 'Member->deleteImage() was called on a file that did not exist.'),
(94, 'success', 'Question successfully added', 'The question was successfully added to the database.');

-- --------------------------------------------------------

--
-- Table structure for table `guest_event_log`
--

CREATE TABLE IF NOT EXISTS `guest_event_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `date` varchar(10) DEFAULT NULL,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `referrer` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `host` varchar(255) NOT NULL DEFAULT '',
  `ua` varchar(255) NOT NULL DEFAULT '',
  `member_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `more_info` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `member_types`
--

CREATE TABLE IF NOT EXISTS `member_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `can_login` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `has_website` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `member_types`
--

INSERT INTO `member_types` (`id`, `name`, `title`, `description`, `can_login`, `has_website`) VALUES
(1, 'admin', 'Administrator', 'This kind of member has complete control over the entire site.', 1, 0),
(2, 'cp', 'Control Panel (Customers)', 'These members are the ones that use the Control Panel to create their pages.', 1, 1),
(3, 'guest', 'Guest', 'Used when the member type is unknown, not set, or not available.', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `page_authors`
--

CREATE TABLE IF NOT EXISTS `page_authors` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `about` varchar(255) NOT NULL DEFAULT '',
  `picture` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `page_subtypes`
--

CREATE TABLE IF NOT EXISTS `page_subtypes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subtype_action` enum('none','show_child_table','show_child_list','show_description_list') NOT NULL DEFAULT 'none',
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `menu_title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `page_subtypes`
--

INSERT INTO `page_subtypes` (`id`, `subtype_action`, `name`, `title`, `description`, `menu_title`) VALUES
(1, 'none', 'home', 'Home', 'The homepage of the website', 'Choose a State'),
(2, 'none', 'state', 'State', 'Shows a map of a state', 'Choose County'),
(3, 'show_description_list', 'sitemap', 'Site Map', 'Shows a sitemap.', ''),
(4, 'show_child_list', 'directory', 'Directory Page', 'Shows a list of it&#039;s subpages', ''),
(5, 'none', 'article', 'Article', 'An Article', '');

-- --------------------------------------------------------

--
-- Table structure for table `page_templates`
--

CREATE TABLE IF NOT EXISTS `page_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `header` text NOT NULL,
  `footer` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `page_templates`
--

INSERT INTO `page_templates` (`id`, `name`, `description`, `header`, `footer`) VALUES
(7, 'testprep', 'Test Prep Pages', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\r\n<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">\r\n<head>\r\n<title>{title}</title>\r\n<meta name="robots" content="noindex" />\r\n<meta name="description" content="{description}" />\r\n<link rel="stylesheet" href="{inc_uri}/testprep.css" type="text/css" media="all" />\r\n<script type="text/javascript" src="{inc_uri}/links-forms.js"></script>\r\n</head>\r\n<body>\r\n<h1>{heading}</h1>\r\n{content}', '</body>\r\n</html>\r\n'),
(4, 'two_column', 'Very simple template with two columns.', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\r\n<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">\r\n<head><title>{title}</title>\r\n<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />\r\n<link rel="stylesheet" href="{inc_uri}/admin-style.css" type="text/css" media="all" />\r\n<script type="text/javascript" src="{inc_uri}/links-forms.js"></script>\r\n</head><body>\r\n<table style="text-align:left; background-color: #fff4df" id="pagetop">\r\n{top_menu}\r\n<tr><td id="adminleftcolumn">\r\n{left_menu}\r\n</td>\r\n<td style="vertical-align: top;" colspan="2">\r\n{breadcrumbs}\r\n<div class="subpages">\r\n<p class="title">Sub Pages</p>\r\n{right_menu}\r\n</div>\r\n<h1>{heading}</h1>\r\n{content}\r\n<p class="backlink">{back_link}</p>', '<p class="backlink">{back_link}</p>\r\n<p class="backlink"><a href="#pagetop">&#8657; Back to Top &#8657;</a></p>\r\n</td></tr></table>\r\n</body></html>\r\n        '),
(5, 'three_column2', 'A three column layout with slight variations to three_column.', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\r\n<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">\r\n<head>\r\n<title>{title}</title>\r\n<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />\r\n<link rel="stylesheet" href="{inc_uri}/admin-style.css" type="text/css" media="all" />\r\n<script type="text/javascript" src="{inc_uri}/links-forms.js"></script>\r\n{header_stuff}\r\n</head>\r\n<body>\r\n<div id="wrapper">\r\n<table cellspacing="0" cellpadding="0">\r\n<tr><td id="leftcolumn">\r\n<p class="title">{menu_title}</p>\r\n{left_menu}\r\n</td>\r\n<td class="middlecolumn">\r\n{breadcrumbs}\r\n<h1>{heading}</h1>\r\n{content}', '</td>\r\n</tr>\r\n</table>\r\n</div>\r\n</body>\r\n</html>');

-- --------------------------------------------------------

--
-- Table structure for table `page_types`
--

CREATE TABLE IF NOT EXISTS `page_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `template` int(10) unsigned NOT NULL DEFAULT '0',
  `force_login` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `prevent_caching` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `has_subtypes` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `left_menu_type` enum('none','child_list','full_list','relative_list','custom') NOT NULL DEFAULT 'none',
  `right_menu_type` enum('none','new_subtype_list','child_list') NOT NULL DEFAULT 'none',
  `right_menu_parameter` varchar(255) NOT NULL DEFAULT '',
  `show_back_links` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `use_creation_info` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `page_types`
--

INSERT INTO `page_types` (`id`, `name`, `title`, `description`, `template`, `force_login`, `prevent_caching`, `has_subtypes`, `left_menu_type`, `right_menu_type`, `right_menu_parameter`, `show_back_links`, `use_creation_info`) VALUES
(5, 'admin', 'Admin Panel', 'Admin Panel pages', 4, 1, 1, 0, 'relative_list', 'child_list', '', 1, 0),
(2, 'cp', 'Control Panel', 'Member Control Panel pages.', 1, 1, 1, 0, 'none', 'none', '', 1, 0),
(11, 'www', 'Test Prep', 'Test Prep Pages', 7, 0, 0, 1, 'none', 'none', '', 0, 0),
(12, 'special', 'Special Pages', 'Special Pages', 5, 0, 0, 0, 'custom', 'none', '', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `prep_questions`
--

CREATE TABLE IF NOT EXISTS `prep_questions` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `category` smallint(5) unsigned NOT NULL DEFAULT '0',
  `question` varchar(255) NOT NULL DEFAULT '',
  `answer` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `prep_question_categories`
--

CREATE TABLE IF NOT EXISTS `prep_question_categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `special_event_log`
--

CREATE TABLE IF NOT EXISTS `special_event_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `date` varchar(10) DEFAULT NULL,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `referrer` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `host` varchar(255) NOT NULL DEFAULT '',
  `ua` varchar(255) NOT NULL DEFAULT '',
  `member_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `more_info` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `special_hit_log`
--

CREATE TABLE IF NOT EXISTS `special_hit_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` varchar(10) DEFAULT NULL,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `referrer` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `host` varchar(255) NOT NULL DEFAULT '',
  `ua` varchar(255) NOT NULL DEFAULT '',
  `session_id` varchar(32) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `special_pages`
--

CREATE TABLE IF NOT EXISTS `special_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `query_string` varchar(255) NOT NULL DEFAULT '',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `heading` varchar(255) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `left_menu` mediumtext NOT NULL,
  `link_title` varchar(255) NOT NULL DEFAULT '',
  `show_on_menu` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`uri`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `special_pages`
--

INSERT INTO `special_pages` (`id`, `uri`, `query_string`, `parent_id`, `title`, `description`, `heading`, `content`, `left_menu`, `link_title`, `show_on_menu`) VALUES
(1, '/', '', 0, 'Home', '', 'Home', '', '', 'Home', 1),
(2, '/cp/', '', 1, '', '', '', '', '', 'Control Panel', 1),
(3, '/cp/login.php', '', 2, 'Control Panel Log In', '', 'Control Panel Log In', '', '', 'Log In', 1),
(4, '/cp/forgot-password.php', '', 2, 'Forgotten Password Form', '', 'Forgotten Password Form', '', '', 'Forgotten Password Form', 1),
(5, '/cp/reset-password.php', '', 2, 'Reset Your Password', '', 'Reset Your Password', '', '', 'Reset Your Password', 1),
(7, '/register/', '', 6, 'Register for an Account', '', 'Account Registration', '', '', 'Account Registration', 1),
(10, '/support/', '', 1, 'Support', '', 'Support', '<p><a href="/cp/support/">Help Desk</a><br />\r\n', '<p class="title">Support</p>\r\n<div class="sidemenu">\r\n<p><a href="/forums/">Support Forums</a></p>\r\n<p><a href="/cp/support/">Help Desk</a></p>\r\n</div>', 'Support', 1),
(11, '/admin/', '', 1, 'Admin Panel', '', 'Admin Panel', '', '', 'Admin Panel', 1),
(12, '/admin/login.php', '', 11, 'Admin Panel Login', '', 'Admin Panel Login', '', '', 'Login', 1);

-- --------------------------------------------------------

--
-- Table structure for table `support_messages`
--

CREATE TABLE IF NOT EXISTS `support_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(10) unsigned NOT NULL DEFAULT '0',
  `member_id` int(10) unsigned NOT NULL DEFAULT '0',
  `date` varchar(10) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `member_type` enum('cp','admin') NOT NULL DEFAULT 'cp',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL DEFAULT '0',
  `creation_date` varchar(10) NOT NULL DEFAULT '',
  `last_update` varchar(10) NOT NULL DEFAULT '',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `last_replier` int(10) unsigned NOT NULL DEFAULT '0',
  `last_replier_type` enum('admin','cp') NOT NULL DEFAULT 'cp',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `www_hit_log`
--

CREATE TABLE IF NOT EXISTS `www_hit_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` varchar(10) DEFAULT NULL,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `referrer` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `host` varchar(255) NOT NULL DEFAULT '',
  `ua` varchar(255) NOT NULL DEFAULT '',
  `session_id` varchar(32) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `www_pages`
--

CREATE TABLE IF NOT EXISTS `www_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `query_string` varchar(255) NOT NULL DEFAULT '',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `heading` varchar(255) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `left_menu` mediumtext NOT NULL,
  `link_title` varchar(255) NOT NULL DEFAULT '',
  `show_on_menu` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `menu_parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `subtype` int(10) unsigned NOT NULL DEFAULT '0',
  `author` int(10) unsigned NOT NULL DEFAULT '0',
  `creation_date` varchar(255) NOT NULL DEFAULT '',
  `last_modified` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`uri`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `www_pages`
--

INSERT INTO `www_pages` (`id`, `uri`, `query_string`, `parent_id`, `title`, `description`, `heading`, `content`, `left_menu`, `link_title`, `show_on_menu`, `menu_parent_id`, `subtype`, `author`, `creation_date`, `last_modified`) VALUES
(1, '/testprep/', '', 0, 'Test Prep Questions', '', 'Test Prep Questions', '', '', 'Test Prep Questions', 1, 0, 5, 0, '', '1103946948'),
(6, '/multiple-choice.php', '', 1, 'Multiple Choice Questions', '', 'Multiple Choice Questions', '', '', 'Multiple Choice Questions', 0, 1, 5, 0, '', ''),
(5, '/sitemap/', '', 1, 'Site Map', 'This is the site map', 'Site Map', '<p>Below is the site map:</p>', '', 'Site Map', 1, 1, 3, 0, '', '1103960908');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
