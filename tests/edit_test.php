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
 * Tests for form/edit.php.
 *
 * @package    local_analytics
 * @author     Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

use local_analytics\form\edit;

/**
 * Class edit_test.
 */
class edit_test extends advanced_testcase {
    /**
     * Test form object.
     *
     * @var
     */
    protected $form;
    /**
     * Submitted data.
     *
     * @var array
     */
    protected $data;

    /**
     * A list of known dimensions scopes.
     *
     * @var array
     */
    protected $knownscopes = array('action', 'visit');

    /**
     * Initial set up.
     */
    public function setUp() {
        global $CFG;

        require_once($CFG->dirroot . '/lib/formslib.php');

        $this->data = array(
            'id' => 3,
            'enabled' => false,
            'type' => 'piwik',
            'siteid' => 'siteID',
            'trackadmin' => 1,
            'masqueradehandling' => 0,
            'cleanurl' => 0,
            'siteurl' => 'site URL',
            'imagetrack' => 1,
            'usedimensions' => 1,
            'dimensions' => array(
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
            ),
        );
        $this->form = new edit();
        $this->form->definition_after_data();


        $this->resetAfterTest();
    }

    /**
     * Data provider for test_create_analytics_form.
     *
     * @return array
     */
    public function test_create_analytics_form_data_provider() {
        return array(
            array('id', true, 'hidden', null),
            array('enabled', true, 'checkbox', 1),
            array('type', true, 'select', null),
            array('siteid', true, 'text', null),
            array('trackadmin', true, 'checkbox', null),
            array('masqueradehandling', true, 'checkbox', 1),
            array('cleanurl', true, 'checkbox', 1),
            array('siteurl', true, 'text', null),
            array('imagetrack', true, 'checkbox', null),
            array('usedimensions', true, 'checkbox', null),
            array('action_add', true, 'submit', 'Add 1 more dimension for action scope'),
            array('visit_add', true, 'submit', 'Add 1 more dimension for visit scope'),
        );
    }

    /**
     * Test form when we create a new analytics.
     *
     * @dataProvider test_create_analytics_form_data_provider
     *
     * @param string $fieldname A name of the field.
     * @param bool $exist A result of checking if the field is exist.
     * @param string $type A type of the field.
     * @param mixed $value A default value of the field.
     */
    public function test_create_analytics_form($fieldname, $exist, $type, $value) {
        $actual = $this->form->get_form()->elementExists($fieldname);

        $this->assertEquals($exist, $actual);
        if ($actual == true) {
            $actualtype = $this->form->get_form()->getElement($fieldname)->getType();
            $this->assertEquals($type, $actualtype);
            $actualvalue = $this->form->get_form()->getElement($fieldname)->getValue();
            $this->assertEquals($value, $actualvalue);
        }

        if ($fieldname == 'type') {
            $this->assertFalse($this->form->get_form()->getElement($fieldname)->isFrozen());
        }
    }

    /**
     * Data provider for test_edit_analytics_form.
     *
     * @return array
     */
    public function test_edit_analytics_form_data_provider() {
        return array(
            array('id', true, 'hidden', 3),
            array('enabled', true, 'checkbox', 0),
            array('type', true, 'select', array('piwik')),
            array('siteid', true, 'text', 'siteID'),
            array('trackadmin', true, 'checkbox', 1),
            array('masqueradehandling', true, 'checkbox', 0),
            array('cleanurl', true, 'checkbox', 0),
            array('siteurl', true, 'text', 'site URL'),
            array('imagetrack', true, 'checkbox', 1),
            array('usedimensions', true, 'checkbox', 1),
            array('dimensionid_action[0]', true, 'text', 'ID1'),
            array('dimensioncontent_action[0]', true, 'select', array('Content 1')),
            array('dimensionid_action[1]', true, 'text', 'ID2'),
            array('dimensioncontent_action[1]', true, 'select', array('Content 2')),
            array('dimensionid_visit[0]', true, 'text', 'ID3'),
            array('dimensioncontent_visit[0]', true, 'select', array('Content 3')),
            array('action_add', true, 'submit', 'Add 1 more dimension for action scope'),
            array('visit_add', true, 'submit', 'Add 1 more dimension for visit scope'),
        );
    }

    /**
     * Test form when we editing an analytics.
     *
     * @dataProvider test_edit_analytics_form_data_provider
     *
     * @param string $fieldname A name of the field.
     * @param bool $exist A result of checking if the field is exist.
     * @param string $type A type of the field.
     * @param mixed $value A default value of the field.
     */
    public function test_edit_analytics_form($fieldname, $exist, $type, $value) {
        $this->form = new edit(null, $this->data['dimensions']);
        $this->form->set_data($this->data);
        $this->form->definition_after_data();

        $actual = $this->form->get_form()->elementExists($fieldname);

        $this->assertEquals($exist, $actual);
        if ($actual == true) {
            $actualtype = $this->form->get_form()->getElement($fieldname)->getType();
            $this->assertEquals($type, $actualtype);
            $actualvalue = $this->form->get_form()->getElement($fieldname)->getValue();
            $this->assertEquals($value, $actualvalue);
        }

        if ($fieldname == 'type') {
            $this->assertTrue($this->form->get_form()->getElement($fieldname)->isFrozen());
        }

    }

    /**
     * A data provider for test_validation.
     *
     * @return array
     */
    public function validation_data_provider() {
        return array(
            array(array('type' => 'not piwik'), '', ''),
            array(array('type' => 'piwik'), 'siteurl', 'You must provide Piwik site URL.'),
            array(array('type' => 'piwik', 'siteurl' => '11111'), 'siteurl', 'You must provide valid Piwik site URL.'),
            array(array('type' => 'piwik', 'siteurl' => 'test@ru.com'), 'siteurl', 'You must provide valid Piwik site URL.'),
            array(array('type' => 'piwik', 'siteurl' => 'http://test.ru'), 'siteurl', 'Please provide URL without http(s).'),
            array(array('type' => 'piwik', 'siteurl' => 'https://test.ru'), 'siteurl', 'Please provide URL without http(s).'),
            array(array('type' => 'piwik', 'siteurl' => ' https://test.ru'), 'siteurl', 'You must provide valid Piwik site URL.'),
            array(array('type' => 'piwik', 'siteurl' => 'test.ru/'), 'siteurl', 'Please provide URL without a trailing slash'),
            array(array('type' => 'piwik', 'siteurl' => 'test.ru/ '), 'siteurl', 'Please provide URL without a trailing slash'),
        );
    }

    /**
     * Test form validation.
     *
     * @dataProvider validation_data_provider
     *
     * @param array $data Data array.
     * @param string $errorkey A key to find in the errors array.
     * @param string $errormessage An error message assosiated with the error key.
     */
    public function test_validation($data, $errorkey, $errormessage) {
        $errors = $this->form->validation($data, array());

        if (!empty($errorkey)) {
            $this->assertTrue(array_key_exists($errorkey, $errors));
        }

        if (!empty($errormessage) && isset($errors[$errorkey])) {
            $this->assertEquals($errormessage, $errors[$errorkey]);
        }
    }

    /**
     * Test build_dimensions on empty data.
     */
    public function test_build_dimensions_when_data_is_empty() {
        $data = new stdClass();
        $dimensions = $this->form->build_dimensions($data);
        $this->assertTrue(is_array($dimensions));
        $this->assertEmpty($dimensions);
    }

    /**
     * Test build_dimensions on correct data, but incorrect scope.
     */
    public function test_build_dimensions_when_correct_data_but_incorrect_scope() {
        $data = new stdClass();

        $data->wrong_scope = 1;
        $data->dimensionid_wrong_scope = array('ID1', 'ID2');
        $data->dimensioncontent_wrong_scope = array('Content1', 'Content2');
        $dimensions = $this->form->build_dimensions($data);
        $this->assertTrue(is_array($dimensions));
        $this->assertEmpty($dimensions);
    }

    /**
     * Test build_dimensions on data where id parameter is broken.
     */
    public function test_build_dimensions_when_broken_id_data() {
        $data = new stdClass();
        foreach ($this->knownscopes as $scope) {
            $dimensionidkey = 'dimensionid_broken_' . $scope;
            $dimensioncontentkey = 'dimensioncontent_' . $scope;

            $data->$scope = 2;
            $data->$dimensionidkey = array('ID1', 'ID2');
            $data->$dimensioncontentkey = array('Content1', 'Content2');
        }
        $dimensions = $this->form->build_dimensions($data);
        $this->assertTrue(is_array($dimensions));
        $this->assertEmpty($dimensions);
    }

    /**
     * Test build_dimensions on data where content parameter is broken.
     */
    public function test_build_dimensions_when_broken_content_data() {
        $data = new stdClass();

        foreach ($this->knownscopes as $scope) {
            $dimensionidkey = 'dimensionid_' . $scope;
            $dimensioncontentkey = 'dimensioncontent_broken_' . $scope;

            $data->$scope = 2;
            $data->$dimensionidkey = array('ID1', 'ID2');
            $data->$dimensioncontentkey = array('Content1', 'Content2');
        }
        $dimensions = $this->form->build_dimensions($data);
        $this->assertTrue(is_array($dimensions));
        $this->assertEmpty($dimensions);
    }

    /**
     * Test build_dimensions on data correct data.
     */
    public function test_build_dimensions_when_all_data_is_correct() {
        $data = new stdClass();

        foreach ($this->knownscopes as $scope) {
            $dimensionidkey = 'dimensionid_' . $scope;
            $dimensioncontentkey = 'dimensioncontent_' . $scope;

            $data->$scope = 2;
            $data->$dimensionidkey = array('ID1', 'ID2');
            $data->$dimensioncontentkey = array('Content1', 'Content2');
        }

        $expected = array(
            'action' => array(
                array(
                    'id' => 'ID1',
                    'content' => 'Content1',
                ),
                array(
                    'id' => 'ID2',
                    'content' => 'Content2',
                ),
            ),
            'visit' => array(
                array(
                    'id' => 'ID1',
                    'content' => 'Content1',
                ),
                array(
                    'id' => 'ID2',
                    'content' => 'Content2',
                ),
            ),
        );

        $dimensions = $this->form->build_dimensions($data);
        $this->assertEquals($expected, $dimensions);
    }

    /**
     * Test build_dimensions on data where some items were deleted.
     */
    public function test_build_dimensions_when_some_dimensions_are_deleted() {
        $data = new stdClass();

        foreach ($this->knownscopes as $scope) {
            $dimensionidkey = 'dimensionid_' . $scope;
            $dimensioncontentkey = 'dimensioncontent_' . $scope;

            $data->$scope = 2;
            $data->$dimensionidkey = array('ID1', 'ID2');
            $data->$dimensioncontentkey = array('Content1', 'Content2');
        }

        $data->delete_visit = array(0 => 1);
        $data->delete_action = array(1 => 1);

        $expected = array(
            'action' => array(
                array(
                    'id' => 'ID1',
                    'content' => 'Content1',
                ),
            ),
            'visit' => array(
                array(
                    'id' => 'ID2',
                    'content' => 'Content2',
                ),
            ),
        );

        $dimensions = $this->form->build_dimensions($data);
        $this->assertEquals($expected, $dimensions);
    }

}