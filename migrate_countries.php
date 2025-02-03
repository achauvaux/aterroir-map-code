<?php

// use Location\Coordinate;
// use Location\Formatter\Geohash\Geohash;
use Sk\Geohash\Geohash;
// use Cevin\Geohash;

include "util.php";

echo "Migrating countries to Strapi...<br>";

// die();

$rsCountries = getDataArrayFromProcedure("getListCountries", null);

$strapiUrl = 'http://localhost:1337/api/countries';
// Votre token JWT pour l'authentification API Strapi
$jwtToken = 'c084886539484c66d3ea808e2716fef9ac33989b4769d1ce0f086049bab0dccce4939bcea56acb9bda3f4b232b8f433bd5d71eb10177ca3f4ba35e5d44cd033d3b2adc04b8635143204a9614089a7a5021eac178b98cc046758e40dbcb866c4acbc4bf34d0d4bdd924c5df9261855a1782362902910d8fcf928ba69995776acf';

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
				// 'coordinates' => [
					'lat' => $country['lat_icon'],
					'lng' => $country['lon_icon'],
				// ],
				// 'geohash' => $geohash
			],
			/* set today date */
			'publishedAt' => date('Y-m-d'),
		]
	];

	// Configuration de la requête cURL
	$ch = curl_init($strapiUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		'Authorization: Bearer ' . $jwtToken,
	]);


	// Envoi de la requête à Strapi
	if (true) {
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
