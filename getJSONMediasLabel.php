<?php

include "util.php";

$json = getJSONArrayFromProcedure("getListLabelMedias", $_REQUEST["id"]);

echo $json;