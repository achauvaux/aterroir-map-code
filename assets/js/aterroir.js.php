<?php

include_once "../../util.php";
// include "lang-settings.php";

?>


// console.log("loading");

var attLabel;

var map;

// tuiles (basemap)
var layerTerrain, layerWatercolor, layerToner, layerPositron, layerPositronNoLabel;
var currentTileLayerForLevel0; // basemap niveau 0
var currentTileLayerOverLevel0; // basemap courante niveau > 0

var baseMaps;
var overlayMaps;

// polygones (pays et régions)
var layerCountriesEurope;
var layerRegionsEurope;
var layerRegionsChine;
var layerRegionsFrance;
var listLayerPolygonIndexed = []; // layers polygones (tous : pays, régions...) indexés par leur code (A3 ou NUTS)

// pays
var listMarkerLevel0 = []; // marqueurs pays
var layerLevel0; // layer level 0

// labels
var listMarkerLabel = []; // marqueurs label
var listMarkerLabelIndexed = []; // idem indexés par leur id
var listListMarkerLabelLevel = []; // listes marqueurs label par niveau
var listLayerLabelsLevel = []; // layers label par niveau
var listListImageMapLabel = []; // listes des images de cartes par label

// PIs
var listMarkerPIIndexed = []; // marqueurs PI indexés par leur id
var listListMarkerPILabel = []; // listes marqueurs PI par label
var listLayerMarkersPILabel = []; // listes layers PI par label


var listActiveLayers = []; // liste des calques actifs

// icones leaflet
var listIconLabel = [];
var listIconBigLabel = [];
var listIconPICategory = [];
var listIconBigPICategory = [];

var JSONPICategories = <?= getJSONArrayFromProcedure("getListPICAtegories"); ?>;

var villesTerroirBourgogneJSON;

var JSONCountries = <?= getJSONArrayFromProcedure("getListCountries", null); ?>;
var JSONRegionsEU = <?= getJSONArrayFromProcedure("getListRegions", "EU", null); ?>;
var JSONRegionsCN = <?= getJSONArrayFromProcedure("getListRegions", "CN", null); ?>;
// var JSONMarkersLabelEU = <?= getJSONArrayFromProcedure("getListLabels", "EU", null, null); ?>;
// var JSONMarkersLabelCN = <?= getJSONArrayFromProcedure("getListLabels", "CN", null, null); ?>;
var JSONMarkersLabel = <?= getJSONArrayFromProcedure("getListLabels", null, null, null); ?>;

var coLang1 = "<?= $coLang1 ?>";
var coLang2 = "<?= $coLang2 ?>";
var restBaseUrl = "<?= $restBaseUrl ?>";

var logOn = false;

var lastRegionMouseOvered, lastRegionClicked;

var commandLegendRegion = [];
var commandLegendLabel = [];
var commandLegendCountries;

var currentCommand;

var listLevelsAterroir = [];
var listListLayerLevel = [];

var aTerroirLevel;
var computeAterroirLevel = true;

var zoomLevel;

var windows = false;

// $(document).on({
//   ajaxStart: function() {
//     alert("loading");
//   },
//   ajaxStop: function() {
//     alert("end loading");
//   }
// });

function initialize() {
  /* 
  pour pouvoir positionner les commandes à de nouveaux endroits. Ex :
  commandLegendRegion[pcodeRegion] = L.control({
    position: 'middleleft'
  });
  */

  L.Map.include({
    _initControlPos: function () {
      var corners = this._controlCorners = {},
        l = 'leaflet-',
        container = this._controlContainer =
          L.DomUtil.create('div', l + 'control-container', this._container);

      function createCorner(vSide, hSide) {
        var className = l + vSide + ' ' + l + hSide;

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

  // création de la carte
  map = L.map('map', {
    zoomSnap: 0.5,
    center: [48.833, 2.333],
    zoom: 4.5,
    minZoom: 4.5,
    zoomControl: false
  });

  map.on('zoomend', function () {
    setLayersByLevel();
  })

  log(map.getZoom());

  map.on('baselayerchange', function (e) {
    // log(e.layer);
    currentTileLayerOverLevel0 = e.layer;
    if (e.layer != layerPositron)
      currentTileLayerForLevel0 = e.layer;
    else
      currentTileLayerForLevel0 = layerPositronNoLabel;
  });

  map.on('click', function () {
    log("do");
  })

  createTilesLayers(); // création des calques "tuiles"

  createPolygonsLayers(); // polygones pays et régions

  createMarkersLevel0();

  createCommandLegendCountries(); // fenêtre des pays

  createMarkersLabel();

  createDataLayers(); // création des calques à partir des tableaux de marqueurs

  // correspondances niveaux aterroir et openstreetmap
  listLevelsAterroir[0] = {
    end: 5
  }; // europe
  listLevelsAterroir[1] = {
    start: 5,
    end: 7
  }; // pays
  listLevelsAterroir[2] = {
    start: 7
    // end: 8
  }; // region
  // listLevelsAterroir[3] = {
  //   start: 8
  // };

  currentTileLayerForLevel0 = layerPositronNoLabel;
  currentTileLayerOverLevel0 = layerPositron;

  listListLayerLevel[0] = [currentTileLayerForLevel0, layerCountriesEurope, layerLevel0];
  listListLayerLevel[1] = [currentTileLayerOverLevel0, layerRegionsEurope, layerRegionsFrance, listLayerLabelsLevel[1]];
  listListLayerLevel[2] = [currentTileLayerOverLevel0, layerRegionsEurope, layerRegionsFrance, listLayerLabelsLevel[1], listLayerLabelsLevel[2]];
  listListLayerLevel[3] = [currentTileLayerOverLevel0, layerRegionsEurope, layerRegionsFrance, listLayerLabelsLevel[1], listLayerLabelsLevel[2]];

  // setCommand(commandLegendCountries);

  // setLayers(listListLayerLevel[0]); // setLayersByLevel fonctionne après le zoomend

  // computeAterroirLevel = true;
  setLayersByLevel();

  // console.log("end loading");
  $(".loading").hide();

}

function getMarkerLabelPopupContent(pmarker) {

  var desc = "";
  desc += "<p>" + pmarker.label["name_" + coLang1] + "</p>";
  // if (pmarker.label["name_town_label"])
  //   desc += pmarker.label["name_town_label"] + "</br>";

  // desc+="<a href='assets/pdf/bourgogne.pdf' target='_blank'><img src='assets/pdf/bourgogne-pdf-screenshot.png' /></a>";
  var pdfFile = getFileNameFromJSONMetaData(pmarker.label["pdf"]);
  if (pdfFile)
    desc += "<a href='assets/pdf/" + pdfFile + "' target='_blank'><img class='pdf-img' src='assets/pdf/" + getFileNameFromJSONMetaData(pmarker.label["pdf_icon"]) + "' /></a>";

  desc += "<p>" + pmarker.label["name_" + coLang2] + "</p>";

  return desc;
}

function getMarkerPIPopupContent(pPI) {

  var desc = "";
  desc += "<h1>" + pPI["name_" + coLang1] + "</h1>";

  // if (pPI["img_pi_filename"]!=0) {
  // desc += "<img src='assets/img/PI/" + getFileNameFromJSONMetaData(pPI["img_pi_filename"]) + "'>";
  var imgJSON = loadJSON(restBaseUrl + "media/" + pPI["img_pi_filename"] + "?_fields=guid");
  // desc += "<img src='assets/img/PI/" + getFileNameFromJSONMetaData(pPI["img_pi_filename"]) + "'>";
  if (imgJSON) {
    desc += "<img src='" + imgJSON["guid"]["rendered"] + "'>";
    desc += "<a href='" + pPI["link"] + "' target='_blank'>" + pPI["name_" + coLang1] + "</a>";
  }
  // }

  return desc;
}

var listPIType = {
  // "AOP": ["AOP", "icon-AOP.png"],
  // "IGP": ["AOP", "icon-IGP.png"],
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

  // var sizeBig = Math.trunc(psize * 1.5 / 2);

  listIconLabel["AOP"] = L.icon({
    iconUrl: 'medias/img/icones-aterroir/logo-AOP.png',
    iconSize: [psize, psize]
  });

  listIconBigLabel["AOP"] = L.icon({
    iconUrl: 'medias/img/icones-aterroir/logo-AOP.png',
    iconSize: [psize * 1.5, psize * 1.5]
  });

  listIconLabel["IGP"] = L.icon({
    iconUrl: 'medias/img/icones-aterroir/logo-IGP.png',
    iconSize: [psize, psize]
  });

  listIconBigLabel["IGP"] = L.icon({
    iconUrl: 'medias/img/icones-aterroir/logo-IGP.png',
    iconSize: [psize * 1.5, psize * 1.5]
  });

  listIconLabel["AOPIGP"] = L.icon({
    iconUrl: 'medias/img/icones-aterroir/logo-AOPIGP.png',
    iconSize: [psize, psize]
  });

  listIconBigLabel["AOPIGP"] = L.icon({
    iconUrl: 'medias/img/icones-aterroir/logo-AOPIGP.png',
    iconSize: [psize * 1.5, psize * 1.5]
  });

  for (var picategory of JSONPICategories) {
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

  var parsed; // depuis JSON généré par php runner

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

  var iconMarker = null;
  var marker = null;
  var listTemp;

  var style;
  var dy;

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

    var toolTipContent =
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

    var IGTooltip = L.tooltip({
      className: "aterroir-tooltip",
      permanent: true,
      direction: label["direction_heel"],
      interactive: true
    });

    IGTooltip.setContent(toolTipContent);

    // ! coordonnées inversées dans geojson umap
    var lat = label["lat"];
    var lon = label["lon"];

    log(lat);
    log(lon);

    var m = L.marker(
      [lat, lon], {
      icon: iconMarker,
      interactive: false
    }
    ).bindTooltip(
      IGTooltip
    ).on("click", function (e) {
      // this.unbindPopup();
      if (aTerroirLevel == 3) { // on est au niveau 3, le niveau max. On fait apparaître la popup info
        if (!this._popup)
          this.bindPopup(getMarkerLabelPopupContent(this), {
            className: "label"
          }).openPopup();
        // this.openPopup();
      } else if (lastRegionClicked == this.layerRegion) { // la région est déjà sélectionnée : on était au niveau 2 et on passe au niveau 3
        // if (!hasPI(this.label["id_label"])) return;
        var tempCommand = getCommandLegendLabel(this.label["id_label"]);
        if (tempCommand == null) return;
        setCommand(tempCommand); // fenêtre F3
        map.setView(this.getLatLng(), 10); // TODO : centrer sur image terroir
        setAterroirLevel(3);
      } else { // on arrive sur la région (on était à un niveau inférieur ou dans une autre région)
        if (this.layerRegion) { // on vérifie qu'un polygone région est associé au marqueur
          lastRegionClicked = this.layerRegion;
          setAterroirLevel(2);
          map.fitBounds(this.layerRegion.getBounds()); // on centre sur la région
          setCommand(getCommandLegendRegion(this.label["code_region"])); // fenêtre F2
        }
      }

    }).on("mouseover", function (e) {
      if (aTerroirLevel > 1)
        $("#IG-" + this.label["id_label"] + " .talon").css("background", "#ffcd00");
      if (this.layerRegion)
        regionFocusOn(this.layerRegion);
      if (aTerroirLevel == 3)
        markerLabelFocusOn(this);
    }).on("mouseout", function (e) {
      if (this.layerRegion)
        regionFocusOut(this.layerRegion);
      $("#IG-" + this.label["id_label"] + " .talon").css("background", talonBGColorByLevel[aTerroirLevel]);
      if (lastRegionClicked)
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

  var bAOP = false;
  var bIGP = false;

  for (var label of JSONMarkersLabel) {
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

  var bAOP = false;
  var bIGP = false;

  for (var label of JSONMarkersLabel) {
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

  var iconMarker;
  var country;
  var region;
  var classe;

  for (country of JSONCountries) {

    if (country["code_country"] != 'CHN') {

      var typeIconCountry = getTypeIconCountry(country["code_country"]);

      iconMarker = listIconLabel[typeIconCountry];

      classe = "talon-" + country["direction_heel"];

      var toolTipContent =
        "<div class='talon-pays " + classe + "'>" +
        "<p>" +
        country["name_" + coLang1] +
        "</p>" +
        "<p>" +
        country["name_" + coLang2].toUpperCase() +
        "</p>" +
        "</div>";

      var m = L.marker(
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

    var typeIconRegion = getTypeIconRegion(region["code_region"]);

    iconMarker = listIconLabel[typeIconRegion];

    classe = "talon-" + region["direction_heel"];

    var toolTipContent =
      "<div class='talon-pays " + classe + "'>" +
      "<p>" +
      region["name_" + coLang1].toUpperCase() +
      "</p>" +
      "<p>" +
      region["name_" + coLang2].toUpperCase() +
      "</p>" +
      "</div>";

    var m = L.marker(
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

  var imageBounds = [
    [plat1, plon1],
    [plat2, plon2]
  ];

  var imageMap = L.imageOverlay("assets/img/maps/" + pimageUrl, imageBounds, {
    interactive: true,
    opacity: 0.5
  });

  return imageMap;
}


function createCommandLegendCountries() {
  commandLegendCountries = L.control({
    position: 'middleleft'
  });

  commandLegendCountries.onAdd = function (map) {

    var div = L.DomUtil.create('div', 'command');

    /* IMP! Ne pas propager l'événement de click à la carte sinon les pop-ups de marqueurs se ferment immédiatement */
    L.DomEvent.addListener(div, 'click', L.DomEvent.stopPropagation).addListener(div, 'click', L.DomEvent.preventDefault);
    L.DomEvent.addListener(div, 'mousewheel', L.DomEvent.stopPropagation); // .addListener(div, 'mousewheel', L.DomEvent.preventDefault);

    var html = `
      <div id="F1" class="aterroir-window">
        <section class="left">
          <div class="menu-item" onmouseenter="openRight('F1-countries');">
            <img src="medias/img/icones-aterroir/Bouton F Pays.png">
          </div>
          <div class="menu-item" onmouseenter="openRight('F1-regions');">
            <img src="medias/img/icones-aterroir/Bouton F Régions.png">
          </div>
          <div class="menu-item" onmouseenter="openRight('F1-infos');">
            <img src="medias/img/icones-aterroir/Bouton F InfoSite.png">
          </div>
          <div class="menu-item" onmouseenter="openRight('F1-formulaire');">
            <img src="medias/img/icones-aterroir/Bouton F Courrier.png">
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
          <div class="footer"><img src="medias/img/icones-aterroir/aterroir-logo.png"></div>
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
                <div class="talon-item" onclick='legendRegionClick("` + region["code_region"] + `")' onmouseover='legendRegionOver("` + region["code_region"] + `")' onmouseout='legendRegionOut("` + region["code_region"] + `")'>
                  <p>` + region["name_" + coLang1] + `</p>
                  <p>` + region["name_" + coLang2] + `</p>
                </div>
              </li>`;
    html += `
            </ul>
          </div>
          <div class="footer"><img src="medias/img/icones-aterroir/aterroir-logo.png"></div>
        </section>
        <section id="F1-infos" class="right" onmouseleave="closeRight();" style="display: none;">
          <div class="header">
            <h1>Infos</h1>
          </div>
          <div class="content">
          </div>
          <div class="footer"><img src="medias/img/icones-aterroir/aterroir-logo.png"></div>
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
          <div class="footer"><img src="medias/img/icones-aterroir/aterroir-logo.png"></div>
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

  commandLegendRegion[pcodeRegion].onAdd = function (map) {

    var div = L.DomUtil.create('div', 'command');
    L.DomEvent.addListener(div, 'click', L.DomEvent.stopPropagation).addListener(div, 'click', L.DomEvent.preventDefault); // TODO : autres événements (scroll...)
    L.DomEvent.addListener(div, 'mousewheel', L.DomEvent.stopPropagation); // .addListener(div, 'mousewheel', L.DomEvent.preventDefault);

    var html = getF2Html(pcodeRegion);

    div.innerHTML = html;

    return div;
  };

  return commandLegendRegion[pcodeRegion];

}

function getJSONMarkersPILabel(pidLabel) {

  var jsonMarkers = [];
  /*
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
  */
  return jsonMarkers;
}

function getJSONMapsLabel(pidLabel) {

  var jsonMaps;

  $.ajax({
    url: "getJSONMapsLabel.php",
    type: "POST",
    async: false, // Mode synchrone indispensable
    data: ({
      id: pidLabel
    }),
    success: function (data) {
      jsonMaps = JSON.parse(data); // !!! return ici ne marche pas malgré synchrone (!?)
    }
  });

  return jsonMaps;
}

var f2Html = [];

function getF2Html(pcodeRegion) {

  if (pcodeRegion in f2Html)
    return f2Html[pcodeRegion];

  var html;

  $.ajax({
    url: "get-f2-html.php",
    type: "POST",
    async: false, // Mode synchrone indispensable
    data: ({
      code_region: pcodeRegion
    }),
    success: function (data) {
      html = data; // !!! return ici ne marche pas malgré synchrone (!?)
    }
  });

  f2Html[pcodeRegion] = html;

  return html;
}

var f3Html = [];

function getF3Html(pidLabel) {

  if (pidLabel in f3Html)
    return f3Html[pidLabel];

  var html;

  $.ajax({
    url: "get-f3-html-rest.php",
    type: "POST",
    async: false, // Mode synchrone indispensable
    data: ({
      id_label: pidLabel
    }),
    success: function (data) {
      html = data; // !!! return ici ne marche pas malgré synchrone (!?)
    }
  });

  f3Html[pidLabel] = html;

  return html;
}

function createLayerMarkersPILabel(pidLabel) {

  var JSONMarkersPILabel = getJSONMarkersPILabel(pidLabel);

  listListMarkerPILabel[pidLabel] = [];

  for (var PI of JSONMarkersPILabel) {

    var m = L.marker(
      [PI["lat"], PI["lon"]], {
      icon: listIconPICategory[PI["id_picategory"]]
      // interactive: true
    }).bindPopup(
      getMarkerPIPopupContent(PI)
    ).on("mouseover", function (e) {
      markerPIFocusOn(this);
    }).on("mouseout", function (e) {
      markerPIFocusOut(this);
    });

    m.PI = PI;
    listListMarkerPILabel[pidLabel].push(m);
    listMarkerPIIndexed[PI["id_pi"]] = m;
  }

  listLayerMarkersPILabel[pidLabel] = L.layerGroup(listListMarkerPILabel[pidLabel]);
}

function createMapsLabel(pidLabel) {

  listListImageMapLabel[pidLabel] = [];

  var JSONMapsLabel = getJSONMapsLabel(pidLabel);

  for (var map of JSONMapsLabel) {
    var imgMap = createImageMap(getFileNameFromJSONMetaData(map["img_map_filename"]), map["lat_lefttop"], map["lon_lefttop"], map["lat_rightbottom"], map["lon_rightbottom"]);
    listListImageMapLabel[pidLabel].push(imgMap);
  }
}

var currentLabel;

function getCommandLegendLabel(pidLabel) {

  $(".loading").show();

  if (currentLabel && map.hasLayer(listLayerMarkersPILabel[currentLabel]))
    map.removeLayer(listLayerMarkersPILabel[currentLabel]);

  if (listListImageMapLabel[currentLabel] != undefined)
    for (var imageMap of listListImageMapLabel[currentLabel]) {
      imageMap.removeFrom(map);
    }

  if (pidLabel in commandLegendLabel) {

    if (commandLegendLabel[pidLabel] != null) {

      currentLabel = pidLabel;
      map.addLayer(listLayerMarkersPILabel[pidLabel]);

      for (var imageMap of listListImageMapLabel[pidLabel]) {
        imageMap.addTo(map);
      }

    }

    $(".loading").hide();
    return commandLegendLabel[pidLabel];
  }

  createLayerMarkersPILabel(pidLabel);

  if (listListMarkerPILabel[pidLabel].length == 0) {
    commandLegendLabel[pidLabel] = null;
    $(".loading").hide();
    // return null;
  }

  currentLabel = pidLabel;
  map.addLayer(listLayerMarkersPILabel[pidLabel]);

  createMapsLabel(pidLabel);

  for (var imageMap of listListImageMapLabel[pidLabel]) {
    imageMap.addTo(map);
  }

  commandLegendLabel[pidLabel] = L.control({
    position: 'middleleft'
  });

  var m = getMarkerLabelById(pidLabel);

  commandLegendLabel[pidLabel].onAdd = function (map) {

    var div = L.DomUtil.create('div', 'command');
    L.DomEvent.addListener(div, 'click', L.DomEvent.stopPropagation).addListener(div, 'click', L.DomEvent.preventDefault);
    L.DomEvent.addListener(div, 'mousewheel', L.DomEvent.stopPropagation); // .addListener(div, 'mousewheel', L.DomEvent.preventDefault);

    var html = getF3Html(pidLabel);

    div.innerHTML = html;

    return div;
  };

  $(".loading").hide();
  return commandLegendLabel[pidLabel];

}

function getListPIByType(pJSONMarkersPI, pidLabel, ptypePI) {
  var listPI = [];
  for (var PI of pJSONMarkersPI)
    if (PI["id_label"] == pidLabel && PI["code_category"] == ptypePI)
      listPI.push(PI);

  return listPI;
}

function togglePI(pid) {
  var marker = getMarkerLabelById(pid)
  if (!map.hasLayer(marker))
    map.addLayer(marker);
  else
    map.removeLayer(marker);

}

function getLabelVal(pidLabel, pnameprop) {
  for (var label of JSONMarkersLabelEU)
    if (label["id_label"] == pidLabel)
      return label[pnameprop];

  return null;
}

function togglePIType(pidLabel, ptype) {
  var marker;
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

var listMarkerPILabelLevel = [];

function createDataLayers() { // création des calques à partir des tableaux de marqueurs

  for (var i in listListMarkerLabelLevel) {
    listLayerLabelsLevel[i] = L.layerGroup(listListMarkerLabelLevel[i]);
  }

  // layerLevel3 = L.layerGroup(listMarkerPIAll);

}

function createPolygonsLayers() {

  var EUCountryJSON = loadJSON("assets/geojson/pays-europe-nodomtom.json");

  layerCountriesEurope = L.geoJSON(EUCountryJSON, {
    onEachFeature: function (feature, layer) {
      listLayerPolygonIndexed[feature.properties["adm0_a3"]] = layer;
      layer.on('click', function (e) {
        map.fitBounds(layer.getBounds());
      });
      layer.on('mouseover', function (e) {
        layer.setStyle({
          color: "yellow",
          opacity: 1,
          weight: 0
        });
      });
      layer.on('mouseout', function (e) {
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

  var nutJSON = loadJSON("assets/geojson/regions-europe.json");

  layerRegionsEurope = L.geoJSON(nutJSON, {
    onEachFeature: function (feature, layer) {
      listLayerPolygonIndexed[feature.properties["name"]] = layer;
      layer.on('click', function (e) {
        legendRegionClick(feature.properties["name"]);
      });
      layer.on('mouseover', function (e) {
        regionFocusOn(layer);
      });
      layer.on('mouseout', function (e) {
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

  var nutFranceJSON = loadJSON("assets/geojson/regions-france-nodomtom.json");

  layerRegionsFrance = L.geoJSON(nutFranceJSON, {
    onEachFeature: function (feature, layer) {
      listLayerPolygonIndexed[feature.properties["nuts2"]] = layer;
      layer.on('click', function (e) {
        legendRegionClick(feature.properties["nuts2"]);
      });
      layer.on('mouseover', function (e) {
        regionFocusOn(layer);
      });
      layer.on('mouseout', function (e) {
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

  var nutJSONCN = loadJSON("assets/geojson/gadm36_CHN_1.json");

  layerRegionsChine = L.geoJSON(nutJSONCN, {
    onEachFeature: function (feature, layer) {
      listLayerPolygonIndexed[feature.properties["HASC_1"]] = layer;
      layer.on('click', function (e) {
        lastRegionClicked = layer;
        setAterroirLevel(2);
        map.fitBounds(layer.getBounds());
        setCommand(getCommandLegendRegion(feature.properties["HASC_1"]));
      });
      layer.on('mouseover', function (e) {
        regionFocusOn(layer);
      });
      layer.on('mouseout', function (e) {
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

  // layerTerrain = new L.StamenTileLayer("terrain");
  // layerWatercolor = new L.StamenTileLayer("watercolor");
  // layerToner = new L.StamenTileLayer("toner");
  layerPositron = new L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/light_all/{z}/{x}/{y}.png");
  layerPositronNoLabel = new L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/light_nolabels/{z}/{x}/{y}.png");

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
  var json = null;

  $.ajax({
    'async': false,
    'global': false,
    'url': pfile,
    'dataType': "json",
    'success': function (data) {
      json = data;
    }
  });

  return json;
}

Array.prototype.remove = function () {
  var what, a = arguments,
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

  var listLayersToRemove = [];

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
    if (layer && !listActiveLayers.includes(layer)) { // TODO : pourquoi layer undefined parfois ?
      listActiveLayers.push(layer);
      map.addLayer(layer);
    }
  }
}

// var talonBGColorByLevel = ["white", "white", "#ffcd00", "#ffcd00"];
var talonBGColorByLevel = ["white", "white", "white", "#ffcd00"];

L.Layer.prototype.setInteractive = function (interactive) {
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

  if (aTerroirLevel >= 3)
    for (var m of listMarkerLabel) {
      m.options.interactive = true;
      m._tooltip.options.interactive = false;
    }
  else
    for (var m of listMarkerLabel) {
      m.options.interactive = false;
      m._tooltip.options.interactive = true;
    }

  if (aTerroirLevel < 2) {
    lastRegionClicked = null;
    setCommand(commandLegendCountries)
  }

  setLayers(listListLayerLevel[aTerroirLevel]);
  $(".talon").css('background', talonBGColorByLevel[aTerroirLevel]);

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

var lastMarkerOnMap;

function markerLabelFocusOn(pmarker) {
  pmarker.setIcon(listIconBigLabel[pmarker.label["code_label"]]);
  $("#IG-" + pmarker.id + " .talon").css("background", "#fffb00");
}

function markerLabelFocusOut(pmarker) {
  pmarker.setIcon(listIconLabel[pmarker.label["code_label"]]);
  $("#IG-" + pmarker.id + " .talon").css("background", talonBGColorByLevel[aTerroirLevel]);
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
  if (aTerroirLevel < 3 && playerRegion != lastRegionClicked) {
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
  var m = getMarkerLabelById(pid);
  lastMarkerOnMap = map.hasLayer(m);
  if (!lastMarkerOnMap)
    map.addLayer(m);
}

function legendMarkerLabelMouseOut(pid) {
  var m = getMarkerLabelById(pid);
  if (!lastMarkerOnMap)
    map.removeLayer(m);
}

function legendMarkerLabelClick(pid) {
  var marker = getMarkerLabelById(pid);
  marker.fire("click");
  // map.setView(marker.getLatLng(), 10);
}

function legendMarkerPIClick(pid) {
  var marker = getMarkerPIById(pid);
  marker.fire("click");
  // log("marker.openPopup();");
}

function legendCountryClick(pcode) {
  // var countryLayer = getCountryLayerByCode(pcode);
  var countryLayer = getLayerPolygonByCode(pcode);
  countryLayer.fire("click");
}

function legendCountryOver(pcode) {
  // var countryLayer = getCountryLayerByCode(pcode);
  var countryLayer = getLayerPolygonByCode(pcode);
  countryLayer.fire("mouseover");
}

function legendCountryOut(pcode) {
  // var countryLayer = getCountryLayerByCode(pcode);
  var countryLayer = getLayerPolygonByCode(pcode);
  countryLayer.fire("mouseout");
}

function legendRegionClick(pcode) {
  // var regionLayer = getRegionLayerByCode(pcode);
  var regionLayer = getLayerPolygonByCode(pcode);
  lastRegionClicked = regionLayer;
  setAterroirLevel(2);
  map.fitBounds(regionLayer.getBounds());
  regionFocusOut();
  setCommand(getCommandLegendRegion(pcode));
}

function legendRegionOver(pcode) {
  var regionLayer = getLayerPolygonByCode(pcode);
  regionLayer.fire("mouseover");
}

function legendRegionOut(pcode) {
  var regionLayer = getLayerPolygonByCode(pcode);
  regionLayer.fire("mouseout");
}


function getMarkerLabelById(pid) {
  return listMarkerLabelIndexed[pid];
}

function getMarkerPIById(pid) {
  return listMarkerPIIndexed[pid];
}

function getAterroirLevel(pzoomLevel) {
  for (var nuLevel in listLevelsAterroir) {
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

var lastItem;

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

function setCommand(pcommand) {

  if (!windows) return;

  if (pcommand == currentCommand)
    return;

  if (currentCommand != null)
    map.removeControl(currentCommand);

  pcommand.addTo(map);

  currentCommand = pcommand;
}