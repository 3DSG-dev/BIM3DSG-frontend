<?php
include("auth.php");
if(!isset($_SESSION['validUser'])) {
	header("Location: http://" . $_SERVER["HTTP_HOST"]);	
}	
	
set_time_limit(0); 
$dbconn = pg_connect($_SESSION['dbConnectionString']) or die ('Error connecting to db');
$codiceOggetto = isset($_GET['codiceOggetto'])?$_GET['codiceOggetto']:$_POST['codiceOggetto'];
$subVersion = isset($_GET['subVersion'])?$_GET['subVersion']:$_POST['subVersion'];
$codiceCampo = isset($_GET['codiceCampo'])?$_GET['codiceCampo']:$_POST['codiceCampo'];
$valore = isset($_GET['valore'])?$_GET['valore']:$_POST['valore'];
$valore = pg_escape_string ($valore);

if (($codiceOggetto!="null") && ($codiceOggetto!="") && ($subVersion!="null") && ($subVersion!="") && ($codiceCampo!="null") && ($codiceCampo!="")) {
	if (($valore == "null") || ($valore == "")) {
		$SQL = "SELECT setoggettisubversioninfoschedavalue($codiceOggetto, $subVersion, $codiceCampo, '')";
	} else {
		$SQL = "SELECT setoggettisubversioninfoschedavalue($codiceOggetto, $subVersion, $codiceCampo, '$valore')";
	}
}
else
{
	die("Invalid parameters");
}

$result1 = pg_query($dbconn, $SQL) or die ("Error: $SQL");
//$result1 = pg_query($dbconn, utf8_encode($SQL)) or die ("Error: $SQL");

//echo $SQL;
echo json_encode("ok");
pg_close($dbconn);
?>

