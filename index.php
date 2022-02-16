<?php

// echo $_SERVER['REQUEST_URI'];
// var_dump($_SERVER['QUERY_STRING']);

include_once "util.php";

$z = array_key_exists("z", $_REQUEST) ? $_REQUEST["z"] : $zone;

switch($z) {
  case "eu":
    $indexFile = "carte-eu.php";
    break;
  case "cn":
    $indexFile = "carte-cn.php";
    break;
  default:
    $indexFile = "carte-eu.php";
}

include $indexFile;