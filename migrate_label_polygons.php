<?php

// use Location\Coordinate;
// use Location\Formatter\Geohash\Geohash;
use Sk\Geohash\Geohash;
// use Cevin\Geohash;

include "util.php";

echo "Migrating labels polygons to Strapi...<br>";

// die();

$rsPolygons = getDataArrayFromProcedure("getListLabelPolygons", null, null, null);

$strapiUrl = 'http://localhost:1337/api/labels';
$strapiBaseUrl = 'http://localhost:1337';

// Votre token JWT pour l'authentification API Strapi
$jwtToken = 'c084886539484c66d3ea808e2716fef9ac33989b4769d1ce0f086049bab0dccce4939bcea56acb9bda3f4b232b8f433bd5d71eb10177ca3f4ba35e5d44cd033d3b2adc04b8635143204a9614089a7a5021eac178b98cc046758e40dbcb866c4acbc4bf34d0d4bdd924c5df9261855a1782362902910d8fcf928ba69995776acf';
$strapiToken = $jwtToken;

// $g = new Geohash();

foreach ($rsPolygons as $polygons) {

    $polygons_json = json_decode($polygons['filename']);
    $color = $polygons['color'];
    // get the file name from the full path
    $name = $polygons['name_EN'];
    $labelId = findStrapiLabelIdByName($name);

    $uploadedMedias = [];
    
    foreach ($polygons_json as $polygon) {
        $polygon_filename_full = $polygon->name;
        $polygon_filename = basename($polygon_filename_full);
        $filePath = 'medias/geojson/terroirs/' . $polygon_filename;

        // Télécharger l'image vers Strapi
        $uploadedMedias[] = uploadFileToStrapi($filePath);

        // Trouver l'ID Strapi du label par `name_local`

    }

    // Mettre à jour le label dans Strapi pour définir `marker_icon`
    if ($labelId && $uploadedMedias) {
        updateLabelPolygon($labelId, $uploadedMedias, $name, $color);
    }
}

function uploadFileToStrapi($filePath) {

    global $strapiBaseUrl, $strapiToken;

    $curl = curl_init();
    $fileData = new CURLFile(realpath($filePath), 'application/geo+json', basename($filePath));

    curl_setopt_array($curl, [
        CURLOPT_URL => "$strapiBaseUrl/api/upload",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => ['files' => $fileData],
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $strapiToken",
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "uploadFileToStrapi cURL Error #:" . $err . "<br>";
    } else {
        $decodedResponse = json_decode($response, true);
        // Supposons que Strapi renvoie l'ID du fichier média dans la réponse
        return $decodedResponse[0]['id'];
    }
}

function findStrapiLabelIdByName($name) {

    global $strapiBaseUrl, $strapiToken;

    $curl = curl_init();
    $curlopt_url = $strapiBaseUrl . '/api/labels?filters[name][name_en][$eq]=' . urlencode($name) . '&fields=id';

    curl_setopt_array($curl, [
        CURLOPT_URL => $curlopt_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $strapiToken",
            'Content-Type: application/json',
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "findStrapiLabelIdByName cURL Error #:" . $err . "<br>";
    } else {
        $decodedResponse = json_decode($response, true);
        // Supposons que le premier label correspondant est le bon
        $id = $decodedResponse['data'][0]['id'];

        if ($id == null) {
            echo "Label $name not found.<br>";
        } else {
            echo "Label $name found with ID $id.<br>";
        }

        return $id;
    }
}

function updateLabelPolygon($labelId, $mediaIds, $name, $color) {

    global $strapiBaseUrl, $strapiToken;

    $curl = curl_init();
    $data = json_encode([
        'data' => [
            'polygons' => $mediaIds,
            'polygons_color' => $color,
        ],
    ]);

    curl_setopt_array($curl, [
        CURLOPT_URL => "$strapiBaseUrl/api/labels/$labelId",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $strapiToken",
            'Content-Type: application/json',
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "updateLabelPolygon cURL Error #:" . $err . "<br>";
    } else {
        echo "Label $name updated successfully.<br>";
    }
}


// create a generic to make an API call to Strapi
function sendRequest($url, $payload) {

	global $jwtToken;

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	if ($payload) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
	}

	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		'Authorization: Bearer ' . $jwtToken,
	]);

	$response = curl_exec($ch);
	curl_close($ch);

	return json_decode($response, true);
}