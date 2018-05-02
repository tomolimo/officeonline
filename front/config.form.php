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

include ( "../../../inc/includes.php");

$config = new PluginOfficeonlineConfig();
if (isset($_POST["update"])) {
   $config->check($_POST['id'], UPDATE);

   // save
   $config->update($_POST);


   Html::back();

} else if (isset($_POST["refresh"])) {
   $config->refresh($_POST); // used to refresh process list, task category list
   Html::back();
}

Html::redirect($CFG_GLPI["root_doc"]."/front/config.form.php?forcetab=".
             urlencode('PluginOfficeonlineConfig$1'));
