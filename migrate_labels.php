<?php

// use Location\Coordinate;
// use Location\Formatter\Geohash\Geohash;
use Sk\Geohash\Geohash;
// use Cevin\Geohash;

include "util.php";

echo "Migrating labels to Strapi...<br>";

// die();

$rsLabels = getDataArrayFromProcedure("getListLabels", null, null, null);

$strapiUrl = 'http://localhost:1337/api/labels';
// Votre token JWT pour l'authentification API Strapi
$jwtToken = 'c084886539484c66d3ea808e2716fef9ac33989b4769d1ce0f086049bab0dccce4939bcea56acb9bda3f4b232b8f433bd5d71eb10177ca3f4ba35e5d44cd033d3b2adc04b8635143204a9614089a7a5021eac178b98cc046758e40dbcb866c4acbc4bf34d0d4bdd924c5df9261855a1782362902910d8fcf928ba69995776acf';

// $g = new Geohash();

$jsonRegions = sendRequest('http://localhost:1337/api/regions', null);

// create countries array from $jsonCountries indexec by code_nuts
$regions = [];
foreach ($jsonRegions['data'] as $region) {
	$regions[$region['attributes']['code_nuts']] = $region['id'];
}

foreach ($rsLabels as $label) {
	
	// Préparation de la payload pour l'API Strapi, ajustement pour le champ marker
	
	// $coordinate = new Coordinate($label['lat_icon'], $label['lon_icon']);
	// $geohash = (new Geohash())->format($coordinate);
	// $geohash = $g->encode($label['lat_icon'], $label['lon_icon'], 5);
	// echo $label['code_country'] . " " . $label['lat_icon'] . " " . $label['lon_icon'] . " " . $geohash;

	$payload = [
		'data' => [
			'name' => [
				'name_en' => $label['name_EN'],
				'name_fr' => $label['name_FR'],
				'name_cn' => $label['name_CN'],
				'name_local' => $label['name_local']
			],
            'city' => [
				'name_en' => $label['name_town_label'],
				'name_fr' => $label['name_town_label'],
				'name_cn' => $label['name_town_label_CN'],
				'name_local' => $label['name_town_label']
			],
			'code_label' =>$label['code_label'],
			'code_category' =>$label['code_category'],
			'city_zip' =>$label['zip_town_label'],
			'level' =>$label['level'],
			'direction_heel' => ucfirst(strtolower($label['direction_heel'])),
			'marker' => [
				// 'coordinates' => [
					'lat' => $label['lat'],
					'lng' => $label['lon'],
				// ],
				// 'geohash' => $geohash
			],
            'region' => [ $regions[$label['code_region']] ],
			'marker_icon_height' => $label['height_img_icon'],
		]
	];

	// Envoi de la requête à Strapi
	if (true) {
		// Traitement de la réponse
		$responseData = sendRequest('http://localhost:1337/api/labels', $payload);
		if (isset($responseData['data'])) {
			print "Label " . $label['name_FR'] . " ajouté à Strapi avec les détails du marker.<br>";
		} else {
			print "Erreur lors de l'ajout du label " . $label['name_FR'] . " avec les détails du marker.<br>";
		}
	}
	// break;
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