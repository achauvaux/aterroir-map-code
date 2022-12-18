<?php

include "util.php";

$json = getJSONArrayFromProcedure("getListLabelPolygons", null, null, $_REQUEST["id"]);

echo $json;