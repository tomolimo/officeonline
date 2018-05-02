<?php
/*
 * -------------------------------------------------------------------------
OfficeOnline plugin
Copyright (C) 2018 by Raynet SAS a company of A.Raymond Network.

http://www.araymond.com
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


if (!function_exists('arTableExists')) {
   function arTableExists($table) {
      global $DB;
      if (method_exists( $DB, 'tableExists')) {
         return $DB->tableExists($table);
      } else {
         return TableExists($table);
      }
   }
}

if (!function_exists('arFieldExists')) {
   function arFieldExists($table, $field, $usecache = true) {
      global $DB;
      if (method_exists( $DB, 'fieldExists')) {
         return $DB->fieldExists($table, $field, $usecache);
      } else {
         return FieldExists($table, $field, $usecache);
      }
   }
}

/**
 * Summary of plugin_officeonline_install
 * @return true or die!
 */
function plugin_officeonline_install() {
    global $DB;

   if (!arTableExists("glpi_plugin_officeonline_configs")) {
      $query = "  CREATE TABLE `glpi_plugin_officeonline_configs` (
	                    `id` INT(11) NOT NULL AUTO_INCREMENT,
	                    `discovery_url` VARCHAR(512) NOT NULL DEFAULT 'https://my.owa.server/hosting/discovery',
	                    `net_zone` VARCHAR(50) NOT NULL DEFAULT 'internal-https',
	                    PRIMARY KEY (`id`)
                    )
                    COLLATE='utf8_general_ci'
                    ENGINE=InnoDB
                    ;
			";

      $DB->query($query) or die("error creating glpi_plugin_officeonline_configs" . $DB->error());

      // add configuration singleton
      $query = "INSERT INTO `glpi_plugin_officeonline_configs` (`id`) VALUES (1);";
      $DB->query( $query ) or die("error creating default record in glpi_plugin_officeonline_configs" . $DB->error());

   }

   if (!arTableExists("glpi_plugin_officeonline_discoveries")) {
      $query = "  CREATE TABLE `glpi_plugin_officeonline_discoveries` (
	                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                       `app_name` VARCHAR(10) NOT NULL,
                       `app_faviconurl` VARCHAR(512) NOT NULL,
                       `action_name` VARCHAR(30) NOT NULL,
	                    `action_ext` VARCHAR(10) NOT NULL,
	                    `action_urlsrc` VARCHAR(512) NOT NULL,
	                    PRIMARY KEY (`id`),
                       UNIQUE INDEX `action` (`action_name`, `action_ext`)
                    )
                    COLLATE='utf8_general_ci'
                    ENGINE=InnoDB
                    ;
			";

      $DB->query($query) or die("error creating glpi_plugin_officeonline_configs" . $DB->error());
   }



   return true;
}

function plugin_officeonline_uninstall() {
    global $DB;

    return true;
}

