<?php
include ("../../../inc/includes.php");

if (isset($_GET["extensions"])) {
   $disco = new PluginOfficeonlineDiscovery();
   $disco->getEnableExtensions();
}
