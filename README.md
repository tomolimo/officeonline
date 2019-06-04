# Office Online plugin

GLPI plugin that provides an interface with an Office Online Server.

### Implements a WOPI host to support:
1. view of office documents in browsers
1. edition of office documents in browsers

### Document edition feature:
1. Is only available when user has the rights to edit the document.
1. Saves document in-place (replaces the GLPI document with the saved one)
1. Supports single user and co-authoring edition of a document: you may see this functional documentation: [Document collaboration and co-authoring](https://support.office.com/en-us/article/Document-collaboration-and-co-authoring-ee1509b4-1f6e-401e-b04a-782d26f564a4) and this technical one: [Co-authoring using Office Online](http://wopi.readthedocs.io/en/latest/scenarios/coauth.html)
1. When a document has a format that is not supported by the Office Online Server then will fall back on GLPI standard behavior (i.e.: will download the document). Document formats suported by your Office Online Server are available at the discovery URL: `http(s)://YOUR-OFFICE-ONLINE-SERVER/hosting/discovery` 

### Office Online Server can be internal (usually on premise) or external (hosted by an external provider).
Be aware that if you are using an external Office Online Server, then it will need an access to the GLPI plugin, and in particular to: `http(s)://GLPI-EXTERNAL-FQDN/plugins/officeonline/front/wopi.front.php` file.

### Tested with:
1. GLPI: 9.1, 9.2, 9.3
1. An internal Office Online Server: 16.0.7601.6800
1. IE11, FireFox, Chrome, Safari 

### Currently doesn't support, but there are plans to :smiley::
1. Document conversion into Office formats
1. Document creation
1. Document edition into Office applications (desktop applications)
1. Document preview
