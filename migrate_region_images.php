<?php

// use Location\Coordinate;
// use Location\Formatter\Geohash\Geohash;
use Sk\Geohash\Geohash;
// use Cevin\Geohash;

include "util.php";

echo "Migrating Region images to Strapi...<br>";

// die();

$rsRegions = getDataArrayFromProcedure("getListRegions", null, null);

$strapiUrl = 'http://localhost:1337/api/regions';
$strapiBaseUrl = 'http://localhost:1337';

// Votre token JWT pour l'authentification API Strapi
$jwtToken = 'c084886539484c66d3ea808e2716fef9ac33989b4769d1ce0f086049bab0dccce4939bcea56acb9bda3f4b232b8f433bd5d71eb10177ca3f4ba35e5d44cd033d3b2adc04b8635143204a9614089a7a5021eac178b98cc046758e40dbcb866c4acbc4bf34d0d4bdd924c5df9261855a1782362902910d8fcf928ba69995776acf';
$strapiToken = $jwtToken;

// $g = new Geohash();

foreach ($rsRegions as $region) {

    $img_flag = $region['img_logo'];

    if (isJson($img_flag)) {
        $img_flag_json = json_decode($img_flag);
        $img_flag_filename_full = $img_flag_json[0]->name;
        // get the file name from the full path
        $img_flag_filename = basename($img_flag_filename_full);
    } else {
        $img_flag_filename = $img_flag;
    }

    $filePath = 'medias/img/logos-regions/' . $img_flag_filename;
    $name = $region['name_FR'];

    // Télécharger l'image vers Strapi
    $uploadedMedia = uploadImageToStrapi($filePath);

    // Trouver l'ID Strapi du region par `name_local`
    $regionId = findStrapiRegionIdByName($name);

    // Mettre à jour le region dans Strapi pour définir `marker_icon`
    if ($regionId && $uploadedMedia) {
        updateRegionLogo($regionId, $uploadedMedia, $name);
    }
}

function isJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
 }

function uploadImageToStrapi($filePath) {

    global $strapiBaseUrl, $strapiToken;

    $curl = curl_init();
    $fileData = new CURLFile(realpath($filePath), 'image/png', basename($filePath));

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
        echo "uploadImageToStrapi cURL Error #:" . $err . "<br>";
    } else {
        $decodedResponse = json_decode($response, true);
        // Supposons que Strapi renvoie l'ID du fichier média dans la réponse
        return $decodedResponse[0]['id'];
    }
}

function findStrapiRegionIdByName($name) {

    global $strapiBaseUrl, $strapiToken;

    $curl = curl_init();
    $curlopt_url = $strapiBaseUrl . '/api/regions?filters[name][name_fr][$eq]=' . urlencode($name) . '&fields=id';

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
        echo "findStrapiRegionIdByName cURL Error #:" . $err . "<br>";
    } else {
        $decodedResponse = json_decode($response, true);
        // Supposons que le premier region correspondant est le bon
        $id = $decodedResponse['data'][0]['id'];

        if ($id == null) {
            echo "region $name not found.<br>";
        }

        return $id;
    }
}

function updateRegionLogo($regionId, $mediaId, $name) {

    global $strapiBaseUrl, $strapiToken;

    $curl = curl_init();
    $data = json_encode([
        'data' => [
            'logo_image' => $mediaId
        ],
    ]);

    curl_setopt_array($curl, [
        CURLOPT_URL => "$strapiBaseUrl/api/regions/$regionId",
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
        echo "updateRegionLogo cURL Error #:" . $err . "<br>";
    } else {
        echo "Region $name updated successfully.<br>";
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