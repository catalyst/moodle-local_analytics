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
 * Disable or enable an analytics.
 *
 * @package     local_analytics
 * @author      Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_analytics\settings\analytics_manager;
use local_analytics\settings\analytics;

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_analytics_manage');

$action = 'status';
$id = required_param('id', PARAM_INT);

$manageurl = new moodle_url('/local/analytics/manage.php');
$manager = new analytics_manager();
$record = $manager->get($id);

if (empty($record) || !isset($record->enabled)) {
    print_error('not_found', 'local_analytics', $manageurl);
}

$record->enabled = 1 - $record->enabled;

$analytics = new analytics($record);
$manager->save($analytics);

redirect($manageurl);
