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
 * Course name dimension definition.
 */

namespace local_analytics\dimension;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/dimension_interface.php');

class course_enrolment_method implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    public static $name = 'course_enrolment_method';

    /**
     * Scope of the dimension.
     */
    public static $scope = 'action';

    /**
     * Get the value for js to send.
     *
     * @return mixed
     *   The value of the dimension.
     */
    public function value() {
        global $COURSE, $USER, $DB;

        $context = \context_course::instance($COURSE->id);

        // Based on is_enrolled in accesslib.php.

        if ($context->instanceid == SITEID) {
            // Everybody participates on frontpage.
            return false;
        }

        $until = enrol_get_enrolment_end($context->instanceid, $USER->id);

        if ($until === false) {
            return false;
        }

        // Stolen from is_enrolled.
        $sql = "SELECT e.enrol
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid)
                      JOIN {user} u ON u.id = ue.userid
                     WHERE ue.userid = :userid AND u.deleted = 0";
        $params = ['userid' => $USER->id, 'courseid' => $COURSE->id];
        $method = $DB->get_field_sql($sql, $params, IGNORE_MULTIPLE);

        return $method;
    }
}
