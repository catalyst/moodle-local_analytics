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

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__.'/dimension_interface.php');

class course_category_hierarchy_full_path implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    public static $name = 'course_category_hierarchy_full_path';

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
        global $COURSE;

        $context = \context_course::instance($COURSE->id);
        $parents = $context->get_parent_contexts();

        // The lowest level is given first.
        $parents = array_reverse($parents);

        // Elide the top level ('System') context.
        array_shift($parents);

        $result = '';

        foreach ($parents as $key => $content) {
            $result .= '\\'.$content->get_context_name(false);
        }

        $result = substr($result, 1);
        return $result;
    }
}
