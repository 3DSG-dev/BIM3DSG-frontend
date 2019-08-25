<?php

include("auth.php");
if(!isset($_SESSION['validUser'])) {
	header("Location: http://" . $_SERVER["HTTP_HOST"]);	
}	
	
set_time_limit(0); 
$dbconn = pg_connect($_SESSION['dbConnectionString']) or die ('Error connecting to db');
	$codiceOggetto = isset($_GET['codiceOggetto'])?$_GET['codiceOggetto']:$_POST['codiceOggetto'];
	$URL = isset($_GET['URL'])?$_GET['URL']:$_POST['URL'];
$SQL= 'SELECT * FROM "PhotogrammetryPhotoDataOggetti" JOIN "PhotogrammetryProjectData" ON "PhotogrammetryPhotoDataOggetti"."CodicePhotogrammetryProjectData" = "PhotogrammetryProjectData"."Codice" WHERE "CodiceOggetto" = '. $codiceOggetto . ' AND "URL" = '. "'$URL'";
$result1 = pg_query($dbconn, $SQL) or die ("Error: $SQL");

echo json_encode(pg_fetch_array($result1, NULL, PGSQL_ASSOC));
pg_close($dbconn);
?>
