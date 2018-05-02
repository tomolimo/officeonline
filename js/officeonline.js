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

$(function () {
   
   var tmr = 0;
   var tmrCount = 1;

   function setOfficeOnlineURL () {
      $('a[href^="/front/document.send.php?docid="]').each(function () {
         this.href = this.href.replace('/front/document.send.php?docid=', '/plugins/officeonline/front/document.view.php?docid=');
         //this.target = '';
      });
      if (tmr) {
         if (tmrCount++ >= 50) {
            clearInterval(tmr);
            tmr = 0;
         }
      }
   }
   //debugger;
   
   setOfficeOnlineURL();

   $(document).ajaxComplete(function (event, jqXHR, ajaxOptions) {
      //debugger;
      try {
         if (jqXHR.responseText.match(/\/front\/document\.send.php\?docid=\d+/i)) {
            //debugger;
            // if in the ajx response are present some links to download documents, then 
            // will set them to point to office online URL
            tmrCount = 1 ;
            tmr = setInterval(setOfficeOnlineURL, 200);
         }
      } catch (e) {
      }

   });
   
});