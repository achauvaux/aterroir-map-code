<?php

include "util.php";

$id_label = $_REQUEST["id_label"];

$rsLabel = getDataArrayFromProcedure("getDetailLabel", $id_label);

$id_region = $rsLabel[0]["id_region"];

$rsLabels = getDataArrayFromProcedure("getListLabels", null, null, $id_region);
// $rsPIs = getDataArrayFromProcedure("getListPI", null, $id_region, null, null);
$rsOTs = getDataArrayFromProcedure("getListPI", null, $id_region, null, 1);
$rsPICategories = getDataArrayFromProcedure("getListPICategories");

?>

<div id="F3" class="aterroir-window">
  <section class="left">
    <div class="menu-item" onmouseenter="openRight('F3-region-labels');">
      <img src="img/icones-aterroir/Bouton F Terroir.png">
    </div>
    <div class="menu-item" onmouseenter="openRight('F3-OTs');">
      <img src="img/icones-aterroir/Bouton F InfoOT.png">
    </div>
    <div class="list-PI">
      <?php foreach ($rsPICategories as $row) { ?>
        <div id="label-<?= $id_label ?>-F3-PI-type-<?= $row["id_picategory"] ?>" class="menu-item PI" onmouseenter="openRight('F3-PI-type-<?= $row['id_picategory'] ?>');" onclick="togglePIType(29,'<?= $row['id_picategory'] ?>');">
          <img src="img/icones-categories-pi/<?= $row['img_icon_category'] ?>">
        </div>
      <?php } ?>
    </div>
    <div class="menu-item" onmouseenter="openRight('F3-infos');">
      <img src="img/icones-aterroir/Bouton F InfoSite.png">
    </div>
    <div class="menu-item" onmouseenter="openRight('F3-formulaire');">
      <img src="img/icones-aterroir/Bouton F Courrier.png">
    </div>
  </section>
  <section id="F3-region-labels" class="right" onmouseleave="closeRight();" style="display: none;">
    <div class="header">
      <h1>Labels</h1>
    </div>
    <div class="content">
      <ul class="list-items">
        <?php foreach ($rsLabels as $row) { ?>
          <li class="legend-item">
            <div class="flag">
              <img src="img/images-labels/<?= $row["img_icon_filename"] ?>" alt="">
            </div>
            <div class="talon-item" onclick="legendMarkerLabelClick(<?= $row['id_label'] ?>);">
              <p><?= $row['name_CN'] ?></p>
              <p><?= $row['name_FR'] ?></p>
            </div>
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="footer"><img src="img/icones-aterroir/aterroir-logo.png"></div>
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
              <img src="img/icones-aterroir/icon-OT-local.png" alt="">
            </div>
            <div class="talon-item" onclick="legendMarkerPIClick(<?= $row['id_pi'] ?>);">
              <p><?= $row['name_CN'] ?></p>
              <p><?= $row['name_FR'] ?></p>
            </div>
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="footer"><img src="img/icones-aterroir/aterroir-logo.png"></div>
  </section>
  <?php foreach ($rsPICategories as $row) { ?>
    <section id="F3-PI-type-<?= $row["id_picategory"] ?>" class="right" onmouseleave="closeRight();" style="display: none;">
      <div class="header">
        <h1><?= $row["name_FR"] ?></h1>
      </div>
      <div class="content">
        <ul class="list-items">
          <?php
          $rsPIs = getDataArrayFromProcedure("getListPIs", null, null, $id_label, $row["id_picategory"]);
          foreach ($rsPIs as $row2) {
          ?>
            <li class="legend-item PI">
              <div class="icon" onclick="togglePI(<?= $row2['id_pi'] ?>);" onmouseover="itemPIFocusOn(<?= $row2['id_pi'] ?>);" onmouseout="itemPIFocusOut(<?= $row2['id_pi'] ?>);">
                <img src="img/icones-categories-pi/<?= $row["img_icon_category"] ?>" alt="">
              </div>
              <div class="photo">
                <img src="img/PI/<?= $row2['img_pi_filename'] ?>" alt="">
              </div>
              <div class="talon-item PI" onclick="legendMarkerPIClick(<?= $row2['id_pi'] ?>);">
                <p><?= $row2['name_FR'] ?></p>
                <p><?= $row2['name_CN'] ?></p>
              </div>
            </li>
          <?php } ?>
        </ul>
      </div>
      <div class="footer"><img src="img/icones-aterroir/aterroir-logo.png"></div>
    </section>
  <?php } ?>
  <section id="F3-infos" class="right" onmouseleave="closeRight();" style="display: none;">
    <div class="header">
      <h1>Infos</h1>
    </div>
    <div class="content">
    </div>
    <div class="footer"><img src="img/icones-aterroir/aterroir-logo.png"></div>
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
    <div class="footer"><img src="img/icones-aterroir/aterroir-logo.png"></div>
  </section>
</div>