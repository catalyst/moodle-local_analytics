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
 * Edit page for analytics.
 *
 * @package    local_analytics
 * @author     Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_analytics\form\edit;
use local_analytics\settings\analytics;
use local_analytics\settings\analytics_manager;

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_analytics_manage');

$edit = optional_param('edit', 0, PARAM_INT);

$manageurl = new moodle_url('/local/analytics/manage.php');

$action = 'create';
$record = null;
$analytics = null;
$dimensions = null;
$manager = new analytics_manager();

if ($edit) {
    $record = $manager->get($edit);
    if (empty($record)) {
        print_error('not_found', 'local_analytics', $manageurl);
    }
    $action = 'edit';
    $analytics = new analytics($record);
    $dimensions = $analytics->get_property('dimensions');
}

$mform = new edit(null, $dimensions);
$mform->set_data($record);

if ($mform->is_cancelled()) {
    redirect($manageurl);
} else if ($data = $mform->get_data()) {
    $analytics = new analytics($data);
    $manager->save($analytics);
    redirect($manageurl);
}


$PAGE->navbar->add(get_string($action . '_breadcrumb', 'local_analytics'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string($action . '_heading', 'local_analytics'));
$mform->display();
echo $OUTPUT->footer();
