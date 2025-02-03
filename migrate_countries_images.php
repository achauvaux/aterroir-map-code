<?php

// use Location\Coordinate;
// use Location\Formatter\Geohash\Geohash;
use Sk\Geohash\Geohash;
// use Cevin\Geohash;

include "util.php";

echo "Migrating Country images to Strapi...<br>";

// die();

$rsCountries = getDataArrayFromProcedure("getListCountries", null);

$strapiUrl = 'http://localhost:1337/api/countries';
$strapiBaseUrl = 'http://localhost:1337';

// Votre token JWT pour l'authentification API Strapi
$jwtToken = 'c084886539484c66d3ea808e2716fef9ac33989b4769d1ce0f086049bab0dccce4939bcea56acb9bda3f4b232b8f433bd5d71eb10177ca3f4ba35e5d44cd033d3b2adc04b8635143204a9614089a7a5021eac178b98cc046758e40dbcb866c4acbc4bf34d0d4bdd924c5df9261855a1782362902910d8fcf928ba69995776acf';
$strapiToken = $jwtToken;

// $g = new Geohash();

foreach ($rsCountries as $country) {

    $img_flag = $country['img_icon'];

    if (isJson($img_flag)) {
        $img_flag_json = json_decode($img_flag);
        $img_flag_filename_full = $img_flag_json[0]->name;
        // get the file name from the full path
        $img_flag_filename = basename($img_flag_filename_full);
    } else {
        $img_flag_filename = $img_flag;
    }

    $filePath = 'medias/img/flags/' . $img_flag_filename;
    $name = $country['name_FR'];

    // Télécharger l'image vers Strapi
    $uploadedMedia = uploadImageToStrapi($filePath);

    // Trouver l'ID Strapi du country par `name_local`
    $countryId = findStrapiCountryIdByName($name);

    // Mettre à jour le country dans Strapi pour définir `marker_icon`
    if ($countryId && $uploadedMedia) {
        updateCountryFlag($countryId, $uploadedMedia, $name);
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

function findStrapiCountryIdByName($name) {

    global $strapiBaseUrl, $strapiToken;

    $curl = curl_init();
    $curlopt_url = $strapiBaseUrl . '/api/countries?filters[name][name_fr][$eq]=' . urlencode($name) . '&fields=id';

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
        echo "findStrapiCountryIdByName cURL Error #:" . $err . "<br>";
    } else {
        $decodedResponse = json_decode($response, true);
        // Supposons que le premier country correspondant est le bon
        $id = $decodedResponse['data'][0]['id'];

        if ($id == null) {
            echo "country $name not found.<br>";
        }

        return $id;
    }
}

function updateCountryFlag($countryId, $mediaId, $name) {

    global $strapiBaseUrl, $strapiToken;

    $curl = curl_init();
    $data = json_encode([
        'data' => [
            'flag_image' => $mediaId
        ],
    ]);

    curl_setopt_array($curl, [
        CURLOPT_URL => "$strapiBaseUrl/api/countries/$countryId",
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
        echo "updateCountryFlag cURL Error #:" . $err . "<br>";
    } else {
        echo "Country $name updated successfully.<br>";
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