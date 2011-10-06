<?php
/*
 ----------------------------------------------------------------------------- 
 Kaart Generator Versie 1
 (C) 2008 Provincie Drenthe.
 Auteurs: Richard de Bruin
 ----------------------------------------------------------------------------- 
*/

include("arcservice/arcservice_class.php");

if (isset($_GET['e'])) {
	$e = $_GET['e'];
	//$_SESSION["PDF"] = TRUE;
}

if (isset($_GET['p'])) {
	$p = $_GET['p'];
}

if (isset($_GET['title'])) {
	$title = $_GET['title'];
}

if (isset($_GET['ext'])) {
	$ext = $_GET['ext'];
}

if (isset($_GET['test'])) {
	$test = $_GET['test'];
}

//if (isset($_GET['title'])) {
	//$p = $_GET['title'];
//}

if (isset($e)) {
	switch ($e) {				
		case "@KAART":
			F_KAART($p, $ext);
			break;
		case "@GBIKAART":
			F_GBIKAART($p, $ext);
			break;
		case "@KAARTFULL":
			F_KAARTFULL();
			break;	
		case "@GBISERVICE":
			F_GBISERVICE($p);
			break;
	}
}

function F_KAART($p, $ext) {
	global $test;
	$server = $_SERVER['SERVER_NAME'];
	debug ($server);
	
	if ($server == "paros-test") {
		$mapservice = "GDB_Geoportaalbinnen";
	}
	else {
		$mapservice = "GDB_Geoportaal";
	}
	debug($mapservice);
	$scriptnaam = $_SERVER['SCRIPT_NAME'];
	debug ($scriptnaam);

if ($p <> "" AND $p <> NULL) {
	$tokens = explode("|", $p);
	$sarxml = new ArcService($server, $mapservice);
	for ($iRecord = 0; $iRecord < count($tokens); $iRecord++) {
		$sDataset = $tokens[$iRecord];
		$sTitel = explode(";", $sDataset);
		$sImsid = explode(".", $sTitel[0]);
		debug ($sarxml->Legend($sImsid[1]));
		$legend = explode("/",$sarxml->Legend($sImsid[1]));
		$sImglink = $legend[count($legend)-1];
		debug ($sImglink);
		if ($iRecord == (count($tokens) - 1)) {
			$Laag = $Laag . $sImsid[1];
			$TitelAll = $TitelAll . $sTitel[1] . "$";
			$sImglink1 = $sImglink1 . $sImglink;
		}
		else {
			$Laag = $Laag . $sImsid[1] . ",";
			$TitelAll = $TitelAll . $sTitel[1] . "$";
			$sImglink1 = $sImglink1 . $sImglink . ",";
		}		
	}
	if ($test) {
		print 'test=' . $test . '<br>';
		print ("http://" . $server . $scriptnaam . "?e=@KAARTFULL&laag=geoportaal&vis=" . $Laag . "&tvis=" . $TitelAll . "&gdb_legend=" . $sImglink1 . "&ext=" . $ext);
		print ("<br><a href=\"http://" . $server . $scriptnaam . "?e=@KAARTFULL&laag=geoportaal&vis=" . $Laag . "&tvis=" . $TitelAll . "&gdb_legend=" . $sImglink1 . "&ext=" . $ext . "\">link</a>");
	}
	else {
		print ("<SCRIPT>");
		print ("self.location = \"?e=@KAARTFULL&laag=geoportaal&vis=" . $Laag . "&tvis=" . $TitelAll . "&gdb_legend=" . $sImglink1 . "&ext=" . $ext . "\"");
		print ("</SCRIPT>");
	}
}
}

function F_KAARTFULL () {

print("<script type=\"text/javascript\" src=\"js/swfobject.js\"></script>\n");
//print("<script type=\"text/javascript\" src=\"js/layers.js\"></script>\n");
print("<script type=\"text/javascript\" src=\"js/fscripts_full.js\"></script>\n");
print("<style type=\"text/css\">\n");
print("#flashcontent { height: 96%; }\n");
print(".randkaartdisc {\n");
print("font-family:Verdana, Geneva, Arial, Helvetica, sans-serif;\n");
print("font-size: 85.00%;\n"); 
print("color: #444;\n");
print("font-size: 9px;\n");
print("text-align: left;\n");
print ("}\n");
print("</style>\n");
print("</head>\n");
print("<body>\n");
print("<div id=\"flashcontent\">\n");
print("<script type=\"text/javascript\">\n");
print("goFlamingo(\"../config\",\"geoportaal_maak_kaart,locationfinder\");\n");
print("</script>");
print("</div>\n");
}

function debug ($text) {
	global $test;
	if ($test) {
		print $argv[1] . $text . '<br>';
	}
}

?>
