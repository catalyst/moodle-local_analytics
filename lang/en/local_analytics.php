<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Piwik Analytics
 *
 * This module provides extensive analytics, without the privacy concerns
 * of using Google Analytics, see install_piwik.txt for installing Piwik
 *
 * @package    local_analytics
 * @copyright  2013 David Bezemer, www.davidbezemer.nl
 * @author     David Bezemer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Analytics';
$string['type'] = 'Analytics';
$string['type_help'] = 'Choose the type of Analytics you want to insert';
$string['siteid'] = 'Site ID';
$string['siteid_help'] = 'Enter your Site ID or Google Analytics';
$string['siteurl'] = 'Analytics URL';
$string['siteurl_help'] = 'Enter your Piwik Analytics URL without http(s) or a trailing slash (for both Google Analytics types leave empty)';
$string['enabled'] = 'Enabled';
$string['enabled_helpc'] = 'Enable Analytics for Moodle';
$string['imagetrack'] = 'Image Tracking';
$string['cleanurl'] = 'Clean URLs';
$string['cleanurl_help'] = 'Generate clean URL for in advanced tracking';
$string['imagetrack_help'] = 'Enable Image Tracking for Moodle for browsers with JavaScript disabled (only for Piwik)';
$string['trackadmin'] = 'Tracking Admins';
$string['trackadmin_help'] = 'Enable tracking of Admin users (not recommended)';
$string['view'] = 'View';
$string['edit'] = 'Edit';
$string['piwik'] = 'Piwik';
$string['ganalytics'] = 'Google Analytics (deprecated)';
$string['guniversal'] = 'Google Universal Analytics';
$string['location'] = 'Tracking code location';
$string['locationdesc'] = 'The place on the page where you want to place the code, header will yield the most reliable results, but footer gives the best performance. If you do not get correct results in Google/Piwik set this to "Header"';
$string['masquerade_handling'] = 'Track masquerading users';
$string['masquerade_handling_help'] = 'Whether to track users who are masquerading as other users or are using a modified role. The default (unchecked) is to not include them in tracking data.';
$string['head'] = 'Header';
$string['topofbody'] = 'Top of body';
$string['footer'] = 'Footer';

$string['usedimensions'] = 'Piwik custom dimensions';
$string['usedimensions_help'] = 'Use custom dimensions for Piwik rather than custom variables.';

$string['dimensionid'] = 'Piwik dimension ID for {$a} scope';
$string['dimensionid_help'] = 'Dimension ID in {$a} scope. If the dimension is used, this ID is required.';

$string['dimensioncontent'] = 'Dimension value for {$a} scope';
$string['dimensionid_help'] = 'Dimension ID in {$a} scope. If the dimension is used, this ID is required.';

$string['adddimension'] = 'Add 1 more dimension for {$a} scope';

$string['course_full_name'] = 'Course full name';
$string['course_short_name'] = 'Course short name';
$string['course_id_number'] = 'Course ID number';
$string['course_category_hierarchy_full_path'] = 'Course category hierarchy full path';
$string['course_enrolment_method'] = 'Course enrolment method';
$string['context'] = 'Context';
$string['user_name'] = 'User name';
$string['user_role'] = 'User role';
$string['user_email_domain'] = 'User email domain';
$string['user_department'] = 'User department';
$string['user_institution'] = 'User institution';
$string['is_on_campus'] = 'User is on campus';
$string['is_on_bundoora_campus'] = 'User is on Bundoora campus network';
$string['user_profile_field_faculty_cost_code'] = 'Faculty cost code user profile field';
