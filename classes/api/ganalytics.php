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
 * @package   local_analytics
 * @copyright David Bezemer <info@davidbezemer.nl>, www.davidbezemer.nl
 * @author    David Bezemer <info@davidbezemer.nl>, Bas Brands <bmbrands@gmail.com>, Gavin Henrick <gavin@lts.ie>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_analytics\api;

defined('MOODLE_INTERNAL') || die();

class ganalytics extends AbstractLocalAnalytics {
    public static function insert_tracking() {
        global $CFG;

        $siteid = get_config('local_analytics', 'siteid');
        $cleanurl = get_config('local_analytics', 'cleanurl');
        $location = "additionalhtml".get_config('local_analytics', 'location');

        if (self::should_track()) {
            $page = ($cleanurl ? "'".self::trackurl(true, true)."'" : '');
            $CFG->$location .= <<<HTML
<script type="text/javascript" name="localga">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '{$siteid}']);
  _gaq.push(['_trackPageview', {$page}]);
  _gaq.push(['_setSiteSpeedSampleRate', 50]);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript';
    ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>

HTML;
        }
    }
}
