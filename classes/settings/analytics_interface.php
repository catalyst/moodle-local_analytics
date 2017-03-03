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
 * Analytics interface.
 *
 * @package     local_analytics
 * @author      Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_analytics\settings;

/**
 * Interface analytics_interface describes analytics behaviour.
 *
 * @package local_analytics\settings
 */
interface analytics_interface {
    /**
     * Check if an analytics is enabled.
     *
     * @return bool
     */
    public function is_enabled();

    /**
     * Return property value.
     *
     * @param string $name Property name.
     *
     * @return mixed Property value.
     *
     * @throws \coding_exception If invalid property requested.
     */
    public function get_property($name);
}
