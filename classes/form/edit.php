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
 * Edit form.
 *
 * @package     local_analytics
 * @author      Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_analytics\form;

use local_analytics\dimensions;
use moodleform;

/**
 * Class for edit form.
 *
 * @package local_analytics\form
 */
class edit extends moodleform {

    /**
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('checkbox', 'enabled', get_string('enabled', 'local_analytics'));
        $mform->addHelpButton('enabled', 'enabled', 'local_analytics');
        $mform->setDefault('enabled', 1);

        $choices = array(
            'piwik' => get_string('piwik', 'local_analytics'),
            'guniversal' => get_string('guniversal', 'local_analytics'),
            'ganalytics' => get_string('ganalytics', 'local_analytics'),
        );

        $mform->addElement('select', 'type', get_string('type', 'local_analytics'), $choices);
        $mform->addHelpButton('type', 'type', 'local_analytics');
        $mform->setType('type', PARAM_TEXT);

        $mform->addElement('text', 'siteid', get_string('siteid', 'local_analytics'));
        $mform->addHelpButton('siteid', 'siteid', 'local_analytics');
        $mform->setType('siteid', PARAM_TEXT);
        $this->_form->addRule('siteid', get_string('required'), 'required', null, 'client');

        $mform->addElement('checkbox', 'trackadmin', get_string('trackadmin', 'local_analytics'));
        $mform->addHelpButton('trackadmin', 'trackadmin', 'local_analytics');

        $mform->addElement('checkbox', 'masquerade_handling', get_string('masquerade_handling', 'local_analytics'));
        $mform->addHelpButton('masquerade_handling', 'masquerade_handling', 'local_analytics');
        $mform->setDefault('masquerade_handling', 1);

        $mform->addElement('checkbox', 'cleanurl', get_string('cleanurl', 'local_analytics'));
        $mform->addHelpButton('cleanurl', 'cleanurl', 'local_analytics');
        $mform->setDefault('cleanurl', 1);

        $mform->addElement('text', 'siteurl', get_string('siteurl', 'local_analytics'));
        $mform->addHelpButton('siteurl', 'siteurl', 'local_analytics');
        $mform->setType('siteurl', PARAM_TEXT);
        $this->disable_element_if_not_piwik('siteurl');

        $mform->addElement('checkbox', 'imagetrack', get_string('imagetrack', 'local_analytics'));
        $mform->addHelpButton('imagetrack', 'imagetrack', 'local_analytics');
        $this->disable_element_if_not_piwik('imagetrack');

        $mform->addElement('checkbox', 'usedimensions', get_string('usedimensions', 'local_analytics'));
        $mform->addHelpButton('usedimensions', 'usedimensions', 'local_analytics');
        $this->disable_element_if_not_piwik('usedimensions');

        $this->add_dimension_fields();

        $this->add_action_buttons();
    }

    /**
     * Add custom dimension fields to the form.
     *
     * @throws \coding_exception
     */
    protected function add_dimension_fields() {
        $mform = $this->_form;

        $plugins = dimensions::instantiate_plugins();

        foreach ($plugins as $scope => $scopeplugins) {
            $elementobjs = array();
            $repeatoptions = array();
            $elementobjs[$scope] = array();
            $repeatoptions[$scope] = array();

            $choices = dimensions::setting_options($scope);

            $dimensionidkey = 'dimensionid_' . $scope;
            $dimensioncontentkey = 'dimensioncontent_' . $scope;

            $elementobjs[$scope][] = $mform->createElement('html', '<hr>');

            $repeatoptions[][$dimensionidkey]['type'] = PARAM_TEXT;
            $repeatoptions[$scope][$dimensionidkey]['disabledif'] = array('type', 'neq', 'piwik');
            $elementobjs[$scope][] = $mform->createElement('text',
                $dimensionidkey,
                get_string('dimensionid', 'local_analytics', $scope)
            );

            $repeatoptions[$scope][$dimensioncontentkey]['type'] = PARAM_TEXT;
            $repeatoptions[$scope][$dimensioncontentkey]['disabledif'] = array('type', 'neq', 'piwik');
            $elementobjs[$scope][] = $mform->createElement('select',
                $dimensioncontentkey,
                get_string('dimensioncontent', 'local_analytics', $scope),
                $choices
            );

            $addtext = get_string('adddimension', 'local_analytics', $scope);
            $addname = $scope . '_add';

            $this->repeat_elements($elementobjs[$scope], 0, $repeatoptions[$scope], $scope, $addname, 1, $addtext, false);
            $this->disable_element_if_not_piwik($addname);
        }
    }

    /**
     * Disable the form element if analytics element is not Piwik.
     *
     * @param string $name A name of the form element.
     */
    protected function disable_element_if_not_piwik($name) {
        $mform = $this->_form;
        $mform->disabledIf($name, 'type', 'neq', 'piwik');
    }
}

