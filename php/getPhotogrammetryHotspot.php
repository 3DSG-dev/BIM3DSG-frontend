<?php

include("auth.php");
if(!isset($_SESSION['validUser'])) {
	header("Location: http://" . $_SERVER["HTTP_HOST"]);	
}	
	
set_time_limit(0); 
$dbconn = pg_connect($_SESSION['dbConnectionString']) or die ('Error connecting to db');
$user =$_SESSION['validUserName'];

$SQL= 'SELECT "Modelli3D_LoD"."CodiceModello", xc, yc, zc, "Radius" FROM "Modelli3D_LoD" JOIN "Import" ON "Modelli3D_LoD"."CodiceModello" = "Import"."CodiceModello" WHERE "HotSpot" = true AND "User"=\'' . $user . "'";
$result1 = pg_query($dbconn, $SQL) or die ("Error: $SQL");
$myArray =array(); 
while($tmp = pg_fetch_array($result1, NULL, PGSQL_ASSOC))
{
	$myArray[] = $tmp; 
}
echo json_encode($myArray);
pg_close($dbconn);
?>
