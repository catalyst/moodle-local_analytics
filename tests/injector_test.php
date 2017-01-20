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
 * Testcase for injector
 *
 * @package     local_analytics
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2016 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_analytics\injector;

defined('MOODLE_INTERNAL') || die();

/**
 * Testcase for injector
 *
 * @package     local_analytics
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @author      Nigel Cunningham
 * @copyright   2016 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_analytics_test extends advanced_testcase {
    public function setUp() {
        parent::setUp();

        $this->resetAfterTest();
        injector::reset();

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

    public function test_injector_called_once_injects_code() {
        global $CFG;
        injector::inject();
        $this->assertNotEmpty($CFG->additionalhtmlhead);
    }

    public function test_injector_called_twice_does_not_reinject_code() {
        global $CFG;

        injector::inject(); // First inject.
        $CFG->additionalhtmlhead = 'Already injected.';
        injector::inject(); // Second inject, should not reinject.

        $this->assertSame('Already injected.', $CFG->additionalhtmlhead);
    }
}
