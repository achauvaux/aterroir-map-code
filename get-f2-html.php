<?php

include "util.php";

// TODO: Move token to a separate file
$jwtToken = 'c084886539484c66d3ea808e2716fef9ac33989b4769d1ce0f086049bab0dccce4939bcea56acb9bda3f4b232b8f433bd5d71eb10177ca3f4ba35e5d44cd033d3b2adc04b8635143204a9614089a7a5021eac178b98cc046758e40dbcb866c4acbc4bf34d0d4bdd924c5df9261855a1782362902910d8fcf928ba69995776acf';

$code_region = $_REQUEST["code_region"];
// $id_region=$_REQUEST["id_region"];

$rsRegion = sendRequest('http://localhost:1337/api/regions?populate=*&filters[code_nuts]=' . $code_region, null)['data'];

if (empty($rsRegion)) return;

$id_region = $rsRegion[0]["id"];
$id_country = $rsRegion[0]["attributes"]["country"]["data"]["id"];

$rsRegions = sendRequest('http://localhost:1337/api/regions?populate=*&filters[country][id]=' . $id_country, null)['data'];
$rsLabels = sendRequest('http://localhost:1337/api/labels?populate=*&filters[region][id]=' . $id_region, null)['data'];
$rsOTs = [];

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

?>

<div id="F2" class="aterroir-window">
  <section class="left">
    <div class="menu-item" onmouseenter="openRight('F2-regions');">
      <img src="medias/img/icones-aterroir/Bouton F Regions.png">
    </div>
    <div class="menu-item" onmouseenter="openRight('F2-region-labels');">
      <img src="medias/img/icones-aterroir/Bouton F Terroir.png">
    </div>
    <div class="menu-item" onmouseenter="openRight('F2-OTs');">
      <img src="medias/img/icones-aterroir/Bouton F InfoOT.png">
    </div>
    <div class="menu-item" onmouseenter="openRight('F2-infos');">
      <img src="medias/img/icones-aterroir/Bouton F InfoSite.png">
    </div>
    <div class="menu-item" onmouseenter="openRight('F2-formulaire');">
      <img src="medias/img/icones-aterroir/Bouton F Courrier.png">
    </div>
  </section>
  <section id="F2-regions" class="right" onmouseleave="closeRight();" style="display: none;">
    <div class="header">
      <h1>Régions</h1>
    </div>
    <div class="content">
      <ul class="list-items">
        <?php 
        foreach ($rsRegions as $rec) { 
          $row = $rec["attributes"];
          $logo_image_path = $row["logo_image"]["data"]["attributes"]["url"];
          if ($logo_image_path)
            $logo_image_url = "http://localhost:1337" . $logo_image_path;
          else
            $logo_image_url = "http://localhost:1337/uploads/flag_europe_76fbd4fe3d.png";
        ?>
          <li class="legend-item">
            <div class="flag">
              <img src="<?= $logo_image_url ?>" alt="">
            </div>
            <div class="talon-item" onclick="goToRegion('<?= $row['code_nuts'] ?>')" onmouseover="legendRegionOver('<?= $row['code_nuts'] ?>')" onmouseout="legendRegionOut('<?= $row['code_nuts'] ?>')">
              <p><?= $row['name']['name_cn'] ?></p>
              <p><?= $row['name']['name_fr'] ?></p>
            </div>
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="footer"><img src="medias/img/icones-aterroir/aterroir-logo.png"></div>
  </section>
  <section id="F2-region-labels" class="right" onmouseleave="closeRight();" style="display: none;">
    <div class="header">
      <h1>Labels</h1>
    </div>
    <div class="content">
      <ul class="list-items">
        <?php foreach ($rsLabels as $rec) {
          $id_label = $rec["id"];
          $row = $rec["attributes"];
          $label_image = "http://localhost:1337" . $row["marker_icon"]["data"]["attributes"]["url"];
        ?>
          <li class="legend-item">
            <div class="icon-label">
              <img src="<?= $label_image ?>" alt="">
            </div>
            <div class="talon-item" onclick="goToLabel(<?= $id_label ?>);">
              <p><?= $row['name']['name_cn'] ?></p>
              <p><?= $row['name']['name_fr'] ?></p>
            </div>
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="footer"><img src="medias/img/icones-aterroir/aterroir-logo.png"></div>
  </section>
  <section id="F2-OTs" class="right" onmouseleave="closeRight();" style="display: none;">
    <div class="header">
      <h1>OT</h1>
    </div>
    <div class="content">
      <ul class="list-items">
        <?php foreach ($rsOTs as $row) { ?>
          <li class="legend-item">
            <div class="flag">
              <img src="medias/img/icones-aterroir/icon-OT-local.png" alt="">
            </div>
            <div class="talon-item">
              <p><?= $row['name_CN'] ?></p>
              <p><?= $row['name_FR'] ?></p>
            </div>
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="footer"><img src="medias/img/icones-aterroir/aterroir-logo.png"></div>
  </section>
  <section id="F2-infos" class="right" onmouseleave="closeRight();" style="display: none;">
    <div class="header">
      <h1>Infos</h1>
    </div>
    <div class="content">
    </div>
    <div class="footer"><img src="medias/img/icones-aterroir/aterroir-logo.png"></div>
  </section>
  <section id="F2-formulaire" class="right" onmouseleave="closeRight();" style="display: none;">
    <div class="header">
      <h1>Contact</h1>
    </div>
    <div class="content">
      <!-- <form> -->
      <div class="form-group">
        <label for="exampleFormControlInput1">Nom</label>
        <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="">
      </div>
      <div class="form-group">
        <label for="exampleFormControlInput1">Email</label>
        <input type="email" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com">
      </div>
      <div class="form-group">
        <label for="exampleFormControlTextarea1">Message</label>
        <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
      </div>
      <button class="btn btn-primary">Envoyer</button>
      <!-- </form> -->
    </div>
    <div class="footer"><img src="medias/img/icones-aterroir/aterroir-logo.png"></div>
  </section>
</div>