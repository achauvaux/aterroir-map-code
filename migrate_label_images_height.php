<?php

// use Location\Coordinate;
// use Location\Formatter\Geohash\Geohash;
use Sk\Geohash\Geohash;
// use Cevin\Geohash;

include "util.php";

echo "Migrating labels images to Strapi...<br>";

// die();

$rsLabels = getDataArrayFromProcedure("getListLabels", null, null, null);

$strapiUrl = 'http://localhost:1338/api/labels';
$strapiBaseUrl = 'http://localhost:1338';

// Votre token JWT pour l'authentification API Strapi
$jwtToken = '6afb7b639162f356dc5f5750c8b094b7d931636b87a9402097f0614f3ef9975a5b9f37a6a776cd5eb9942a84f73a336295938027956e17302e7b9ca7d8a799ae25b30460e13e2d2602b2bd6b1bbb863323d499b4f49dea26db6775167910a5712d9cc4b6923bbfb6a0b2d3795b0291ec54c087f53d5fd19b072c8a1c1fc3d307';
$strapiToken = $jwtToken;

// $g = new Geohash();

foreach ($rsLabels as $label) {

    $name = $label['name_EN'];
    $labelId = findStrapiLabelIdByName($name);
    updateLabelMarkerIconHeight($labelId, $label["height_img_icon"], $name);
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
        }

        return $id;
    }
}

function updateLabelMarkerIconHeight($labelId, $height, $name) {

    global $strapiBaseUrl, $strapiToken;

    $curl = curl_init();
    $data = json_encode([
        'data' => [
            'marker_icon_height' => $height
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
        echo "updateLabelMarkerIconHeight cURL Error #:" . $err . "<br>";
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