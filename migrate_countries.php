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
$jwtToken = '8ff94e0aab95375592ce0bceb1e8ea7cd110ee88e699487d44cc84eb9f935b7aff7ec949fca78d6afe0189093a3463a3f6f29e591789bb28995288c403facb75995c3e4c93544c1a6a053f90b501d15bc8feacd77d28a0c06b7a2e91c00b0690f8b87b5a0d5d2e0f7cb6d2affb3f1ecfbf97fa1668f102e4102b0b36e386ecf7';

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
