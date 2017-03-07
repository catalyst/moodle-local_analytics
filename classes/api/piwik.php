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

use local_analytics\dimensions;
use local_analytics\settings\analytics_interface;

defined('MOODLE_INTERNAL') || die();

class piwik extends AbstractLocalAnalytics {
    /**
     * Build a custom variable string.
     *
     * @param integer $index   The custom variable index number (1 through 5).
     * @param string  $name    The key name.
     * @param string  $value   The value string.
     * @return string The generated string.
     */
    static public function local_get_custom_var_string($index, $name, $value) {
        return <<<EOD
_paq.push(["setCustomVariable", {$index}, "{$name}", "{$value}", "page"]);

EOD;
    }

    /**
     * see http://piwik.org/blog/2012/10/using-custom-variables-in-piwik-tutorial/
     *
     * There can be up to 5 Custom Variables in the piwik callback.
     * These are dynamically defined
     *
     * Note, in the future this will be replaced with 'Custom Dimensions'
     * - http://piwik.org/docs/custom-variables/
     * https://piwik.org/faq/general/faq_21117/
     */
    static public function insert_custom_moodle_variables(analytics_interface $analytics) {
        global $COURSE, $USER;
        $customvars = "";
        $context = \context_course::instance($COURSE->id);

        // Option is visit/page, see http://piwik.org/docs/custom-variables/ for more information.
        $scope = 'page';

        // User Details ("John Smith ([user_id])").
        $customvars .= self::local_get_custom_var_string(1, 'UserName', self::user_full_name(), $scope);

        // User Role.
        if (is_siteadmin($USER->id)) {
            $rolestr = "Admin";
        } else {
            $roles = get_user_roles($context, $USER->id);
            $rolestr = [];
            foreach ($roles as $role) {
                $rolestr[] = role_get_name($role, $context);
            }
            $rolestr = implode(', ', $rolestr);
        }
        $customvars .= self::local_get_custom_var_string(2, 'UserRole', $rolestr, $scope);

        // Context Type: i.e. page , course, activity ?
        $customvars .= self::local_get_custom_var_string(3, 'Context', $context->get_context_name(), $scope);

        // Course Name "Mathematics for Accountants ([course_id])".
        $customvars .= self::local_get_custom_var_string(4, 'CourseName', $COURSE->fullname, $scope);

        // Max 5 Variables.
        return $customvars;
    }

    /**
     * Build a custom dimension string.
     *
     * @param integer $index
     *            The custom dimension index number (Piwik assigne).
     *            * @param string $context
     *            The string describing the context.
     * @param string  $value
     *            The value string.
     *
     * @return string The generated string.
     */
    static public function local_get_custom_dimension_string($index, $value) {
        $result = '_paq.push(["setCustomDimension", ';
        $result .= 'customDimensionId = '.$index.', ';
        $result .= 'customDimensionValue = "'.$value.'"';
        $result .= "]);\n";

        return $result;
    }

    /**
     * Return the details of a dimension, if configured and usable.
     *
     * @param string $scope
     *   The scope (visit or action) being considered.
     * @param int    $index
     *   The setting index within that slot to consider.
     *
     * @return mixed
     *   Array containing the data if it's to be used, null otherwise.
     */
    static public function get_dimension_values($scope, $data) {
        $plugins = dimensions::instantiate_plugins();

        $dimension = $data['content'];

        if ($dimension == '') {
            return null;
        }

        $key = '\local_analytics\dimension\\'.$dimension;

        if (!array_key_exists($key, $plugins[$scope])) {
            debugging("Local Analytics Piwik Dimension Plugin '${dimension}' is missing.", DEBUG_NORMAL);

            return null;
        }

        $dimensionid = $data['id'];

        if ($dimensionid == '') {
            debugging("Local Analytics Piwik dimension action plugin has been chosen but no
                        ID has been supplied.", DEBUG_NORMAL);

            return null;
        }

        $value = $plugins[$scope][$key]->value();

        return [$dimensionid, $dimension, $value];
    }

    /**
     * Get the variables for a scope.
     *
     * Find out how many settings are enabled, then iterate over them seeing which (if any) are to be used for this
     * page.
     *
     * @param string $scope
     *   The name of the scope being considered (visit or action)
     *
     * @return array
     *   An array of the details to pass to the renderer.
     */
    static public function dimensions_for_scope($scope, $dimensions) {
        $result = [];

        foreach ($dimensions as $data) {
            list($dimensionid, $dimension, $value) = self::get_dimension_values($scope, $data);

            if (!$dimensionid || !$value) {
                continue;
            }

            $result[] = ['id' => $dimensionid, 'dimension' => $dimension, 'value' => $value];
        }

        return $result;
    }

    /**
     * Render the variables for action scope.
     *
     * @param array $dimensions
     *   The list of dimensions to render,
     *
     * @return
     *   The rendered action.
     */
    static public function render_dimensions_for_action_scope($dimensions) {
        $result = '';
        foreach ($dimensions as $dimension) {
            $result .= self::local_get_custom_dimension_string($dimension['id'], $dimension['value']);
        }

        return $result;
    }

    /**
     * Render the variables for visit scope.
     *
     * @param array $dimensions
     *   The list of dimensions to render,
     *
     * @return
     *   The rendered visit.
     */
    static public function render_dimensions_for_visit_scope($dimensions) {
        // Scope is action. For now, just use trackPageView. @TODO: Use trackEvent too.
        $content = ['trackPageView', self::trackurl()];
        $object = new \stdClass();

        foreach ($dimensions as $dimension) {
            $attrib = 'dimension'.$dimension['id'];
            $object->$attrib = $dimension['value'];
        }

        if (!empty($dimensions)) {
            $content[] = $object;
        }

        $result = "_paq.push(".json_encode($content).");\n";
        return $result;
    }

    /**
     * Add Javascript for custom dimensions.
     *
     * http://developer.piwik.org/guides/tracking-javascript-guide#custom-dimensions
     */
    static public function insert_custom_moodle_dimensions(analytics_interface $analytics) {

        $plugins = dimensions::instantiate_plugins();
        $customvars = '';
        $data = $analytics->get_property('dimensions');

        foreach (array_keys($plugins) as $scope) {
            if (isset($data[$scope]) && !empty($data[$scope])) {
                $dimensions = self::dimensions_for_scope($scope, $data[$scope]);
                $renderer = "render_dimensions_for_${scope}_scope";
                $customvars .= self::$renderer($dimensions);
            }
        }

        return $customvars;
    }

    /**
     * see http://piwik.org/blog/2012/10/using-custom-variables-in-piwik-tutorial/
     *
     * There can be up to 5 Custom Variables in the piwik callback.
     * These are dynamically defined
     *
     * Note, in the future this will be replaced with 'Custom Dimensions'
     * - http://piwik.org/docs/custom-variables/
     * https://piwik.org/faq/general/faq_21117/
     */
    static public function local_insert_custom_moodle_vars(analytics_interface $analytics) {
        if ($analytics->get_property('usedimensions')) {
            return self::insert_custom_moodle_dimensions($analytics);
        } else {
            return self::insert_custom_moodle_variables($analytics);
        }
    }

    static public function insert_tracking(analytics_interface $analytics) {
        global $CFG, $USER;

        $imagetrack = $analytics->get_property('imagetrack');
        $siteurl = $analytics->get_property('siteurl');
        $siteid = $analytics->get_property('siteid');
        $cleanurl = $analytics->get_property('cleanurl');
        $location = "additionalhtmlhead";

        if (!empty($siteurl)) {
            if ($imagetrack) {
                $addition = '<noscript><p><img src="//'.$siteurl.'/piwik.php?idsite='.$siteid.'" style="border:0;" alt="" /></p></noscript>';
            } else {
                $addition = '';
            }

            if ($cleanurl) {
                $doctitle = "_paq.push(['setDocumentTitle', '".self::trackurl()."']);\n";
            } else {
                $doctitle = "";
            }

            if (self::should_track($analytics)) {
                $CFG->$location .= "
    <!-- Start Piwik Code -->
    <script type='text/javascript'>
        var _paq = _paq || [];
        ".$doctitle.self::local_insert_custom_moodle_vars($analytics)."
        _paq.push(['setUserId', $USER->id]);
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        _paq.push(['enableHeartBeatTimer', 30]);
        (function() {
          var u='//".$siteurl."/';
          _paq.push(['setTrackerUrl', u+'piwik.php']);
          _paq.push(['setSiteId', ".$siteid."]); var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
        g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
        })();
    </script>".$addition."<!-- End Piwik Code -->\n";
            }
        }
    }
}
