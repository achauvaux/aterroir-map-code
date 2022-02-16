<?php

include "util.php";

$idLabel = $_REQUEST["id_label"];

$restBaseUrl = "http://51.91.157.23/aterroir-wp-jl/wp-json/wp/v2/"; // TODO _fields=acf
// $rqParams = "ip?_fields=acf&filter[meta_key]=idLabel&filter[meta_value]=$idLabel";
// $rqParams = "ip?filter[meta_key]=id_label&filter[meta_value]=$idLabel";
$rqParams = "ip?filter[meta_query][relation]=AND&filter[meta_query][0][key]=id_label&filter[meta_query][0][value]=$idLabel&filter[meta_query][1][key]=id_picategory&filter[meta_query][1][value]=";
$restUrl = $restBaseUrl . $rqParams;

function getQueryUrl($pid_category)
{
    global $restUrl;

    $queryUrl = $restUrl . $pid_category;

    return $queryUrl;
}

function getPIJSON($pid_category)
{
    return json_decode(file_get_contents(getQueryUrl($pid_category)), true);
}

$rsLabel = getDataArrayFromProcedure("getDetailLabel", $idLabel);

$id_region = $rsLabel[0]["id_region"];

$rsLabels = getDataArrayFromProcedure("getListLabels", null, null, $id_region);
// $rsPIs = getDataArrayFromProcedure("getListPI", null, $id_region, null, null);
$rsOTs = getDataArrayFromProcedure("getListPI", null, $id_region, null, 1);
$rsPICategories = getDataArrayFromProcedure("getListPICategories");

$JSONPIs = [];

?>

<div id="F3" class="aterroir-window">
    <section class="left">
        <div class="menu-item" onmouseenter="openRight('F3-OTs');">
            <img src="img/icones-aterroir/Bouton F InfoOT.png">
        </div>
        <div class="list-PI">
            <?php foreach ($rsPICategories as $row) {
                $JSONPIs[$row["id_picategory"]] = getPIJSON($row["id_picategory"]);
                if (count($JSONPIs[$row["id_picategory"]]) == 0) continue;
            ?>
                <div id="label-<?= $idLabel ?>-F3-PI-type-<?= $row["id_picategory"] ?>" class="menu-item PI" onmouseenter="openRight('F3-PI-type-<?= $row['id_picategory'] ?>');" onclick="togglePIType(<?= $idLabel ?>,'<?= $row['id_picategory'] ?>');">
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
    <?php foreach ($rsPICategories as $row) {
        if (count($JSONPIs[$row["id_picategory"]]) == 0) continue;
    ?>
        <section id="F3-PI-type-<?= $row["id_picategory"] ?>" class="right" onmouseleave="closeRight();" style="display: none;">
            <div class="header">
                <h1><?= $row["name_FR"] ?></h1>
            </div>
            <div class="content">
                <ul class="list-items">
                    <?php
                    // $rsPIs = getDataArrayFromProcedure("getListPIs", null, null, $idLabel, $row["id_picategory"]);
                    // $JSONPIs = getPIJSON($row["id_picategory"]);
                    foreach ($JSONPIs[$row["id_picategory"]] as $row2) {
                        $rowACF = $row2["acf"];
                        $rowACF["id_pi"] = $row2["id"];
                        $rendered = "";
                        if ($rowACF["img_pi_filename"] != 0) {
                            $media = json_decode(file_get_contents($restBaseUrl . "media/" . $rowACF["img_pi_filename"] . "?_fields=guid"), true);
                            $rendered =  $media["guid"]["rendered"];
                        }
                    ?>
                        <li class="legend-item PI">
                            <div class="icon" onclick="togglePI(<?= $rowACF['id_pi'] ?>);" onmouseover="itemPIFocusOn(<?= $rowACF['id_pi'] ?>);" onmouseout="itemPIFocusOut(<?= $rowACF['id_pi'] ?>);">
                                <img src="img/icones-categories-pi/<?= $row["img_icon_category"] ?>" alt="">
                            </div>
                            <div class="photo">
                                <!-- <img src="img/PI/<?= $rowACF['img_pi_filename'] ?>" alt=""> -->
                                <img src="<?= $rendered ?>" alt="">
                            </div>
                            <div class="talon-item PI" onclick="legendMarkerPIClick(<?= $rowACF['id_pi'] ?>);">
                                <p><?= $rowACF['name_FR'] ?></p>
                                <p><?= $rowACF['name_CN'] ?></p>
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