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
 * Tests for settings/analytics_manager.php.
 *
 * @package    local_analytics
 * @author     Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_analytics\settings\analytics_manager;

/**
 * Class analytics_manager_test.
 */
class analytics_manager_test extends advanced_testcase {
    /**
     * Test data for analytics.
     *
     * @var object
     */
    protected $data;

    /**
     * Test manager.
     *
     * @var local_analytics\settings\analytics_manager
     */
    protected $manager;

    /**
     * Initial set up.
     */
    public function setUp() {
        global $DB;

        $this->resetAfterTest();

        $this->data = array();

        for ($i = 1; $i <= 4; $i++) {
            $data = new stdClass();
            $data->enabled = $i;
            $data->location = 'location' . $i;
            $data->type = 'piwik' . $i;
            $data->siteid = 'siteID' . $i;
            $data->trackadmin = $i;
            $data->masqueradehandling = $i;
            $data->cleanurl = $i;
            $data->siteurl = 'site URL' . $i;
            $data->imagetrack = $i;
            $data->usedimensions = $i;
            $data->dimensions = [
                'action' => [
                    [
                        'id' => 'ID' . $i,
                        'content' => 'Content' . $i,
                    ],
                ],
            ];

            $this->data[$i] = $data;
            $data->dimensions = serialize($data->dimensions);

            $id = $DB->insert_record('local_analytics', $data);

            $data->dimensions = unserialize($data->dimensions);
            $this->data[$i]->id = $id;
        }

        $this->manager = new analytics_manager();
    }

    /**
     * Test that analytics_manager implements analytics_manager_interface.
     */
    public function test_analytics_manager_implements_analytics_manager_interface() {
        $this->assertInstanceOf('local_analytics\settings\analytics_manager_interface', $this->manager);
    }

    /**
     * Test that analytics_manager uses local_analytics table.
     */
    public function test_analytics_manager_uses_correct_table() {
        $this->assertEquals('local_analytics', analytics_manager::TABLE_NAME);
    }

    /**
     * Test that analytics_manager can get record using its ID.
     */
    public function test_analytics_manager_can_get_record() {
        for ($i = 1; $i <= 4; $i++) {
            $actual = $this->manager->get($i);
            $this->assertEquals($this->data[$i], $actual);
        }
    }

    /**
     * Test that analytics_manager can save new record.
     */
    public function test_analytics_manager_can_save_new_record() {
        global $DB;

        $data = $this->data[3];
        unset($data->id);

        $analytics = new \local_analytics\settings\analytics($data);
        $id = $this->manager->save($analytics);

        $actual = $DB->get_record('local_analytics', array('id' => $id));

        $data->id = $id;
        $data->dimensions = serialize($data->dimensions);

        $this->assertEquals($data, $actual);
    }

    /**
     * Test that analytics_manager can update record.
     */
    public function test_analytics_manager_can_update_record() {
        global $DB;

        $data = $this->data[2];
        $data->enabled = 1;
        $data->location = 'New location';
        $data->type = 'New piwik';
        $data->siteid = 'New siteID';
        $data->trackadmin = 1;
        $data->masqueradehandling = 1;
        $data->cleanurl = 1;
        $data->siteurl = 'New site URL';
        $data->imagetrack = 1;
        $data->usedimensions = 1;
        $data->dimensions = [
            'action' => [
                [
                    'id' => 'New ID',
                    'content' => 'New Content',
                ],
            ],
        ];

        $analytics = new \local_analytics\settings\analytics($data);
        $this->manager->save($analytics);

        $actual = $DB->get_record('local_analytics', array('id' => $data->id));
        $data->dimensions = serialize($data->dimensions);

        $this->assertEquals($data, $actual);
    }

    /**
     * Test that analytics_manager can get all records.
     */
    public function test_analytics_manager_can_get_all() {
        $actual = $this->manager->get_all();
        $expected = array();

        foreach ($this->data as $record) {
            $expected[$record->id] = $record;
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that analytics_manager can get all enabled records.
     */
    public function test_analytics_manager_can_get_all_enabled () {
        $actual = $this->manager->get_enabled();
        $expected = array(
            $this->data[1]->id => $this->data[1],
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * Teat that analytics_manager can delete a record.
     */
    public function test_analytics_manager_can_delete() {
        global $DB;

        $this->manager->delete($this->data[1]->id);
        $this->manager->delete($this->data[4]->id);

        $this->assertFalse($DB->record_exists('local_analytics', array('id' => $this->data[1]->id)));
        $this->assertFalse($DB->record_exists('local_analytics', array('id' => $this->data[4]->id)));
        $this->assertTrue($DB->record_exists('local_analytics', array('id' => $this->data[2]->id)));
        $this->assertTrue($DB->record_exists('local_analytics', array('id' => $this->data[3]->id)));
    }

    /**
     * Test that analytics_manager saves null is dimensions are empty or not array.
     */
    public function test_analytics_manager_saves_null_if_dimensions_empty_or_not_array() {
        global $DB;

        // Empty array.
        $data = $this->data[3];
        $data->dimensions = array();
        unset($data->id);

        $analytics = new \local_analytics\settings\analytics($data);
        $id = $this->manager->save($analytics);

        $actual = $DB->get_record('local_analytics', array('id' => $id));

        $this->assertNull($actual->dimensions);

        // Empty string.
        $data = $this->data[3];
        $data->dimensions = '';
        unset($data->id);

        $analytics = new \local_analytics\settings\analytics($data);
        $id = $this->manager->save($analytics);

        $actual = $DB->get_record('local_analytics', array('id' => $id));

        $this->assertNull($actual->dimensions);

        // String.
        $data = $this->data[3];
        $data->dimensions = 'Sting';
        unset($data->id);

        $analytics = new \local_analytics\settings\analytics($data);
        $id = $this->manager->save($analytics);

        $actual = $DB->get_record('local_analytics', array('id' => $id));

        $this->assertNull($actual->dimensions);

        // Integer.
        $data = $this->data[3];
        $data->dimensions = 123;
        unset($data->id);

        $analytics = new \local_analytics\settings\analytics($data);
        $id = $this->manager->save($analytics);

        $actual = $DB->get_record('local_analytics', array('id' => $id));

        $this->assertNull($actual->dimensions);

        // Object.
        $data = $this->data[3];
        $data->dimensions = new stdClass();
        $data->dimensions->data = 1;
        unset($data->id);

        $analytics = new \local_analytics\settings\analytics($data);
        $id = $this->manager->save($analytics);

        $actual = $DB->get_record('local_analytics', array('id' => $id));

        $this->assertNull($actual->dimensions);
    }

}
