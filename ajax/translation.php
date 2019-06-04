<?php
include ("../../../inc/includes.php");

if (isset($_GET['lang'])) {
   //$lang = array('lang'=>'fr_FR');
   echo json_encode($LANG['officeonline']['document']['title']);
}
