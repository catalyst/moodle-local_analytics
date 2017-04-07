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
 * Analytics manager.
 *
 * @package     local_analytics
 * @author      Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_analytics\settings;

use stdClass;
use cache;

/**
 * Class analytics_manager manages analytics in DB.
 *
 * @package local_analytics\settings
 */
class analytics_manager implements analytics_manager_interface {
    /**
     * An analytics table name table name.
     */
    const TABLE_NAME = 'local_analytics';

    /**
     * A name of the cache component.
     */
    const CACHE_COMPONENT = 'local_analytics';

    /**
     * A name of the cache area.
     */
    const CACHE_AREA = 'records';

    /**
     * A cache key name to store all records.
     */
    const CACHE_ALL_RECORDS_KEY = 'allrecords';

    /**
     * A cache key name to store only enabled records.
     */
    const CACHE_ENABLED_RECORDS_KEY = 'enabledrecords';

    /**
     * Global DB object.
     *
     * @var \moodle_database
     */
    protected $db;

    /**
     * A a cache instance.
     *
     * @var \cache_application|\cache_session|\cache_store
     */
    protected $cache;

    /**
     * A list of all records.
     *
     * @var bool|false|mixed
     */
    protected $allrecords = false;

    /**
     * A list of enabled records.
     *
     * @var bool|false|mixed
     */
    protected $enabledrecords = false;

    /**
     * Constructor.
     */
    public function __construct() {
        global $DB;

        $this->db = $DB;
        $this->cache = cache::make(self::CACHE_COMPONENT, self::CACHE_AREA);
        $this->allrecords = $this->cache->get(self::CACHE_ALL_RECORDS_KEY);
        $this->enabledrecords = $this->cache->get(self::CACHE_ENABLED_RECORDS_KEY);
    }

    /**
     * Returns single analytics record.
     *
     * Note: we don't want to use cache here.
     *
     * @param int $id Analytics record ID.
     *
     * @return mixed
     */
    public function get($id) {
        $record = $this->db->get_record(self::TABLE_NAME, array('id' => $id));

        if (!empty($record) && !empty($record->dimensions)) {
            $record->dimensions = unserialize($record->dimensions);
        }

        return $record;
    }

    /**
     * Returns all existing analytics records.
     *
     * @return array
     */
    public function get_all() {
        if ($this->allrecords === false) {
            $this->allrecords = $this->get_multiple();
            $this->cache->set(self::CACHE_ALL_RECORDS_KEY, $this->allrecords);
        }

        return $this->allrecords;
    }

    /**
     * Returns all existing enabled analytics records.
     *
     * @return array
     */
    public function get_enabled() {
        if ($this->enabledrecords === false) {
            $this->enabledrecords = $this->get_multiple(array('enabled' => 1));
            $this->cache->set(self::CACHE_ENABLED_RECORDS_KEY, $this->enabledrecords);
        }

        return $this->enabledrecords;
    }

    /**
     * Saves analytics data.
     *
     * @param \local_analytics\settings\analytics_interface $analytics
     *
     * @return int ID of the analytics.
     */
    public function save(analytics_interface $analytics) {
        $record = $this->build_record($analytics);

        if (empty($record->id)) {
            $record->id = $this->db->insert_record(self::TABLE_NAME, $record, true);
        } else {
            $this->db->update_record(self::TABLE_NAME, $record);
        }

        $this->clear_caches();

        return $record->id;
    }

    /**
     * Build analytics record for inserting/updaing it in DB.
     *
     * @param \local_analytics\settings\analytics_interface $analytics
     *
     * @return \stdClass
     */
    protected function build_record(analytics_interface $analytics) {
        $record = new stdClass();

        foreach ($this->get_table_fields() as $fieldname) {
            $value = $analytics->get_property($fieldname);
            if ($fieldname == 'dimensions') {
                // We expect dimensions to be a not empty array.
                if (is_array($value) && !empty($value)) {
                    $value = serialize($value);
                } else {
                    $value = null;
                }
            }

            $record->$fieldname = $value;
        }

        return $record;
    }

    /**
     * Delete analytics.
     *
     * @param int $id Analytics record ID.
     *
     * @return void
     */
    public function delete($id) {
        $this->db->delete_records(self::TABLE_NAME, array('id' => $id));
        $this->clear_caches();
    }

    /**
     * Get multiple records of the analytics.
     *
     * @param array $params Parameters to use in get_records functions.
     *
     * @return array A list of analytics.
     */
    protected function get_multiple($params = array()) {
        $records = $this->db->get_records(self::TABLE_NAME, $params, 'id');

        if (!empty($records)) {
            foreach ($records as $record) {
                if (!empty($record) && !empty($record->dimensions)) {
                    $record->dimensions = unserialize($record->dimensions);
                    $records[$record->id] = $record;
                }
            }
        }

        return $records;
    }

    /**
     * Return a list of the fields for analytics table.
     *
     * @return array A list of fields.
     */
    protected function get_table_fields() {
        return array_keys($this->db->get_columns(self::TABLE_NAME));
    }

    /**
     * Clear caches for analytics records.
     */
    protected function clear_caches() {
        $this->allrecords = false;
        $this->enabledrecords = false;
        $this->cache->delete(self::CACHE_ALL_RECORDS_KEY);
        $this->cache->delete(self::CACHE_ENABLED_RECORDS_KEY);
    }

}