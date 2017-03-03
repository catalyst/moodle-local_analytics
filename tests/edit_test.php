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
     * Initial set up.
     */
    public function setUp() {
        global $CFG;

        require_once($CFG->dirroot . '/lib/formslib.php');

        $this->data = array(
            'id' => 3,
            'enabled' => 0,
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

        $this->resetAfterTest();
    }

    /**
     * Data provider for test_new_analytics_form_definition.
     *
     * @return array
     */
    public function test_create_analytics_form_definition_data_provider() {
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
     * Test form definition when we create a new analytics.
     *
     * @dataProvider test_create_analytics_form_definition_data_provider
     *
     * @param string $fieldname A name of the field.
     * @param bool $exist A result of checking if the field is exist.
     * @param string $type A type of the field.
     * @param mixed $value A default value of the field.
     */
    public function test_create_analytics_form_definition($fieldname, $exist, $type, $value) {
        $this->form->definition();

        $actual = $this->form->get_form()->elementExists($fieldname);

        $this->assertEquals($exist, $actual);
        if ($actual == true) {
            $actualtype = $this->form->get_form()->getElement($fieldname)->getType();
            $this->assertEquals($type, $actualtype);
            $actualvalue = $this->form->get_form()->getElement($fieldname)->getValue();
            $this->assertEquals($value, $actualvalue);
        }
    }

    /**
     * Data provider for test_new_analytics_form_definition.
     *
     * @return array
     */
    public function test_edit_analytics_form_definition_data_provider() {
        return array(
            array('id', true, 'hidden', $this->data['id']),
            array('enabled', true, 'checkbox', $this->data['enabled']),
            array('type', true, 'select', $this->data['type']),
            array('siteid', true, 'text', $this->data['siteid']),
            array('trackadmin', true, 'checkbox', $this->data['trackadmin']),
            array('masqueradehandling', true, 'checkbox', $this->data['masqueradehandling']),
            array('cleanurl', true, 'checkbox', $this->data['cleanurl']),
            array('siteurl', true, 'text', $this->data['siteurl']),
            array('imagetrack', true, 'checkbox', $this->data['imagetrack']),
            array('usedimensions', true, 'checkbox', $this->data['usedimensions']),
            array('dimensionid_action[0]', true, 'text', 'ID1'),
            array('dimensioncontent_action[0]', true, 'select', 'Content 1'),
            array('dimensionid_action[1]', true, 'text', 'ID2'),
            array('dimensioncontent_action[1]', true, 'select', 'Content 2'),
            array('dimensionid_visit[0]', true, 'text', 'ID3'),
            array('dimensioncontent_visit[0]', true, 'select', 'Content 3'),
            array('action_add', true, 'submit', 'Add 1 more dimension for action scope'),
            array('visit_add', true, 'submit', 'Add 1 more dimension for visit scope'),
        );
    }

    /**
     * Test form definition when we editing an analytics and dimensions are set.
     *
     * @dataProvider test_edit_analytics_form_definition_data_provider
     *
     * @param string $fieldname A name of the field.
     * @param bool $exist A result of checking if the field is exist.
     * @param string $type A type of the field.
     */
    public function test_edit_analytics_form_definition($fieldname, $exist, $type) {
        $this->form = new edit(null, $this->data['dimensions']);
        $this->form->set_data($this->data);
        $this->form->definition();

        $actual = $this->form->get_form()->elementExists($fieldname);

        $this->assertEquals($exist, $actual);
        if ($actual == true) {
            $actualtype = $this->form->get_form()->getElement($fieldname)->getType();
            $this->assertEquals($type, $actualtype);
        }
    }

}