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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}


/**
 * discovery short summary.
 *
 * discovery description.
 *
 * @version 1.0
 * @author morono
 */
class PluginOfficeonlineDiscovery extends CommonDBTM {

   static function getTypeName($nb = 0) {
      return _n('OWA Discovery', 'OWA Discoveries', $nb);
   }

   static function getDiscoveryList($action_name) {
       $condition=[
        'action_name' => strtolower($action_name)
       ];
       $dbu = new DbUtils();
       $list = $dbu->getAllDataFromTable( self::getTable(), $condition, false, '`app_name`, `action_ext`');
       $actions = [];
       foreach ($list as $action) {
          $actions[$action['action_ext']] = $action;
       }
       return $actions;
   }

   static function discoverOWA($owaurl, $netzone) {

      $disco = file_get_contents($owaurl);
      if ($disco === false) {
         return false;
      }

      $xml = new SimpleXMLElement($disco);
      $actions = $xml->xpath('/wopi-discovery/net-zone[@name="'.$netzone.'"]/app/action');
      foreach ($actions as $action) {
         $attributes = $action->attributes();
         $action_name = strtolower((string)$attributes['name']);
         $action_urlsrc = (string)$attributes['urlsrc'];
         $action_ext = strtolower((string)$attributes['ext']);
         $tmp = $action->xpath('ancestor::app[1]');
         $app = $tmp[0];
         $attributes = $app->attributes();
         $app_faviconurl = (string)$attributes['favIconUrl'];
         $app_name = (string)$attributes['name'];

         // delete optional parameters in the $action_urlsrc
         $regex = "/(?'options'<[^>]+>)/";
         if (preg_match_all($regex, $action_urlsrc, $matches)) {
            $action_urlsrc = str_replace( $matches['options'], '', $action_urlsrc);
         }

         if ($action_urlsrc == ""
            || $action_ext == ""
            || $app_faviconurl == ""
            || $app_name == ""
            || $action_name == "" ) {
            continue;
         }

         // here we may add or replace record in self table.
         $disco = new self;
         //if ($disco->getFromDBByQuery("WHERE `action_name` = '$action_name' AND `action_ext` = '$action_ext'")) {
         if ($disco->getFromDBByRequest([
             'WHERE'  => [
                'action_name'  => $action_name,
                'action_ext'  => $action_ext
             ],
         ])) {
            // found a record, then update it
            $disco->update( [
               'id' => $disco->fields['id'],
               'app_name'       => $app_name,
               'app_faviconurl' => $app_faviconurl,
               'action_name'    => $action_name,
               'action_ext'     => $action_ext,
               'action_urlsrc'  => $action_urlsrc
               ]);
         } else {
            // not record yet, then add it
            $disco->add( [
               'app_name'       => $app_name,
               'app_faviconurl' => $app_faviconurl,
               'action_name'    => $action_name,
               'action_ext'     => $action_ext,
               'action_urlsrc'  => $action_urlsrc,
               'is_active'      => 0
               ]);
         }
      }

      return true;
   }


   /*
    * Get enabled extensions
    */
   function getEnableExtensions() {
      $found = $this->find("`action_name` = 'view' AND `is_active` = 1");
      if ($found) {
         echo json_encode($found);
      }
      return false;
   }


   /*
    * Update rows where action_ext = action_ext of current element.
    */
   function post_updateItem($history = 1) {
      global $DB;
      if ($this->fields['action_ext']) {
         $res = $DB->query("UPDATE `glpi_plugin_officeonline_discoveries` SET `is_active` = ".$this->fields['is_active'] ." WHERE `action_ext` = '".$this->fields['action_ext'] ."'" );
      }
   }

}
