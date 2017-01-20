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
 * Analytics tests.
 *
 * @package    local_analytics
 * @category   test
 * @copyright  2016 Catalyst IT
 * @author     Nigel Cunningham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_analytics;

use advanced_testcase;
use context_course;
use context_module;
use core\session\manager;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__DIR__).'/lib.php');

/**
 * Analytics tests class.
 *
 * @package    local_analytics
 * @category   test
 * @copyright  2016 Catalyst IT
 * @author     Nigel Cunningham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mock_page {
    public $context = null;

    private $editing = false;

    public $heading = 'This is a heading';

    public function user_is_editing() {
        return $this->editing;
    }

    public function set_editing($value) {
        $this->editing = $value;
    }
}

/**
 * Class local_analytics_testcase
 */
class local_analytics_types_testcase extends advanced_testcase {
    /** @var stdClass Keeps course object */
    private $course;

    /** @var stdClass Keeps wiki object */
    private $wiki;

    /**
     * Setup test data.
     */
    public function setUp() {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();
        injector::reset();

        // Create course and wiki.
        $this->course = $this->getDataGenerator()->create_course();
        $this->wiki = $this->getDataGenerator()->create_module('wiki', ['course' => $this->course->id]);

        // Assign the guest role to the guest user in the course.
        $context = context_course::instance($this->course->id);
        role_assign(6, 1, $context->id);

        // Set default config to minimise repetition across tests.
        set_config('enabled', true, 'local_analytics');
        set_config('analytics', 'piwik', 'local_analytics');
        set_config('imagetrack', true, 'local_analytics');
        set_config('siteurl', 'somewhere', 'local_analytics');
        set_config('siteid', 2468, 'local_analytics');
        set_config('trackadmin', true, 'local_analytics');
        set_config('masquerade_handling', true, 'local_analytics');
        set_config('cleanurl', true, 'local_analytics');
        set_config('location', 'head', 'local_analytics');
        set_config('piwikusedimensions', false, 'local_analytics');
    }

    /**
     * Test that shouldTrack returns TRUE for site admins when trackadmin on.
     *
     * GIVEN the local analytics plugin
     * WHEN its shouldTrack function is invoked
     * AND trackadmin is TRUE
     * AND the user is a site admin
     * THEN the result should be TRUE
     */
    public function test_should_track_return_true_for_siteadmins_when_track_admin_on() {
        $piwik = new api\piwik();
        $actual = $piwik::should_track();

        $this->assertTrue($actual);
    }

    /**
     * Test that shouldTrack returns FALSE for site admins when trackadmin off.
     *
     * GIVEN the local analytics plugin
     * WHEN its shouldTrack function is invoked
     * AND trackadmin is FALSE
     * AND the user is a site admin
     * THEN the result should be FALSE
     */
    public function test_should_track_return_true_for_siteadmins_when_track_admin_off() {
        set_config('trackadmin', false, 'local_analytics');

        $piwik = new api\piwik();
        $actual = $piwik::should_track();

        $this->assertFalse($actual);
    }

    /**
     * Test that shouldTrack returns TRUE for non site admins.
     *
     * GIVEN the local analytics plugin
     * WHEN its shouldTrack function is invoked
     * AND the user is not a site admin
     * THEN the result should be TRUE
     */
    public function test_should_track_return_true_for_non_siteadmins() {

        $this->setGuestUser();

        $piwik = new api\piwik();
        $actual = $piwik::should_track();

        $this->assertTrue($actual);

        // Trackadmin shouldn't make a difference.
        set_config('trackadmin', false, 'local_analytics');
        $actual = $piwik::should_track();

        $this->assertTrue($actual);
    }

    /**
     * Test that enabling Piwik analytics causes no JS to be added.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is FALSE
     * THEN no additional content should be added to the output.
     *
     * @test
     */
    public function test_disabled_analytics_results_in_no_output() {
        global $CFG;

        set_config('enabled', false, 'local_analytics');

        injector::inject();

        $this->assertEmpty($CFG->additionalhtmlhead);
    }

    /**
     * Test that having a bogus analytics engine setting enabled results in a debugging message.
     *
     * This test deliberately has 'test' at the start of the name because at the time of writing, the
     * debugging() code doesn't detect @ test when deciding how to dispose of a debugging message.
     * It will therefore mess up the debugging output without this hint.
     *
     * GIVEN the local analytics plugin
     * WHEN the enabled setting for the module is TRUE
     * AND the analytics module is invalid
     * THEN a debugging message should be added to the output.
     */
    public function test_enabled_bogus_module_results_in_debugging_message() {
        set_config('analytics', 'i_am_bogus', 'local_analytics');

        injector::inject();

        $this->assertDebuggingCalled();
    }

    /**
     * Test that userFullName gives the real user name if the user is not masquerading.
     *
     * GIVEN the local analytics plugin
     * WHEN the userFullName function is invoked
     * AND the user is not masquerading
     * THEN the returned string should be the full name of the user.
     */
    public function test_user_full_name_gives_real_user_name_if_not_masquerading() {
        $piwik = new api\piwik();
        $actual = $piwik::user_full_name();
        $this->assertEquals('Admin User', $actual);
    }

    /**
     * Test that userFullName gives the real user name if masquerade handling is enabled.
     *
     * GIVEN the local analytics plugin
     * WHEN the userFullName function is invoked
     * AND the masquerade_handling config is TRUE
     * THEN the returned string should be the full name of the admin user.
     */
    public function test_user_full_name_gives_assumed_user_name_if_masquerading_and_handling_disabled() {
        set_config('masquerade_handling', false, 'local_analytics');

        $systemcontext = \context_system::instance(0);
        manager::loginas(1, $systemcontext);

        $piwik = new api\piwik();
        $actual = $piwik::user_full_name();
        $this->assertEquals('Guest user  ', $actual);
    }

    /**
     * Test that userFullName gives the masqueraded user name if masquerade handling is disabled.
     *
     * GIVEN the local analytics plugin
     * WHEN the userFullName function is invoked
     * AND the masquerade_handling config is TRUE
     * THEN the returned string should be the full name of the admin user.
     */
    public function test_user_full_name_gives_real_user_name_if_masquerade_handling_enabled() {
        $systemcontext = \context_system::instance(0);
        manager::loginas(1, $systemcontext);

        $piwik = new api\piwik();
        $actual = $piwik::user_full_name();
        $this->assertEquals('Admin User', $actual);
    }

    /**
     * Test that Piwik track URL for a course is generated correctly.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_analytics_trackurl function in the piwik support is invoked
     * AND a course category name and course full name can be used
     * THEN it should return the expected URL.
     */
    public function test_piwik_track_url_for_course_returns_expected_string() {
        global $PAGE;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($this->course->id);

        $piwik = new api\piwik();
        $trackurl = $piwik::trackurl();

        $this->assertEquals("Miscellaneous/Test course 1/View", $trackurl);
    }

    /**
     * Test that Piwik track URL for a course with editing enabled is generated correctly.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_analytics_trackurl function in the piwik support is invoked
     * AND editing is enabled
     * AND a course category name and course full name can be used
     * THEN it should return the expected URL.
     */
    public function test_piwik_track_url_for_course_being_edited_returns_expected_string() {
        global $PAGE;

        $PAGE = new mock_page();
        $PAGE->set_editing(true);
        $PAGE->context = context_course::instance($this->course->id);

        $piwik = new api\piwik();
        $trackurl = $piwik::trackurl();

        $this->assertEquals("Miscellaneous/Test course 1/Edit", $trackurl);
    }

    /**
     * Test that Piwik track URL for an activity within a course is generated correctly.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_analytics_trackurl function in the piwik support is invoked
     * AND a course category name, course full name and activity name can be used
     * THEN it should return the expected URL.
     */
    public function test_piwik_track_url_for_activity_in_course_returns_expected_string() {
        global $PAGE;

        $PAGE = new mock_page();
        $PAGE->context = context_module::instance($this->wiki->cmid);

        $piwik = new api\piwik();
        $trackurl = $piwik::trackurl();

        $this->assertEquals("Miscellaneous/Test course 1/wiki/Wiki 1", $trackurl);
    }

    /**
     * Test that Piwik custom variable string generation produces anticipated output.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_get_custom_var_string function in the piwik support is invoked
     * THEN it should return the expected string.
     */
    public function test_piwik_custom_variable_string_generation_produces_expected_output() {
        $piwik = new api\piwik();
        $actual = $piwik::local_get_custom_var_string(987, 'name', 'value', 'context');

        $expected = '_paq.push(["setCustomVariable", 987, "name", "value", "page"]);'."\n";

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that Piwik insert custom moodle vars function returns the anticipated vars string for siteadmins.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_get_custom_var_string function in the piwik support is invoked
     * AND the user is a siteadmin
     * THEN it should return the expected string.
     */
    public function test_piwik_custom_moodle_vars_generation_produces_expected_output_for_admin() {
        global $PAGE;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($this->course->id);

        $piwik = new api\piwik();
        $actual = $piwik::local_insert_custom_moodle_vars();

        $expected = '_paq.push(["setCustomVariable", 1, "UserName", "Admin User", "page"]);'."\n";
        $expected .= '_paq.push(["setCustomVariable", 2, "UserRole", "Admin", "page"]);'."\n";
        $expected .= '_paq.push(["setCustomVariable", 3, "Context", "Front page", "page"]);'."\n";
        $expected .= '_paq.push(["setCustomVariable", 4, "CourseName", "PHPUnit test site", "page"]);'."\n";

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that Piwik insert custom moodle vars function returns the anticipated vars string for non admins.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_get_custom_var_string function in the piwik support is invoked
     * AND the user is not a site administrator
     * THEN it should return the expected string.
     */
    public function test_piwik_custom_moodle_vars_generation_produces_expected_output_for_non_admin() {
        global $PAGE, $COURSE;

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $this->setGuestUser();

        $piwik = new api\piwik();
        $actual = $piwik::local_insert_custom_moodle_vars();

        $expected = '_paq.push(["setCustomVariable", 1, "UserName", "Guest user  ", "page"]);'."\n";
        $expected .= '_paq.push(["setCustomVariable", 2, "UserRole", "Guest", "page"]);'."\n";
        $expected .= '_paq.push(["setCustomVariable", 3, "Context", "Course: Test course 1", "page"]);'."\n";
        $expected .= '_paq.push(["setCustomVariable", 4, "CourseName", "Test course 1", "page"]);'."\n";

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that Piwik's insert tracking function works as expected.
     *
     * GIVEN the local analytics plugin
     * WHEN the insert_tracking function in the piwik support is invoked
     * THEN the Pikiw Javascript should be inserted in the additionalhtml
     */
    public function test_piwik_inserts_expected_javascript_in_additionalhtml() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', ['id' => 1]);

        $piwik = new api\piwik();
        $piwik::insert_tracking();

        $actual = $CFG->additionalhtmlhead;
        $expected = file_get_contents(__DIR__.'/expected/piwik_additional.html');

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that Piwik's insert tracking function works as expected without clean URLs.
     *
     * GIVEN the local analytics plugin
     * WHEN the insert_tracking function in the piwik support is invoked
     * AND the clean URL option is disabled
     * THEN the Pikiw Javascript should be as expected.
     */
    public function test_piwik_inserts_expected_javascript_in_additionalhtml_without_clean_url_option() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('cleanurl', false, 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', ['id' => 1]);

        $piwik = new api\piwik();
        $piwik::insert_tracking();

        $actual = $CFG->additionalhtmlhead;
        $expected = file_get_contents(__DIR__.'/expected/piwik_additional_no_cleanurl.html');

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that Piwik's insert tracking function works as expected without image tracking turned on.
     *
     * GIVEN the local analytics plugin
     * WHEN the insert_tracking function in the piwik support is invoked
     * AND image tracking is disabled
     * THEN the Pikiw Javascript should be inserted in the additionalhtml as expected.
     */
    public function test_piwik_inserts_expected_javascript_in_additionalhtml_without_image_track_option() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('imagetrack', false, 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', ['id' => 1]);

        $piwik = new api\piwik();
        $piwik::insert_tracking();

        $actual = $CFG->additionalhtmlhead;
        $expected = file_get_contents(__DIR__.'/expected/piwik_additional_no_imagetrack.html');

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that enabling Piwik analytics causes appropriate JS to be added.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the analytics module is set to Piwik
     * THEN the Piwik Javascript should be added to the output.
     */
    public function test_piwik_module_enabled_results_in_expected_output() {
        global $CFG;

        injector::inject();

        $this->assertNotEmpty($CFG->additionalhtmlhead);
    }

    /**
     * Test that enabling Google analytics universal causes appropriate JS to be added for a course page.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics Universal
     * THEN the GA Universal Javascript should be added to the output.
     */
    public function test_google_analytics_track_url_for_course_is_correct_for_course_page_being_viewed() {
        global $PAGE, $COURSE, $USER, $DB;

        set_config('analytics', 'ganalytics', 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', ['id' => 1]);

        $ga = new api\ganalytics();
        $actual = $ga::trackurl(true, true);

        $this->assertEquals("/Miscellaneous/Test+course+1/View", $actual);
    }

    /**
     * Test that tracker URL for a course page being edited is correct when using GA.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics
     * THEN the tracker URL should be /Miscellaneous/Test+course+1/Edit.
     */
    public function test_google_analytics_track_url_for_course_is_correct_for_course_page_being_edited() {
        global $PAGE, $COURSE, $USER, $DB;

        set_config('analytics', 'ganalytics', 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);
        $PAGE->set_editing(true);

        $USER = $DB->get_record('user', ['id' => 1]);

        $ga = new api\ganalytics();
        $actual = $ga::trackurl(true, true);

        $this->assertEquals("/Miscellaneous/Test+course+1/Edit", $actual);
    }

    /**
     * Test that GA track URL for an activity within a course is generated correctly.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_analytics_trackurl function in the GA support is invoked
     * AND a course category name, course full name and activity name can be used
     * THEN it should return the expected URL.
     */
    public function test_google_analytics_track_url_for_activity_in_course_is_correct() {
        global $PAGE;

        set_config('analytics', 'ganalytics', 'local_analytics');

        $PAGE = new mock_page();
        $PAGE->context = context_module::instance($this->wiki->cmid);

        $ga = new api\ganalytics();
        $trackurl = $ga::trackurl(true, true);

        $this->assertEquals("/Miscellaneous/Test+course+1/wiki/Wiki+1", $trackurl);
    }

    /**
     * Test that enabling Google analytics universal causes appropriate JS to be added when clean URL is disabled.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics Universal
     * AND the clean URL option is disabled
     * THEN the GA Universal Javascript should be added to the output.
     */
    public function test_ga_track_url_for_activity_in_course_is_correct_for_course_page_with_unclean_url() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('analytics', 'ganalytics', 'local_analytics');
        set_config('cleanurl', false, 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', ['id' => 1]);

        injector::inject();

        $expected = file_get_contents(__DIR__.'/expected/google_analytics_course_page_unclean_url.html');
        $actual = $CFG->additionalhtmlhead;

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that enabling Google analytics universal causes appropriate JS to be added when clean URL is enabled.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics Universal
     * AND the clean URL option is enabled
     * THEN the GA Universal Javascript should be added to the output.
     */
    public function test_ga_track_url_for_activity_in_course_is_correct_for_course_page_with_clean_url() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('analytics', 'ganalytics', 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', ['id' => 1]);

        injector::inject();

        $expected = file_get_contents(__DIR__.'/expected/google_analytics_course_page.html');
        $actual = $CFG->additionalhtmlhead;

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that enabling Google analytics universal causes appropriate JS to be added for a course page.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics Universal
     * THEN the GA Universal Javascript should be added to the output.
     */
    public function test_ga_universal_track_url_for_course_is_correct_for_course_page_being_viewed() {
        global $PAGE, $COURSE, $USER, $DB;

        set_config('analytics', 'guniversal', 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', ['id' => 1]);

        $ga = new api\guniversal();
        $actual = $ga::trackurl(true, true);

        $this->assertEquals("/Miscellaneous/Test+course+1/View", $actual);
    }

    /**
     * Test that tracker URL for a course page being edited is correct when using GA.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics
     * THEN the tracker URL should be /Miscellaneous/Test+course+1/Edit.
     */
    public function test_ga_universal_track_url_for_course_is_correct_for_course_page_being_edited() {
        global $PAGE, $COURSE, $USER, $DB;

        set_config('analytics', 'guniversal', 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);
        $PAGE->set_editing(true);

        $USER = $DB->get_record('user', ['id' => 1]);

        $ga = new api\guniversal();
        $actual = $ga::trackurl(true, true);

        $this->assertEquals("/Miscellaneous/Test+course+1/Edit", $actual);
    }

    /**
     * Test that GA universal track URL for an activity within a course is generated correctly.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_analytics_trackurl function in the piwik support is invoked
     * AND a course category name, course full name and activity name can be used
     * THEN it should return the expected URL.
     */
    public function test_ga_universal_track_url_for_activity_in_course() {
        global $PAGE;

        set_config('analytics', 'guniversal', 'local_analytics');

        $PAGE = new mock_page();
        $PAGE->context = context_module::instance($this->wiki->cmid);

        $guniversal = new api\guniversal();
        $trackurl = $guniversal::trackurl(true, true);

        $this->assertEquals("/Miscellaneous/Test+course+1/wiki/Wiki+1", $trackurl);
    }

    /**
     * Test that enabling Google analytics universal causes appropriate JS to be added.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics Universal
     * THEN the GA Universal Javascript should be added to the output.
     */
    public function test_enabled_ga_universal_module_results_in_expected_output() {
        global $USER, $DB, $CFG;

        set_config('analytics', 'guniversal', 'local_analytics');
        set_config('imagetrack', false, 'local_analytics');

        $USER = $DB->get_record('user', ['id' => 1]);

        injector::inject();

        $expected = file_get_contents(__DIR__.'/expected/google_analytics_universal.html');
        $actual = $CFG->additionalhtmlhead;

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that enabling Google analytics universal causes appropriate JS to be added when clean URL is disabled.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics Universal
     * AND the clean URL option is disabled
     * THEN the GA Universal Javascript should be added to the output.
     */
    public function test_enabled_ga_universal_module_results_in_expected_output_for_course_page_with_unclean_url() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('analytics', 'guniversal', 'local_analytics');
        set_config('cleanurl', false, 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', ['id' => 1]);

        injector::inject();

        $expected = file_get_contents(__DIR__.'/expected/google_analytics_universal_course_unclean_url.html');
        $actual = $CFG->additionalhtmlhead;

        $this->assertEquals($expected, $actual);
    }
}
