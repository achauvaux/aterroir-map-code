<?php

include "util.php";

$co_lang = $_REQUEST["co_lang"] == "CN" ? "CN" : "EU";

$json = getJSONArrayFromProcedure("getListLabelMedias", $_REQUEST["id"], $co_lang);

echo $json;