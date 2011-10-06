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

//if (isset($_GET['title'])) {
	//$p = $_GET['title'];
//}

if (isset($e)) {
	switch ($e) {				
		case "@KAART":
			F_KAART($p, $title);
			break;
		case "@GBIKAART":
			F_GBIKAART($p);
			break;
		case "@KAARTFULL":
			F_KAARTFULL();
			break;	
		case "@GBISERVICE":
			F_GBISERVICE($p);
			break;
	}
}

function F_KAART($p, $title) {
if ($p <> "" AND $p <> NULL) {
	$tokens = explode("|", $p);
	//$sarxml = new ArcService("paros", "GDB_Geoportaal");
	$sarxml = new ArcService("paros", "GDB_Geoportaal");
	for ($iRecord = 0; $iRecord < count($tokens); $iRecord++) {
		$sDataset = $tokens[$iRecord];
		$sTitel = explode(";", $sDataset);
		$sImsid = explode(".", $sTitel[0]);
		//$sImglink = "../metadata/legenda/" . $sImsid[1] . ".jpg"; 
		$sImglink = substr($sarxml->Legend($sImsid[1]), 39);
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
	print ("<SCRIPT>");
	//print ("alert(" . $sImglink1 . ");\n");
	print ("self.location = \"?e=@KAARTFULL&laag=geoportaal&vis=" . $Laag . "&tvis=" . $TitelAll . "&gdb_legend=" . $sImglink1 . "\"");
	print ("</SCRIPT>");
}
}

function F_KAARTFULL () {
print("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n");
print("<html>\n");
print("<head>\n");
print("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n");
print("<title>Provincie Drenthe - Kernen Check</title>\n");
print("<link rel=\"stylesheet\" type=\"text/css\" href=\"style/drenthe.css\">\n");
print("<script type=\"text/javascript\" src=\"js/swfobject.js\"></script>\n");
print("<script type=\"text/javascript\" src=\"js/fscripts_full1.js\"></script>\n");
print("<style type=\"text/css\">\n");
print("/* hide from ie on mac \*/\n");
print("html {\n");
print("height: 100%;\n");
print("}\n");
print("#flashcontent {\n");
print("height: 100%;\n");
print("}\n");
print("/* end hide */\n");
print("body {\n");
print("height: 100%;\n");
print("margin: 0;\n");
print("padding: 0;\n");
print("background-color: #000000;\n");
print("}\n");
print("</style>\n");
print("</head>\n");
print("<body style=\"background: white\">\n");
//print("<div id=\"header2\">\n");
//print("<div id=\"titlebar\">\n");
//print("<div class=\"title\">\n");
//print("<div id=\"subtitle\">\n");
//print("Geoportaal Drenthe\n");
//print("</div>\n");
//print("</div>\n");
//print("<div class=\"provincies\">\n");
//print("</div>\n");
//print("</div>\n");
//print("<div id=\"menu\">\n");
//print("<div id=\"menuleft\">\n");
//print("</div>\n");
//print("<div id=\"menuright\">\n");
//print("</div>\n");
//print("</div>\n");
//print("</div>\n");
print("<div id=\"kaart\">\n");
print("<div id=\"flashcontent\" style=\"width: 100%; height: 100%;\"></div>\n");
print("<script type=\"text/javascript\">\n");
print("goFlamingo(\"../config\",\"geoportaal_maak_kaart,locationfinder\");\n");
print("</script>");
//print("<script type=\"text/javascript\">\n");
//print("var so = new SWFObject(\"flamingo/flamingo.swf?config=../config/geoportaal_maak_kaart.xml,../config/locationfinder.xml\", \"flamingo\", \"100%\", \"100%\", \"8\", \"#FFFFFF\");\n");
//print("so.addParam(\"wmode\", \"transparent\");\n");
//print("so.write(\"flashcontent\");\n");
//print("var flamingo = document.getElementById(\"flamingo\");\n");
//print("</script>\n");
print("</div>\n");
print("</body>\n");
print("</html>\n");
}

?>
