<?php

include "util.php";

// TODO: Move token to a separate file
$jwtToken = '6afb7b639162f356dc5f5750c8b094b7d931636b87a9402097f0614f3ef9975a5b9f37a6a776cd5eb9942a84f73a336295938027956e17302e7b9ca7d8a799ae25b30460e13e2d2602b2bd6b1bbb863323d499b4f49dea26db6775167910a5712d9cc4b6923bbfb6a0b2d3795b0291ec54c087f53d5fd19b072c8a1c1fc3d307';

$id_label = $_REQUEST["id_label"];

// get label from strapi
$rsLabel = sendRequest('http://51.91.157.23:1337/api/labels/' . $id_label . '?populate=*', null)['data'];

$id_region = $rsLabel["attributes"]["region"]["data"]["id"];

$rsLabels = sendRequest('http://51.91.157.23:1337/api/labels?populate=*&filters[region][id]=' . $id_region, null)['data'];
$rsOTs = [];
$rsPICategories =[];
$rsPIs = [];

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

<div id="F3" class="aterroir-window">
  <section class="left">
    <div class="menu-item" onmouseenter="openRight('F3-region-labels');">
      <img src="medias/img/icones-aterroir/Bouton F Terroir.png">
    </div>
    <div class="menu-item" onmouseenter="openRight('F3-OTs');">
      <img src="medias/img/icones-aterroir/Bouton F InfoOT.png">
    </div>
    <div class="list-PI">
      <?php foreach ($rsPICategories as $row) {
        $rsPIs[$row["id_picategory"]] = [];
        if (empty($rsPIs[$row["id_picategory"]])) continue;
      ?>
        <div id="label-<?= $id_label ?>-F3-PI-type-<?= $row["id_picategory"] ?>" class="menu-item PI" onmouseenter="openRight('F3-PI-type-<?= $row['id_picategory'] ?>');" onclick="togglePIType(29,'<?= $row['id_picategory'] ?>');">
          <img src="assets/img/icones-categories-pi/<?= $row['img_icon_category'] ?>">
        </div>
      <?php } ?>
    </div>
    <div class="menu-item" onmouseenter="openRight('F3-infos');">
      <img src="medias/img/icones-aterroir/Bouton F InfoSite.png">
    </div>
    <div class="menu-item" onmouseenter="openRight('F3-formulaire');">
      <img src="medias/img/icones-aterroir/Bouton F Courrier.png">
    </div>
  </section>
  <section id="F3-region-labels" class="right" onmouseleave="closeRight();" style="display: none;">
    <div class="header">
      <h1>Labels</h1>
    </div>
    <div class="content">
      <ul class="list-items">
        <?php 
          foreach ($rsLabels as $rec) {
            $id_label_tmp = $rec["id"];
            $row = $rec["attributes"];
            $label_image = "http://51.91.157.23:1337" . $row["marker_icon"]["data"]["attributes"]["url"];
        ?>
          <li class="legend-item">
            <div class="icon-label">
              <img src="<?= $label_image ?>" alt="">
            </div>
            <div class="talon-item" onclick="goToLabel(<?= $id_label_tmp ?>);">
              <p><?= $row['name']['name_cn'] ?></p>
              <p><?= $row['name']['name_fr'] ?></p>
            </div>
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="footer"><img src="medias/img/icones-aterroir/aterroir-logo.png"></div>
  </section>
  <section id="F3-OTs" class="right" onmouseleave="closeRight();" style="display: none;">
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
            <div class="talon-item" onclick="legendMarkerPIClick(<?= $row['id_pi'] ?>);">
              <p><?= $row['name_CN'] ?></p>
              <p><?= $row['name_FR'] ?></p>
            </div>
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="footer"><img src="medias/img/icones-aterroir/aterroir-logo.png"></div>
  </section>
  <?php foreach ($rsPICategories as $row) { ?>
    <section id="F3-PI-type-<?= $row["id_picategory"] ?>" class="right" onmouseleave="closeRight();" style="display: none;">
      <div class="header">
        <h1><?= $row["name_FR"] ?></h1>
      </div>
      <div class="content">
        <ul class="list-items">
          <?php
          $rsPIcat = $rsPIs[$row["id_picategory"]];
          foreach ($rsPIcat as $row2) {
          ?>
            <li class="legend-item PI">
              <div class="icon" onclick="togglePI(<?= $row2['id_pi'] ?>);" onmouseover="itemPIFocusOn(<?= $row2['id_pi'] ?>);" onmouseout="itemPIFocusOut(<?= $row2['id_pi'] ?>);">
                <img src="assets/img/icones-categories-pi/<?= $row["img_icon_category"] ?>" alt="">
              </div>
              <div class="photo">
                <img src="assets/img/PI/<?= $row2['img_pi_filename'] ?>" alt="">
              </div>
              <div class="talon-item PI" onclick="legendMarkerPIClick(<?= $row2['id_pi'] ?>);">
                <p><?= $row2['name_FR'] ?></p>
                <p><?= $row2['name_CN'] ?></p>
              </div>
            </li>
          <?php } ?>
        </ul>
      </div>
      <div class="footer"><img src="medias/img/icones-aterroir/aterroir-logo.png"></div>
    </section>
  <?php } ?>
  <section id="F3-infos" class="right" onmouseleave="closeRight();" style="display: none;">
    <div class="header">
      <h1>Infos</h1>
    </div>
    <div class="content">
    </div>
    <div class="footer"><img src="medias/img/icones-aterroir/aterroir-logo.png"></div>
  </section>
  <section id="F3-formulaire" class="right" onmouseleave="closeRight();" style="display: none;">
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