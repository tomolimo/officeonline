<?php
/*
 * -------------------------------------------------------------------------
OfficeOnline plugin
Copyright (C) 2018-2024 by Raynet SAS a company of A.Raymond Network.

https://www.araymond.com
-------------------------------------------------------------------------

LICENSE

This file is part of OfficeOnline plugin for GLPI.

This file is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

GLPI is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GLPI. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Olivier Moron
// Purpose of file: to setup office online plugin to GLPI
// ----------------------------------------------------------------------
define ("PLUGIN_OFFICEONLINE_VERSION", "3.1.0");
// Minimal GLPI version, inclusive
define('PLUGIN_OFFICEONLINE_MIN_GLPI', '10.0');
// Maximum GLPI version, exclusive
define('PLUGIN_OFFICEONLINE_MAX_GLPI', '10.1');
/**
 * Summary of plugin_init_officeonline
 */
function plugin_init_officeonline() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   if (Session::getLoginUserID()) {

      if (Session::haveRightsOr("config", [READ, UPDATE])) {
         Plugin::registerClass('PluginOfficeonlineConfig', ['addtabon' => 'Config']);
         $PLUGIN_HOOKS['config_page']['officeonline'] = 'front/config.form.php';
      }

      $PLUGIN_HOOKS['add_javascript']['officeonline'] = "js/officeonline.js";

      // this hook will not redefine menu, but will output the GLPI_OFFICEONLINE_PLUGIN_DATA js variable
      // to pass officeonline data to the js
      $PLUGIN_HOOKS['redefine_menus']['officeonline'] = 'plugin_officeonline_redefine_menus';

   }

   $PLUGIN_HOOKS['csrf_compliant']['officeonline'] = true;

}

/**
 * Summary of plugin_version_officeonline
 * @return string[]
 */
function plugin_version_officeonline() {

   return ['name'           => 'Office Online',
           'version'        => PLUGIN_OFFICEONLINE_VERSION,
           'author'         => 'Olivier Moron',
           'license'        => 'GPLv2+',
           'homepage'       => 'https://github.com/tomolimo/officeonline',
           'requirements' => [
               'glpi'         => [
                  'min' => PLUGIN_OFFICEONLINE_MIN_GLPI,
                  'max' => PLUGIN_OFFICEONLINE_MAX_GLPI
         ],
            ]
         ];
}


/**
 * Summary of plugin_officeonline_check_prerequisites
 * @return bool
 */
function plugin_officeonline_check_prerequisites() {
   global $DB, $LANG;

    // Strict version check (could be less strict, or could allow various version)
   if (version_compare(GLPI_VERSION, PLUGIN_OFFICEONLINE_MIN_GLPI, 'lt') || version_compare(GLPI_VERSION, PLUGIN_OFFICEONLINE_MAX_GLPI, 'ge')) {
      echo "This plugin requires GLPI >= ".PLUGIN_OFFICEONLINE_MIN_GLPI." and < ".PLUGIN_OFFICEONLINE_MAX_GLPI;
      return false;
   }

   return true;
}


/**
 * Summary of plugin_officeonline_check_config
 * @param mixed $verbose
 * @return bool
 */
function plugin_officeonline_check_config($verbose = false) {

   return true;
}

