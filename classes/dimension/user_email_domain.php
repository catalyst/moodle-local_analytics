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
 * User name dimension definition.
 */

namespace local_analytics\dimension;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/dimension_interface.php');

class user_email_domain implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    public static $name = 'user_email_domain';

    /**
     * Scope of the dimension.
     */
    public static $scope = 'visit';

    /**
     * Get the value for js to send.
     *
     * @return mixed
     *   The value of the dimension.
     */
    public function value() {
        global $USER;

        // Handle guest without error.
        if (!isset($USER->email)) {
            return false;
        }

        $parts = explode('@', $USER->email);
        return $parts[1];
    }
}
