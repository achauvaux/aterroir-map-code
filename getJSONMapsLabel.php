<?php

include "util.php";

$json = getJSONArrayFromProcedure("getListLabelMaps", null, null, $_REQUEST["id"]);

echo $json;