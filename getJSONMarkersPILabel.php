<?php

include "util.php";

$idLabel = $_REQUEST["id"];
// $json = getJSONArrayFromProcedure("getListPIs", null, null, $idLabel, null);

// $restBaseUrl = "http://51.91.157.23/aterroir-wp-jl/wp-json/wp/v2/"; // TOTO _fields
$restBaseUrl = "https://aterroir.org/wp-json/wp/v2/"; // TODO _fields=acf
// $rqParams = "ip?_fields=acf&filter[meta_key]=id_label&filter[meta_value]=$idLabel";
$rqParams = "ip?filter[meta_key]=id_label&filter[meta_value]=$idLabel";
$restUrl = $restBaseUrl . $rqParams;

function getPIJSON() {
  global $restUrl;
  return json_decode(file_get_contents($restUrl), true);
}

$json = getPIJSON();

$rows = [];

foreach ($json as $row) {
  // array_push($rows, $row["acf"]);
  $modifiedRow = $row["acf"]; // on récupère l'ensemble des champs acf
  $modifiedRow["id_pi"] = $row["id"]; // on remplace l'id_pi non défini par l'id interne du post wp
  $rows[] = $modifiedRow;
}

$json = json_encode($rows);

echo $json;
