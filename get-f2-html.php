<?php

include "util.php";

// TODO: Move token to a separate file
$jwtToken = '6afb7b639162f356dc5f5750c8b094b7d931636b87a9402097f0614f3ef9975a5b9f37a6a776cd5eb9942a84f73a336295938027956e17302e7b9ca7d8a799ae25b30460e13e2d2602b2bd6b1bbb863323d499b4f49dea26db6775167910a5712d9cc4b6923bbfb6a0b2d3795b0291ec54c087f53d5fd19b072c8a1c1fc3d307';

$code_region = $_REQUEST["code_region"];
// $id_region=$_REQUEST["id_region"];

$rsRegion = sendRequest('http://51.91.157.23:1337/api/regions?populate=*&filters[code_nuts]=' . $code_region, null)['data'];

if (empty($rsRegion)) return;

$id_region = $rsRegion[0]["id"];
$id_country = $rsRegion[0]["attributes"]["country"]["data"]["id"];

$rsRegions = sendRequest('http://51.91.157.23:1337/api/regions?populate=*&filters[country][id]=' . $id_country, null)['data'];
$rsLabels = sendRequest('http://51.91.157.23:1337/api/labels?populate=*&filters[region][id]=' . $id_region, null)['data'];
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
            $logo_image_url = "http://51.91.157.23:1337" . $logo_image_path;
          else
            $logo_image_url = "http://51.91.157.23:1337/uploads/flag_europe_76fbd4fe3d.png";
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
          $label_image = "http://51.91.157.23:1337" . $row["marker_icon"]["data"]["attributes"]["url"];
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