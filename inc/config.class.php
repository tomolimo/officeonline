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

class PluginOfficeonlineConfig extends CommonDBTM {

   static private $_instance = NULL;

   /**
    * Summary of canCreate
    * @return boolean
    */
   static function canCreate() {
      return Session::haveRight('config', UPDATE);
   }

   /**
    * Summary of canView
    * @return boolean
    */
   static function canView() {
      return Session::haveRight('config', READ);
   }

   /**
    * Summary of canUpdate
    * @return boolean
    */
   static function canUpdate() {
      return Session::haveRight('config', UPDATE);
   }

   /**
    * Summary of getTypeName
    * @param mixed $nb plural
    * @return mixed
    */
   static function getTypeName($nb=0) {
      global $LANG;

      return $LANG['officeonline']['config']['setup'];
   }

   /**
    * Summary of getName
    * @param mixed $with_comment with comment
    * @return mixed
    */
   function getName($with_comment=0) {
      global $LANG;

      return $LANG['officeonline']['title'][1];
   }

   /**
    * Summary of getInstance
    * @return PluginProcessmakerConfig
    */
   static function getInstance() {

      if (!isset(self::$_instance)) {
         self::$_instance = new self();
         if (!self::$_instance->getFromDB(1)) {
            self::$_instance->getEmpty();
         }
      }
      return self::$_instance;
   }

   ///**
   //* Prepare input datas for updating the item
   //* @param array $input used to update the item
   //* @return array the modified $input array
   //**/
   //function prepareInputForUpdate($input) {
   //   global $CFG_GLPI;

   //   if (!isset($input["maintenance"])) {
   //      $input["maintenance"] = 0;
   //   }

   //   if (isset($input["pm_dbserver_passwd"])) {
   //      if (empty($input["pm_dbserver_passwd"])) {
   //         unset($input["pm_dbserver_passwd"]);
   //      } else {
   //         $input["pm_dbserver_passwd"] = Toolbox::encrypt(stripslashes($input["pm_dbserver_passwd"]), GLPIKEY);
   //      }
   //   }

   //   if (isset($input["_blank_pm_dbserver_passwd"]) && $input["_blank_pm_dbserver_passwd"]) {
   //      $input['pm_dbserver_passwd'] = '';
   //   }

   //   if (isset($input["pm_admin_passwd"])) {
   //      if (empty($input["pm_admin_passwd"])) {
   //         unset($input["pm_admin_passwd"]);
   //      } else {
   //         $input["pm_admin_passwd"] = Toolbox::encrypt(stripslashes($input["pm_admin_passwd"]), GLPIKEY);
   //      }
   //   }

   //   if (isset($input["_blank_pm_admin_passwd"]) && $input["_blank_pm_admin_passwd"]) {
   //      $input['pm_admin_passwd'] = '';
   //   }

   //   $input['domain'] = self::getCommonDomain( $CFG_GLPI['url_base'], $input['pm_server_URL'] );

   //   return $input;
   //}

   ///**
   // * Summary of getCommonDomain
   // * @param mixed $url1 first url
   // * @param mixed $url2 second url
   // * @return string the common domain part of the given urls
   // */
   //static function getCommonDomain($url1, $url2) {
   //   $domain = '';
   //   try {
   //      $glpi = explode(".", parse_url($url1, PHP_URL_HOST));
   //      $pm = explode( ".", parse_url($url2, PHP_URL_HOST));
   //      $cglpi = array_pop( $glpi );
   //      $cpm = array_pop( $pm );
   //      while ($cglpi && $cpm && $cglpi == $cpm) {
   //         $domain = $cglpi.($domain==''?'':'.'.$domain);
   //         $cglpi = array_pop( $glpi );
   //         $cpm = array_pop( $pm );
   //      }
   //      if ($domain != '') {
   //         return $domain;
   //      }
   //   } catch (Exception $e) {
   //      $domain = '';
   //   }
   //   return $domain;
   //}

   /**
    * Summary of showConfigForm
    * @param mixed $item is the config
    * @return boolean
    */
   static function showConfigForm($item) {
      global $LANG, $CFG_GLPI;

      $config = self::getInstance();

      $config->showFormHeader(['colspan' => 4]);

      echo "<tr class='tab_bg_1'>";
      echo "<td >".$LANG['officeonline']['config']['discovery_url']."</td><td >";
      echo "<input size='50' type='text' name='discovery_url' value='".$config->fields['discovery_url']."'>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".$LANG['officeonline']['config']['net_zone']."</td><td >";
      echo "<input type='text' name='net_zone' value='".$config->fields['net_zone']."'>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".$LANG['officeonline']['config']['connectionstatus']."</td><td >";

      if ($config->fields['discovery_url'] != ''
         && $config->fields['net_zone'] != ''
         && PluginOfficeonlineDiscovery::discoverOWA($config->fields['discovery_url'], $config->fields['net_zone']) ) {
         echo "<font color='green'>".__('Test successful, and discovery of OWA URLs complete');
      } else {
         echo "<font color='red'>".__('Test failed, discovery of OWA URLs not done');
      }
      echo "</font></span></td></tr>\n";

      $config->showFormButtons(array('candel'=>false));

      return false;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if ($item->getType()=='Config') {
         return $LANG['officeonline']['title'][1];
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Config') {
         self::showConfigForm($item);
      }
      return true;
   }

}
