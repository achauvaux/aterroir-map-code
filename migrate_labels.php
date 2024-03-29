<?php

// use Location\Coordinate;
// use Location\Formatter\Geohash\Geohash;
use Sk\Geohash\Geohash;
// use Cevin\Geohash;

include "util.php";

echo "Migrating labels to Strapi...<br>";

// die();

$rsLabels = getDataArrayFromProcedure("getListLabels", null, null, null);

$strapiUrl = 'http://localhost:1338/api/labels';
// Votre token JWT pour l'authentification API Strapi
$jwtToken = '6afb7b639162f356dc5f5750c8b094b7d931636b87a9402097f0614f3ef9975a5b9f37a6a776cd5eb9942a84f73a336295938027956e17302e7b9ca7d8a799ae25b30460e13e2d2602b2bd6b1bbb863323d499b4f49dea26db6775167910a5712d9cc4b6923bbfb6a0b2d3795b0291ec54c087f53d5fd19b072c8a1c1fc3d307';

// $g = new Geohash();

$jsonRegions = sendRequest('http://localhost:1338/api/regions', null);

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
				'coordinates' => [
					'lat' => $label['lat'],
					'lng' => $label['lon'],
				],
				// 'geohash' => $geohash
			],
            'region' => [ $regions[$label['code_region']] ]
		]
	];

	// Envoi de la requête à Strapi
	if (true) {
		// Traitement de la réponse
		$responseData = sendRequest('http://localhost:1338/api/labels', $payload);
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