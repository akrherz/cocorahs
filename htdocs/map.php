<?php
include("../config/settings.inc.php"); 

include("$rootpath/include/forms.php");
include("$rootpath/include/database.inc.php");
include("$rootpath/include/network.php");
dl("php_mapscript_461.so");

$ERROR = "";
$layers = isset($_GET["layers"]) ? $_GET["layers"] : Array();
$year = isset($_GET["year"]) ? $_GET["year"] : date("Y");
$month = isset($_GET["month"]) ? $_GET["month"] : date("m");
$day = isset($_GET["day"]) ? $_GET["day"] : date("d");
$ts = mktime(0,0,0,$month, $day, $year);
$showSiteLabel = isset($_GET["sitelabel"]);
$imgwidth = isset($_GET['width']) ? $_GET['width'] : 640;
$imgheight = isset($_GET['height']) ? $_GET['height'] : 480;

 /**
  * Something simple to enable click interface on a PHP mapcript
  * application
  */
function click2geo($oextents, $click_x, $click_y, $imgsz_x, $imgsz_y, $zoom) {                                                                              
  $arExtents = explode(",", $oextents);
  $ll_x = $arExtents[0];
  $ll_y = $arExtents[1];
  $ur_x = $arExtents[2];
  $ur_y = $arExtents[3];
//  print_r($arExtents);
                                                                              
  $dy = ($ur_y - $ll_y) / floatval($imgsz_y);
  $dx = ($ur_x - $ll_x) / floatval($imgsz_x);
                                                                              
  $centerX = ($click_x * $dx) + $ll_x ;
  $centerY = $ur_y - ($click_y * $dy) ;
                                                                              
  if (intval($zoom) < 0)
    $zoom = -1 / intval($zoom) ;
                                                                              
  $n_ll_x = $centerX - (($dx * $zoom) * ($imgsz_x / 2.00));
  $n_ur_x = $centerX + (($dx * $zoom) * ($imgsz_x / 2.00));
  $n_ll_y = $centerY - (($dy * $zoom) * ($imgsz_y / 2.00));
  $n_ur_y = $centerY + (($dy * $zoom) * ($imgsz_y / 2.00));
                                                                              
  return $n_ll_x .",". $n_ll_y .",". $n_ur_x .",". $n_ur_y ;
}

$zoom     = isset($_GET['zoom']) ? $_GET['zoom'] : 0;
$oextents = isset($_GET['extents']) ? $_GET['extents'] : "200000,4400000,710000,4900000";
if ( $zoom == 0 ){ // Full Extents
  unset($_GET["img_x"]);
  $oextents = "200000,4400000,710000,4900000";
}
if ( isset($_GET['img_x']) && strlen($_GET['img_x']) > 0 ){
	$extents = click2geo($oextents, $_GET['img_x'], $_GET['img_y'],
	$imgwidth, $imgheight, $zoom);
} else {
   $extents = $oextents;
}

$var = isset($_GET['var']) ? $_GET['var'] : 'pday';

// Get station table dictionary
$nt = new NetworkTable("IACOCORAHS");

// Get obs for date
$conn = iemdb("access");
$obs = Array();
$sql = "SELECT station, pday from summary_$year WHERE day = '". date("Y-m-d", $ts) ."' and network = 'IACOCORAHS' and pday >= 0";
$rs = pg_query($conn, $sql);
for( $i=0; $row = @pg_fetch_assoc($rs,$i); $i++)
{
   $id = $row["station"];
   if (! array_key_exists($id, $nt->table)) continue;
   $obs[ $id ] = $row;
   $obs[ $id ]["lat"] = $nt->table[$id]["latitude"];
   $obs[ $id ]["lon"] = $nt->table[$id]["longitude"];
   $obs[ $id ]["short"] = $nt->table[$id]["name"];
}

$titlets = date("d M Y", $ts);


if (strlen($var) == 0){
  $var = "tmpf";
}

$varDef = Array(
  "pday" => "24-hour rainfall",
);

$rnd = Array("tmpf" => 0,
  "dwpf" => 0,
  "sknt" => 0,
  "max_sknt" => 0,
  "feel" => 0,
  "pmonth" => 2,
  "pday" => 2);

$map = ms_newMapObj("../data/gis/base.map");
$map->setProjection("init=epsg:26915");
$map->set("height", 480);
$map->set("width", 640);

$arExtents = explode(",", $extents);
$map->setextent($arExtents[0], $arExtents[1], $arExtents[2], $arExtents[3]);

$counties = $map->getlayerbyname("counties");
$counties->set("status", MS_ON);

$snet = $map->getlayerbyname("snet");
$snet->set("status", MS_ON);
$sclass = $snet->getClass(0);

$cities = $map->getlayerbyname("cities");
$cities->set("status", 1);

$states = $map->getlayerbyname("states");
$states->set("status", 1);


  $interstates = $map->getlayerbyname("interstates");
  $interstates->set("status", 1);

  $roads = $map->getlayerbyname("roads");
  $roads->set("status", 1);

  $ilbl = $map->getlayerbyname("interstates_label");
  $ilbl->set("status", 1);

  $rlbl = $map->getlayerbyname("roads_label");
  $rlbl->set("status", 1);



$bar = $map->getlayerbyname("barlsd");
$bar->set("status", 1);
$subbar = $map->getlayerbyname("subtitlebar");
$subbar->set("status", 1);


$img = $map->prepareImage();
$cities->draw($img);

$map->drawLabelCache($img);

$roads->draw($img);
$interstates->draw($img);
$rlbl->draw($img);
$ilbl->draw($img);

$counties->draw($img);
$states->draw($img);

foreach($obs as $key => $value){

  $lat = $value["lat"];
  $lon = $value["lon"];

  if ($showSiteLabel)
  {
   $pt = ms_newPointObj();
   $pt->setXY($lon, $lat, 0);
   $pt->draw($map, $snet, $img, 1, $value["short"] );
   $pt->free();
  }

     $pt = ms_newPointObj();
     $pt->setXY($lon, $lat, 0);
     $pt->draw($map, $snet, $img, 0, round($value[$var], $rnd[$var]) );
     $pt->free();
}
//  $ts = strftime("%I %p");


//$map->drawLabelCache($img);
$bar->draw($img);



$layer = $map->getLayerByName("credits");
$point = ms_newpointobj();
$point->setXY(330, 48);
$point->draw($map, $layer, $img, "credits", $varDef[$var] ." reported on ". $titlets );

if (isset($_GET["download"]))
{
  header("Content-type: application/octet-stream");
  header("Content-Disposition: attachment; filename=schoolnet.png");
  $img->saveImage('');
} else 
{
  header("Content-type: image/png");
  $img->saveImage('');
}
?>
