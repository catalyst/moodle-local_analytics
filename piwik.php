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
 * @author     David Bezemer <info@davidbezemer.nl>, Bas Brands <bmbrands@gmail.com>, Gavin Henrick <gavin@lts.ie>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
function local_analytics_trackurl() {
    global $DB, $PAGE, $COURSE, $SITE;
    $pageinfo = get_context_info_array($PAGE->context->id);
    $trackurl = "'";

    // Adds course category name.
    if (isset($pageinfo[1]->category)) {
        if ($category = $DB->get_record('course_categories', array('id'=>$pageinfo[1]->category))) {
            $cats=explode("/",$category->path);
            foreach(array_filter($cats) as $cat) {
                if ($categorydepth = $DB->get_record("course_categories", array("id" => $cat))) {;
                    $trackurl .= $categorydepth->name.'/';
                }
            }
        }
    }

    // Adds course full name.
    if (isset($pageinfo[1]->fullname)) {
        if (isset($pageinfo[2]->name)) {
            $trackurl .= $pageinfo[1]->fullname.'/';
        } else if ($PAGE->user_is_editing()) {
            $trackurl .= $pageinfo[1]->fullname.'/'.get_string('edit', 'local_analytics');
        } else {
            $trackurl .= $pageinfo[1]->fullname.'/'.get_string('view', 'local_analytics');
        }
    }

    // Adds activity name.
    if (isset($pageinfo[2]->name)) {
        $trackurl .= $pageinfo[2]->modname.'/'.$pageinfo[2]->name;
    }
    
    $trackurl .= "'";
    return $trackurl;
}

/**
 * see http://piwik.org/blog/2012/10/using-custom-variables-in-piwik-tutorial/
 *
 * There can be up to 5 Custom Variables in the piwik callback.
 *  These are dynamically defined

 * Note, in the future this will be replaced with 'Custom Dimensions'
 *  - http://piwik.org/docs/custom-variables/
 *    https://piwik.org/faq/general/faq_21117/
 **/

function local_insert_custom_moodle_vars() {
    global $DB, $PAGE, $COURSE, $SITE;
    $customvars="";

    // Option is visit/page
    // see http://piwik.org/docs/custom-variables/
    $scope = 'page';

    //User Details
    // "John Smith ([user_id])"
    $customvars.='piwikTracker.setCustomVariable(1, "UserName", "'.("").'", '.$scope.');';
    $customvars.='piwikTracker.trackPageView();'; #NOTE: I'm not sure if you have do this every time.

    //User Role
    $customvars.='piwikTracker.setCustomVariable(2, "UserRole", "'.("").'", '.$scope.');';
    $customvars.='piwikTracker.trackPageView();';

    //Context Type: i.e. page , course, activity ?
    $customvars.='piwikTracker.setCustomVariable(3, "Context", "'.("").'", '.$scope.');';
    $customvars.='piwikTracker.trackPageView();';

    //Course Name
    // "Mathematics for Accountants ([course_id])"
    $customvars.='piwikTracker.setCustomVariable(4, "CourseName", "'.("").'", '.$scope.');';
    $customvars.='piwikTracker.trackPageView();';

    //Max 5 Variables

    return $customvars;

}
 
function insert_local_analytics_tracking() {
    global $CFG;
    $enabled = get_config('local_analytics', 'enabled');
    $imagetrack = get_config('local_analytics', 'imagetrack');
    $siteurl = get_config('local_analytics', 'siteurl');
    $siteid = get_config('local_analytics', 'siteid');
    $trackadmin = get_config('local_analytics', 'trackadmin');
    $cleanurl = get_config('local_analytics', 'cleanurl');
	$location = "additionalhtml".get_config('local_analytics', 'location');
    
	if (!empty($siteurl)) {
		if ($imagetrack) {
			$addition = '<noscript><p><img src="//'.$siteurl.'/piwik.php?idsite='.$siteid.'" style="border:0;" alt="" /></p></noscript>';
		} else {
			$addition = '';
		}
		
		if ($cleanurl) {
			$doctitle = "_paq.push(['setDocumentTitle', ".local_analytics_trackurl()."]);";
		} else {
			$doctitle = "";
		}
		
		if ($enabled && (!is_siteadmin() || $trackadmin)) {
			$CFG->$location .= "
<!-- Start Piwik Code -->
<script type='text/javascript'>
    var _paq = _paq || [];
    ".$doctitle."
    _paq.push(['trackPageView']);
    _paq.push(['enableLinkTracking']);
    (function() {
      var u='//".$siteurl."/';
      _paq.push(['setTrackerUrl', u+'piwik.php']);
      _paq.push(['setSiteId', ".$siteid."]);".local_insert_custom_moodle_vars().
      "var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
    })();
</script>".$addition.
"<!-- End Piwik Code -->";
		}
	}
}

insert_local_analytics_tracking();