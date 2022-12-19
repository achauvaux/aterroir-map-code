<?php

include "util.php";

$code_region = $_REQUEST["code_region"];
// $id_region=$_REQUEST["id_region"];

$rsRegion = getDataArrayFromProcedure("getDetailRegion2", $code_region);

if (empty($rsRegion)) return;

$id_country = $rsRegion[0]["id_country"];
$id_region = $rsRegion[0]["id_region"];

$rsRegions = getDataArrayFromProcedure("getListRegions", null, $id_country);
$rsLabels = getDataArrayFromProcedure("getListLabels", null, null, $id_region);
$rsOTs = getDataArrayFromProcedure("getListPI", null, $id_region, null, 1);

?>

<div id="F2" class="aterroir-window">
  <section class="left">
    <div class="menu-item" onmouseenter="openRight('F2-regions');">
      <img src="assets/img/icones-aterroir/Bouton F Régions.png">
    </div>
    <div class="menu-item" onmouseenter="openRight('F2-region-labels');">
      <img src="assets/img/icones-aterroir/Bouton F Terroir.png">
    </div>
    <div class="menu-item" onmouseenter="openRight('F2-OTs');">
      <img src="assets/img/icones-aterroir/Bouton F InfoOT.png">
    </div>
    <div class="menu-item" onmouseenter="openRight('F2-infos');">
      <img src="assets/img/icones-aterroir/Bouton F InfoSite.png">
    </div>
    <div class="menu-item" onmouseenter="openRight('F2-formulaire');">
      <img src="assets/img/icones-aterroir/Bouton F Courrier.png">
    </div>
  </section>
  <section id="F2-regions" class="right" onmouseleave="closeRight();" style="display: none;">
    <div class="header">
      <h1>Régions</h1>
    </div>
    <div class="content">
      <ul class="list-items">
        <?php foreach ($rsRegions as $row) { ?>
          <li class="legend-item">
            <div class="flag">
              <img src="assets/img/logos-regions/<?= $row["img_logo"] ?>" alt="">
            </div>
            <div class="talon-item" onclick="goToRegion('<?= $row['code_region'] ?>')" onmouseover="legendRegionOver('<?= $row['code_region'] ?>')" onmouseout="legendRegionOut('<?= $row['code_region'] ?>')">
              <p><?= $row['name_CN'] ?></p>
              <p><?= $row['name_FR'] ?></p>
            </div>
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="footer"><img src="assets/img/icones-aterroir/aterroir-logo.png"></div>
  </section>
  <section id="F2-region-labels" class="right" onmouseleave="closeRight();" style="display: none;">
    <div class="header">
      <h1>Labels</h1>
    </div>
    <div class="content">
      <ul class="list-items">
        <?php foreach ($rsLabels as $row) { ?>
          <li class="legend-item">
            <div class="flag">
              <img src="assets/img/images-labels/<?= $row['img_icon_filename'] ?>" alt="">
            </div>
            <div class="talon-item" onclick="legendMarkerLabelClick(<?= $row['id_label'] ?>);">
              <p><?= $row['name_CN'] ?></p>
              <p><?= $row['name_FR'] ?></p>
            </div>
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="footer"><img src="assets/img/icones-aterroir/aterroir-logo.png"></div>
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
              <img src="assets/img/icones-aterroir/icon-OT-local.png" alt="">
            </div>
            <div class="talon-item">
              <p><?= $row['name_CN'] ?></p>
              <p><?= $row['name_FR'] ?></p>
            </div>
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="footer"><img src="assets/img/icones-aterroir/aterroir-logo.png"></div>
  </section>
  <section id="F2-infos" class="right" onmouseleave="closeRight();" style="display: none;">
    <div class="header">
      <h1>Infos</h1>
    </div>
    <div class="content">
    </div>
    <div class="footer"><img src="assets/img/icones-aterroir/aterroir-logo.png"></div>
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
    <div class="footer"><img src="assets/img/icones-aterroir/aterroir-logo.png"></div>
  </section>
</div>