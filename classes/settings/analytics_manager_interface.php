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
 * Analytics manager interface.
 *
 * @package     local_analytics
 * @author      Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_analytics\settings;

/**
 * Interface analytics_manager_interface describes analytics manager behaviour.
 *
 * @package local_analytics\settings
 */
interface analytics_manager_interface {

    /**
     * Returns single analytics record.
     *
     * @param int $id Analytics record ID.
     *
     * @return mixed
     */
    public function get($id);

    /**
     * Returns all existing analytics records.
     *
     * @return array
     */
    public function get_all();

    /**
     * Returns all existing enabled analytics records.
     *
     * @return array
     */
    public function get_enabled();

    /**
     * Saves analytics data.
     *
     * @param \local_analytics\settings\analytics_interface $analytics
     *
     * @return int ID of the analytics.
     */
    public function save(analytics_interface $analytics);

    /**
     * Delete analytics.
     *
     * @param int $id Analytics record ID.
     *
     * @return void
     */
    public function delete($id);
}
