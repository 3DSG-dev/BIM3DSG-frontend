<?php

include("../../php/auth.php");
if(!isset($_SESSION['validUser'])) {
	header("Location: http://" . $_SERVER["HTTP_HOST"]);
}

set_time_limit(0); 
//var_dump($_POST);
//die();

if(isset($_FILES["FileInputFile"]) && $_FILES["FileInputFile"]["error"]== UPLOAD_ERR_OK)
{
	############ Edit settings ##############
	//$UploadDirectory	= '/var/www/BIMV14/fwlib/uploader/uploads/'; //specify upload directory ends with / (slash)
	$UploadDirectory	= $_SERVER["DOCUMENT_ROOT"] . 'fwlib/uploader/uploads/';
	##########################################
	
	/*
	Note : You will run into errors or blank page if "memory_limit" or "upload_max_filesize" is set to low in "php.ini". 
	Open "php.ini" file, and search for "memory_limit" or "upload_max_filesize" limit 
	and set them adequately, also check "post_max_size".
	*/
	
	//check if this is an ajax request
	if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
		die();
	}
	
	
	//Is file size is less than allowed size.
	if ($_FILES["FileInput"]["size"] > 15728640) {
		die("File size is too big!");
	}
	
	//allowed file type Server side check
/*	switch(strtolower($_FILES['FileInput']['type']))
		{
			//allowed file types
            case 'image/png': 
			case 'image/gif': 
			case 'image/jpeg': 
			case 'image/pjpeg':
			//case 'text/plain':
			//case 'text/html': //html file
			//case 'application/x-zip-compressed':
			//case 'application/pdf':
			//case 'application/msword':
			//case 'application/vnd.ms-excel':
			//case 'video/mp4':
				break;
			default:
				die('Unsupported File!'); //output error
	}*/
	
	$File_Name          = strtolower($_FILES['FileInputFile']['name']);
	$File_Ext           = substr($File_Name, strrpos($File_Name, '.')); //get file extention
	$Random_Number      = rand(0, 9999999999); //Random number to be added to name.
	$NewFileName 		= $Random_Number.$File_Ext; //new file name

	if(move_uploaded_file($_FILES['FileInputFile']['tmp_name'], $UploadDirectory.$NewFileName ))
	   {
///////////////////*****

		$dbconn = pg_connect($_SESSION['dbConnectionString']) or die ('Error connecting to db');
		$codiceOggetto = isset($_GET['codiceFileOggetto'])?$_GET['codiceFileOggetto']:$_POST['codiceFileOggetto'];
		$codiceIntervento = isset($_GET['codiceFileIntervento'])?$_GET['codiceFileIntervento']:$_POST['codiceFileIntervento'];
		//$URL = isset($_GET['URL'])?$_GET['URL']:$_POST['URL'];
		$mittente = isset($_GET['mittenteFile'])?$_GET['mittenteFile']:$_POST['mittenteFile'];
		$dataIns = isset($_GET['dataInsFile'])?$_GET['dataInsFile']:$_POST['dataInsFile'];

		$URL = $File_Name;
		$fileName= $UploadDirectory.$NewFileName;

		$quality = 0;

		$file =  file_get_contents($fileName); 
		$fileEncodedByteA = pg_escape_bytea($file); 
		$fileThumb =  file_get_contents($fileNameThumb); 
		$fileThumbEncodedByteA = pg_escape_bytea($fileThumb); 

		$now= date("Y-m-d H:i:s");
		if ($mittente==11){
			$SQL= "INSERT INTO \"MaterialeOggetti\" VALUES ($codiceOggetto, '$URL', 'file', $quality, '$fileEncodedByteA', NULL, '$dataIns', 7, 4, 0, 'web', 'web','$now', '" . $_SESSION['validUserName'] . "')";
			$result1 = pg_query($dbconn, utf8_encode($SQL)) or die ("Error: $SQL");
		}
		if ($mittente==12){
			$SQL= "INSERT INTO \"MaterialeVersioni\" VALUES ($codiceOggetto, '$URL', 'file', $quality, '$fileEncodedByteA', NULL, '$dataIns', 7, 4, 0, 'web', 'web','$now', '" . $_SESSION['validUserName'] . "')";
			$result1 = pg_query($dbconn, utf8_encode($SQL)) or die ("Error: $SQL");
		}

		pg_close($dbconn);

		unlink($fileName);
		unlink($UploadDirectory.$NewFileName);

		die('Success! File Uploaded.');
	}else{
		die('error uploading File !');
	}
	
}
else
{
	die('Something wrong with upload! Is "upload_max_filesize" set correctly?');
}
