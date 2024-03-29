<?php

// use Location\Coordinate;
// use Location\Formatter\Geohash\Geohash;
use Sk\Geohash\Geohash;
// use Cevin\Geohash;

include "util.php";

echo "Migrating regions to Strapi...<br>";

// die();

$rsRegions = getDataArrayFromProcedure("getListRegions", null, null);

// Votre token JWT pour l'authentification API Strapi
$jwtToken = '6afb7b639162f356dc5f5750c8b094b7d931636b87a9402097f0614f3ef9975a5b9f37a6a776cd5eb9942a84f73a336295938027956e17302e7b9ca7d8a799ae25b30460e13e2d2602b2bd6b1bbb863323d499b4f49dea26db6775167910a5712d9cc4b6923bbfb6a0b2d3795b0291ec54c087f53d5fd19b072c8a1c1fc3d307';

// $g = new Geohash();

$jsonCountries = sendRequest('http://localhost:1338/api/countries', null);

// create countries array from $jsonCountries indexec by code_nuts
$countries = [];
foreach ($jsonCountries['data'] as $country) {
	$countries[$country['attributes']['code_nuts']] = $country['id'];
}

// create

foreach ($rsRegions as $region) {
	
	// Préparation de la payload pour l'API Strapi, ajustement pour le champ marker
	
	// $coordinate = new Coordinate($region['lat_icon'], $region['lon_icon']);
	// $geohash = (new Geohash())->format($coordinate);
	// $geohash = $g->encode($region['lat_icon'], $region['lon_icon'], 5);
	// echo $region['code_country'] . " " . $region['lat_icon'] . " " . $region['lon_icon'] . " " . $geohash;

	$payload = [
		'data' => [
			'code_nuts' => $region['code_region'],
			'name' => [
				'name_en' => $region['name_EN'],
				'name_fr' => $region['name_FR'],
				'name_cn' => $region['name_CN'],
				'name_local' => $region['name_local']
			],
      'capital' => [
				'name_en' => $region['name_capital_region'],
				'name_fr' => $region['name_capital_region'],
				'name_cn' => $region['name_CN'],
				'name_local' => $region['name_capital_region']
			],
			'direction_heel' => ucfirst(strtolower($region['direction_heel'] == '' ? 'Bottom' : $region['direction_heel'])),
			'marker' => [
				'coordinates' => [
					'lat' => $region['lat_capital'],
					'lng' => $region['lon_capital'],
				],
				// 'geohash' => $geohash
			],
			'country' => [ $countries[$region['code_country']] ]
		]
	];

	// Envoi de la requête à Strapi
	if (true) {
		// Traitement de la réponse
		$responseData = sendRequest('http://localhost:1338/api/regions', $payload);
		if (isset($responseData['data'])) {
			print "Région " . $region['name_FR'] . " ajouté à Strapi avec les détails du marker.<br>";
		} else {
			print "Erreur lors de l'ajout de la région " . $region['name_FR'] . " avec les détails du marker.<br>";
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
