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
     * Return form object.
     *
     * @return \MoodleQuickForm
     */
    public function get_form() {
        return $this->_form;
    }

    /**
     * {@inheritDoc}
     *
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

        $mform->addElement('checkbox', 'masqueradehandling', get_string('masqueradehandling', 'local_analytics'));
        $mform->addHelpButton('masqueradehandling', 'masqueradehandling', 'local_analytics');
        $mform->setDefault('masqueradehandling', 1);

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
     * {@inheritDoc}
     *
     * @see moodleform::validation()
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['type'] == 'piwik') {
            if (!isset($data['siteurl']) || empty($data['siteurl'])) {
                $errors['siteurl'] = 'You must provide Piwik site URL.';
            } else {
                if (empty(clean_param($data['siteurl'], PARAM_URL))) {
                    $errors['siteurl'] = 'You must provide valid Piwik site URL.';
                }

                if (preg_match("/^(http|https):\/\//", $data['siteurl'])) {
                    $errors['siteurl'] = 'Please provide URL without http(s).';
                }

                if (substr(trim($data['siteurl']), -1) == '/') {
                    $errors['siteurl'] = 'Please provide URL without a trailing slash';
                }
            }
        }

        // TODO: check if we have the same record.

        return $errors;
    }

    /**
     * {@inheritDoc}
     *
     * @see moodleform::get_data()
     */
    public function get_data() {
        $data = parent::get_data();

        if (!empty($data)) {
            $data->dimensions = $this->build_dimensions($data);
        }

        return $data;
    }

    /**
     * Build dimensions array from the form data.
     *
     * @param \stdClass $data Data object returned by get_data() function.
     *
     * @return array A list of dimensions keyed by scope.
     */
    public function build_dimensions(\stdClass $data) {
        $dimensions = array();
        $plugins = dimensions::instantiate_plugins();

        foreach ($plugins as $scope => $scopeplugins) {

            // Check if we are actually getting dimensions of the scope from the form.
            if (isset($data->$scope) && !empty($data->$scope)) {

                // Let's build required keys to access the values in the data.
                $dimensionidkey = 'dimensionid_' . $scope;
                $dimensioncontentkey = 'dimensioncontent_' . $scope;
                $dimensiondeletekey = 'delete_' . $scope;

                // How many dimensions of that scope was submitted?
                $numberofdimensions = $data->$scope;

                if (isset($data->$dimensionidkey) && isset($data->$dimensioncontentkey)) {

                    // Iterate through all dimensions of that scope.
                    for ($i = 0; $i < $numberofdimensions; $i++) {

                        // Check if the current dimension has id set and 'Delete" checkbox is not checked.
                        if (!empty($data->{$dimensionidkey}[$i]) && !isset($data->{$dimensiondeletekey}[$i])) {

                            if (!isset($dimensions[$scope])) {
                                $dimensions[$scope] = array();
                            }

                            $dimensions[$scope][] = array(
                                'id' => $data->{$dimensionidkey}[$i],
                                'content' => $data->{$dimensioncontentkey}[$i],
                            );
                        }
                    }
                }
            }
        }

        return $dimensions;
    }

    /**
     * {@inheritDoc}
     *
     * @see moodleform::definition_after_data()
     */
    public function definition_after_data() {
        parent::definition_after_data();

        $mform = $this->_form;

        $id = $mform->getElementValue('id');

        if (!empty($id)) {
            $mform->freeze(array('type'));
        }
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
            $dimensiondeletekey = 'delete_' . $scope;
            $currentdimensions = array();

            if (!empty($this->_customdata)) {
                if (isset($this->_customdata[$scope]) && !empty($this->_customdata[$scope])) {
                    foreach ($this->_customdata[$scope] as $default) {
                        $currentdimensions[] = $default;
                    }
                }
            }

            $currentnum = count($currentdimensions);

            $elementobjs[$scope][] = $mform->createElement('html', '<hr>');

            $repeatoptions[$scope][$dimensionidkey]['type'] = PARAM_TEXT;
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

            $elementobjs[$scope][] = $mform->createElement('checkbox',
                $dimensiondeletekey,
                get_string('delete')
            );

            $addtext = get_string('adddimension', 'local_analytics', $scope);
            $addname = $scope . '_add';

            $this->repeat_elements($elementobjs[$scope], $currentnum, $repeatoptions[$scope], $scope, $addname, 1, $addtext, false);

            if ($currentnum > 0) {
                foreach ($currentdimensions as $key => $currentdimension) {
                    $mform->setDefault($dimensionidkey . "[{$key}]", $currentdimension['id']);
                    $mform->setDefault($dimensioncontentkey . "[{$key}]", $currentdimension['content']);
                    $mform->setType($dimensionidkey . "[{$key}]", PARAM_TEXT);
                    $mform->setType($dimensioncontentkey . "[{$key}]", PARAM_TEXT);

                }
            }

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

