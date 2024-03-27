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

$(function () {

   var tmr = 0;
   var tmrCount = 1;
   var extensions = typeof GLPI_OFFICEONLINE_PLUGIN_DATA === "undefined" ? [] : GLPI_OFFICEONLINE_PLUGIN_DATA;

   function setOfficeOnlineURL() {
      $('a[href^="/front/document.send.php?docid="]').each(function () {
         let rex = new RegExp('(\\.' + extensions.join("\\b)|(\\.") + '\\b)', 'i');
         if ((this.text && this.text.match(rex))
            || (this.title && this.title.match(rex))
            || (this.alt && this.alt.match(rex))) { //if doctype is in the array, display an icon to display the document in the browser.
            var href = this.href.replace('/front/document.send.php?docid=', '/plugins/officeonline/front/document.view.php?docid=');
            var obj = $("<a class='btn btn-sm btn-ghost-secondary ARbuttons' data-bs-toggle='tooltip' data-bs-placement='top' title='" + __("View and edit in your browser", "officeonline") + "'><i class='ti ti-file-pencil'></i></a>").attr('href', href);
            var these = [
               $(this).parent('td'),
               $(this).parent().parent().find('div.list-group-item-actions')
            ];
            these.forEach(e => {
               if (e.length == 1 && e.find('.ARbuttons').length == 0) {
                  e.append(obj);
               }
            });
         }
      });
      if (tmr) {
         if (tmrCount++ >= 50) {
            clearInterval(tmr);
            tmr = 0;
         }
      }
   }

   // 
   setTimeout(setOfficeOnlineURL, 2000); // to be sure that translation will be loaded :(

   // ajaxcomplet will be used when documents are listed in a tab for an item
   $(document).ajaxComplete(function (event, jqXHR, ajaxOptions) {
      try {
         //debugger;
         if (jqXHR.responseText.match(/\/front\/document\.send.php\?docid=\d+/i)) {
               // if in the ajax response are present some links to download documents, then
               // will set them to point to office online URL
               tmrCount = 1;
               tmr = setInterval(setOfficeOnlineURL, 200);
         }
      } catch (e) {
      }
   });

});
