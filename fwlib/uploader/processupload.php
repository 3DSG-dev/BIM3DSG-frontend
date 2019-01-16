<?php

include("../../php/auth.php");
if(!isset($_SESSION['validUser'])) {
	header("Location: http://" . $_SERVER["HTTP_HOST"]);
}

set_time_limit(0); 
//var_dump($_POST);
//die();

if(isset($_FILES["FileInput"]) && $_FILES["FileInput"]["error"]== UPLOAD_ERR_OK)
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
	switch(strtolower($_FILES['FileInput']['type']))
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
	}
	
	$File_Name          = strtolower($_FILES['FileInput']['name']);
	$File_Ext           = substr($File_Name, strrpos($File_Name, '.')); //get file extention
	$Random_Number      = rand(0, 9999999999); //Random number to be added to name.
	$NewFileName 		= $Random_Number.$File_Ext; //new file name

	if(move_uploaded_file($_FILES['FileInput']['tmp_name'], $UploadDirectory.$NewFileName ))
	   {
///////////////////*****
		$img = imagecreatefromjpeg( $UploadDirectory.$NewFileName );
		$width = imagesx( $img );
		$height = imagesy( $img );

		$thumbMaxSize = 1600;
		$new_width = 0;
		$new_height = 0;
		// calculate thumbnail size
		if ($width > $height) 
		{
			$new_width = $thumbMaxSize;
			$new_height = floor( $height * ( $thumbMaxSize / $width ) );
		}
		else 
		{
			$new_height = $thumbMaxSize;
			$new_width = floor( $width * ( $thumbMaxSize / $height ) );
		}
		// create a new temporary image
		$tmp_img = imagecreatetruecolor( $new_width, $new_height );

		// copy and resize old image into new image 
		imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

		// save thumbnail into a file
		imagejpeg( $tmp_img, $UploadDirectory."_H_".$NewFileName );		
///////////////////*****
		$thumbMaxSize = 192;
		$new_width = 0;
		$new_height = 0;
		// calculate thumbnail size
		if ($width > $height) 
		{
			$new_width = $thumbMaxSize;
			$new_height = floor( $height * ( $thumbMaxSize / $width ) );
		}
		else 
		{
			$new_height = $thumbMaxSize;
			$new_width = floor( $width * ( $thumbMaxSize / $height ) );
		}
		// create a new temporary image
		$tmp_img = imagecreatetruecolor( $new_width, $new_height );

		// copy and resize old image into new image 
		imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

		// save thumbnail into a file
		imagejpeg( $tmp_img, $UploadDirectory."_L_".$NewFileName );		
		///////////////////*****

		$exif = exif_read_data($UploadDirectory.$NewFileName, 0, true);

		$dbconn = pg_connect($_SESSION['dbConnectionString']) or die ('Error connecting to db');
		$codiceOggetto = isset($_GET['codiceOggetto'])?$_GET['codiceOggetto']:$_POST['codiceOggetto'];
		$codiceIntervento = isset($_GET['codiceIntervento'])?$_GET['codiceIntervento']:$_POST['codiceIntervento'];
		//$URL = isset($_GET['URL'])?$_GET['URL']:$_POST['URL'];
		$mittente = isset($_GET['mittente'])?$_GET['mittente']:$_POST['mittente'];
		$dataIns = isset($_GET['dataIns'])?$_GET['dataIns']:$_POST['dataIns'];
		$dataIns=isset($exif['EXIF']['DateTimeOriginal'])?preg_replace('/:/', '-', $exif['EXIF']['DateTimeOriginal'], 2):date('Y-m-d H:i:s');
/*		if ($mittente==1){
			$URL=$URL . date('Ymd',strtotime($dataIns)). "/" . $File_Name;
		}
		else {
			$URL=$URL. "Intervento " . $codiceIntervento . "/" . date('Ymd',strtotime($dataIns)). "/" . $File_Name;
		}*/
		$URL = $File_Name;
		$fileName= isset($_GET['fileName'])?$_GET['fileName']:$_POST['fileName'];
		$fileNameThumb= $UploadDirectory."_L_".$NewFileName;
		$fileName=  $UploadDirectory."_H_".$NewFileName;

		$wh = getimagesize($fileName);
		$quality = max($wh[0], $wh[1]);
		$whThumb = getimagesize($fileNameThumb);
		$qualityThumb = max($whThumb[0], $whThumb[1]);

		$file =  file_get_contents($fileName); 
		$fileEncodedByteA = pg_escape_bytea($file); 
		$fileThumb =  file_get_contents($fileNameThumb); 
		$fileThumbEncodedByteA = pg_escape_bytea($fileThumb); 

		$now= date("Y-m-d H:i:s");
		if ($mittente==1){
			$SQL= "INSERT INTO \"MaterialeOggetti\" VALUES ($codiceOggetto, '$URL', 'immagine', $quality, '$fileEncodedByteA', NULL, '$dataIns', 7, 4, 0, 'web', 'web','$now', '" . $_SESSION['validUserName'] . "')";
			$result1 = pg_query($dbconn, utf8_encode($SQL)) or die ("Error: $SQL");

			$SQL= "INSERT INTO \"MaterialeOggetti\" VALUES ($codiceOggetto, '$URL', 'immagine', $qualityThumb, '$fileThumbEncodedByteA', NULL, '$dataIns', 7, 4, 0, 'web', 'web','$now', '" . $_SESSION['validUserName'] . "');";
			$result1 = pg_query($dbconn, utf8_encode($SQL)) or die ("Error: $SQL");
		}
		if ($mittente==2){
			$SQL= "INSERT INTO \"MaterialeVersioni\" VALUES ($codiceOggetto, '$URL', 'immagine', $quality, '$fileEncodedByteA', NULL, '$dataIns', 7, 4, 0, 'web', 'web','$now', '" . $_SESSION['validUserName'] . "')";
			$result1 = pg_query($dbconn, utf8_encode($SQL)) or die ("Error: $SQL");

			$SQL= "INSERT INTO \"MaterialeVersioni\" VALUES ($codiceOggetto, '$URL', 'immagine', $qualityThumb, '$fileThumbEncodedByteA', NULL, '$dataIns', 7, 4, 0, 'web', 'web','$now', '" . $_SESSION['validUserName'] . "');";
			$result1 = pg_query($dbconn, utf8_encode($SQL)) or die ("Error: $SQL");
		}
/*		else{
			$SQL= "INSERT INTO \"Materiale_interventi\" VALUES ($codiceIntervento, '$URL', 'immagine', $quality, NULL, '$dataIns', 7, 4, 0, 'web', 'web', '$fileEncodedByteA','$now')";
			$result1 = pg_query($dbconn, utf8_encode($SQL)) or die ("Error: $SQL");

			$SQL= "INSERT INTO \"Materiale_interventi\" VALUES ($codiceIntervento, '$URL', 'immagine', $qualityThumb, NULL, '$dataIns', 7, 4, 0, 'web', 'web', '$fileThumbEncodedByteA','$now');";
			$result1 = pg_query($dbconn, utf8_encode($SQL)) or die ("Error: $SQL");
		}*/
		//echo json_encode("ok");
		pg_close($dbconn);

		unlink($fileName);
		unlink($fileNameThumb);
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
