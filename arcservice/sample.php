<?php
include("arcservice_class.php");
//print ("<html>");
//print ("<body>");
if (!empty($_GET)){
	$sarxml = new ArcService($_GET["server"], $_GET["service"]);
	//print ("<img src=\"" . $sarxml->Image($sarxml->minx, $sarxml->miny, $sarxml->maxx, $sarxml->maxy) . "\">");
	//print ("<img src=\"" . $sarxml->Legend($_GET["laag"]) . "\">");
	$sarxml->Legend($_GET["laag"]);
	
	/*
	print ("<p>");
	print ("<b>Server :</b> = " . $_GET["server"] . "<br>");
	print ("<b>Service :</b> = " . $_GET["service"] . "<br>");
	print ("<b>Number of Layers :</b> = " . count($sarxml->Layers) . "<br>");
	print ("<b>Service Type :</b> = " . $sarxml->servicetype . "<br>");
	print ("<b>Map Units :</b> = " . $sarxml->unit . "<br>");
	print ("<b>Max X :</b> = " . $sarxml->maxx . "<br>");
	print ("<b>Max Y :</b> = " . $sarxml->maxy . "<br>");
	print ("<b>Min X :</b> = ". $sarxml->minx . "<br>");
	print ("<b>Min Y :</b> = ". $sarxml->miny . "<br>");
	print ("</p>");
	print ("<p>");
	foreach($sarxml->Layers as $livello){
		print "<p><b>".$livello["name"]."</b> (<b>type</b> = ".$livello["type"].", <b>id</b> = ".$livello["id"].", <b>visible</b> = ".$livello["visible"].", <b>maxscale</b> = ".$livello["maxscale"].", <b>minscale</b> = ".$livello["minscale"].")";
		print "<ul>";
		$campi = $livello["fields"];
		foreach($campi as $campo){
		print "<li>".$campo["name"]." (<b>type</b> = ".$campo["type"].", <b>size</b> = ".$campo["size"].", <b>precision</b> = ".$campo["precision"].")</li>";
		}
		print "</ul>";
		print "</p>";
	}
	
	print ("</p>");	
	}
	else {
		print ("<form action=\"" . $_SERVER["PHP_SELF"] . "\" method=\"GET\">");
		print ("<b>Server : </b><input type=\"text\" name=\"server\"><br>");
		print ("<b>Service : </b><input type=\"text\" name=\"service\"><br>");
		print ("<input type=\"submit\" value=\"Overview\">");
		print ("</form>");
*/
		}	
	
//print ("</body>");
//print ("</html>");