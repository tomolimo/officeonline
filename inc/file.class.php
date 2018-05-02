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

class PluginOfficeonlineFile {

   /**
    * Summary of getUserName
    * @param mixed $users_id
    * @return string
    */
   static private function getUserName($users_id){
      if ($users_id == -1) {
         $name = 'Anonymous';
      } else {
         // get the user name from DB
         $user = new User;
         $user->getFromDB($users_id);
         $name = $user->getRawName();
      }
      return $name;
   }


   /**
    * Summary of getUserId
    * @param mixed $access_token
    * @return mixed
    */
   static private function getUserId($access_token) {
      $arr = explode('_', $access_token);
      return $arr[1];
   }


   /**
    * Summary of canUpdate
    * @param mixed $access_token
    * @return boolean
    */
   static private function canUpdate($access_token) {
      $arr = explode('_', $access_token);
      return $arr[2] == 1;
   }


   /**
    * Summary of getFileLockPath
    * @param mixed $docid
    * @return string
    */
   static private function getFileLockPath($docid){
      return GLPI_DOC_DIR."/_lock/~officeonline_$docid.lck";
   }


   /**
    * Summary of getFileLockContent
    * @param mixed $docid
    * @return string
    */
   static private function getFileLockContent($docid) {
      $lockpath = self::getFileLockPath($docid);
      return file_exists($lockpath) ? file_get_contents($lockpath) : '';
   }


	/**
	 * Summary of checkFileInfo
	 * @param mixed $docid
	 * @param mixed $access_token
	 * @param mixed $sc
	 * @return string
	 */
	static function checkFileInfo($docid, $access_token, $sc='') {
      global $CFG_GLPI;
      $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https://" : "http://";

      $doc = new Document;
      $doc->getFromDB($docid);

		header("Content-Type: application/json; charset=UTF-8");

      $completefilepath = GLPI_DOC_DIR."/".$doc->fields['filepath'];
      if (!file_exists($completefilepath)) {
         http_response_code(404);
         return json_encode([]);
      }

		$size = filesize($completefilepath);
		$hash = base64_encode(hash_file('sha256', $completefilepath, true));
		$modif = filemtime($completefilepath); //date('Y-m-d H:i:s', filemtime(self::getFilePath($docid)));
		$users_id = self::getUserId($access_token);
      $canUpdate = self::canUpdate($access_token);

      $closeUrl = $protocol.$_SERVER['SERVER_NAME' ].$CFG_GLPI["root_doc"]."/front/document.form.php?id=$docid";
      if ($sc != '') {
         $closeUrl = $sc;
      }
		return json_encode(["BaseFileName" => $doc->fields['filename'],
							"OwnerId" => "$users_id",
							"UserId" => "$users_id",
							"UserFriendlyName" => self::getUserName($users_id),
							"Size" => $size,
							"SHA256" => $hash,
							"Version" => "$modif",
							"SupportsGetLock" => true,
							"SupportsLocks" => true,
							"SupportsUpdate" => true,
                     "UserCanNotWriteRelative" => true,
                     "SupportsExtendedLockLength" => true,
							"UserCanWrite" => $canUpdate,
                     "BreadcrumbBrandName" => "GLPI",
                     "BreadcrumbBrandUrl" => $protocol.$_SERVER['SERVER_NAME' ].$CFG_GLPI["root_doc"],
                     "BreadcrumbFolderName" => $doc->getTypeName(),
                     "BreadcrumbFolderUrl" => $protocol.$_SERVER['SERVER_NAME' ].$CFG_GLPI["root_doc"]."/front/document.form.php?id=$docid",
                     "CloseUrl" => $closeUrl,
							"DownloadUrl" => $protocol.$_SERVER['SERVER_NAME' ].$CFG_GLPI["root_doc"]."/front/document.send.php?docid=$docid"
							 ]) ;
	}


	/**
	 * Summary of getFile
	 * @param mixed $docid
	 * @param mixed $access_token
	 * @return string
	 */
	static function getFile($docid, $access_token) {
		header("Content-Type: application/octet-stream");

      $doc = new Document;
      $doc->getFromDB($docid);

		//header("Content-Disposition: inline; filename=\"".$doc->fields['filename']."\"");
		$file_content = file_get_contents( GLPI_DOC_DIR."/".$doc->fields['filepath']);

		return $file_content;
	}


	/**
	 * Summary of putFile
	 * @param mixed $docid
	 * @param mixed $data
	 * @param mixed $access_token
	 * @param mixed $request_headers
	 * @return void
	 */
	static function putFile($docid, $data, $access_token, $request_headers) {
		header("Content-Type: text/html; charset=UTF-8");

      $doc = new Document;
      $doc->getFromDB($docid);

      $sha1sum = sha1($data);

      $former_filepath = $doc->fields['filepath'];

      $current_lock = self::getFileLockContent($docid);

		$size = filesize(GLPI_DOC_DIR."/".$former_filepath);

      if (($current_lock == '' && $size > 0 )
         || ($current_lock != '' && $current_lock != $request_headers['X-Wopi-Lock'])) {
         //409
         header('X-Wopi-Lock: '.$current_lock);
         http_response_code(409);
         return ;
      }
      // editnew action is currrently not supported
      //elseif ($current_lock == '') {
      //   // editnew action
      //   header('X-Wopi-Lock: '.$current_lock);
      //}

      $new_filepath = Document::getUploadFileValidLocationName(explode('/', $former_filepath)[0], $sha1sum);

		if (file_put_contents(GLPI_DOC_DIR."/".$new_filepath, $data) !== false) {
         // then update the sha1sum, users_id and filepath
         $users_id = self::getUserId($access_token);
         $doc->update(['id' => $docid, 'sha1sum' => $sha1sum, 'filepath' => $new_filepath, 'users_id' => $users_id ]);
         // and delete previous file
         @unlink(GLPI_DOC_DIR."/".$former_filepath);
      }
	}

	static function lock($docid, $access_token, $request_headers) {
		header("Content-Type: text/html; charset=UTF-8");

      $current_lock = self::getFileLockContent($docid);
      if ($current_lock != '' && $current_lock != $request_headers['X-Wopi-Lock']) {
         header('X-Wopi-Lock: '.$current_lock);
         http_response_code(409);
      } else {
         file_put_contents(self::getFileLockPath($docid), $request_headers['X-Wopi-Lock']);
      }
	}

   static function unlockAndRelock($docid, $access_token, $request_headers) {
		header("Content-Type: text/html; charset=UTF-8");

      $current_lock = self::getFileLockContent($docid);
      if ($current_lock != '' && $current_lock != $request_headers['X-Wopi-Oldlock']) {
         header('X-Wopi-Lock: '.$current_lock);
         http_response_code(409);
      } else {
         file_put_contents(self::getFileLockPath($docid), $request_headers['X-Wopi-Lock']);
      }
	}

   static function refreshLock($docid, $access_token, $request_headers) {
		header("Content-Type: text/html; charset=UTF-8");

      $current_lock = self::getFileLockContent($docid);
      if ($current_lock == '' || $current_lock != $request_headers['X-Wopi-Lock']) {
         header('X-Wopi-Lock: '.$current_lock);
         http_response_code(409);
      } else {
         file_put_contents(self::getFileLockPath($docid), $request_headers['X-Wopi-Lock']);
      }
	}


   static function unlock($docid, $access_token, $request_headers) {
		header("Content-Type: text/html; charset=UTF-8");

      $current_lock = self::getFileLockContent($docid);
      if ($current_lock == '' || $current_lock != $request_headers['X-Wopi-Lock']) {
         header('X-Wopi-Lock: '.$current_lock);
         http_response_code(409);
      } else {
         unlink(self::getFileLockPath($docid));
      }
	}


   static function getLock($docid) {
		header("Content-Type: text/html; charset=UTF-8");

      $current_lock = self::getFileLockContent($docid);
      header('X-Wopi-Lock: '.$current_lock);
	}


   /**
    * Summary of putRelativeFile
    * PutRelativeFile is not currently supported
    * @param mixed $docid
    * @param mixed $data
    * @param mixed $access_token
    * @param mixed $request_headers
    */
   static function putRelativeFile($docid, $data, $access_token, $request_headers) {
		header("Content-Type: application/octet-stream");
      http_response_code(501);

      // X-WOPI-RelativeTarget
      // X-WOPI-SuggestedTarget
      return '';
   }
}