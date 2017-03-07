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
 * Tests for settings/analytics.php.
 *
 * @package    local_analytics
 * @author     Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_analytics\settings\analytics;

/**
 * Class analytics_test.
 */
class analytics_test extends advanced_testcase {
    /**
     * Test data for analytics.
     *
     * @var object
     */
    protected $data;

    /**
     * Test analytic instance.
     *
     * @var local_analytics\settings\analytics
     */
    protected $analytics;

    /**
     * Initial set up.
     */
    public function setUp() {
        $this->data = new stdClass();
        $this->data->id = 3;
        $this->data->enabled = false;
        $this->data->type = 'piwik';
        $this->data->siteid = 'siteID';
        $this->data->trackadmin = 1;
        $this->data->masqueradehandling = 0;
        $this->data->cleanurl = 0;
        $this->data->siteurl = 'site URL';
        $this->data->imagetrack = 1;
        $this->data->usedimensions = 1;
        $this->data->dimensions = array(
            'action' => array(
                array(
                    'id' => 'ID1',
                    'content' => 'Content 1'
                ),
                array(
                    'id' => 'ID2',
                    'content' => 'Content 2'
                )

            ),
            'visit' => array(
                array(
                    'id' => 'ID3',
                    'content' => 'Content 3'
                )
            ),
        );

        $this->resetAfterTest();
        $this->analytics = new analytics($this->data);
    }

    /**
     * Test that analytics implements analytics_interface.
     */
    public function test_analytics_implements_analytic_interface() {
        $this->assertInstanceOf('local_analytics\settings\analytics_interface', $this->analytics);
    }

    /**
     * Test that coding exception is thrown if construct analytics using empty data.
     *
     * @expectedException coding_exception
     * @expectedExceptionMessage Empty $data object is provided for analytics class.
     */
    public function test_throw_exception_on_empty_data() {
        $analytics = new analytics(new stdClass());
    }

    /**
     * Data provider for test_default_property_values_if_constructed_without_correct_properties.
     *
     * @return array
     */
    public function test_default_property_values_if_constructed_without_correct_properties_data_provider() {
        return array(
            array('id', null),
            array('enabled', 0),
            array('type', 'piwik'),
            array('siteid', 1),
            array('trackadmin', 0),
            array('masqueradehandling', 0),
            array('cleanurl', 0),
            array('siteurl', null),
            array('imagetrack', 0),
            array('usedimensions', 0),
            array('dimensions', array()),
        );
    }

    /**
     * Test default analytics values.
     *
     * @dataProvider test_default_property_values_if_constructed_without_correct_properties_data_provider
     *
     * @param string $name A name of the property.
     * @param mixed $value An expected result of get_property($name) function.
     */
    public function test_default_property_values_if_constructed_without_correct_properties($name, $value) {
        $this->data = new stdClass();
        $this->data->invalid = 'some invalid parameter';
        $this->analytics = new analytics($this->data);

        $this->assertEquals($value, $this->analytics->get_property($name));
    }

    /**
     * Test that coding exception is thrown if try to get invalid property.

     * @expectedException coding_exception
     * @expectedExceptionMessage Coding error detected, it must be fixed by a programmer: Requested invalid property. (invalid)
     */
    public function test_throw_exception_on_getting_invalid_property() {
        $this->analytics->get_property('invalid');
    }

    /**
     * Data provider for test_get_property_return_correct_value.
     *
     * @return array
     */
    public function test_get_property_return_correct_value_data_provider() {
        $dimensions = array(
            'action' => array(
                array(
                    'id' => 'ID1',
                    'content' => 'Content 1'
                ),
                array(
                    'id' => 'ID2',
                    'content' => 'Content 2'
                )

            ),
            'visit' => array(
                array(
                    'id' => 'ID3',
                    'content' => 'Content 3'
                )
            ),
        );

        return array(
            array('id', 3),
            array('enabled', 0),
            array('type', 'piwik'),
            array('siteid', 'siteID'),
            array('trackadmin', 1),
            array('masqueradehandling', 0),
            array('cleanurl', 0),
            array('siteurl', 'site URL'),
            array('imagetrack', 1),
            array('usedimensions', 1),
            array('dimensions', $dimensions),
        );
    }

    /**
     * Test that we can get correct properties.
     *
     * @dataProvider test_get_property_return_correct_value_data_provider
     *
     * @param string $name A name of the property.
     * @param mixed $value An expected result of get_property($name) function.
     */
    public function test_get_property_return_correct_value($name, $value) {
        $actual = $this->analytics->get_property($name);
        $this->assertEquals($value, $actual);
    }

    /**
     * Test that we can get correct dimensions.
     */
    public function test_return_correct_dimensions_property() {
        // Array.
        $this->analytics = new analytics($this->data);
        $actual = $this->analytics->get_property('dimensions');
        $this->assertTrue(is_array($actual));
        $this->assertEquals($this->data->dimensions, $actual);

        // Dimensions not set.
        unset($this->data->dimensions);
        $this->analytics = new analytics($this->data);
        $actual = $this->analytics->get_property('dimensions');
        $this->assertTrue(is_array($actual));
        $this->assertTrue(empty($actual));

        // Integer.
        $this->data->dimensions = 1;
        $this->analytics = new analytics($this->data);
        $actual = $this->analytics->get_property('dimensions');
        $this->assertTrue(is_array($actual));
        $this->assertTrue(empty($actual));

        // Null.
        $this->data->dimensions = null;
        $this->analytics = new analytics($this->data);
        $actual = $this->analytics->get_property('dimensions');
        $this->assertTrue(is_array($actual));
        $this->assertTrue(empty($actual));

        // String.
        $this->data->dimensions = 'String';
        $this->analytics = new analytics($this->data);
        $actual = $this->analytics->get_property('dimensions');
        $this->assertTrue(is_array($actual));
        $this->assertTrue(empty($actual));

        // Class.
        $this->data->dimensions = new stdClass();
        $this->analytics = new analytics($this->data);
        $actual = $this->analytics->get_property('dimensions');
        $this->assertTrue(is_array($actual));
        $this->assertTrue(empty($actual));
    }

    /**
     * Data provider for test_is_enabled.
     *
     * @return array
     */
    public function test_is_enabled_data_provider() {
        return array(
            array(0, false),
            array(1, true),
            array('string', true),
            array(null, false),
            array(array(), false),
            array(array(0), true),
            array(new stdClass(), true),
        );
    }

    /**
     * Test if is_enabled function works as expected.
     *
     * @dataProvider test_is_enabled_data_provider
     *
     * @param mixed $value A value of the enabled property.
     * @param bool $expected An expected result of is_enabled() function.
     */
    public function test_is_enabled($value, $expected) {
        $this->data->enabled = $value;
        $this->analytics = new analytics($this->data);

        $this->assertEquals($expected, $this->analytics->is_enabled());
    }

}
