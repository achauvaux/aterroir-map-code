<?php

include_once "util.php";
// include "lang-settings.php";


$subdomain = null;

$idLabel = 0;
$idRegion = 0;
$idCountry = 0;

// if (isset($_REQUEST["id_label"])) {
//   $idLabel = $_REQUEST["id_label"];
// }

$rsBasemap = [];

$domain = $_SERVER['SERVER_NAME'];
// $domain = $_REQUEST["url"];

// $subdomain = preg_replace('/^(?:([^\.]+)\.)?aterroir\.eu$/', '\1', $domain);
preg_match('/^(?:([^\.]+)\.)?aterroir\.([a-z]+)$/', $domain, $matches, PREG_UNMATCHED_AS_NULL);

// preg_match('/(foo)(bar)(baz)/', 'foobarbaz', $matches);
$subdomain = $matches[1];
$zone = $matches[2];

if ($zone == "eu") {
  $coLang1 = "FR";
  $coLang2 = "CN";
} else if ($zone == "cn") {
  $coLang1 = "CN";
  $coLang2 = "FR";
}

if ((!isset($subdomain) || $subdomain == "www") && isset($_REQUEST["s"]))
  $subdomain = $_REQUEST["s"];

// if (isset($_REQUEST["s"])) {
if (isset($subdomain)) {
  // $subdomain = $_REQUEST["s"];
  $rsMap = getDataArrayFromProcedure("getDetailMap", $subdomain);

  if (isset($rsMap[0]["id_label"]))
    $idLabel = $rsMap[0]["id_label"];
  else if (isset($rsMap[0]["id_region"]))
    $idRegion = $rsMap[0]["id_region"];
  else if (isset($rsMap[0]["id_country"]))
    $idCountry = $rsMap[0]["id_country"];

  if (isset($rsMap[0]["id_basemap"]))
    $rsBasemap = getDataArrayFromProcedure("getDetailBasemap", $rsMap[0]["id_basemap"]);
}

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <title>Terroirs Europe</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <link rel="stylesheet" href="assets/css/leaflet.css" />
  <link rel="stylesheet" href="assets/css/aterroir.css">
  <link rel="stylesheet" href="assets/css/spin.css">
  <!-- <link rel="stylesheet" href="assets/css/bootstrap.css"> -->
  <script src="assets/js/leaflet.js"></script>
  <!-- <script type="text/javascript" src="assets/js/spin.js"></script> -->
  <!-- <script type="text/javascript" src="assets/js/leaflet.spin.js"></script> -->
  <script type="text/javascript" src="assets/js/tile.stamen.js"></script>
  <script type="text/javascript" src="assets/js/jquery-3.6.0.min.js"></script>
  <style type='text/css'>
    #map {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
    }
  </style>
</head>

<body onload="initialize()">
  <div class="loading">Loading…</div>

  <div id="map"></div>
  <script type="text/javascript">
    // log("loading");

    let attLabel;

    let map;

    let JSONMap;

    let zone = '<?= $zone ?>';

    <?php if (isset($rsMap)) { ?>
      JSONMap = <?= json_encode($rsMap) ?>;
    <?php } ?>

    // tuiles (basemap)
    let layerTerrain, layerWatercolor, layerToner, layerPositron, layerPositronNoLabel;
    let JSONbasemap = <?= json_encode($rsBasemap) ?>;

    let layerBasemap;
    let currentTileLayerForLevel0; // basemap niveau 0
    let currentTileLayerOverLevel0; // basemap courante niveau > 0

    let baseMaps;
    let overlayMaps;

    // polygones (pays et régions)
    let layerCountriesEurope;
    let layerRegionsEurope;
    let layerRegionsChine;
    let layerRegionsFrance;
    let listLayerPolygonIndexed = []; // layers polygones (tous : pays, régions...) indexés par leur code (A3 ou NUTS)

    // pays
    let listMarkerLevel0 = []; // marqueurs pays
    let layerLevel0; // layer level 0

    // labels
    let listMarkerLabel = []; // marqueurs label
    let listMarkerLabelIndexed = []; // idem indexés par leur id
    let listListMarkerLabelLevel = []; // listes marqueurs label par niveau
    let listLayerLabelsLevel = []; // layers label par niveau
    let listListImageMapLabel = []; // listes des images de cartes par label
    let listListPolMapLabel = []; // listes des images de cartes par label

    let listLayerImagesLabel = [];
    let listLayerPolygonsLabel = [];

    // PIs
    let listMarkerPIIndexed = []; // marqueurs PI indexés par leur id
    let listListMarkerPILabel = []; // listes marqueurs PI par label
    let listLayerPIMarkersLabel = []; // listes layers PI par label


    let listActiveLayers = []; // liste des calques actifs

    // icones leaflet
    let listIconLabel = [];
    let listIconBigLabel = [];
    let listIconPICategory = [];
    let listIconBigPICategory = [];

    let JSONPICategories = <?= getJSONArrayFromProcedure("getListPICAtegories"); ?>;

    let villesTerroirBourgogneJSON;

    let JSONCountries = <?= getJSONArrayFromProcedure("getListCountries", null); ?>;
    let JSONRegionsEU = <?= getJSONArrayFromProcedure("getListRegions", "EU", null); ?>;
    let JSONRegionsCN = <?= getJSONArrayFromProcedure("getListRegions", "CN", null); ?>;
    // let JSONMarkersLabelEU = <?= getJSONArrayFromProcedure("getListLabels", "EU", null, null); ?>;
    // let JSONMarkersLabelCN = <?= getJSONArrayFromProcedure("getListLabels", "CN", null, null); ?>;
    let JSONMarkersLabel = <?= getJSONArrayFromProcedure("getListLabels", null, null, null); ?>;

    let coLang1 = "<?= $coLang1 ?>";
    let coLang2 = "<?= $coLang2 ?>";
    let restBaseUrl = "<?= $restBaseUrl ?>";

    let logOn = false;

    let lastRegionMouseOvered, currentRegionLayer;

    let commandLegendRegion = [];
    let commandLegendLabel = [];
    let commandLegendCountries;

    let currentCommand;

    let listLevelsAterroir = [];
    let listListLayerLevel = [];

    let aTerroirLevel;
    let computeAterroirLevel = true;

    let zoomLevel;

    let isWindows = true;

    let typeMap = 'global';
    let idLabelMap, idRegionMap, idCountryMap;

    <?php if ($idLabel) { ?>
      idLabelMap = <?= $idLabel ?>;
      let JSONLabel = <?= getJSONArrayFromProcedure("getDetailLabel", $idLabel); ?>; // données JSON du label (carte terroir) 
      let JSONLabelMap = JSONLabel[0];
      typeMap = 'label';
      isWindows = true;
    <?php } else if ($idRegion) { ?>
      idRegionMap = <?= $idRegion ?>;
      let JSONRegion = <?= getJSONArrayFromProcedure("getDetailRegion", $idRegion); ?>;
      let JSONRegionMap = JSONRegion[0];
      typeMap = 'region';
    <?php } else if ($idCountry) { ?>
      idCountryMap = <?= $idCountry ?>;
      let JSONCountry = <?= getJSONArrayFromProcedure("getDetailCountry", $idCountry); ?>;
      let JSONCountryMap = JSONCountry[0];
      typeMap = 'country';
    <?php } ?>

    function initialize() {
      /* 
      pour pouvoir positionner les commandes à de nouveaux endroits. Ex :
      commandLegendRegion[pcodeRegion] = L.control({
        position: 'middleleft'
      });
      */

      L.Map.include({
        _initControlPos: function() {
          let corners = this._controlCorners = {},
            l = 'leaflet-',
            container = this._controlContainer =
            L.DomUtil.create('div', l + 'control-container', this._container);

          function createCorner(vSide, hSide) {
            let className = l + vSide + ' ' + l + hSide;

            corners[vSide + hSide] = L.DomUtil.create('div', className, container);
          }

          createCorner('top', 'left');
          createCorner('top', 'right');
          createCorner('bottom', 'left');
          createCorner('bottom', 'right');

          createCorner('top', 'center');
          createCorner('middle', 'center');
          createCorner('middle', 'left');
          createCorner('middle', 'right');
          createCorner('bottom', 'center');
        }
      });

      createCustomedIcons(20); // création des icones de marqueurs

      let bounds;
      let center0, zoom0;

      if (typeMap == 'label') {
        // let bounds = new L.LatLngBounds(new L.LatLng(JSONLabelMap["bounds_top_left_lat"], JSONLabelMap["bounds_top_left_lon"]), new L.LatLng(JSONLabelMap["bounds_bottom_right_lat"], JSONLabelMap["bounds_bottom_right_lon"]));
        if (!JSONLabelMap["bounds_top_left_lat"] || !JSONLabelMap["bounds_top_left_lon"] || !JSONLabelMap["bounds_bottom_right_lat"] || !JSONLabelMap["bounds_bottom_right_lon"])
          bounds = new L.LatLngBounds(new L.LatLng(Number(JSONLabelMap["lat"]) - 0.5, Number(JSONLabelMap["lon"]) - 0.5), new L.LatLng(Number(JSONLabelMap["lat"]) + 0.5, Number(JSONLabelMap["lon"]) + 0.5));
        else
          bounds = new L.LatLngBounds(new L.LatLng(JSONLabelMap["bounds_top_left_lat"], JSONLabelMap["bounds_top_left_lon"]), new L.LatLng(JSONLabelMap["bounds_bottom_right_lat"], JSONLabelMap["bounds_bottom_right_lon"]));
      }

      let centerZone;

      if (zone == 'eu')
        centerZone = [33, 116.3947];
      else if (zone == 'cn')
        centerZone = [48.833, 2.333];


      // création de la carte
      map = L.map('map', {
        zoomSnap: 0.5,
        // center: center0,
        // zoom: zoom0,
        center: centerZone,
        zoom: 4.5,
        minZoom: 4.5,
        zoomControl: false
      });

      // let cmd = L.control({
      //   position: 'topleft'
      // });

      // cmd.onAdd = function(map) {

      //   let div = L.DomUtil.create('div', 'command');


      //   div.innerHTML = '<button onclick="map.spin(true)">spin on</button>';
      //   div.innerHTML += '<button onclick="map.spin(false)">spin off</button>';

      //   div.innerHTML += `<button onclick='$(".loading").show()'>spin on 2</button>`;
      //   div.innerHTML += `<button onclick='$(".loading").hide()'>spin off 2</button>`;

      //   return div;
      // };

      // cmd.addTo(map);

      // map.spin(true);

      map.on('zoomend', function() {
        log("zoomend - setLayersByLevel()");
        setLayersByLevel();
      })

      log(map.getZoom());

      map.on('baselayerchange', function(e) {
        // log(e.layer);
        currentTileLayerOverLevel0 = e.layer;
        if (e.layer != layerPositron)
          currentTileLayerForLevel0 = e.layer;
        else
          currentTileLayerForLevel0 = layerPositronNoLabel;
      });

      map.on('click', function() {
        log("do");
      })

      createTilesLayers(); // création des calques "tuiles"

      createPolygonsLayers(); // polygones pays et régions

      createMarkersLevel0();

      createCommandLegendCountries(); // fenêtre des pays

      createMarkersLabel();

      createDataLayers(); // création des calques à partir des tableaux de marqueurs

      setCommandChoiceMap();

      if (JSONMap && JSONMap[0]) setCommandPartner();

      // correspondances niveaux aterroir et openstreetmap
      listLevelsAterroir[0] = {
        end: 5
      }; // europe
      listLevelsAterroir[1] = {
        start: 5,
        end: 7
      }; // pays
      listLevelsAterroir[2] = {
        start: 7,
        end: 8
      }
      listLevelsAterroir[3] = {
        start: 8
      };

      if (layerBasemap) {
        currentTileLayerForLevel0 = layerBasemap;
        currentTileLayerOverLevel0 = layerBasemap;
      } else {
        currentTileLayerForLevel0 = layerPositronNoLabel;
        currentTileLayerOverLevel0 = layerPositron;
      }

      listListLayerLevel[0] = [currentTileLayerForLevel0, layerCountriesEurope, layerRegionsChine, layerLevel0];
      listListLayerLevel[1] = [currentTileLayerOverLevel0, layerRegionsEurope, layerRegionsChine, layerRegionsFrance, listLayerLabelsLevel[1]];
      listListLayerLevel[2] = [currentTileLayerOverLevel0, layerRegionsEurope, layerRegionsChine, layerRegionsFrance, listLayerLabelsLevel[1], listLayerLabelsLevel[2]];
      listListLayerLevel[3] = [currentTileLayerOverLevel0, layerRegionsEurope, layerRegionsChine, layerRegionsFrance, listLayerLabelsLevel[1], listLayerLabelsLevel[2], null, null, null];

      if (typeMap == 'label') {
        // map.fitBounds(bounds); // on centre sur la région
        // setAterroirLevel(3);
        // legendMarkerLabelClick(idLabelMap);
        goToLabel(idLabelMap);
      } else if (typeMap == 'region') {
        goToRegion(JSONRegionMap['code_region']);
      } else if (typeMap == 'country') {
        legendCountryClick(JSONCountryMap['code_country']);
      }

      if (typeMap == 'global') {
        // setContextualWindow(commandLegendCountries);
        setLayersByLevel();
        // center(zone);
      }

      $(".loading").hide();
      // map.spin(false);

    }

    function getMarkerLabelPopupContent(pmarker) {

      let desc = "";
      desc += "<p>" + pmarker.label["name_" + coLang1] + "</p>";

      let url = pmarker.label["url"];
      let pdfFile = getFileNameFromJSONMetaData(pmarker.label["pdf"]);

      if (url)
        desc += "<a href='" + url + "' target='_blank'><img class='pdf-img' src='assets/pdf/" + getFileNameFromJSONMetaData(pmarker.label["pdf_icon"]) + "' /></a>";
      else if (pdfFile)
        desc += "<a href='assets/pdf/" + pdfFile + "' target='_blank'><img class='pdf-img' src='assets/pdf/" + getFileNameFromJSONMetaData(pmarker.label["pdf_icon"]) + "' /></a>";

      desc += "<p>" + pmarker.label["name_" + coLang2] + "</p>";

      return desc;
    }

    function getMarkerPIPopupContent(pPI) {

      let desc = "";
      desc += "<h1>" + pPI["name_" + coLang1] + "</h1>";

      let imgJSON = null; //loadJSON(restBaseUrl + "media/" + pPI["img_pi_filename"] + "?_fields=guid");

      if (imgJSON) {
        desc += "<img src='" + imgJSON["guid"]["rendered"] + "'>";
        desc += "<a href='" + pPI["link"] + "' target='_blank'>" + pPI["name_" + coLang1] + "</a>";
      } else {
        desc += "<img src='assets/img/PI/" + getFileNameFromJSONMetaData(pPI["img_pi"]) + "'>";
        desc += "<a href='" + pPI["link"] + "' target='_blank'>" + pPI["name_" + coLang1] + "</a>";
      }

      return desc;
    }

    let listPIType = {
      "OT": ["Offices de Tourisme", "icon-OT.png"],
      "visite": ["Visites", "icon-visite.png"],
      "gastro": ["Gastronomie", "icon-gastro.png"],
      "OT local": ["Offices de Tourisme locales", "icon-OT-local.png"],
      "rando": ["Randonnées", "icon-rando.png"],
      "video": ["Vidéos", "icon-video.png"],
      "carte": ["Cartes", "icon-carte.png"],
      "vente": ["Boutiques", "icon-vente.png"]
    };

    function createCustomedIcons(psize) { // création des icones de marqueurs

      // let sizeBig = Math.trunc(psize * 1.5 / 2);

      listIconLabel["AOP"] = L.icon({
        iconUrl: 'assets/img/icones-aterroir/logo-AOP.png',
        iconSize: [psize, psize]
      });

      listIconBigLabel["AOP"] = L.icon({
        iconUrl: 'assets/img/icones-aterroir/logo-AOP.png',
        iconSize: [psize * 1.5, psize * 1.5]
      });

      listIconLabel["IGP"] = L.icon({
        iconUrl: 'assets/img/icones-aterroir/logo-IGP.png',
        iconSize: [psize, psize]
      });

      listIconBigLabel["IGP"] = L.icon({
        iconUrl: 'assets/img/icones-aterroir/logo-IGP.png',
        iconSize: [psize * 1.5, psize * 1.5]
      });

      listIconLabel["AOPIGP"] = L.icon({
        iconUrl: 'assets/img/icones-aterroir/logo-AOPIGP.png',
        iconSize: [psize, psize]
      });

      listIconBigLabel["AOPIGP"] = L.icon({
        iconUrl: 'assets/img/icones-aterroir/logo-AOPIGP.png',
        iconSize: [psize * 1.5, psize * 1.5]
      });

      for (let picategory of JSONPICategories) {
        listIconPICategory[picategory["id_picategory"]] = L.icon({
          iconUrl: 'assets/img/icones-categories-pi/' + picategory["img_icon_category"],
          iconSize: [psize, psize]
        });
        listIconBigPICategory[picategory["id_picategory"]] = L.icon({
          iconUrl: 'assets/img/icones-categories-pi/' + picategory["img_icon_category"],
          iconSize: [psize * 1.5, psize * 1.5]
        });
      }

    }

    function getFileNameFromJSONMetaData(str) {

      // return str; // TODO extraction depuis champ wp

      let parsed; // depuis JSON généré par php runner

      try {
        parsed = JSON.parse(str);
      } catch (e) {
        return str;
      }

      if (!parsed) return;

      return parsed[0]["name"].split("/").pop();
    }

    function hasPI(pidLabel) {
      return true;
    }

    function createMarkersLabel() { // création des marqueurs label

      let iconMarker = null;
      let marker = null;
      let listTemp;

      let style;
      let dy;

      for (label of JSONMarkersLabel) {

        log(label);
        iconMarker = listIconLabel[label["code_label"]];

        if (label["height_img_icon"] != undefined && label["height_img_icon"] != null)
          dy = label["height_img_icon"] / 2;
        else
          dy = 50;

        switch (label["direction_heel"]) {
          case "left":
            style = "transform: translate(-7px, -" + dy + "px)";
            break;
          case "right":
            style = "transform: translate(7px, -" + dy + "px)";
            break;
          default:
            style = "";
        }

        let toolTipContent =
          "<div id='IG-" + label["id_label"] + "' class='IG " + label["direction_heel"] + "' style='" + style + "'>" +
          "<img src='assets/img/images-labels/" + getFileNameFromJSONMetaData(label["img_icon"]) + "' style='height:" + label["height_img_icon"] + "px'>" +
          "<div class='talon'>" +
          "<p>" +
          label["name_" + coLang1] +
          "</p>" +
          "<p>" +
          label["name_" + coLang2] +
          "</p>" +
          "</div>"; +
        "</div>";

        let IGTooltip = L.tooltip({
          className: "aterroir-tooltip",
          permanent: true,
          direction: label["direction_heel"],
          interactive: true
        });

        IGTooltip.setContent(toolTipContent);

        // ! coordonnées inversées dans geojson umap
        let lat = label["lat"];
        let lon = label["lon"];

        log(lat);
        log(lon);

        let m = L.marker(
          [lat, lon], {
            icon: iconMarker,
            interactive: false
          }
        ).bindTooltip(
          IGTooltip
        ).on("click", function(e) {
          // this.unbindPopup();
          currentMarker = this;
          if (aTerroirLevel == 3 && this.label["id_label"] == currentLabel) { // on est au niveau 3, le niveau max. On fait apparaître la popup info
            if (!this._popup)
              this.bindPopup(getMarkerLabelPopupContent(this), {
                className: "label"
              }).openPopup();
            // this.openPopup();
          } else if (currentRegionLayer == this.layerRegion) { // la région est déjà sélectionnée : on était au niveau 2 et on passe au niveau 3
            /*
            // if (!hasPI(this.label["id_label"])) return;
            let tempCommand = getCommandLegendLabel(this.label["id_label"]);
            if (tempCommand == null) return;
            setContextualWindow(tempCommand); // fenêtre F3
            map.setView(this.getLatLng(), 10); // TODO : centrer sur image terroir
            setAterroirLevel(3);
            */
            goToLabel(this.label["id_label"]);
          } else { // on arrive sur la région (on était à un niveau inférieur ou dans une autre région)
            if (this.layerRegion) { // on vérifie qu'un polygone région est associé au marqueur
              goToRegion(this.label["code_region"]);
            }
          }

        }).on("mouseover", function(e) {
          if (aTerroirLevel > 1 && this.layerRegion == currentRegionLayer)
            $("#IG-" + this.label["id_label"] + " .talon").css("background", "#ffcd00");
          if (this.layerRegion)
            regionFocusOn(this.layerRegion);
          if (aTerroirLevel == 3 && this.label["id_label"] == currentLabel)
            markerLabelFocusOn(this);
        }).on("mouseout", function(e) {
          if (this.layerRegion)
            regionFocusOut(this.layerRegion);
          if (this.layerRegion == currentRegionLayer)
            $("#IG-" + this.label["id_label"] + " .talon").css("background", talonBGColorByLevel[aTerroirLevel]);
          if (currentRegionLayer)
            markerLabelFocusOut(this);
        });

        m.label = label;
        m.layerRegion = getLayerPolygonByCode(label["code_region"]);

        if (listListMarkerLabelLevel[label["level"]] == undefined)
          listListMarkerLabelLevel[label["level"]] = [];

        listListMarkerLabelLevel[label["level"]].push(m);

        listMarkerLabel.push(m);
        listMarkerLabelIndexed[label["id_label"]] = m;
      }
    }

    function getTypeIconCountry(pcode) {

      let bAOP = false;
      let bIGP = false;

      for (let label of JSONMarkersLabel) {
        if (bAOP && bIGP) break;
        if (label["code_country"] == pcode) {
          if (label["code_label"] == "AOP")
            bAOP = true;
          else if (label["code_label"] == "IGP")
            bIGP = true;
        }
      }

      if (bAOP && bIGP)
        return "AOPIGP";
      else if (bAOP)
        return "AOP";
      else if (bIGP)
        return "IGP"
      else
        return "AOPIGP";
    }

    function getTypeIconRegion(pcode) {

      let bAOP = false;
      let bIGP = false;

      for (let label of JSONMarkersLabel) {
        if (bAOP && bIGP) break;
        if (label["code_region"] == pcode) {
          if (label["code_label"] == "AOP")
            bAOP = true;
          else if (label["code_label"] == "IGP")
            bIGP = true;
        }
      }

      if (bAOP && bIGP)
        return "AOPIGP";
      else if (bAOP)
        return "AOP";
      else if (bIGP)
        return "IGP"
      else
        return "AOPIGP";
    }

    function createMarkersLevel0() { // création des marqueurs niveau 0 (en fait nom des pays en chinois TODO : trouver calque des labels chinois)

      let iconMarker;
      let country;
      let region;
      let classe;

      for (country of JSONCountries) {

        if (country["code_country"] != 'CHN') {

          let typeIconCountry = getTypeIconCountry(country["code_country"]);

          iconMarker = listIconLabel[typeIconCountry];

          classe = "talon-" + country["direction_heel"];

          let toolTipContent =
            "<div class='talon-pays " + classe + "'>" +
            "<p>" +
            country["name_" + coLang1].toUpperCase() +
            "</p>" +
            "<p>" +
            country["name_" + coLang2].toUpperCase() +
            "</p>" +
            "</div>";

          let m = L.marker(
            [country["lat_icon"], country["lon_icon"]], {
              icon: iconMarker,
              interactive: false
            }
          ).bindTooltip(
            toolTipContent, {
              permanent: true,
              direction: country["direction_heel"],
              className: "aterroir-tooltip"
            }
          );

          listMarkerLevel0.push(m);
        }
      }

      for (region of JSONRegionsCN) {

        let typeIconRegion = getTypeIconRegion(region["code_region"]);

        iconMarker = listIconLabel[typeIconRegion];

        classe = "talon-" + region["direction_heel"];

        let toolTipContent =
          "<div class='talon-pays " + classe + "'>" +
          "<p>" +
          region["name_" + coLang1].toUpperCase() +
          "</p>" +
          "<p>" +
          region["name_" + coLang2].toUpperCase() +
          "</p>" +
          "</div>";

        let m = L.marker(
          [region["lat_capital"], region["lon_capital"]], {
            icon: iconMarker,
            interactive: false
          }
        ).bindTooltip(
          toolTipContent, {
            permanent: true,
            direction: region["direction_heel"],
            className: "aterroir-tooltip"
          }
        );

        listMarkerLevel0.push(m);
      }

      layerLevel0 = L.layerGroup(listMarkerLevel0);
      layerLevel0.name = "layerLevel0";
    }

    function createImageMap(pimageUrl, plat1, plon1, plat2, plon2) {

      let imageBounds = [
        [plat1, plon1],
        [plat2, plon2]
      ];

      let imageMap = L.imageOverlay("assets/img/maps/" + pimageUrl, imageBounds, {
        interactive: true,
        opacity: 0.5
      });

      return imageMap;
    }


    function createCommandLegendCountries() {
      commandLegendCountries = L.control({
        position: 'middleleft'
      });

      commandLegendCountries.onAdd = function(map) {

        let div = L.DomUtil.create('div', 'command');

        /* IMP! Ne pas propager l'événement de click à la carte sinon les pop-ups de marqueurs se ferment immédiatement */
        L.DomEvent.addListener(div, 'click', L.DomEvent.stopPropagation).addListener(div, 'click', L.DomEvent.preventDefault);
        L.DomEvent.addListener(div, 'mousewheel', L.DomEvent.stopPropagation); // .addListener(div, 'mousewheel', L.DomEvent.preventDefault);

        let html = `
      <div id="F1" class="aterroir-window">
        <section class="left">
          <div class="menu-item" onmouseenter="openRight('F1-countries');">
            <img src="assets/img/icones-aterroir/Bouton F Pays.png">
          </div>
          <div class="menu-item" onmouseenter="openRight('F1-regions');">
            <img src="assets/img/icones-aterroir/Bouton F Régions.png">
          </div>
          <div class="menu-item" onmouseenter="openRight('F1-infos');">
            <img src="assets/img/icones-aterroir/Bouton F InfoSite.png">
          </div>
          <div class="menu-item" onmouseenter="openRight('F1-formulaire');">
            <img src="assets/img/icones-aterroir/Bouton F Courrier.png">
          </div>
        </section>
        <section id="F1-countries" class="right" onmouseleave="closeRight();" style="display: none;">
          <div class="header">
            <h1>Pays</h1>
          </div>
          <div class="content">
            <ul class="list-items">`;
        for (country of JSONCountries) {
          html += `
              <li class="legend-item">
                <div class="flag">
                  <img src="assets/img/flags/` + (country["img_icon"] ?? "flag-europe.png") + `" alt="">
                </div>
                <div class="talon-item" onclick='legendCountryClick("` + country["code_country"] + `")' onmouseover='legendCountryOver("` + country["code_country"] + `")' onmouseout='legendCountryOut("` + country["code_country"] + `")'>
                  <p>` + country["name_" + coLang1] + `</p>
                  <p>` + country["name_" + coLang2] + `</p>
                </div>
              </li>`;
        }
        html += `
            </ul>
          </div>
          <div class="footer"><img src="assets/img/icones-aterroir/aterroir-logo.png"></div>
        </section>
        <section id="F1-regions" class="right" onmouseleave="closeRight();" style="display: none;">
          <div class="header">
            <h1>Régions</h1>
          </div>
          <div class="content">
            <ul class="list-items">`;
        for (region of JSONRegionsEU)
          html += `
              <li class="legend-item">
                <div class="flag">
                  <img src="assets/img/logos-regions/` + (region["img_logo"] ?? "flag-europe.png") + `" alt="">
                </div>
                <div class="talon-item" onclick='goToRegion("` + region["code_region"] + `")' onmouseover='legendRegionOver("` + region["code_region"] + `")' onmouseout='legendRegionOut("` + region["code_region"] + `")'>
                  <p>` + region["name_" + coLang1] + `</p>
                  <p>` + region["name_" + coLang2] + `</p>
                </div>
              </li>`;
        html += `
            </ul>
          </div>
          <div class="footer"><img src="assets/img/icones-aterroir/aterroir-logo.png"></div>
        </section>
        <section id="F1-infos" class="right" onmouseleave="closeRight();" style="display: none;">
          <div class="header">
            <h1>Infos</h1>
          </div>
          <div class="content">
          </div>
          <div class="footer"><img src="assets/img/icones-aterroir/aterroir-logo.png"></div>
        </section>
        <section id="F1-formulaire" class="right" onmouseleave="closeRight();" style="display: none;">
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
      `;

        div.innerHTML = html;

        return div;
      };
    }

    function getCommandLegendRegion(pcodeRegion) {

      if (pcodeRegion in commandLegendRegion)

        return commandLegendRegion[pcodeRegion];

      commandLegendRegion[pcodeRegion] = L.control({
        position: 'middleleft'
      });

      commandLegendRegion[pcodeRegion].onAdd = function(map) {

        let div = L.DomUtil.create('div', 'command');
        L.DomEvent.addListener(div, 'click', L.DomEvent.stopPropagation).addListener(div, 'click', L.DomEvent.preventDefault); // TODO : autres événements (scroll...)
        L.DomEvent.addListener(div, 'mousewheel', L.DomEvent.stopPropagation); // .addListener(div, 'mousewheel', L.DomEvent.preventDefault);

        let html = getF2Html(pcodeRegion);

        div.innerHTML = html;

        return div;
      };

      return commandLegendRegion[pcodeRegion];

    }

    function setCommandPartner() {
      let cmdPartner = L.control({
        position: 'bottomleft'
      });

      let abort = false;

      cmdPartner.onAdd = function(map) {

        let div = L.DomUtil.create('div', 'command');
        // L.DomEvent.addListener(div, 'click', L.DomEvent.stopPropagation).addListener(div, 'click', L.DomEvent.preventDefault); // TODO : autres événements (scroll...)
        // L.DomEvent.addListener(div, 'mousewheel', L.DomEvent.stopPropagation); // .addListener(div, 'mousewheel', L.DomEvent.preventDefault);

        let JSONlogo = JSONMap[0]['img_partner'];
        let logo;

        if (JSONlogo) {
          logo = getFileNameFromJSONMetaData(JSONlogo);
          if (logo) logo = "assets/img/images-partner/" + logo;
        } else {
          if (typeMap == 'region') {
            logo = getFileNameFromJSONMetaData(JSONRegionMap['img_logo']);
            if (logo) logo = "assets/img/logos-regions/" + logo;
          }
        }

        if (!logo) {
          abort = true;
          div.innerHTML = ''
          return div;
        }

        let html =
          `
        <img src="${logo}" style="max-width:200px"/>
        `;

        div.innerHTML = html;

        return div;
      };

      if (!abort)
        cmdPartner.addTo(map);
    }

    function getJSONMarkersPILabel(pidLabel) {

      let jsonMarkers = [];

      $.ajax({
        url: "getJSONMarkersPILabel.php",
        type: "POST",
        async: false, // Mode synchrone indispensable
        data: ({
          id: pidLabel
        }),
        success: function(data) {
          jsonMarkers = JSON.parse(data); // !!! return ici ne marche pas malgré synchrone (!?)
        }
      });

      return jsonMarkers;
    }

    function getJSONMapsLabel(pidLabel) {

      let jsonMaps;

      $.ajax({
        url: "getJSONMapsLabel.php",
        type: "POST",
        async: false, // Mode synchrone indispensable
        data: ({
          id: pidLabel
        }),
        success: function(data) {
          jsonMaps = JSON.parse(data); // !!! return ici ne marche pas malgré synchrone (!?)
        }
      });

      return jsonMaps;
    }

    function getJSONPolygonsLabel(pidLabel) {

      let jsonMaps;

      $.ajax({
        url: "getJSONPolygonsLabel.php",
        type: "POST",
        async: false, // Mode synchrone indispensable
        data: ({
          id: pidLabel
        }),
        success: function(data) {
          jsonMaps = JSON.parse(data); // !!! return ici ne marche pas malgré synchrone (!?)
        }
      });

      return jsonMaps;
    }

    let f2Html = [];

    function getF2Html(pcodeRegion) {

      if (pcodeRegion in f2Html)
        return f2Html[pcodeRegion];

      let html;

      $.ajax({
        url: "get-f2-html.php",
        type: "POST",
        async: false, // Mode synchrone indispensable
        data: ({
          code_region: pcodeRegion
        }),
        success: function(data) {
          html = data; // !!! return ici ne marche pas malgré synchrone (!?)
        }
      });

      f2Html[pcodeRegion] = html;

      return html;
    }

    let f3Html = [];

    function getF3Html(pidLabel) {

      if (pidLabel in f3Html)
        return f3Html[pidLabel];

      let html;

      $.ajax({
        url: "get-f3-html.php",
        type: "POST",
        async: false, // Mode synchrone indispensable
        data: ({
          id_label: pidLabel
        }),
        success: function(data) {
          html = data; // !!! return ici ne marche pas malgré synchrone (!?)
        }
      });

      f3Html[pidLabel] = html;

      return html;
    }

    function createLayerMarkersPILabel(pidLabel) {

      // map.spin(true);

      let JSONMarkersPILabel = getJSONMarkersPILabel(pidLabel);

      listListMarkerPILabel[pidLabel] = [];

      for (let PI of JSONMarkersPILabel) {

        let m = L.marker(
          [PI["lat"], PI["lon"]], {
            icon: listIconPICategory[PI["id_picategory"]]
            // interactive: true
          }).bindPopup(
          getMarkerPIPopupContent(PI)
        ).on("mouseover", function(e) {
          markerPIFocusOn(this);
        }).on("mouseout", function(e) {
          markerPIFocusOut(this);
        });

        m.PI = PI;
        listListMarkerPILabel[pidLabel].push(m);
        listMarkerPIIndexed[PI["id_pi"]] = m;
      }

      listLayerPIMarkersLabel[pidLabel] = L.layerGroup(listListMarkerPILabel[pidLabel]);
    }

    let listImagesAndPolygonsLabel = [];
    let listLayerImagesAndPolygonsLabel = [];

    function createLabelImages(pidLabel) {

      listImagesAndPolygonsLabel = [];

      let JSONMapsLabel = getJSONMapsLabel(pidLabel);

      for (let map of JSONMapsLabel) {
        let imgMap = createImageMap(getFileNameFromJSONMetaData(map["img_map_filename"]), map["lat_lefttop"], map["lon_lefttop"], map["lat_rightbottom"], map["lon_rightbottom"]);
        // layers.push(imgMap);
        listImagesAndPolygonsLabel.push(imgMap);
      }

      // listLayerImagesLabel[pidLabel] = L.layerGroup(layers);
    }

    function createLabelPolygons(pidLabel) {

      listImagesAndPolygonsLabel = [];

      let JSONPolygonsLabel = getJSONPolygonsLabel(pidLabel);

      for (let pols of JSONPolygonsLabel) {
        let files = JSON.parse(pols["filename"]);
        for (let file of files) {
          // let polMapJSON = loadJSON("assets/geojson/terroirs/" + getFileNameFromJSONMetaData(pol["filename"]));
          let polMapJSON = loadJSON("assets/geojson/terroirs/" + file["name"].split("/").pop());
          if (!polMapJSON) continue;
          let tempLayer = L.geoJSON(polMapJSON, {
            onEachFeature: function(feature, layer) {},
            style: {
              color: pols['color'] || "red",
              fillOpacity: pols['opacity'] || 0.2,
              weight: 0
            }
          });

          listImagesAndPolygonsLabel.push(tempLayer);
        }
      }

      // listLayerPolygonsLabel[pidLabel] = L.layerGroup(layers);
      if (listImagesAndPolygonsLabel.length > 0)
        listLayerImagesAndPolygonsLabel[pidLabel] = L.featureGroup(listImagesAndPolygonsLabel);
    }

    function createLabelWindow(pid) {
      commandLegendLabel[pid] = L.control({
        position: 'middleleft'
      });

      let m = getMarkerLabelById(pid);

      commandLegendLabel[pid].onAdd = function(map) {

        let div = L.DomUtil.create('div', 'command');
        L.DomEvent.addListener(div, 'click', L.DomEvent.stopPropagation).addListener(div, 'click', L.DomEvent.preventDefault);
        L.DomEvent.addListener(div, 'mousewheel', L.DomEvent.stopPropagation); // .addListener(div, 'mousewheel', L.DomEvent.preventDefault);

        let html = getF3Html(pid);

        div.innerHTML = html;

        return div;
      };
    }

    function getListPIByType(pJSONMarkersPI, pidLabel, ptypePI) {
      let listPI = [];
      for (let PI of pJSONMarkersPI)
        if (PI["id_label"] == pidLabel && PI["code_category"] == ptypePI)
          listPI.push(PI);

      return listPI;
    }

    function togglePI(pid) {
      let marker = getMarkerLabelById(pid)
      if (!map.hasLayer(marker))
        map.addLayer(marker);
      else
        map.removeLayer(marker);

    }

    function getLabelVal(pidLabel, pnameprop) {
      for (let label of JSONMarkersLabelEU)
        if (label["id_label"] == pidLabel)
          return label[pnameprop];

      return null;
    }

    function togglePIType(pidLabel, ptype) {
      let marker;
      for (marker of listListMarkerPILabel[pidLabel]) {
        if (marker.PI["id_label"] == pidLabel && marker.PI["id_picategory"] == ptype)
          if (!map.hasLayer(marker)) {
            // log("#label-"+pidLabel+"-F3-PI-type-"+ptype);
            $("#label-" + pidLabel + "-F3-PI-type-" + ptype + ">img").css("opacity", "1");
            map.addLayer(marker);
          }
        else {
          $("#label-" + pidLabel + "-F3-PI-type-" + ptype + ">img").css("opacity", "0.5");
          map.removeLayer(marker);
        }
      }
    }

    let listMarkerPILabelLevel = [];

    function createDataLayers() { // création des calques à partir des tableaux de marqueurs

      for (let i in listListMarkerLabelLevel) {
        listLayerLabelsLevel[i] = L.layerGroup(listListMarkerLabelLevel[i]);
      }

      // layerLevel3 = L.layerGroup(listMarkerPIAll);

    }

    function createPolygonsLayers() {

      let EUCountryJSON = loadJSON("assets/geojson/pays-europe-nodomtom.json");

      layerCountriesEurope = L.geoJSON(EUCountryJSON, {
        onEachFeature: function(feature, layer) {
          listLayerPolygonIndexed[feature.properties["adm0_a3"]] = layer;
          layer.on('click', function(e) {
            map.fitBounds(layer.getBounds());
          });
          layer.on('mouseover', function(e) {
            layer.setStyle({
              color: "yellow",
              opacity: 1,
              weight: 0
            });
          });
          layer.on('mouseout', function(e) {
            layer.setStyle({
              color: "white",
              opacity: 0
            });
          });
        },
        style: {
          color: "white",
          opacity: 0
        }
      });

      let nutJSON = loadJSON("assets/geojson/regions-europe.json");

      layerRegionsEurope = L.geoJSON(nutJSON, {
        onEachFeature: function(feature, layer) {
          listLayerPolygonIndexed[feature.properties["name"]] = layer;
          layer.on('click', function(e) {
            goToRegion(feature.properties["name"]);
          });
          layer.on('mouseover', function(e) {
            regionFocusOn(layer);
          });
          layer.on('mouseout', function(e) {
            layer.setStyle({
              color: "white",
              opacity: 0
            });
          });
        },
        style: {
          color: "white",
          opacity: 0,
          weight: 2
        }
      });

      let nutFranceJSON = loadJSON("assets/geojson/regions-france-nodomtom.json");

      layerRegionsFrance = L.geoJSON(nutFranceJSON, {
        onEachFeature: function(feature, layer) {
          listLayerPolygonIndexed[feature.properties["nuts2"]] = layer;
          layer.on('click', function(e) {
            goToRegion(feature.properties["nuts2"]);
          });
          layer.on('mouseover', function(e) {
            regionFocusOn(layer);
          });
          layer.on('mouseout', function(e) {
            layer.setStyle({
              color: "white",
              opacity: 0
            });
          });
        },
        style: {
          color: "white",
          opacity: 0,
          weight: 2
        }
      });

      let nutJSONCN = loadJSON("assets/geojson/gadm36_CHN_1.json");

      layerRegionsChine = L.geoJSON(nutJSONCN, {
        onEachFeature: function(feature, layer) {
          listLayerPolygonIndexed[feature.properties["HASC_1"]] = layer;
          layer.on('click', function(e) {
            currentRegionLayer = layer;
            setAterroirLevel(2);
            map.fitBounds(layer.getBounds());
            // setContextualWindow(getCommandLegendRegion(feature.properties["HASC_1"]));
          });
          layer.on('mouseover', function(e) {
            regionFocusOn(layer);
          });
          layer.on('mouseout', function(e) {
            layer.setStyle({
              color: "white",
              opacity: 0
            });
          });
        },
        style: {
          color: "white",
          opacity: 0,
          weight: 2
        }
      });

    }

    function createTilesLayers() { // création des calques "tuiles"

      layerPositron = new L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/light_all/{z}/{x}/{y}.png");
      layerPositronNoLabel = new L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/light_nolabels/{z}/{x}/{y}.png");

      if (JSONbasemap.length > 0)
        layerBasemap = new L.tileLayer(JSONbasemap[0]["url"]);

    }

    function createMenuTilesLayer() {

      // menu calque tuiles

      baseMaps = {
        "Terrain": layerTerrain,
        "Watercolor": layerWatercolor,
        "Toner": layerToner,
        "Positron": layerPositron,
        "Positron no label": layerPositronNoLabel,
      }

      L.control.layers(baseMaps).addTo(map);
    }

    function loadJSON(pfile) {
      let json = null;

      $.ajax({
        'async': false,
        'global': false,
        'url': pfile,
        'dataType': "json",
        'success': function(data) {
          json = data;
        }
      });

      return json;
    }

    Array.prototype.remove = function() {
      let what, a = arguments,
        L = a.length,
        ax;
      while (L && this.length) {
        what = a[--L];
        while ((ax = this.indexOf(what)) !== -1) {
          this.splice(ax, 1);
        }
      }
      return this;
    };

    function setLayers(plistLayers) { // fait apparaître les calques donnés en paramètre (et seulement eux) sauf le calque de tuile qui reste

      // TODO : stocker calque basemap dans variable et actualiser à chaque station

      let listLayersToRemove = [];

      for (layer of listActiveLayers) { // suppression des calques actifs absents de la liste donnée en paramètre
        if (!plistLayers.includes(layer)) {
          listLayersToRemove.push(layer) // on passe par une liste intermédiaire pour ne pas modifier directement la liste sur laquelle on boucle
        }
      }

      for (layer of listLayersToRemove) {
        listActiveLayers.remove(layer);
        map.removeLayer(layer);
      }

      for (layer of plistLayers) {
        // if (layer.length == 0) continue;
        if (layer && !listActiveLayers.includes(layer)) { // TODO : pourquoi layer undefined parfois ?
          listActiveLayers.push(layer);
          map.addLayer(layer);
        }
      }
    }

    // let talonBGColorByLevel = ["white", "white", "#ffcd00", "#ffcd00"];
    let talonBGColorByLevel = ["white", "white", "white", "#ffcd00"];

    L.Layer.prototype.setInteractive = function(interactive) {
      if (this.getLayers) {
        this.getLayers().forEach(layer => {
          layer.setInteractive(interactive);
        });
        return;
      }
      if (!this._path) {
        return;
      }

      this.options.interactive = interactive;

      if (interactive) {
        L.DomUtil.addClass(this._path, 'leaflet-interactive');
      } else {
        L.DomUtil.removeClass(this._path, 'leaflet-interactive');
      }
    };

    function setLayersByLevel() {

      zoomLevel = map.getZoom();

      if (computeAterroirLevel) {
        aTerroirLevel = getAterroirLevel(zoomLevel);
      }

      log("zoom osm : " + zoomLevel + " / level at : " + aTerroirLevel);

      // TOFIX : don't work when map initialized on label
      // if (aTerroirLevel >= 3)
      //   for (let m of listMarkerLabel) {
      //     m.options.interactive = true;
      //     m._tooltip.options.interactive = false;
      //   }
      // else if (aTerroirLevel == 1)
      //   for (let m of listMarkerLabel) {
      //     m.options.interactive = false;
      //     m._tooltip.options.interactive = false;
      //   }
      // else if (aTerroirLevel == 2)
      //   for (let m of listMarkerLabel) {
      //     m.options.interactive = false;
      //     m._tooltip.options.interactive = true;
      //   }

      if (aTerroirLevel >= 3)
        for (let m of listMarkerLabel) {
          m.options.interactive = true;
        }
      else
        for (let m of listMarkerLabel) {
          m.options.interactive = false;
        }

      if (aTerroirLevel < 2) {
        currentRegionLayer = null;
        // setContextualWindow(commandLegendCountries);
      }

      setLayers(listListLayerLevel[aTerroirLevel]);
      log("setLayersByLevel - setLayers() - level " + aTerroirLevel);

      if (aTerroirLevel != 3) {
        $(".talon").css('background', talonBGColorByLevel[aTerroirLevel]);
      } else {
        for (let m of listMarkerLabel) {
          if (m.layerRegion == currentRegionLayer)
            $("#IG-" + m.label["id_label"] + " .talon").css("background", talonBGColorByLevel[3]);
          else
            $("#IG-" + m.label["id_label"] + " .talon").css("background", talonBGColorByLevel[2]);
        }
      }

      computeAterroirLevel = true;
    }

    function log(pmessage) {
      if (logOn == true) {
        log(pmessage);
      }
    }

    function getLayerPolygonByCode(pcode) {
      return listLayerPolygonIndexed[pcode];
    }

    let lastMarkerOnMap;

    function markerLabelFocusOn(pmarker) {
      pmarker.setIcon(listIconBigLabel[pmarker.label["code_label"]]);
      // $("#IG-" + pmarker.id + " .talon").css("background", "#fffb00");
    }

    function markerLabelFocusOut(pmarker) {
      pmarker.setIcon(listIconLabel[pmarker.label["code_label"]]);
      // $("#IG-" + pmarker.id + " .talon").css("background", talonBGColorByLevel[aTerroirLevel]);
    }

    function markerPIFocusOn(pmarker) {
      pmarker.setIcon(listIconBigPICategory[pmarker.PI["id_picategory"]]);
    }

    function markerPIFocusOut(pmarker) {
      pmarker.setIcon(listIconPICategory[pmarker.PI["id_picategory"]]);
    }

    function itemPIFocusOn(pid) {
      markerPIFocusOn(getMarkerPIById(pid));
    }

    function itemPIFocusOut(pid) {
      markerPIFocusOut(getMarkerPIById(pid));
    }

    function regionFocusOn(playerRegion) {
      regionFocusOut();
      if (playerRegion != currentRegionLayer) {
        lastRegionMouseOvered = playerRegion;
        playerRegion.setStyle({
          color: "yellow"
        });
      }
    }

    function regionFocusOut() {
      if (lastRegionMouseOvered) {
        lastRegionMouseOvered.setStyle({
          color: "white",
          opacity: 0
        });
        lastRegionMouseOvered = null;
      }
    }

    function legendMarkerLabelMouseOver(pid) {
      let m = getMarkerLabelById(pid);
      lastMarkerOnMap = map.hasLayer(m);
      if (!lastMarkerOnMap)
        map.addLayer(m);
    }

    function legendMarkerLabelMouseOut(pid) {
      let m = getMarkerLabelById(pid);
      if (!lastMarkerOnMap)
        map.removeLayer(m);
    }

    function legendMarkerLabelClick(pid) {
      let marker = getMarkerLabelById(pid);
      marker.fire("click");
    }

    function legendMarkerPIClick(pid) {
      let marker = getMarkerPIById(pid);
      marker.fire("click");
    }

    function legendCountryClick(pcode) {
      let countryLayer = getLayerPolygonByCode(pcode);
      countryLayer.fire("click");
    }

    function legendCountryOver(pcode) {
      let countryLayer = getLayerPolygonByCode(pcode);
      countryLayer.fire("mouseover");
    }

    function legendCountryOut(pcode) {
      let countryLayer = getLayerPolygonByCode(pcode);
      countryLayer.fire("mouseout");
    }

    function goToRegion(pcode) {
      if (currentMarker)
        currentMarker.unbindPopup();
      $(".loading").show();
      setTimeout(function() {
        let regionLayer = getLayerPolygonByCode(pcode);
        currentRegionLayer = regionLayer;
        setAterroirLevel(2);
        map.fitBounds(regionLayer.getBounds());
        regionFocusOut();
        // setContextualWindow(getCommandLegendRegion(pcode));
        $(".loading").hide();
      }, 0)

    }

    function setCommandChoiceMap() {

      let commandChoiceMap = L.control({
        position: 'topright'
      });

      commandChoiceMap.onAdd = function(map) {

        let div = L.DomUtil.create('div', 'command');
        // L.DomEvent.addListener(div, 'click', L.DomEvent.stopPropagation).addListener(div, 'click', L.DomEvent.preventDefault); // TODO : autres événements (scroll...)
        L.DomEvent.addListener(div, 'mousewheel', L.DomEvent.stopPropagation); // .addListener(div, 'mousewheel', L.DomEvent.preventDefault);

        let html =
          `
          <div class="choice-lang">
            <!--input type="radio" id="map-eu" name="drone" value="map-eu" onclick="center('eu')" checked-->
            <!--label for="Europe">Europe</label-->
            <a href="#"><img src="assets/img/flags/eu.png" onclick="center('eu')"></a>
          </div>

          <div class="choice-lang">
            <!--input type="radio" id="map-cn" name="drone" value="map-cn" onclick="center('cn')"-->
            <!--label for="China">China</label-->
            <a href="#"><img src="assets/img/flags/cn.png" onclick="center('cn')"></a>
          </div>
          `;

        div.innerHTML = html;

        return div;
      };

      commandChoiceMap.addTo(map);
    }

    function center(zone) {
      // setAterroirLevel(0);
      if (zone == 'eu')
        map.setView([48.833, 2.333], 4.5);
      else if (zone == 'cn')
        map.setView([33, 116.3947], 4.5);
    }

    let labelDataExists = [];

    function centerMapOnLabel(pid) {

      let z1 = map.getZoom();
      log(z1);

      if (listLayerImagesAndPolygonsLabel[pid]) {
        // if (false) {
        log("centerMapOnLabel - map.fitBounds(listLayerImagesAndPolygonsLabel[pid].getBounds());");
        map.fitBounds(listLayerImagesAndPolygonsLabel[pid].getBounds());
        // let l = listLayerPolygonsLabel[pid];
        // setTimeout(() => {
        //   map.fitBounds(listLayerImagesAndPolygonsLabel[pid].getBounds());
        //   setAterroirLevel(3);
        //   setLayersByLevel();
        // }, 0);

      } else {
        let m = getMarkerLabelById(pid);
        map.setView(m.getLatLng(), 10);
      }

      // computeAterroirLevel = false;
      let z2 = map.getZoom();
      log(z2);

      if (z1 == z2) {
        log("centerMapOnLabel - setLayersByLevel();");
        setLayersByLevel();
        setAterroirLevel(3);
      }
    }

    let currentLabel, currentMarker = null;

    function goToLabel(pid) {

      currentLabel = pid;

      if (currentMarker)
        currentMarker.unbindPopup();

      // log("1");
      $(".loading").show();
      // map.spin(true);
      // $(".loading").css("display", "block");

      setTimeout(function() {
        if (!currentRegionLayer) {
          let m = getMarkerLabelById(pid);
          currentRegionLayer = m.layerRegion;
        }

        // goToRegion(m.label["code_region"]);

        if (!labelDataExists[pid]) {
          // createLayerMarkersPILabel(pid);
          // createLabelImages(pid);
          createLabelPolygons(pid);
          // createLabelWindow(pid);
          labelDataExists[pid] = true;
        }

        listListLayerLevel[3][6] = listLayerPIMarkersLabel[pid];
        listListLayerLevel[3][7] = listLayerImagesLabel[pid];
        // listLayerPolygonsCurrentLabel = listListPolMapLabel[pid];
        listListLayerLevel[3][8] = listLayerImagesAndPolygonsLabel[pid];

        log("goToLabel - setAterroirLevel(3)");
        setAterroirLevel(3);
        centerMapOnLabel(pid);
        // setLayersByLevel(); // Au cas où pas de changement

        // setContextualWindow(commandLegendLabel[pid]);

        // log("2");
        $(".loading").hide();
      });


      // map.spin(false);

    }

    function legendRegionOver(pcode) {
      let regionLayer = getLayerPolygonByCode(pcode);
      regionLayer.fire("mouseover");
    }

    function legendRegionOut(pcode) {
      let regionLayer = getLayerPolygonByCode(pcode);
      regionLayer.fire("mouseout");
    }


    function getMarkerLabelById(pid) {
      return listMarkerLabelIndexed[pid];
    }

    function getMarkerPIById(pid) {
      return listMarkerPIIndexed[pid];
    }

    function getAterroirLevel(pzoomLevel) {
      for (let nuLevel in listLevelsAterroir) {
        if (listLevelsAterroir[nuLevel].start != undefined && pzoomLevel <= listLevelsAterroir[nuLevel].start)
          continue;
        if (listLevelsAterroir[nuLevel].end != undefined && pzoomLevel > listLevelsAterroir[nuLevel].end)
          continue;

        return nuLevel;
      }
    }

    function setAterroirLevel(plevel) {
      aTerroirLevel = plevel;
      computeAterroirLevel = false;
    }

    let lastItem;

    function closeRight() {
      if (lastItem)
        $("#" + lastItem).hide();
    }

    function openRight(pitem) {

      if (lastItem)
        $("#" + lastItem).hide();

      lastItem = pitem;
      $("#" + pitem).show(0);
    }

    function setContextualWindow(pcommand) {

      if (!isWindows) return;

      if (pcommand == currentCommand)
        return;

      if (currentCommand != null)
        map.removeControl(currentCommand);

      pcommand.addTo(map);

      currentCommand = pcommand;
    }
  </script>


</body>

</html>