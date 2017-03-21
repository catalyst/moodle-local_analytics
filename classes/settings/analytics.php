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
 * Analytics class.
 *
 * @package     local_analytics
 * @author      Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_analytics\settings;

use coding_exception;
use stdClass;

/**
 * Class analytics described single analytics built from DB record.
 *
 * @package local_analytics\settings
 */
class analytics implements analytics_interface {

    /**
     * Analytics ID.
     *
     * @var int|null
     */
    protected $id = null;

    /**
     * Shows if an analytics is enabled.
     *
     * @var int
     */
    protected $enabled = 0;

    /**
     * Analytics location on the page.
     *
     * @var string
     */
    protected $location = 'head';

    /**
     * Type of an analytics.
     *
     * @var string
     */
    protected $type = 'piwik';

    /**
     * Site ID of an analytics.
     *
     * @var string
     */
    protected $siteid = '1';

    /**
     * Shows if an analytics should track admins.
     *
     * @var int
     */
    protected $trackadmin = 0;

    /**
     * Shows if an analytics should track login as users.
     *
     * @var int
     */
    protected $masqueradehandling = 0;

    /**
     * Shows if an analytics should support clean urls.
     *
     * @var int
     */
    protected $cleanurl = 0;

    /**
     * A site URL of an analytics.
     *
     * @var string|null
     */
    protected $siteurl = null;

    /**
     * Shows if an analytics should track images.
     *
     * @var int
     */
    protected $imagetrack = 0;

    /**
     * Shows if an analytics should use customs dimensions.
     *
     * @var int
     */
    protected $usedimensions = 0;

    /**
     * Custom dimensions list.
     *
     * @var array
     */
    protected $dimensions = array();

    /**
     * Constructor.
     *
     * @param stdClass $data Data to build an analytics from.
     *
     * @throws coding_exception
     */
    public function __construct(stdClass $data) {
        if (empty((array)$data)) {
            throw new coding_exception('Empty $data object is provided for analytics class.');
        }

        foreach ($data as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * Check if an analytics is enabled.
     *
     * @return bool
     */
    public function is_enabled() {
        return !empty($this->get_property('enabled'));
    }

    /**
     * Return property value.
     *
     * @param string $name Property name.
     *
     * @return mixed Property value.
     *
     * @throws \coding_exception If invalid property requested.
     */
    public function get_property($name) {
        if (!property_exists($this, $name)) {
            throw new coding_exception('Requested invalid property.', $name);
        }

        if ($name == 'dimensions') {
            return $this->get_dimensions();
        }

        return $this->$name;
    }

    /**
     * Return dimensions.
     *
     * @return array Dimensions array.
     */
    protected function get_dimensions() {
        if (!is_array($this->dimensions)) {
            $this->dimensions = array();
        }

        return $this->dimensions;
    }
}
