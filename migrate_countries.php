<?php

// use Location\Coordinate;
// use Location\Formatter\Geohash\Geohash;
use Sk\Geohash\Geohash;
// use Cevin\Geohash;

include "util.php";

echo "Migrating countries to Strapi...<br>";

// die();

$rsCountries = getDataArrayFromProcedure("getListCountries", null);

$strapiUrl = 'http://localhost:1338/api/countries';
// Votre token JWT pour l'authentification API Strapi
$jwtToken = '6afb7b639162f356dc5f5750c8b094b7d931636b87a9402097f0614f3ef9975a5b9f37a6a776cd5eb9942a84f73a336295938027956e17302e7b9ca7d8a799ae25b30460e13e2d2602b2bd6b1bbb863323d499b4f49dea26db6775167910a5712d9cc4b6923bbfb6a0b2d3795b0291ec54c087f53d5fd19b072c8a1c1fc3d307';

// $g = new Geohash();

foreach ($rsCountries as $country) {
	
	// Préparation de la payload pour l'API Strapi, ajustement pour le champ marker
	
	// $coordinate = new Coordinate($country['lat_icon'], $country['lon_icon']);
	// $geohash = (new Geohash())->format($coordinate);
	// $geohash = $g->encode($country['lat_icon'], $country['lon_icon'], 5);
	// echo $country['code_country'] . " " . $country['lat_icon'] . " " . $country['lon_icon'] . " " . $geohash;

	$payload = [
		'data' => [
			'code_nuts' => $country['code_country'],
			'code_zone' => $country['code_zone'],
			'name' => [
				'name_en' => $country['name_EN'],
				'name_fr' => $country['name_FR'],
				'name_cn' => $country['name_CN'],
				'name_local' => $country['name_local']
			],
			'direction_heel' => ucfirst(strtolower($country['direction_heel'])),
			'marker' => [
				'coordinates' => [
					'lat' => $country['lat_icon'],
					'lng' => $country['lon_icon'],
				],
				// 'geohash' => $geohash
			]
		]
	];

	// Configuration de la requête cURL
	$ch = curl_init($strapiUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		'Authorization: Bearer ' . $jwtToken,
	]);


	// Envoi de la requête à Strapi
	if (false) {
		$response = curl_exec($ch);
		curl_close($ch);

		// Traitement de la réponse
		$responseData = json_decode($response, true);
		if (isset($responseData['data'])) {
			print "Pays " . $country['name_FR'] . " ajouté à Strapi avec les détails du marker.<br>";
		} else {
			print "Erreur lors de l'ajout du pays " . $country['name_FR'] . " avec les détails du marker.<br>";
		}
	}
	// break;
}
