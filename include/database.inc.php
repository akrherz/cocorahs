<?php
global $_DATABASES;
$_DATABASES = Array(
 'access' => "dbname=iem host=iemdb-iem.local user=nobody",
 'mesosite' => "dbname=mesosite host=iemdb-mesosite.local user=nobody",
);

function iemdb($DBKEY)
{
  global $_DATABASES;
  return pg_connect( $_DATABASES[$DBKEY] );
}
?>
