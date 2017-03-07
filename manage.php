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

use local_analytics\table\analytics_table;
use local_analytics\settings\analytics_manager;
use local_analytics\settings\analytics;

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_analytics_manage');

$createurl = new moodle_url('/local/analytics/edit.php');
$manageurl = new moodle_url('/local/analytics/manage.php');

$manager = new analytics_manager();


$PAGE->set_url($manageurl);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage_heading', 'local_analytics'));

echo $OUTPUT->render(new single_button($createurl, 'Add new Analytics'));

$records = $manager->get_all();

if (!empty($records)) {
    $analyticslist = array();
    foreach ($records as $record) {
        $analyticslist[] = new analytics($record);
    }
    $table = new analytics_table();
    $table->show_data($analyticslist);
    $table->finish_output();
} else {
    echo get_string('no_analytics', 'local_analytics');
}

echo $OUTPUT->footer();
