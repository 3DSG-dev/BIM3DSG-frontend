<?php
include("auth.php");
if(!isset($_SESSION['validUser'])) {
	header("Location: http://" . $_SERVER["HTTP_HOST"]);	
}	

set_time_limit(0); 
$dbconn = pg_connect($_SESSION['dbConnectionString']) or die ('Error connecting to db');
$user =$_SESSION['validUserName'];
$CodiceIntervento = isset($_GET['codiceIntervento'])?$_GET['codiceIntervento']:$_POST['codiceIntervento'];

$SQL="
SELECT * FROM \"Oggetti\" Join \"Relazioni\" 
ON \"Oggetti\".\"Codice\" =\"Relazioni\".\"Padre\"
WHERE \"Relazioni\".\"Intervento\" = $CodiceIntervento 
";

$result1 = pg_query($dbconn, $SQL) or die ("Error: $SQL");
$myArray =array(); 
while($tmp = pg_fetch_array($result1, NULL, PGSQL_ASSOC))
{
	$myArray[] = $tmp;
}
echo json_encode($myArray);
pg_close($dbconn);
?>
