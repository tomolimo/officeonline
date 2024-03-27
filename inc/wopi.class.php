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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginOfficeonlineWopi {

   /**
    * Summary of call
    */
   public function call() {
      header("Access-Control-Allow-Origin: *");

      if (isset($_SERVER['REQUEST_METHOD'])) {

         $request_uri  = $_SERVER['REQUEST_URI'];
         $verb         = $_SERVER['REQUEST_METHOD'];
         $request_headers = getallheaders();

         $sc = '';
         if (isset($request_headers['X-Wopi-Sessioncontext'])) {
            $sc = base64_decode($request_headers['X-Wopi-Sessioncontext']);
         }

         //
         // Files endpoint
         //
         $regexp = "@/wopi\\.front\\.php?/files/(?'docid'[^/?]+)\\?@i";
         if (preg_match($regexp, $request_uri, $matches) && isset($_REQUEST['access_token'])) {
            switch ($verb) {
               case 'GET':
                  //  CheckFileInfo operation
                  echo PluginOfficeonlineFile::checkFileInfo($matches['docid'], $_REQUEST['access_token'], $sc);
                  break;
               case 'POST':
                  if (isset($request_headers['X-Wopi-Override'])) {
                     // LOCK UNLOCK REFRESHLOCK operations
                     switch ($request_headers['X-Wopi-Override']) {
                        case 'LOCK':
                           if (isset($request_headers['X-Wopi-Oldlock'])) {
                              // UnlockAndRelock operation
                              PluginOfficeonlineFile::unlockAndRelock($matches['docid'], $_REQUEST['access_token'], $request_headers);
                           } else {
                              //  Lock operation
                              PluginOfficeonlineFile::lock($matches['docid'], $_REQUEST['access_token'], $request_headers);
                           }
                           break;
                        case 'REFRESHLOCK':
                           //  refreshlock operation
                           PluginOfficeonlineFile::refreshLock($matches['docid'], $_REQUEST['access_token'], $request_headers);
                           break;
                        case 'GET_LOCK':
                           //  GetLock operation
                           PluginOfficeonlineFile::getLock($matches['docid']);
                           break;
                        case 'UNLOCK':
                           // Unlock operation
                           PluginOfficeonlineFile::unlock($matches['docid'], $_REQUEST['access_token'], $request_headers);
                           break;
                        case 'PUT_RELATIVE':
                           // PutRelativeFile operation
                           $request_body = file_get_contents('php://input');
                           PluginOfficeonlineFile::putRelativeFile($matches['docid'], $request_body, $_REQUEST['access_token'], $request_headers);
                           break;

                     }
                  }
                  break;
            }
         } else {
            //
            // File Contents endpoint
            //
            $regexp = "@/wopi\\.front\\.php/files/(?'docid'[^/?]+)/contents\\?@i";
            if (preg_match($regexp, $request_uri, $matches) && isset($_REQUEST['access_token'])) {
               // GetFile and PutFile operations
               switch ($verb) {
                  case 'GET':
                     // getFile operation
                     echo PluginOfficeonlineFile::getFile($matches['docid'], $_REQUEST['access_token']);
                     break;
                  case 'POST':
                     // putFile operation
                     $request_body = file_get_contents('php://input');
                     PluginOfficeonlineFile::putFile($matches['docid'], $request_body, $_REQUEST['access_token'], $request_headers);
                     break;
               }
            }
         }
      }
   }
}

