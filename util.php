<?php


// header("ETag: PUB" . time());
// header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()-10) . " GMT");
// header("Expires: " . gmdate("D, d M Y H:i:s", time() + 5) . " GMT");
// header("Pragma: no-cache");
// header("Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");
// session_cache_limiter("nocache");

// session_start();

// if (array_key_exists("user", $_SESSION)) {
// 	$id_user = $_SESSION["user"]["id"];
// 	$pseudo = $_SESSION["user"]["pseudo"];
// 	$img_avatar = $_SESSION["user"]["img_avatar"];
// 	$first_name = $_SESSION["user"]["first_name"];
// 	$last_name = $_SESSION["user"]["last_name"];
// } else {
// 	$id_user = null;
// 	$img_avatar = "shadow.jpg";
// }

// $ini = parse_ini_file('app-scraping.ini');
$ini = parse_ini_file('app-fhdskjqfhdksjqf4fds65f4.ini');

$db_server = $ini['db_server'];
$db_name = $ini['db_name'];
$db_user = $ini['db_user'];
$db_password = $ini['db_password'];

// $coLang1 = $ini['coLang1'];
// $coLang2 = $ini['coLang2'];

// $zone = $ini['zone'];

$dbh = new PDO("mysql:host=$db_server;dbname=$db_name;charset=UTF8", $db_user, $db_password);

$root_dir = $ini['root_dir'];

$restBaseUrl = $ini['rest'];

function getBaseUrl()
{
	global $root_dir;

	// base directory
	// $base_dir = __DIR__;
	// server protocol
	$protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
	// domain name
	$domain = $_SERVER['SERVER_NAME'];
	// base url
	$base_url = $root_dir;
	// server port
	$port = $_SERVER['SERVER_PORT'];
	$disp_port = ($protocol == 'http' && $port == 80 || $protocol == 'https' && $port == 443) ? '' : ":$port";
	// put em all together to get the complete base URL

	$url = "${protocol}://${domain}${disp_port}/${base_url}";

	return $url; // = http://example.com/path/directory
}

function echor($s)
{
	echo $s . "<br />";
}

function getCursorFromProcedure()
{
	global $dbh;

	$pparams = func_get_args();
	$pprocedure = $pparams[0];
	$pparams = array_slice($pparams, 1);

	$sql = "call $pprocedure(";
	$l = 0;

	foreach ($pparams as $k => $v) {
		$l++;
		$sql .= ":$k";
		if ($l < count($pparams)) $sql .= ",";
	}
	$sql .= ");";

	// echo($sql);
	$stmt = $dbh->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);

	$params = array();
	foreach ($pparams as $k => $v) {
		$params[":$k"] = $v;
	}

	$dbh->beginTransaction();
	$stmt->execute($params);
	$dbh->commit();

	return $stmt;
}

function getCursorFromSQLquery()
{
	global $dbh;

	$pparams = func_get_args();
	$sql = $pparams[0];
	$pparams = array_slice($pparams, 1);
	$l = 0;

	foreach ($pparams as $k => $v) {
		$l++;
		$sql .= ":$k";
		if ($l < count($pparams)) $sql .= ",";
	}

	// echo($sql);
	$stmt = $dbh->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);

	$params = array();
	foreach ($pparams as $k => $v) {
		$params[":$k"] = $v;
	}

	$dbh->beginTransaction();
	$stmt->execute($params);
	$dbh->commit();

	return $stmt;
}

function getDataArrayFromProcedure()
{
	global $dbh;

	$pparams = func_get_args();
	$pprocedure = $pparams[0];
	$pparams = array_slice($pparams, 1);

	$sql = "call $pprocedure(";
	$l = 0;

	foreach ($pparams as $k => $v) {
		$l++;
		$sql .= ":$k";
		if ($l < count($pparams)) $sql .= ",";
	}
	$sql .= ");";

	// echo($sql);
	$stmt = $dbh->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);

	$params = array();
	foreach ($pparams as $k => $v) {
		$params[":$k"] = $v;
	}

	$stmt->execute($params);
	$rs = $stmt->fetchAll();
	$stmt->closeCursor();

	return $rs;
}

function getDataArrayFromSQLQuery()
{
	global $dbh;

	$pparams = func_get_args();
	$sql = $pparams[0];
	$pparams = array_slice($pparams, 1);
	$l = 0;

	// foreach ($pparams as $k => $v) {
	// 	$l++;
	// 	$sql .= ":$k";
	// 	if ($l < count($pparams)) $sql .= ",";
	// }

	// echo($sql);
	$stmt = $dbh->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);

	$params = array();
	foreach ($pparams as $k => $v) {
		$params[":$k"] = $v;
	}

	$stmt->execute($params);
	$rs = $stmt->fetchAll();
	$stmt->closeCursor();

	return $rs;
}

function getJSONArrayFromProcedure()
{
	global $dbh;

	$pparams = func_get_args();
	$pprocedure = $pparams[0];
	$pparams = array_slice($pparams, 1);

	$sql = "call $pprocedure(";
	$l = 0;

	foreach ($pparams as $k => $v) {
		$l++;
		$sql .= ":$k";
		if ($l < count($pparams)) $sql .= ",";
	}
	$sql .= ");";

	// echo($sql);
	$stmt = $dbh->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);

	$params = array();
	foreach ($pparams as $k => $v) {
		$params[":$k"] = $v;
	}

	$stmt->execute($params);
	$rs = $stmt->fetchAll();
	$stmt->closeCursor();

	return json_encode($rs);
}

function getJsonArrayFromPhpArray($data_array)
{
	$row_utf8 = array();
	$json = '[';

	foreach ($data_array as $row) {
		foreach ($row as $column_name => $value) {
			// var_dump($row);
			$text = utf8_encode($value);
			$row_utf8[$column_name] = $text;
		}
		$json .= json_encode($row_utf8) . ',';
	}

	if (strlen($json) > 1) {
		$json = substr($json, 0, strlen($json) - 1);
	}
	$json .= ']';

	return $json;
}

function execProc2($procedure, &$params)
{
	global $dbh;

	$sql = "call $procedure(";
	$l = 0;

	foreach ($params as $k => $v) {
		$l++;
		$sql .= ":$k";
		if ($l < count($params)) $sql .= ",";
	}
	$sql .= ");";

	// echo($sql);
	$stmt = $dbh->prepare($sql);
	// var_dump($stmt);

	$params2 = array();
	foreach ($params as $k => $v) {
		$params2[":$k"] = $v;
	}

	$stmt->execute($params2);

	//var_dump($params2);
}

function execProc()
{
	global $dbh;

	$pparams = func_get_args();
	$pprocedure = $pparams[0];
	$pparams = array_slice($pparams, 1);

	$sql = "call $pprocedure(";
	$l = 0;

	foreach ($pparams as $k => $v) {
		$l++;
		$sql .= ":$k";
		if ($l < count($pparams)) $sql .= ",";
	}
	$sql .= ");";

	// echo($sql);
	// $dbh->beginTransaction();

	$stmt = $dbh->prepare($sql);

	$params = array();
	foreach ($pparams as $k => &$v) {
		$params[":$k"] = $v;
	}

	$stmt->execute($params);

	//	$dbh->commit();
}

function execSQL()
{
	global $dbh;

	$pparams = func_get_args();
	$sql = $pparams[0];
	$pparams = array_slice($pparams, 1);
	$l = 0;

	$stmt = $dbh->prepare($sql);

	$params = array();
	foreach ($pparams as $k => &$v) {
		$params[":$k"] = $v;
	}

	$stmt->execute($params);
}

function execFunc()
{
	global $dbh;

	$pparams = func_get_args();
	$pfunction = $pparams[0];
	$pparams = array_slice($pparams, 1);

	$sql = "select $pfunction(";
	$l = 0;

	foreach ($pparams as $k => $v) {
		$l++;
		$sql .= ":$k";
		if ($l < count($pparams)) $sql .= ",";
	}
	$sql .= ") as result;";

	// echo "$sql<br />";
	$stmt = $dbh->prepare($sql);

	$params = array();
	foreach ($pparams as $k => &$v) {
		$params[":$k"] = $v;
	}

	$stmt->execute($params);
	$rs = $stmt->fetchAll();
	$stmt->closeCursor();

	// var_dump($rs);

	return $rs[0]["result"];
}

function mkOptions($rs, $value, $display)
{
	foreach ($rs as $row) {
		echo "<option value='" . $row[$value] . "'>" . $row[$display] . "</option>";
	}
}

function mkOptions2($arrayName, $bsort = false)
{
	global $$arrayName, $co_lang;
	$options = [];
	$html = "";

	if ($co_lang == "fr") {
		$options = array_keys($$arrayName);
	} else {
		foreach ($$arrayName as $value) {
			$options[] = $value[$co_lang];
		}
	}

	if ($bsort) {
		sort($options);
	}

	foreach ($options as $value) {
		$html .= "<option>$value</option>";
	}

	return $html;
}
