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
   var extensions = [];
   var href = window.location.href;
   var indexOrigin = href.search('/front/document.php');
   var origin = href.substr(0, indexOrigin);
   var translation;

   var prefURL = '';
   var scripts = document.getElementsByTagName('script');
   for (i = 0; i < scripts.length; i++) {
       var pos = scripts[i].src.search("/plugins/officeonline/js/officeonline.js");
      if (pos >= 0) {
          prefURL = scripts[i].src.substr(0, pos);
      }
   }

   function setOfficeOnlineURL() {
      $('a[href^="/front/document.send.php?docid="]').each(function () {
         var href = this.href;
         var title = this.title == '' ? this.text : this.title;
         href = this.href.replace('/front/document.send.php?docid=', '/plugins/officeonline/front/document.view.php?docid=');
         var regex = /[^.]*$/i;
         var ext = title.match(regex);
         ext = ext[0].replace(/[^a-za-z ]/gi, "");
         ext = ext.toLowerCase(); 
         //debugger;
         if ($.inArray(ext, extensions) !== -1) { //if doctype is in the array, display an icon to display the document in the browser.
            var alt = translation;
            //var obj = $("<a title='" + alt + "' class='edit_document fa fa-pencil pointer' ></a>").attr('href', href);
            var obj = $("<a title='" + alt + "' style='margin-left: 9px;' ><img class='middle' src='/plugins/officeonline/pics/view-edit.png' /></a>").attr('href', href);
            //debugger;
            if ($(this).parent().find(".ARbuttons").length == 0) {
               var html_code = "<span class='ARbuttons' style='opacity:0.3'>";
               if ($(this).parent().find(".buttons").length == 0) {
                  $(this).parent().append(html_code);
               } else {
                  $(this).parent().find(".buttons").before(html_code);
               }
               $(this).parent().find(".ARbuttons").append(obj);
               $(this).parents('td').css('white-space', 'nowrap');
            }
         }
      });
      if (tmr) {
         if (tmrCount++ >= 50) {
            clearInterval(tmr);
            tmr = 0;
         }
      }
   }

   /*
    * Get enabled extension in officeonline config.
    */
   // if (indexOrigin > -1) {
       $.get(
           prefURL+"/plugins/officeonline/ajax/translation.php",
           {lang:"lang"},
            function (response) {
                translation = JSON.parse(response);
            }
       );
       $.ajax({
            url: prefURL + "/plugins/officeonline/ajax/extensionList.php",
            type: "GET",
            data: { extensions: "extensions" },
            success: function (resp) {
               if (!resp) {
                   return false;
               }
               ext = JSON.parse(resp);
               Object.keys(ext).forEach(function (element) {
                   extensions.push(ext[element]['action_ext']);
               });
               setOfficeOnlineURL();
            },
            error: function (resultat, statut, erreur) {
            },
            complete: function (resultat, statut) {
            }
         });

       $(document).ajaxComplete(function (event, jqXHR, ajaxOptions) {
         try {
            if (jqXHR.responseText.match(/\/front\/document\.send.php\?docid=\d+/i)) {
                 //debugger;
                 // if in the ajx response are present some links to download documents, then
                 // will set them to point to office online URL
                 tmrCount = 1;
                 tmr = setInterval(setOfficeOnlineURL, 200);
            }
         } catch (e) {
         }
       });
   // }

       $(document).on("mouseenter", ".ARbuttons", function () {
           $(this).css('opacity', '1');
       });
       $(document).on("mouseleave", ".ARbuttons", function () {
           $(this).css('opacity', '0.3');
       });

       $(document).on("click", "#checkAll", function () {
         if ($(this).is(":checked")) {
            $(":checkbox").prop("checked", true);
            $(".active").attr("value", 1);
         } else {
            $(":checkbox").prop("checked", false);
            $(".active").attr("value", 0);
         }
       });
    /*
     * Update value of field 'is_active'
     */
   $(document).on("click", "input[type='checkbox']", function () {
       var name = $(this).parent().find('#id');
      if ($(this).parent('td').find('input[type=hidden]').attr('value') == 0) {
          $(this).parent().find('.active').attr('value', '1');
      } else {
          $(this).parent().find('.active').attr('value', '0');
      }
   });
});
