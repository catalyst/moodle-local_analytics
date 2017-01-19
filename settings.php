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
 * Analytics
 *
 * This module provides extensive analytics on a platform of choice
 * Currently support Google Analytics and Piwik
 *
 * @package    local_analytics
 * @copyright  David Bezemer <info@davidbezemer.nl>, www.davidbezemer.nl
 * @author     David Bezemer <info@davidbezemer.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_analytics\dimensions;

defined('MOODLE_INTERNAL') || die;

if (is_siteadmin()) {
    $settings = new admin_settingpage('local_analytics', get_string('pluginname', 'local_analytics'));
    $ADMIN->add('localplugins', $settings);

    $name = 'local_analytics/enabled';
    $title = get_string('enabled', 'local_analytics');
    $description = get_string('enabled_desc', 'local_analytics');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_analytics/analytics';
    $title = get_string('analytics', 'local_analytics');
    $description = get_string('analyticsdesc', 'local_analytics');
    $ganalytics = get_string('ganalytics', 'local_analytics');
    $guniversal = get_string('guniversal', 'local_analytics');
    $piwik = get_string('piwik', 'local_analytics');
    $default = 'piwik';
    $choices = [
        'piwik'      => $piwik,
        'ganalytics' => $ganalytics,
        'guniversal' => $guniversal,
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    $name = 'local_analytics/siteid';
    $title = get_string('siteid', 'local_analytics');
    $description = get_string('siteid_desc', 'local_analytics');
    $default = '1';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    $name = 'local_analytics/piwikusedimensions';
    $title = get_string('piwikusedimensions', 'local_analytics');
    $description = get_string('piwikusedimensions_desc', 'local_analytics');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    // Find out what scopes are supported (making it future proof).
    $plugins = dimensions::instantiate_plugins();

    foreach ($plugins as $scope => $scopeplugins) {
        $langargs = new \stdClass();
        $langargs->scope = $scope;
        $langargs->custom = ($scope == 'visit') ? 'custom ' : '';

        $name = 'local_analytics/piwik_number_dimensions_'.$scope;
        $title = get_string('piwik_number_dimensions', 'local_analytics', $langargs);
        $description = get_string('piwik_number_dimensions_desc', 'local_analytics', $langargs);
        $default = '5';

        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $settings->add($setting);

        $choices = dimensions::setting_options($scope);
        $numdimensions = get_config('local_analytics', 'piwik_number_dimensions_'.$scope);

        for ($i = 1; $i <= $numdimensions; $i++) {
            // Get an ordinal string to try to make the description less ambiguous.
            // (I could see someone thinking 'Dimension 1' means Piwik ID 1.
            // See http://stackoverflow.com/questions/3109978/display-numbers-with-ordinal-suffix-in-php for info.
            $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
            if (($i % 100) >= 11 && ($i % 100) <= 13) {
                $ordinal = $i.'th';
            } else {
                $ordinal = $i.$ends[$i % 10];
            }

            // A field for entering the dimension ID.
            $name = 'local_analytics/piwikdimensionid_'.$scope.'_'.$i;
            $langargs = new \stdClass();
            $langargs->id = $ordinal;
            $langargs->scope = $scope;
            $title = get_string('piwikdimensionid', 'local_analytics', $langargs);
            $description = get_string('piwikdimensionid_desc', 'local_analytics', $langargs);
            $setting = new admin_setting_configtext($name, $title, $description, '');
            $settings->add($setting);

            // And one for picking what content is used.
            $name = 'local_analytics/piwikdimensioncontent_'.$scope.'_'.$i;
            $langargs = new \stdClass();
            $langargs->id = $ordinal;
            $langargs->scope = $scope;
            $title = get_string('piwikdimension', 'local_analytics', $langargs);
            $description = get_string('piwikdimensiondesc', 'local_analytics', $langargs);
            $setting = new admin_setting_configselect($name, $title, $description, '', $choices);
            $settings->add($setting);
        }
    }

    $name = 'local_analytics/imagetrack';
    $title = get_string('imagetrack', 'local_analytics');
    $description = get_string('imagetrack_desc', 'local_analytics');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_analytics/siteurl';
    $title = get_string('siteurl', 'local_analytics');
    $description = get_string('siteurl_desc', 'local_analytics');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    $name = 'local_analytics/trackadmin';
    $title = get_string('trackadmin', 'local_analytics');
    $description = get_string('trackadmin_desc', 'local_analytics');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_analytics/masquerade_handling';
    $title = get_string('masquerade_handling', 'local_analytics');
    $description = get_string('masquerade_handling_desc', 'local_analytics');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_analytics/cleanurl';
    $title = get_string('cleanurl', 'local_analytics');
    $description = get_string('cleanurl_desc', 'local_analytics');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_analytics/location';
    $title = get_string('location', 'local_analytics');
    $description = get_string('locationdesc', 'local_analytics');
    $head = get_string('head', 'local_analytics');
    $topofbody = get_string('topofbody', 'local_analytics');
    $footer = get_string('footer', 'local_analytics');
    $default = 'head';
    $choices = [
        'head'      => $head,
        'topofbody' => $topofbody,
        'footer'    => $footer,
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
}
