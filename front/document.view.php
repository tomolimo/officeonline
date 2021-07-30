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

include_once '../../../inc/includes.php';

if (!$CFG_GLPI["use_public_faq"]) {
   Session::checkLoginUser();
}


if (isset($_REQUEST['docid'])) {

   $doc = new Document;

   if (!$doc->getFromDB($_REQUEST['docid'])) {
      Html::displayErrorAndDie(__('Unknown file'), true);
   }

   if (!file_exists(GLPI_DOC_DIR."/".$doc->fields['filepath'])) {
      Html::displayErrorAndDie(__('File not found'), true); // Not found

   } else if ($doc->canViewFile($_GET)) {
      if ($doc->fields['sha1sum']
          && $doc->fields['sha1sum'] != sha1_file(GLPI_DOC_DIR."/".$doc->fields['filepath'])) {

         Html::displayErrorAndDie(__('File is altered (bad checksum)'), true); // Doc alterated
      } else {
         viewInBrowser($doc);
      }
   } else {
      Html::displayErrorAndDie(__('Unauthorized access to this file'), true); // No right
   }

}

function viewInBrowser($doc) {
   global $CFG_GLPI;

   //$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https://" : "http://";
   $wopiUrl = $CFG_GLPI['url_base']."/plugins/officeonline/front/wopi.front.php/files/";

   $users_id = Session::getLoginUserID(true);
   $canUpdate = 0;
   if ($users_id != -1) { // not anonymous, check user's rights
      $canUpdate = (Document::canUpdate() && $doc->canUpdateItem() ? 1 : 0);
   }

   //-1 for anonymous
   $access_token = $doc->fields['id'].'_'.($users_id > 0 ? $users_id : -1).'_'.$canUpdate;

   $title='';
   $fileext = '';
   $regex = "/(?'title'.*)\\.(?'ext'.*)/";
   if (preg_match($regex, $doc->fields['filename'], $matches)) {
      $title = $matches['title'];
      $fileext = strtolower($matches['ext']);
   }

   $disco_list = PluginOfficeonlineDiscovery::getDiscoveryList('view');

   if (isset($disco_list[$fileext]) && get_headers($disco_list[$fileext]['action_urlsrc']) !== false) {
      $file_content = file_get_contents('document.view.tpl');

      $file_content = str_replace( [
         '##favIconUrl##',
         '##title##',
         '##urlsrc##',
         '##access_token##'
         ], [
            $disco_list[$fileext]['app_faviconurl'],
            $title,
            $disco_list[$fileext]['action_urlsrc'].'WOPISrc='.urlencode($wopiUrl.$doc->fields['id'])."&sc=".urlencode($_SERVER['HTTP_REFERER']),
            $access_token
         ], $file_content);

      echo $file_content;
   } else {
      $port = empty($_SERVER['SERVER_PORT'])?'':':'.$_SERVER['SERVER_PORT'];
      header("Location: ".$CFG_GLPI['url_base'].'/front/document.send.php?docid='.$doc->fields['id']);
   }

}
