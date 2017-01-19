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
 * Interface to enumerate and use dimension classes
 */

namespace local_analytics;

defined('MOODLE_INTERNAL') || die();

class dimensions {
    /**
     * The array of class instances.
     */
    static private $dimensioninstances = null;

    /**
     * Find class instances and populate the array.
     *
     * Moodle core 3.0 and lower don't have a way to find all the classes automagically :(
     * See MDL-46155.
     *
     * @return array of strings
     *   A list of the names of files containing plugins.
     */
    static public function enumerate_plugins() {
        $dir = dirname(__FILE__).'/dimension';

        $listoffiles = scandir($dir);
        foreach ($listoffiles as $index => $entry) {
            if ($entry == '.' || $entry == '..' || substr($entry, -4) != '.php' || $entry == 'dimension_interface.php') {
                unset($listoffiles[$index]);
            }
        }

        return $listoffiles;
    }

    /**
     * Instantiate a single plugin and add it to the class level cache.
     *
     * @param string $classname
     *   The name of the class that should be defined by the file.
     */
    static public function instantiate_plugin($classname) {

        $instance = new $classname;
        $scope = $instance::$scope;

        if (!array_key_exists($scope, self::$dimensioninstances)) {
            self::$dimensioninstances[$scope] = [];
        }

        if (!is_null($instance)) {
            self::$dimensioninstances[$scope][$classname] = $instance;
        }
    }

    /**
     * Instantiate plugins and populate the array.
     *
     * @return array
     *   An array keyed by plugin scope and class, with values being class instances.
     */
    static public function instantiate_plugins() {
        if (is_null(self::$dimensioninstances)) {
            $listoffiles = self::enumerate_plugins();

            self::$dimensioninstances = [];

            foreach ($listoffiles as $index => $entry) {
                $classname = '\local_analytics\dimension\\'.substr($entry, 0, -4);

                self::instantiate_plugin($classname);
            }
        }

        return self::$dimensioninstances;
    }

    /**
     * Get plugin options list.
     *
     * @param string $scoperequested
     *   The scope by which to filter results.
     *
     * @return array
     *   An array of items for a select combo.
     */
    static public function setting_options($scoperequested) {
        static $result = null;

        if (is_null($result)) {
            $plugins = self::instantiate_plugins();

            $result = [];

            foreach ($plugins as $scope => $scopeplugins) {
                // Nothing is selected entry.
                $result[$scope][''] = '';

                foreach ($scopeplugins as $file => $plugin) {
                    $langstring = get_string($plugin::$name, 'local_analytics');
                    $result[$scope][$plugin::$name] = $langstring;
                }
            }
        }

        return $result[$scoperequested];
    }
}
