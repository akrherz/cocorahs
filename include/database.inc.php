<?php
//$iem40 = '10.10.10.40';
//$iem20 = '10.10.10.20';
//$iem30 = '10.10.10.30';
$iem40 = 'mesonet.agron.iastate.edu';
$iem20 = 'mesonet-db1.agron.iastate.edu';
$iem30 = 'kcci.mesonet.agron.iastate.edu';
global $_DATABASES;
$_DATABASES = Array(
 'access' => "dbname=iem host=$iem20 user=nobody",
 'hads' => "dbname=hads host=$iem20 user=nobody",
 'asos' => "dbname=asos host=$iem20 user=nobody",
 'coop' => "dbname=coop host=$iem20 user=nobody",
 'awos' => "dbname=awos host=$iem20 user=nobody",
 'rwis' => "dbname=rwis host=$iem20 user=nobody",
 'rwis2' => "dbname=rwis2 host=$iem20 user=nobody",
 'wepp' => "dbname=wepp host=$iem20 user=nobody",
 'snet' => "dbname=snet host=$iem20 user=nobody",
 'mesosite' => "dbname=mesosite host=$iem20 user=nobody",
 'isuag' => "dbname=isuag host=$iem20 user=nobody",
 'other' => "dbname=other host=$iem20 user=nobody",
 'postgis' => "dbname=postgis host=$iem20 user=nobody",
 'portfolio' => "dbname=portfolio host=meteor.geol.iastate.edu user=mesonet",
 'scan' => "dbname=scan host=$iem20 user=nobody",
);

function iemdb($DBKEY)
{
  global $_DATABASES;
  return pg_connect( $_DATABASES[$DBKEY] );
}
?>
