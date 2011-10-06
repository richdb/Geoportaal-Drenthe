<?php
/*
 ----------------------------------------------------------------------------- 
 Geoportaal Versie 2
 (C) 2008 Provincie Drenthe.
 Auteurs: Richard de Bruin
 ----------------------------------------------------------------------------- 
 *
 * ----------------------------------------------------------------------------- 
 * GDB - Metadata Geografische Gegevens         
 * ----------------------------------------------------------------------------- 
 *        
 *      Copyright 2008 Provincie Drenthe
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
*/

require("adodb/adodb.inc.php");
include ("ezpdf/class.ezpdf.php");
include("arcservice/arcservice_class.php");

session_start();
$BASEPATH = dirname(__FILE__)."\\";

$_SESSION["PROVINCIE"] = FALSE;		

//controleren of ip-adres van gebruiker van de provincie is, zo ja dan ook niet openbare 
//bestanden tonen

$sURL = explode("/",curPageURL());

if (!empty($sURL[2])) {
	if ($sURL[2] == "paros") {		
			$_SESSION["PROVINCIE"] = TRUE;		
	}
}

//log bezoeker gegevens
if (!@$_SESSION["LOGJE"] == TRUE) {
	if (@$_SESSION["PROVINCIE"] == TRUE) {
		$file = "../log/geoportaal_intra_bezoekers.txt";
		$open = fopen( $file, "a" );
		fputs( $open, date("H:i:s, d-m-Y") . " | " . $_SERVER['HTTP_X_FORWARDED_FOR'] . " | "  . $_SERVER['REMOTE_ADDR'] . " | " . $_SERVER['HTTP_USER_AGENT'] . " | " . $_SERVER['HTTP_REFERER'] . "\n" );
		fclose( $open );
		$_SESSION["LOGJE"] = TRUE;
	}
	else {
		$file = "../log/geoportaal_internet_bezoekers.txt";
		$open = fopen( $file, "a" );
		fputs( $open, date("H:i:s, d-m-Y") . " | " . $_SERVER['HTTP_X_FORWARDED_FOR'] . " | " . $_SERVER['REMOTE_ADDR'] . " | " . $_SERVER['HTTP_USER_AGENT'] . " | " . $_SERVER['HTTP_REFERER'] . "\n" );
		fclose( $open );
		$_SESSION["LOGJE"] = TRUE;
	}

}

$db = ADONewConnection('access');
$dsn = "Driver={Microsoft Access Driver (*.mdb)};Dbq=".$BASEPATH."..\databases\gdb_database.mdb;Uid=;Pwd=;";

if (!$dsn){
	Error_handler("Fout in database connectie", $dsn);        
	exit();
}

if (isset($_GET['e'])) {
	$e = $_GET['e'];
	//$_SESSION["PDF"] = TRUE;
}
if (isset($_GET['p'])) {
	$p = $_GET['p'];
}

if (isset($e)) {
	switch ($e) {		
		case "@GBI":
			F_GBI($p);
			break;
		case "@GBIEXEC":		
			F_GBIEXEC($p);
			break;		
		case "@GBILEGEND":
			F_GBILEGEND($p);
			break;
		case "@GBILEGENDKAART":
			F_GBILEGENDKAART();
			break;
		case "@BODEMKAART":
			F_BODEMKAART($p);
			break;
		case "@GBIKAART":
			F_GBIKAART($p);
			break;
		case "@GBIKAARTFULL":
			F_GBIKAARTFULL();
			break;	
		case "@GBISERVICE":
			F_GBISERVICE($p);
			break;
	}
}
else {
	F_GBIEXEC("MENU");
}

function F_GBIEXEC($p) {
$tokens = explode("|", $p);
$sCommand = $tokens[0];

F_STYLE();

if ($sCommand == "LOGIN") {
	$sPW =  @$_POST["PW"];
	if ($sPW == "mooi") {
		$_SESSION["GBIMUTEREN"] = TRUE;
	}
	else {
		$_SESSION["GBIMUTEREN"] = FALSE;
	}	
	$sCommand = "MENU";
}

if ($sCommand == "LOGOUT") {
	$_SESSION["GBIMUTEREN"] = FALSE;
	$sCommand = "MENU";
}

if ($sCommand == "MENU") {	
	print("<div class=\"linkerkolom\">\n");
	print("<div class=\"broodkruimel\">\n");
	print("<a href=\"http://www.provincie.drenthe.nl\">Home</a><span class=\"tussenteken\"> </span>Geoportaal</span>\n");
	print("</div>\n");
	print("<BR><BR>\n");
	print("<BR>\n");
    print("<h1>Welkom bij het geoportaal van de provincie Drenthe</h1>\n");
	print("<div class=\"nieuwsberichten\">\n");
    print("<div class=\"nieuwsitem\">\n");
	print("<BR>\n");    
    print("<BR>\n");
    
	//print("</DIV>\n");
    //print("<div id=\"rechterbalk\"><div class=\"blok\"><div class=\"blokheader donkerblauw\"><p>Zoeken</p></div></div>");
		
	//<div id="rechterbalk"><div class="blok"><div class="blokheader donkerblauw"><p>Zoeken</p></div><div><div class="bloklist-nolink-item donkerblauw"><div class="bloklist-nolink-itemtext donkerblauw"><h2 class="tekstbrowser">Zoeken</h2><form id="zoekformulier" action="http://huisnet/organisatie/doet/organisatiegids/" method="get"><div class="verborgen"></div><input type="text" onClick="emptysearchox(this)" name="zoeken_term" id="zoeken_term" value="trefwoord" /><input type="hidden" name="ZoeSitIdt" value="21" /><input type="submit" class="zoekknop" id="zoekknop" value="" /><br /><input type="radio" onClick="switchSearchTarget('http://huisnet/algemene_onderdelen/zoeken/')" class="zoekradio" name="scope" id="ZoeIntranet" value="intranet" /><label for="ZoeIntranet" class="labelradio">Huisnet</label><input type="radio" onClick="switchSearchTarget('http://huisnet/organisatie/doet/organisatiegids/')" class="zoekradio" checked="checked" name="scope" id="ZoePersonen" value="Personen" /><label for="ZoePersonen" class="labelradio">personen</label></form><div class="clearfix"><!----></div><form id="zoekuitgebreidformulier" action="http://huisnet/algemene_onderdelen/zoeken/" method="get"><div class="verborgen"></div><input type="hidden" name="ZoeSitIdt" value="21" /><input type="hidden" name="zoeken_term" id="zoeken_uitgebreid_term" /><input type="submit" class="zoekknop" id="zoekuitgebreidknop" value="Uitgebreid zoeken" /></form><br /></div></div></div></div>
	
	print("<div class=\"left2\">\n");
	print("<br>\n");
	print("<center>\n");
	print("<div class=\"left2zoek\">Zoek in Geoportaal<form NAME=FRMQUERY METHOD=POST ACTION=\"?e=@GBI&p=SEARCHLIST\">");	
	print("<input size=\"80\" type=\"text\" class=\"leftinput\" name=\"KEYWORDS\"/>\n");
	print("<br>\n");
	
	print("<input type=\"submit\" class=\"leftbutton\" value=\"Zoeken\"/>\n");
	print("</center>\n");
	
	print("</div>\n");
	print("</div>\n");
	
	print("<div class=\"left3\">\n");
	print("<center>\n");
	print("<div class=\"left1kop\">Nieuwste of laatst bijgewerkte datasets</div>\n");
	print("<br>\n");
	if (@$_SESSION["PROVINCIE"] == TRUE) {
		$sRecords = F_SELECTRECORD("SELECT TOP 4 DATASET_TITEL, DATACODE, ALT_TITEL, INVOERDATUM FROM DATASET ORDER BY INVOERDATUM DESC");
	}
	else {
		$sRecords = F_SELECTRECORD("SELECT TOP 4 DATASET_TITEL, DATACODE, ALT_TITEL, INVOERDATUM FROM DATASET WHERE VEILIGHEID = 'vrij toegankelijk' ORDER BY INVOERDATUM DESC");	
	}
	    
	unset($aRecords);
	if ($sRecords <> "") {
		$aRecords = explode("|", $sRecords);
	}
	
	
	if (isset($aRecords)) {
		for ($iRecord = 0; $iRecord < count($aRecords); $iRecord++) {
			$sRecord = $aRecords[$iRecord];
			$aRecord = explode("^", $sRecord);
			$sTitel     = $aRecord[0];
			$sDatacode = $aRecord[1];
			$sAlttitel = $aRecord[2];
			$sDatum = $aRecord[3];
			
			$dt = strtotime($sDatum);
			$corrected_date=BST_finder($dt);	
			$sRoContent = date("d-m-Y", $corrected_date);			
			//<A HREF=\"kaart.php?e=@KAART&p=" . $aDatacode . "\" TITLE=\"" . $sTitel. "\" rel=\"shadowbox;width=1024;height=786\">Bekijk kaartlaag</A>//
			print ($sTitel . " | <a href=\"kaart.php?e=@KAART&p=" . $sAlttitel . "\" title=\"" . $sTitel. "\" class=\"extlink\">Bekijk kaartlaag</a>" . " | " . "<a href='?e=@GBI&p=DATASETEDIT|" . $sDatacode . "|" . $sTitel . "|" . $sLetter . "|" . $sAlttitel . "|" . "' 
			title=\"Metagegevens van " . $sTitel . ".\" class=\"extlink1\" >Bekijk metagegevens</a>");
			print("<br>\n");
		}
	}
	
	print("Omgevingsvisie Drenthe 2010 | <a href=\"http://www.drenthe.info/kaarten/website/metadata/download/ogv2010_shapes.zip\" >Shape formaat</a> | <a href=\"http://www.drenthe.info/kaarten/website/metadata/download/ogv2010_filegeodatabase_arcgis92.zip\" >Filegeodatabase formaat</a>");
	print("</center>\n");
	print("</div>\n");	
	
	print("<BR><BR><a href=mailto:post@drenthe.nl class=\"lefturl\">Vragen of opmerkingen?</a>\n");
	
	
    
    print("</DIV>\n");
	print("</DIV>\n");
	print("<BR>\n");
	F_ENDSTYLE();
	
	
	/*
	print("<div class=\"left1\">\n");
	print("<div class=\"left1kop\">Welkom bij het Geoportaal van de provincie Drenthe</div>\n");
	print("Binnen de provincie is veel geografische informatie te vinden waarmee beleid wordt ondersteund of gevisualiseerd. <br>Het Geoportaal voorziet in de behoefte actuele geografische 
	bestanden in een gebruiksvriendelijke open vorm beschikbaar te stellen aan met name andere overheden, waterschappen en ingenieursbureaus. De gegevens kunnen echter door iedereen worden geraadpleegd.<br>
    De gegevens kunnen in verschillende professionele formaten worden gedownload.<br><br>
    <B>Zoeken</B><br>In het geoportaal kunt u zoeken op <B>datasets</B>. Datasets maken vaak onderdeel uit van een kaart, 
	die opgebouwd is uit meerdere datasets. Wilt u een individuele dataset zoeken, raadplegen en downloaden, dan kunt u hier terecht op het Geoportaal.<br><br>
	Wilt u een <B>complete kaart</B> raadplegen, zoals het Cultuurhistorisch Kompas? dan verwijzen we u graag naar de 
	<A HREF=\"http://www.provincie.drenthe.nl/ik_zoek/kaartmateriaal/\" TARGET=\"blank\"><B>Atlas van Drenthe</B></A>.\n");    
	print("</div>\n");
	
	*/
}

if ($sCommand == "OVERPORTAAL") {
	F_LOG("Gebruik van deze site");
	print("<div class=\"linkerkolom\">\n");
	print("<div class=\"broodkruimel\">\n");
	print("<a href=\"http://www.provincie.drenthe.nl\">Home</a><span class=\"tussenteken\"> </span><a href=\"index.php\">Geoportaal</a><span class=\"tussenteken\"> </span>Over het geoportaal\n");
	print("</div>\n");
	print("<br><br><br>\n");
	print("<h2>Over het geoportaal</h2>\n");
	print("<div class=\"nieuwsberichten\">\n");
    print("<div class=\"nieuwsitem\">\n");
	print("<br>\n");
    print("<div class=\"inhoud\">\n");
    print("<div class=\"tekst\">\n");	
	print("<div class=\"tekst1\">\n");	
	print("Binnen de provincie is veel geografische informatie te vinden waarmee beleid wordt ondersteund of gevisualiseerd. <br>Het Geoportaal voorziet in de behoefte actuele geografische 
	bestanden in een gebruiksvriendelijke open vorm beschikbaar te stellen aan met name andere overheden, waterschappen en ingenieursbureaus. De gegevens kunnen echter door iedereen worden geraadpleegd.<br>
    De gegevens kunnen in verschillende professionele formaten worden gedownload.<br><br>
    <B>Zoeken</B><br>In het geoportaal kunt u zoeken op <B>datasets</B>. Datasets maken vaak onderdeel uit van een kaart, 
	die opgebouwd is uit meerdere datasets. Wilt u een individuele dataset zoeken, raadplegen en downloaden, dan kunt u hier terecht op het Geoportaal.<br><br>
	Wilt u een <B>complete kaart</B> raadplegen, zoals het Cultuurhistorisch Kompas? dan verwijzen we u graag naar de 
	<A HREF=\"http://www.provincie.drenthe.nl/ik_zoek/kaartmateriaal/\" TARGET=\"blank\"><B>Atlas van Drenthe</B></A>.\n");    		
	print("<br>\n");
	print("<br>\n");	
	print("</div>\n");
    print("</div>\n");
    print("</div>\n");
    print("</div>\n");
	print("</div>\n");
	print("<br>\n");	
	F_ENDSTYLE();
}

if ($sCommand == "GEBRUIKSITE") {
	F_LOG("Gebruik van deze site");
	print("<div class=\"linkerkolom\">\n");
	print("<div class=\"broodkruimel\">\n");
	print("<a href=\"http://www.provincie.drenthe.nl\">Home</a><span class=\"tussenteken\"> </span><a href=\"index.php\">Geoportaal</a><span class=\"tussenteken\"> </span>Over de kaarten\n");
	print("</div>\n");
	print("<br><br><br>\n");
	print("<h2>Over de kaarten</h2>\n");
	print("<div class=\"nieuwsberichten\">\n");
    print("<div class=\"nieuwsitem\">\n");
	print("<br>\n");
    print("<div class=\"inhoud\">\n");
    print("<div class=\"tekst\">\n");	
	print("<div class=\"tekst1\">\n");	
	print("De ruimtelijke gegevens die door de provincie Drenthe worden beheerd zijn op te delen in twee typen:<br><br>");
	print("<UL>");
	print("<LI>Openbare gegevens: kaartmateriaal waar de provincie broneigenaar van is. Deze gegevens mogen bekeken én gedownload worden.");
	print("<LI> Niet openbare gegevens: kaartmateriaal waar de provincie niet de broneigenaar van is. Dit zijn bijvoorbeeld topografische ondergronden, die bij het TopKadaster worden aangeschaft.");
	print("</UL>");
	print("De openbare kaartgegevens vindt u terug in dit <B>Geoportaal</B>.<br><br>");

	print("Met het Drentse Geoportaal kunt u:");
	print("<UL>");
	print("<LI>ruimtelijke gegevens opzoeken");
	print("<LI>metagegevens raadplegen en hier desgewenst een PDF-bestand van maken");
	print("<LI>De door u geselecteerde ruimtelijke gegevens tonen in de Flamingo kaartviewer");
	print("<LI>De gegevens downloaden in het door u gewenste formaat.");
	print("</UL><br>");

	print("Op de <a href=\"?e=@GBIEXEC&p=GEBRUIKVIEWER\">help-pagina</a> krijgt u meer uitleg over de functies van het Geoportaal.<br><br>");

	print("Heeft u vragen over het Geoportaal of over de inhoud van dit portaal, dan kunt u mailen naar <a href=mailto:post@drenthe.nl>de provincie Drenthe</a>.");
	
	print("<br>\n");
	print("<br>\n");	
	print("</div>\n");
    print("</div>\n");
    print("</div>\n");
    print("</div>\n");
	print("</div>\n");
	print("<br>\n");	
	F_ENDSTYLE();
}

if ($sCommand == "GEBRUIKVIEWER") {
	F_LOG("Gebruik van het geoportaal");
	print("<div class=\"linkerkolom\">\n");
	print("<div class=\"broodkruimel\">\n");
	print("<a href=\"http://www.provincie.drenthe.nl\">Home</a><span class=\"tussenteken\"> </span><a href=\"index.php\">Geoportaal</a><span class=\"tussenteken\"> </span>Uitleg\n");
	print("</div>\n");
	print("<br><br><br>\n");
	print("<h2>Uitleg</h2>\n");
	print("<div class=\"nieuwsberichten\">\n");
    print("<div class=\"nieuwsitem\">\n");
	print("<br>\n");
	print("<div class=\"inhoud\">\n");
    print("<div class=\"tekst\">\n");	
	print("<div class=\"tekst1\">\n");	
	print("Op deze pagina vindt u informatie over hoe u het Geoportaal kunt gebruiken. Informatie over het gebruik van de kaartviewer vind u in de kaartviewer.<br><br>\n");
	print("Er zijn drie ingangen in het geoportaal:<br><br>\n");
	
	print("<UL>\n");
	print("<LI>Alfabetisch zoeken: alfabetisch bladeren door alle aanwezige kaartlagen\n");
	print("<LI>Zoeken op trefwoord: vrij zoeken door titels en omschrijvingen van de kaartlagen\n");
	print("<LI>Maak een kaart: maak een kaart naar eigen inzicht door gebruik van verschillende kaartlagen.\n");
	print("</UL>\n");
	print("<br>\n");
	
	print("<B><H5>Alfabetisch zoeken</H5></B>\n");
	print("<UL>\n");
	print("<LI>Klik in het menu op <img src=\"images/alfabet.png\">. U krijgt vervolgens een overzicht van de eerste acht beschikbare kaartlagen, gesorteerd op alfabet.\n");
	print("<LI>Klik op de titel van een kaartlaag om gedetailleerde informatie (metagegevens) over deze kaartlaag te bekijken. Zie <i>Metagegevens</i> (onderaan deze pagina) voor meer informatie over het gebruik van de metagegevens.\n");
	print("<LI>Klik op <IMG src=\"images/bekijk.png\"> (rechts achter de titel) om de kaartlaag direct te bekijken in de kaartviewer\n");
	print("<LI>Klik eventueel op <IMG src=\"images/volgende.png\"> als u verder wilt bladeren door de kaartlagen\n");
	print("</UL>\n");
	
	print("<br>\n");
	print("<B><H5>Zoeken op trefwoord</H5></B>\n");
	print("<UL>\n");
	print("<LI>Klik in het menu op <img src=\"images/trefwoord.png\">.\n");
	print("<LI>Voer het trefwoord in waarop u wilt zoeken. Er wordt voor u gezocht op titel, omschrijving en toegekende trefwoorden.\n");
	print("<LI>U krijgt vervolgens een overzicht van de eerste acht door u geselecteerde kaartlagen, gesorteerd op alfabet.\n");
	print("<LI>Klik op de titel van een kaartlaag om gedetailleerde informatie (metagegevens) over deze kaartlaag te bekijken. Zie <i>Metagegevens</i> (onderaan deze pagina) voor meer informatie over het gebruik van de metagegevens.\n");
	print("<LI>Klik op <img src=\"images/bekijk.png\"> (rechts achter de titel) om de kaartlaag direct te bekijken in de kaartviewer\n");
	print("<LI>Klik eventueel op <IMG src=\"images/volgende.png\"> als u verder wilt bladeren door de kaartlagen\n");
	print("</UL>\n");

	print("<br>\n");
	print("<B><H5>Maak een kaart</H5></B>\n");
	print("<UL>\n");
	print("<LI>Klik in het menu op <IMG src=\"images/maakkaart.png\">.\n");
	print("<LI>Kies uit de lijst de thematische (kant en klare) kaart van uw keuze.\n");
	print("</UL>\n");
	
	print("<br>\n");
	print("<B><H5>Metagegevens</H5></B>\n");
	print("Als u op de titel van de door u gewenste kaartlaag heeft geklikt, komt u op een pagina <B>Metagegevens</B>. Op deze pagina staan vijf blokken met informatie:");
	print("<UL>\n");
	print("<LI>Algemeen: beschrijvende informatie van de kaartlaag, zoals titel en omschrijving\n");
	print("<LI>Inhoud: informatie over op welk beleidsterrein de kaartlaag betrekking heeft en wie inhoudelijk contactpersoon is\n");
	print("<LI>Items: Informatie over de classificatie-gegevens van de kaartlaag (opmaak van de legenda)\n");
	print("<LI>Metadata: informatie over deze metagegevens.\n");
	print("<LI>Specifiek: technische, geografische informatie over de kaartlaag\n");
	print("</UL>\n");

	print("Voor het alledaags gebruik zullen met name Algemeen, Inhoud en Items waardevolle informatie bevatten.\n");
	print("<br><br>\n");
	print("Op de Metagegevens-pagina kunt u:\n");

	print("<UL>\n");
	print("<LI>Een PDF-bestand maken van de metagegevens die u op de pagina ziet. <br> Klik hiervoor op <IMG SRC=\"images/pdf.png\">\n");
	print("<LI>De kaart behorend bij deze metagegevens openen in de kaartviewer. <br> Klik hiervoor op <IMG SRC=\"images/bekijk.png\">\n");
	print("</UL>\n");
	
	print("<br>\n");
	print("<br>\n");
	
	print("</div>\n");
    print("</div>\n");
    print("</div>\n");
    print("</div>\n");
	print("</div>\n");
	print("<br>\n");		
	F_ENDSTYLE();
}

if ($sCommand == "GISPROV") {
	F_LOG("GIS binnen de provincie Drenthe");
	print("<div class=\"linkerkolom\">\n");
	print("<div class=\"broodkruimel\">\n");
	print("<a href=\"http://www.provincie.drenthe.nl\">Home</a><span class=\"tussenteken\"> </span><a href=\"index.php\">Geoportaal</a><span class=\"tussenteken\"> </span>GIS binnen de provincie Drenthe\n");
	print("</div>\n");
	print("<br><br><br>\n");
	print("<h2>GIS binnen de provincie Drenthe</h2>\n");
	print("<div class=\"nieuwsberichten\">\n");
    print("<div class=\"nieuwsitem\">\n");
	print("<br>\n");
	print("<div class=\"inhoud\">\n");
    print("<div class=\"tekst\">\n");	
	print("<div class=\"tekst1\">\n");
	
	print("Sinds 2008 maakt GIS en Cartografie onderdeel uit van de afdeling FO, team Informatie. Vroeger bracht GIS en Cartografie met kaarten en grafieken het provinciaal beleid in beeld. Tegenwoordig doet GIS en Cartografie meer: zij beheert, analyseert, verwerkt en publiceert ruimtelijke gegevens om tot een goed onderbouwd provinciaal beleid te komen.<br><br>\n");
	print("Zeker 70% van de gegevens die relevant zijn voor de provincie hebben een ruimtelijk aspect. Wij kunnen helpen deze ruimtelijke gegevens in te zetten voor het beleid. En vervolgens om dit provinciaal beleid te verbeelden in kaarten, diagrammen en grafieken. Een greep uit de mogelijkheden, van luchtfoto's via basisbestanden en basistopografie tot eindproducten.<br><br>");
	print("Enkele mogelijheden:<br><br>");
	print("<UL>");
	print("<LI>afdrukken van de meest recente topografische kaarten in full color of in andere kleurstellingen.");
	print("<LI>topografische ondergronden in grijs of grijsblauw.");
	print("<LI>topo-thematische kaarten; sterk aan de topografie gerelateerde thema's.");
	print("<LI>statistische kaarten; per gemeente of kern b.v. bevolkingsgegevens.");
	print("<LI>routekaarten; bereikbaarheid van een locatie of excursies en fiets- of wandelroutes.");
	print("<LI>hoogtekaart.");
	print("<LI>diagrammen.");
	print("<LI>gebiedskaarten; bijvoorbeeld voor het gebiedenbeleid.");
	print("<LI>afdrukken van historische kaarten ");
	print("<LI>kladkaarten.");
	print("<LI>kaarten gereed maken voor druk.");
	print("<LI>kaarten gereed maken voor rapporten.");
	print("<LI>grote en kleine oplagen.");
	print("<LI>panelen en buitenborden.");
	print("<LI>afdrukken van luchtfoto's.");
	print("<LI>cartografische en statistische inhoud van websites.");
	print("<LI>ruimtelijke analyses mbv ca. 500 basisbestanden.");
	print("<LI>infographics; aangeklede statistische kaarten en diagrammen.");
	print("<LI>actualiteitskaarten.");
	print("<LI>3D-kaarten.");
	print("<LI>digitaal beschikbaar stellen via ons externe Geoloket.");	
	print("</UL>");
	print("<br>\n");
	print("Natuurlijk is dit maar een beperkte opsomming. Bent u nieuwsgierig naar de verschillende producten en wil je meer weten over de mogelijkheden? Neem dan contact met ons op door het sturen van een mail naar <a href=mailto:post@drenthe.nl>GIS Cartografie</a>.");		
	print("<br>\n");
	print("<br>\n");
		
	print("</div>\n");
    print("</div>\n");
    print("</div>\n");
    print("</div>\n");
	print("</div>\n");
	print("<br>\n");	
	F_ENDSTYLE();
}

if ($sCommand == "VRAGEN") {
	F_LOG("Vragen");
	print("<div class=\"linkerkolom\">\n");
	print("<div class=\"broodkruimel\">\n");
	print("<a href=\"http://www.provincie.drenthe.nl\">Home</a><span class=\"tussenteken\"> </span><a href=\"index.php\">Geoportaal</a><span class=\"tussenteken\"> </span>Veelgestelde vragen\n");
	print("</div>\n");	
	print("<br><br><br>\n");
	print("<h2>Veelgestelde vragen</h2>\n");
	print("<div class=\"nieuwsberichten\">\n");
    print("<div class=\"nieuwsitem\">\n");
	print("<br>\n");
	print("<div class=\"inhoud\">\n");
    print("<div class=\"tekst\">\n");	
	print("<div class=\"tekst1\">\n");	
	print("<B>Wat is geo-informatie?</B><br>\n");
	print("Geodata is informatie die verwijst naar een plek op de aarde. Dit kan beleidsinformatie zijn, informatie over een gebouw, maar ook informatie van een satelliet of informatie die is opgesteld door landmeetkundigen. De afkorting GIS staat voor Geografisch Informatie Systeem. Met 'Informatie Systeem' wordt een computersysteem bedoeld waarmee de geografische informatie gemaakt, bewerkt, beheerd en gepresenteerd kan worden. Kortom, geodata zijn de bestanden waar in het GIS mee gewerkt wordt.<br><br>\n");
	print("<B>Wat is de relatie tussen geo-informatie en GIS?</B><br>\n");
	print("Geodata (of geo-informatie) is een verkorte naam voor geografische informatie: informatie waarin een ruimtelijk element is opgenomen. Met een ruimtelijk element bedoelen we een verwijzing naar een plek op de aarde. Dit kan informatie over een gebouw zijn, maar ook informatie van een satelliet of informatie die is opgesteld door landmeetkundigen. Geodata wordt meestal als een kaart gepresenteerd.<br><br>\n");	
	print("<B>Wat kun je met geo-informatie?</B><br>\n");
	print("Geografische informatie wordt door een GIS meestal geleverd in zogenaamde shapefiles, een bestandsformaat voor geografische data. Deze shapefiles kunnen in een GIS gecombineerd worden met andere shapefiles, topografische kaarten, luchtfoto's en andere geodata, waardoor er op eenvoudige wijze samengestelde kaarten te maken zijn. Ook ruimtelijke analyses, bijvoorbeeld vergelijkingen tussen deze bestanden, zijn te maken. De bestanden zijn geschikt voor hergebruik. Dit maakt het mogelijk allerlei verschillende combinaties maken.<br><br>Het is ook mogelijk de veranderingen in bijvoorbeeld een landschap gedurende een langere periode te zien door een dataset met twee of meer opnamemomenten te vergelijken. Hiermee kan bijvoorbeeld de groei van een stad in beeld worden gebracht, of het stroombed van een rivier.
<br><br>\n");
	print("<B>Wat is het bestandsformaat van de kaarten?</B><br>\n");
	print("Alle kaarten worden aangeboden in de meest gebruikte formaten, zoals SHAPE, DWG, DXF, DGN, GML en EPS.<br><br>\n");
	print("<B>Hoe kan ik de metadata, geleverd bij een kaart, bekijken?</B><br>\n");
	print("Bij elke dataset is een *.xml-bestand en .pdf-bestand toegevoegd. Het xml-bestand bevat de metadata in een technisch formaat en het pdf-bestand in een leesbaar formaat.<br><br>\n");
	print("<B>Wat is metadata?</B><br>\n");
	print("Metadata betekent letterlijk: data over data (gegevens over gegevens), het is beschrijvende informatie over data en betreft dus niet de gegevens zelf. Metadata zijn belangrijk om het overzicht te bewaren van de beschikbare gegevens en om gemakkelijker naar de echte gegevens te kunnen zoeken.<br><br>\n");
	print("<B>Welke kosten zijn aan de geodatasets verbonden?</B><br>\n");
	print("Er zijn geen kosten aan het opvragen van de kaarten verbonden. Wij adviseren om na afloop van het project waarvoor de kaart gebruikt is, de data te verwijderen en bij een volgend project opnieuw te downloaden. Hierdoor voorkomt u dat eventuele tussentijdse wijzigingen aan u voorbij gaan.<br><br>\n");
	print("<B>Wat zijn de juridische gebruiksbeperkingen?</B><br>\n");
	print("In elke publicatie waarin de gegevens van provincie Drenthe zijn gebruikt, zal gebruiker de vermelding: \"Bron: provincie Drenthe\" moeten opnemen. Indien het een bewerking van de gegevens betreft, dient te worden opgenomen: \"Gebaseerd op de gegevens van de provincie Drenthe\".<br><br>\n");
	print("<B>Als ik bestanden van derden nodig heb, maar het is geen provinciale opdracht, kan ik deze bestanden dan op een andere manier krijgen?</B><br>\n");
	print("Nee, de provincie heeft met bestandseigenaren afspraken gemaakt waarin duidelijk vermeld is dat enkel in kader van provinciale opdrachten bestanden uitgeleverd mogen worden. Aanvullend dient dit door een kopie van de provinciale opdracht bevestigd te zijn.<br><br>\n");
	print("<B>Hoe kan ik bestanden downloaden?</B><br>\n");
	print("Als je de optie 'Bekijk Kaartlaag' kiest kom je in de kaartviewer terecht. Hier zie je een button met een winkelwagentje, waarmee een downlaod venster wordt geopend.<br><br>\n");
	print("<B>Kan ik meerdere bestanden tegelijk downloaden?</B><br>\n");
	print("Omwille van de stabiliteit en snelheid van het uitleverproces, is gekozen voor enkelvoudige download.<br><br>\n");
	print("<B>Kan ik ook alleen de attributen downloaden?</B><br>\n");
	print("Dit is helaas niet mogelijk.<br><br>\n");
	print("<B>Hoe kan ik een kaart afdrukken?</B><br>\n");
	print("Als je de optie 'Bekijk Kaartlaag' kiest kom je in de kaartviewer terecht. Hier zie je een button met een printer, waarmee een liggende afdruk op A4 of A3 formaat kan maken.<br><br>\n");
	print("<B>Waarom wordt er geen Google Earth of Maps gebruikt?</B><br>\n");
	print("Google Earth en Maps zijn mooie producten. Alleen deze prodcuten hebben als voorwaarde dat je de interface van Google gebruikt. Bij gebruik binnen andere interfaces dan zijn er kosten aan verbonden. Google Earth is
	snel, maar de techniek achter deze snelheid heeft ook zijn keerpunten. Zo zijn de luchtfoto's binnen Google Earth soms zwaar verouderd (meer dan 5 jaar oud). Ook is het gebruik van zogenaamde 'polygonen' bestanden binnen 
	Google Earth en Maps beperkt.<br><br>\n");
	print("<B>Het Geoportaal is soms traag bij het inladen</B><br>\n");
	print("Door test die wij zelf hebben uitgevoerd blijkt dat bij gebruik van Firefox als webbrowser de snelheid met een factor 5 tot 10 kan worden versneld t.o.v. Microsoft Internet Explorer.</a><br><br>\n");	
	print("<B>Het Geoportaal ziet er mooi uit, kan ik het ook gebruiken?</B><br>\n");
	print("Het Geoportaal van Drenthe is ontwikkeld onder de GPL licentie. De Flamingo Viewer (kaartviewer) wordt door de gezamelijke provincies ontwikkeld en onderhouden. Het Geoportaal Drenthe wordt door de provincie
		Drenthe zelf ontwikkeld en onderhouden. Deze beide producten zijn door de GPL licentie voor iedereen gratis te verkrijgen, en dus te gebruiken om een eigen Geoportaal op te zetten.<br><br>\n");	
	print("<B>Waar kan ik met mijn vragen terecht?</B><br>\n");
	print("Voor vragen of opmerkingen over het gebruik en de werking van het geo-dataportaal kunt u steeds contact opnemen met <a href=mailto:post@drenthe.nl>GIS Cartografie</a><br><br>\n");	
	print("<br>\n");
	print("</div>\n");
    print("</div>\n");
    print("</div>\n");
    print("</div>\n");
	print("</div>\n");
	print("<br>\n");		
	F_ENDSTYLE();
}

if ($sCommand == "UPDATEMORE") {
	$sTable = $tokens[1];
	$sField = $tokens[2];
	$sWhereOne = $tokens[3];
	$sWhereTwo = $tokens[4];
	$sValue = $tokens[5];	
	
	F_SELECTRECORD("UPDATE " . $sTable . " SET " . $sField . " = '" . $sValue . "' WHERE DATACODE= " . $sWhereOne . " AND ITEMNAAM = '" . $sWhereTwo . "'");
}

if ($sCommand == "UPDATE") {
	$sTable = $tokens[1];
	$sField = $tokens[2];
	$sWhere = $tokens[3];
	$sValue = $tokens[4];	
	F_SELECTRECORD("UPDATE " . $sTable . " SET " . $sField . " = '" . $sValue . "' WHERE " . $sWhere);
}

if ($sCommand == "DELETE") {
	$sTable = $tokens[1];
	$sWhere = $tokens[2];
	$sSQL = "DELETE FROM " . $sTable . " WHERE " . $sWhere;
    F_SELECTRECORD (sSQL);
	print ("<SCRIPT>");
	print ("document.location.reload();");
	print ("</SCRIPT>");
}

if ($sCommand == "INSERT") {
	$sTable = $tokens[1];
	$sFieldName = $tokens[2];
	$sFieldValue = $tokens[3];  
	$sSQL = "INSERT INTO " . $sTable . "(" . $sFieldName . ") VALUES (" . $sFieldValue . ")";
    F_SELECTRECORD ($sSQL);
}

if ($sCommand == "LINK") {
   $sTable = $tokens[1];
   $sField1 = $tokens[2];
   $sField2 = $tokens[3];
   $sValue1 = $tokens[4];
   $sValue2 = $tokens[5];
  
   $sSQL = "INSERT INTO " . $sTable . "(" . $sField1 . "," . $sField2 . ") VALUES (" . $sValue1 . "," . $sValue2 . ")";
   F_SELECTRECORD ($sSQL);
} 

}

function F_GBI($p) {
global $db;

$tokens = explode("|", $p);
$sAction = $tokens[0];


if ($sAction == "DATASETSEARCH") {
	F_LOG("Zoeken op trefwoord");
	F_STYLE();
	print("<div class=\"zoekkolom\">\n");
	print("<div class=\"broodkruimel\">\n");
	print("<a href=\"http://www.provincie.drenthe.nl\">Home</a><span class=\"tussenteken\"> </span><a href=\"index.php\">Geoportaal</a><span class=\"tussenteken\"> </span>Zoeken op trefwoord </span>\n");
	print("</div>\n");
	print("<br>\n");
	print("<br>\n");
	print("<br>\n");
	print("<div class=\"zoeken\"><h2>Zoek naar</h2><form NAME=FRMQUERY METHOD=POST ACTION=\"?e=@GBI&p=SEARCHLIST\">");
	print("<input size=\"30\" type=\"text\" class=\"zoekbox\" name=\"KEYWORDS\"/>\n");
	print("<input type=\"submit\" class=\"zoekbutton\" value=\"zoeken\"/>\n");
	print("</div>\n");
	print("</div>\n");	
	print("<br>\n");	
	print("<br>\n");
	print("<br>\n");	
}

if ($sAction == "DATASETLIST") {
	F_LOG("Alfabetisch zoeken");
	F_STYLE();
	if (count($tokens) == 2) {
		$sLetter = $tokens[1];
	}
	else {
		$sLetter = $tokens[2];
	}
	print("<div class=\"linkerkolom\">\n");
	if ($sLetter <> "" AND $sLetter <> NULL) {
		if (@$_SESSION["PROVINCIE"] == TRUE) {
			$sWhere = "DATASET.DATASET_TITEL LIKE '" . $sLetter . "%' AND DATASET.TYPE = 1 OR DATASET.DATASET_TITEL LIKE '" . $sLetter . "%' AND DATASET.TYPE = 3";
		}
		else {
			$sWhere = "DATASET.DATASET_TITEL LIKE '" . $sLetter . "%' AND DATASET.TYPE = 1 AND DATASET.VEILIGHEID = 'vrij toegankelijk' OR DATASET.DATASET_TITEL LIKE '" . $sLetter . "%' AND DATASET.TYPE = 3 AND DATASET.VEILIGHEID = 'vrij toegankelijk'";
		}
	}
	else {
		if (@$_SESSION["PROVINCIE"] == TRUE) {
			$sWhere = "DATASET.DATASET_TITEL LIKE 'a%' AND DATASET.TYPE = 1 OR DATASET.DATASET_TITEL LIKE 'a%' AND DATASET.TYPE = 3";
			$sLetter = 'a';
		}
		else {
			$sWhere = "DATASET.DATASET_TITEL LIKE 'a%' AND DATASET.TYPE = 1 AND DATASET.VEILIGHEID = 'vrij toegankelijk' OR DATASET.DATASET_TITEL LIKE 'a%' AND DATASET.TYPE = 3 AND DATASET.VEILIGHEID = 'vrij toegankelijk'";
			$sLetter = 'a';
		}
	}                    
   
	if (@$_SESSION["PROVINCIE"] == TRUE) {	
		$totaantal = F_SELECTRECORD("SELECT COUNT(*) FROM DATASET WHERE TYPE = 1 OR TYPE = 3");
	}
	else {
		$totaantal = F_SELECTRECORD("SELECT COUNT(*) FROM DATASET WHERE TYPE = 1 AND VEILIGHEID = 'vrij toegankelijk' OR TYPE = 3 AND VEILIGHEID = 'vrij toegankelijk'");
	}
	   
	//$sSQL = "SELECT DISTINCT DATASET.DATACODE, DATASET.DATASET_TITEL, DATASET.EIGENAAR FROM DATASET, TREFCODE, TREFTEXT, MEMOTABEL WHERE TREFCODE.DATACODE = DATASET.DATACODE AND TREFCODE.TREFCODE = TREFTEXT.TREFCODE AND DATASET.DATACODE = MEMOTABEL.CODE AND " . $sWhere . " ORDER BY " . $sOrder;
	$sSQL = "SELECT DISTINCT DATASET.DATACODE, DATASET.DATASET_TITEL, DATASET.ALT_TITEL, MEMOTABEL.TEKST, DATASET.STATUS, DATASET.TYPE, DATASET.INVOERDATUM 
	FROM ((DATASET INNER JOIN MEMOTABEL ON DATASET.OMSCHRIJVING_CODE = MEMOTABEL.CODE) INNER JOIN TREFCODE ON DATASET.DATACODE = TREFCODE.DATACODE) INNER JOIN TREFTEXT 
	ON TREFCODE.TREFCODE = TREFTEXT.TREFCODE 
	WHERE " . $sWhere . " ORDER BY DATASET.STATUS, DATASET.DATASET_TITEL";		
	//WHERE " . $sWhere . " ORDER BY DATASET.STATUS, DATASET.INVOERDATUM DESC, DATASET.DATASET_TITEL";		
		
	$sRecords = F_SELECTRECORD($sSQL);
	
	unset($aRecords);
	
	if ($sRecords <> "") {
		
		$aRecords = explode("|", $sRecords);
	}
	
	$aantal = count($aRecords);	
	
	print("<div class=\"broodkruimel\">\n");
	print("<a href=\"http://www.provincie.drenthe.nl\">Home</a><span class=\"tussenteken\"> </span><a href=\"index.php\">Geoportaal</a><span class=\"tussenteken\"> </span><a href=\"?e=@GBI&p=DATASETLIST\">Alfabetisch zoeken</a><span class=\"tussenteken\"> </span>$sLetter </span>\n");
	print("</div>\n");
	print("<br>\n");
	
	print("<div class=\"geo\">\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|a\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter A.\">A</A>\n");	
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|b\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter B.\">B</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|c\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter C.\">C</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|d\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter D.\">D</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|e\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter E.\">E</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|f\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter F.\">F</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|g\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter G.\">G</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|h\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter H.\">H</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|i\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter I.\">I</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|j\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter J.\">J</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|k\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter K.\">K</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|l\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter L.\">L</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|m\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter M.\">M</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|n\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter N.\">N</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|o\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter O.\">O</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|p\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter P.\">P</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|q\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter Q.\">Q</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|r\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter R.\">R</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|s\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter S.\">S</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|t\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter T.\">T</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|u\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter U.\">U</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|v\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter V.\">V</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|w\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter W.\">W</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|x\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter X.\">X</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|y\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter Y.\">Y</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("<A HREF=\"?e=@GBI&p=DATASETLIST|z\" TITLE=\"Bekijk alle kaartlagen waarvan de titel begint met de letter Z.\">Z</A>\n");			
	print("<span class=\"pijplijn\"> </span>\n");
	print("</div>\n");

	
	print("<div class=\"titel\">\n");
    print("<br>\n");
    print("<h2>$aantal kaartlagen van de $totaantal kaartlagen</h2>\n");
    print("</div>\n");
	
	if (count($aRecords) > 0) {
		if ($sFilterValue <> "" AND $sFilterValue <> NULL) {
			print("<center>\n");
			print("Gevonden kaartlagen die voldoen aan uw zoekopdracht: <B>" . $sFilterValue . "</B>.<br><br>\n");
			print("</center>\n");
		}		
	}
	else {
		if ($sFilterValue <> "" AND $sFilterValue <> NULL) {
			print("<center>\n");
			print("Geen kaartlagen gevonden die voldoen aan uw zoekopdracht: <B>" . $sFilterValue . "</B>.<br>Wilt u opnieuw <a href=\"index.php?e=@GBI&p=DATASETSEARCH\">zoeken</a>?<br><br>");
			print("</center>\n");
		}
	}
	
		
	if (isset($aRecords)) {		
		for ($iRecord = 0; $iRecord < count($aRecords); $iRecord++) {
			$sRecord = $aRecords[$iRecord];
			$aRecord = explode("^", $sRecord);
			if ($aRecord[4] == "historisch archief") {					
				print("<div class=\"nieuwsberichtenhist\">\n");
				print("<div class=\"geo\">\n");
				//print("<A class=\"extlink\" HREF=\"http://www.drenthe.info/kaarten/geoserver/geo/wms?service=WMS&version=1.1.0&request=GetMap&layers=geo:" . $aRecord[2] . "&styles=&bbox=206874.0,517761.0,265459.062,574830.0&width=512&height=498&srs=EPSG:28992&format=application/openlayers\"" . " TITLE=\"" . $aRecord[1]. "\">Bekijk kaartlaag</A>");
				//print("<A class=\extlink\" HREF=\"http://www.drenthe.info/kaarten/geoserver/geo/wms?service=WMS&version=1.1.0&request=GetMap&layers=geo:" . $aRecord[2] . "&styles=&bbox=206874.0,517761.0,265459.062,574830.0&width=512&height=498&srs=EPSG:28992&format=application/openlayers\"" . " TITLE=\"" . $aRecord[1]. "\">Bekijk kaartlaag</A>");
				print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\">Bekijk kaartlaag</A>");
				//print("<a href=\"?e=@GBILEGEND&p=DATASETLIST|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $sLetter . "|" . $aRecord[2] . "\" class=\"extlink\" title=\"Bekijk de gegevens op kaart.\" >Bekijk kaartlaag</A>\n");
				print("</div>\n");
				print("<br>\n");
				print("<div class=\"nieuwsitem\">\n");
				print("<div class=\"inhoud\"><div class=\"kop\"><A HREF='?e=@GBI&p=DATASETEDIT|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $sLetter . "|" . $aRecord[2] . "|" . "' class=\"extlink1\" title=\"Bekijk de metagegevens van de kaartlaag.\" >" . $aRecord[1] . " HISTORISCH ARCHIEF</A>\n");
				print("</div>\n");
			}
			else {
				print("<div class=\"nieuwsberichten\">\n");
				print("<div class=\"geo\">\n");
				//print("<A class=\"extlink\" HREF=\"http://www.drenthe.info/kaarten/geoserver/geo/wms?service=WMS&version=1.1.0&request=GetMap&layers=geo:" . $aRecord[2] . "&styles=&bbox=206874.0,517761.0,265459.062,574830.0&width=512&height=498&srs=EPSG:28992&format=application/openlayers\"" . " TITLE=\"" . $aRecord[1]. "\">Bekijk kaartlaag</A>");
				//print("<A class=\"extlink\" HREF=\"http://www.drenthe.info/kaarten/geoserver/geo/wms?service=WMS&version=1.1.0&request=GetMap&layers=geo:" . $aRecord[2] . "&styles=&bbox=206874.0,517761.0,265459.062,574830.0&width=1200&height=600&srs=EPSG:28992&format=application/openlayers\"" . " TITLE=\"" . $aRecord[1]. "\">Bekijk kaartlaag</A>");
				print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\">Bekijk kaartlaag</A>");
				//print("<A HREF=\"?e=@GBILEGEND&p=DATASETLIST|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $sLetter . "|" . $aRecord[2] . "\" title=\"Bekijk de gegevens op kaart.\" class=\"extlink\">Bekijk kaartlaag</A>\n");
				print("</div>\n");				
				print("<br>\n");
				print("<div class=\"nieuwsitem\">\n");
				print("<div class=\"inhoud\"><div class=\"kop\"><A HREF='?e=@GBI&p=DATASETEDIT|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $sLetter . "|" . $aRecord[2] . "|" . "' class=\"extlink1\" title=\"Bekijk de metagegevens van de kaartlaag.\" >" . $aRecord[1] . "</A>\n");
				print("</div>\n");
			}			
		    $sCode = F_SELECTRECORD("SELECT OMSCHRIJVING_CODE FROM DATASET WHERE DATACODE = " . $aRecord[0]);
			$sPlaattitel = F_SELECTRECORD("SELECT ALT_TITEL FROM DATASET WHERE DATACODE = " . $aRecord[0]);
			$sImsid = explode(".", $sPlaattitel);
			$sPlaattitel = $sImsid[1];			
			print("<div class=\"tekstplaat\">\n");
			//print("<A class=\"extlink\" HREF=\"http://www.drenthe.info/kaarten/geoserver/geo/wms?service=WMS&version=1.1.0&request=GetMap&layers=geo:" . $aRecord[2] . "&styles=&bbox=206874.0,517761.0,265459.062,574830.0&width=512&height=498&srs=EPSG:28992&format=application/openlayers\"" . " TITLE=\"" . $aRecord[1]. "\"><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");
			print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\"><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");
			//print ("<A HREF=\"?e=@GBILEGEND&p=DATASETLIST|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $sLetter . "|" . $aRecord[2] . "\" BORDER=\"0\" class=\"extlink\" title=\"Bekijk de gegevens op kaart.\"><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");						
			print("</div>\n");
			print("<div class=\"tekst1\">\n");			
			print(F_GBIOMSCHRIJF("MEMOTABEL" . "|" . "TEKST" . "|" . "CODE=" . $sCode));
			print("<br><br>\n");
			print("</div>\n");
			//$sarxml = new ArcService("paros", "GDB_Geoportaal");
			
			//print("<div class=\"tekstthumb\">\n");
			//print ("<img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\">");			
			//copy($sarxml->ImageLaag($sPlaattitel, $sarxml->minx, $sarxml->miny, $sarxml->maxx, $sarxml->maxy),'../metadata/thumbs/' . $sPlaattitel . '.jpg');
			//$fp=fopen('../metadata/thumbs/' . $sPlaattitel . '.jpg','wb');
			//fwrite($fp,$sarxml->ImageLaag($sPlaattitel, $sarxml->minx, $sarxml->miny, $sarxml->maxx, $sarxml->maxy));
			// fclose($fp);
			//	print("</div>\n");
			print("</div>\n");			
			print("</div>\n");
			print("</div>\n");				
			}		
	}	
		
    
	print("<br>\n");   
	F_ENDSTYLE();	
}

if ($sAction == "SEARCHLIST") {
	F_STYLE();
	print("<div class=\"linkerkolom\">\n");
	$sWhere = "DATASET.TYPE = 1 AND DATASET.VEILIGHEID = 'vrij toegankelijk' OR DATASET.TYPE = 3 AND DATASET.VEILIGHEID = 'vrij toegankelijk'";
	                          
    $sFilterValue = @$_POST["KEYWORDS"];
			
	if ($sFilterValue <> "" AND $sFilterValue <> NULL) {
		F_LOG("Trefwoord : " . $sFilterValue);
		if (@$_SESSION["PROVINCIE"] == TRUE) {		
		$sWhere = "instr(DATASET.DATASET_TITEL, '" . $sFilterValue . "') AND DATASET.TYPE = 1 OR instr(DATASET.DATASET_TITEL, '" . $sFilterValue . "') AND DATASET.TYPE = 3 OR instr(MEMOTABEL.TEKST, '" . $sFilterValue . "') AND DATASET.TYPE = 1 OR instr(MEMOTABEL.TEKST, '" . $sFilterValue . "') AND DATASET.TYPE = 3 OR instr(TREFTEXT.TREFWOORD, '" . $sFilterValue . "') AND DATASET.TYPE = 1 OR instr(TREFTEXT.TREFWOORD, '" . $sFilterValue . "') AND DATASET.TYPE = 3\n";
		$sWaarde = $sFilterValue;
		}
		else {
		$sWhere = "instr(DATASET.DATASET_TITEL, '" . $sFilterValue . "') AND DATASET.TYPE = 1 AND DATASET.VEILIGHEID = 'vrij toegankelijk' OR instr(DATASET.DATASET_TITEL, '" . $sFilterValue . "') AND DATASET.TYPE = 3 AND DATASET.VEILIGHEID = 'vrij toegankelijk' OR instr(MEMOTABEL.TEKST, '" . $sFilterValue . "') AND DATASET.TYPE = 1 AND DATASET.VEILIGHEID = 'vrij toegankelijk' OR instr(MEMOTABEL.TEKST, '" . $sFilterValue . "') AND DATASET.TYPE = 3 AND DATASET.VEILIGHEID = 'vrij toegankelijk' OR instr(TREFTEXT.TREFWOORD, '" . $sFilterValue . "') AND DATASET.TYPE = 1 AND DATASET.VEILIGHEID = 'vrij toegankelijk' OR instr(TREFTEXT.TREFWOORD, '" . $sFilterValue . "') AND DATASET.TYPE = 3 AND DATASET.VEILIGHEID = 'vrij toegankelijk'\n";
		$sWaarde = $sFilterValue;
		}
    }
	
	$sPosition = 0;
	
	if (count($tokens) > 1) {
		$sPosition = $tokens[1];
		if ($sPosition == 0) {
			$sWelk = "";
			$sWaarde = $tokens[3];			
		}
		else {
			$sWelk = $tokens[2];
			$sWaarde = $tokens[3];
		}
	}
	
	if ($sWaarde <> "") {
		if (@$_SESSION["PROVINCIE"] == TRUE) {
		$sWhere = "DATASET.DATASET_TITEL LIKE '%" . $sWaarde . "%' AND DATASET.TYPE = 1 OR DATASET.DATASET_TITEL LIKE '%" . $sWaarde . "%' AND DATASET.TYPE = 3 OR MEMOTABEL.TEKST Like '%" . $sWaarde . "%' AND DATASET.TYPE = 1 OR MEMOTABEL.TEKST LIKE '%" . $sWaarde . "%' AND DATASET.TYPE = 3 OR TREFTEXT.TREFWOORD LIKE '%" . $sWaarde . "%' AND DATASET.TYPE = 1 OR TREFTEXT.TREFWOORD LIKE '%" . $sWaarde . "%' AND DATASET.TYPE = 3\n";
		}
		else {
		$sWhere = "DATASET.DATASET_TITEL LIKE '%" . $sWaarde . "%' AND DATASET.TYPE = 1 AND DATASET.VEILIGHEID = 'vrij toegankelijk' OR DATASET.DATASET_TITEL LIKE '%" . $sWaarde . "%' AND DATASET.TYPE = 3 AND DATASET.VEILIGHEID = 'vrij toegankelijk' OR MEMOTABEL.TEKST LIKE '%" . $sWaarde . "%' AND DATASET.TYPE = 1 AND DATASET.VEILIGHEID = 'vrij toegankelijk' OR MEMOTABEL.TEKST LIKE '%" . $sWaarde . "%' AND DATASET.TYPE = 3 AND DATASET.VEILIGHEID = 'vrij toegankelijk' OR TREFTEXT.TREFWOORD LIKE '%" . $sWaarde . "%' AND DATASET.TYPE = 1  AND DATASET.VEILIGHEID = 'vrij toegankelijk' OR TREFTEXT.TREFWOORD LIKE '%" . $sWaarde . "%' AND DATASET.TYPE = 3  AND DATASET.VEILIGHEID = 'vrij toegankelijk'\n";
		}
    }	
		
	//$sSQL = "SELECT DISTINCT DATASET.DATACODE, DATASET.DATASET_TITEL, DATASET.EIGENAAR FROM DATASET, TREFCODE, TREFTEXT, MEMOTABEL WHERE TREFCODE.DATACODE = DATASET.DATACODE AND TREFCODE.TREFCODE = TREFTEXT.TREFCODE AND DATASET.DATACODE = MEMOTABEL.CODE AND " . $sWhere . " ORDER BY " . $sOrder;
	$sSQL = "SELECT DISTINCT DATASET.DATACODE, DATASET.DATASET_TITEL, DATASET.ALT_TITEL, MEMOTABEL.TEKST, DATASET.STATUS, DATASET.TYPE, DATASET.INVOERDATUM 
	FROM ((DATASET INNER JOIN MEMOTABEL ON DATASET.OMSCHRIJVING_CODE = MEMOTABEL.CODE) INNER JOIN TREFCODE ON DATASET.DATACODE = TREFCODE.DATACODE) INNER JOIN TREFTEXT 
	ON TREFCODE.TREFCODE = TREFTEXT.TREFCODE 
	WHERE " . $sWhere . " ORDER BY DATASET.STATUS, DATASET.INVOERDATUM DESC, DATASET.DATASET_TITEL";
	

	//print ($sSQL);
			
	$sRecords = F_SELECTRECORD($sSQL);
	
	unset($aRecords);
	
	if ($sRecords <> "") {
		$aRecords = explode("|", $sRecords);
	}
	
	$aantal = count($aRecords);	
	
	print("<div class=\"broodkruimel\">\n");
	print("<a href=\"http://www.provincie.drenthe.nl\">Home</a><span class=\"tussenteken\"> </span><a href=\"index.php\">Geoportaal</a><span class=\"tussenteken\"> </span>Zoeken op trefwoord </span>\n");
	print("</div>\n");
	print("<br>\n");	
		
	if ($Welk == "") {
		$sPositionNext = $sPosition + 8;
		$sPositionPrev = $sPosition;
	}
		
	if ($sWelk == "next") {
		$sPositionNext = $sPosition + 8;
		$sPositionPrev = $sPosition;
	}
	
	if ($sWelk == "prev") {
		$sPositionNext = $sPosition;
		$sPositionPrev = $sPosition - 8;
		if ($sPositionPrev < 0) {
			$sPositionPrev = 0;
			$sPositionNext = 8;
		}
	}	
	
	$aantal_laag = $sPositionPrev + 1;
	if ($sPositionNext + 8 > $aantal) {
		$aantal_hoog = $aantal;
	}
	else {
		$aantal_hoog = $sPositionNext;
	}	
		
	if (count($aRecords) > 0) {
		if ($sFilterValue <> "" AND $sFilterValue <> NULL) {
			print("<div class=\"searchkop\">\n");
			print("Gevonden kaartlagen die voldoen aan uw zoekopdracht: <B>" . $sFilterValue . "</B>.<br><br>\n");
			print("</div>\n");
		}
		elseif ($sWaarde <> "") {
			print("<div class=\"searchkop\">\n");
			print("Gevonden kaartlagen die voldoen aan uw zoekopdracht: <B>" . $sWaarde . "</B>.<br><br>\n");
			print("</div>\n");
		}		
	}
	else {
		if ($sFilterValue <> "" AND $sFilterValue <> NULL) {
			print("<div class=\"searchkop\">\n");
			print("Geen kaartlagen gevonden die voldoen aan uw zoekopdracht: <B>" . $sFilterValue . "</B>. <br>Wilt u opnieuw <a href=\"index.php\">zoeken</a>?<br><br>");
			print("</div>\n");
		}
	}
		
	if (count($aRecords) > 0) {
	
	if ($sPositionPrev == 0 and $aantal > 8) {
		print("<A HREF='?e=@GBI&p=SEARCHLIST|" . $sPositionNext .  "|next|" . $sWaarde . "' class=\"leesverder\">Volgende</A>\n");
	}
	elseif ($sPositionNext + 8 > $aantal) {
		print("<A HREF='?e=@GBI&p=SEARCHLIST|" . $sPositionPrev. "|prev|" . $sWaarde . "' class=\"leesterug\">Vorige</A>\n");
	}
	else {
		print("<A HREF='?e=@GBI&p=SEARCHLIST|" . $sPositionPrev. "|prev|" . $sWaarde . "' class=\"leesterug\">Vorige</A><span class=\"pijplijn\"> </span><A HREF='?e=@GBI&p=SEARCHLIST|" . $sPositionNext .  "|next|" . $sWaarde . "' class=\"leesverder\">Volgende</A>\n");
	}
	
	print("<div class=\"titel\">\n");
    print("<br>\n");
    print("<h2>Kaartlaag $aantal_laag tot $aantal_hoog van de $aantal kaartlagen</h2>\n");
    print("</div>\n");
		
	
	if (isset($aRecords)) {
		if (count($aRecords) > 8) {		
			if ($sPositionNext + 8 > $aantal) {
				for ($iRecord = $sPositionPrev ; $iRecord < count($aRecords); $iRecord++) {
					$sRecord = $aRecords[$iRecord];
					$aRecord = explode("^", $sRecord);
					if ($aRecord[4] == "historisch archief") {					
						print("<div class=\"nieuwsberichtenhist\">\n");
						print("<div class=\"geo\">\n");
						//type kaart checken
						if ($aRecord[5] == 3) {
							print("<A TARGET=\"_blank\" HREF=\"" . $aRecord[2] . "\" TITLE=\"Bekijk de gegevens op kaart.\">Bekijk kaartlaag</A>\n");
						}
						else {
							print("<a href=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\">Bekijk kaartlaag</a>");
							//print("<A HREF=\"?e=@GBILEGEND&p=SEARCHLIST|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $iRecord . "|" . $aRecord[2] . "|" . $sWaarde . "|&laag=geoportaal&vis=" . $aRecord[2] . "&gdb=" . $aRecord[1] . "\" TITLE=\"Bekijk de gegevens op kaart.\">Bekijk kaartlaag</A>\n");
						}						
						print("</div>\n");
						print("<br>\n");
						print("<div class=\"nieuwsitem\">\n");
						print("<div class=\"inhoud\"><div class=\"kop\"><A HREF='?e=@GBI&p=DATASETEDIT|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $iRecord . "|" . $aRecord[2] . "|" . "' class=\"extlink1\" title=\"Bekijk de metagegevens van de kaartlaag.\">" . $aRecord[1] . " HISTORISCH ARCHIEF</A>\n");
						print("</div>\n");						
					}
					else {
						print("<div class=\"nieuwsberichten\">\n");
						print("<div class=\"geo\">\n");
						//type kaart checken
						if ($aRecord[5] == 3) {
							print("<A TARGET=\"_blank\" HREF=\"" . $aRecord[2] . "\" TITLE=\"Bekijk de gegevens op kaart.\">Bekijk kaartlaag</A>\n");
						}
						else {
							print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink1\">Bekijk kaartlaag</A>");
							//print("<A HREF=\"?e=@GBILEGEND&p=SEARCHLIST|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $iRecord . "|" . $aRecord[2] . "|" . $sWaarde . "|&laag=geoportaal&vis=" . $aRecord[2] . "&gdb=" . $aRecord[1] . "\" TITLE=\"Bekijk de gegevens op kaart.\">Bekijk kaartlaag</A>\n");
						}							
						print("</div>\n");
						print("<br>\n");
						print("<div class=\"nieuwsitem\">\n");
						print("<div class=\"inhoud\"><div class=\"kop\"><A HREF='?e=@GBI&p=DATASETEDIT|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $iRecord . "|" . $aRecord[2] . "|" . "' class=\"extlink\" title=\"Bekijk de metagegevens van de kaartlaag.\">" . $aRecord[1] . "</A>\n");
						print("</div>\n");						
					}					
			$sCode = F_SELECTRECORD("SELECT OMSCHRIJVING_CODE FROM DATASET WHERE DATACODE = " . $aRecord[0]);
			$sPlaattitel1 = F_SELECTRECORD("SELECT ALT_TITEL FROM DATASET WHERE DATACODE = " . $aRecord[0]);
			$sType = F_SELECTRECORD("SELECT TYPE FROM DATASET WHERE DATACODE = " . $aRecord[0]);
			$sImsid = explode(".", $sPlaattitel1);
			$sPlaattitel = $sImsid[1];	
			if ($sType == 3) {
				$sPlaattitel = "TYPE3";
			}
			print("<div class=\"tekstplaat\">\n");
			if ($sType == 3) {
			print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\"><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");
			//print ("<A HREF=\"" . $sPlaattitel1 . "\" TARGET=\"_blank\" BORDER=\"0\" TITLE=\"Bekijk de gegevens op kaart.\" ><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");						
			}
			else {
			print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\"><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");
			//print ("<A HREF=\"?e=@GBILEGEND&p=DATASETLIST|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $sLetter . "|" . $aRecord[2] . "\" BORDER=\"0\" TITLE=\"Bekijk de gegevens op kaart.\" ><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");						
			}
			print("</div>\n");
			print("<div class=\"tekst1\">\n");			
			print(F_GBIOMSCHRIJF("MEMOTABEL" . "|" . "TEKST" . "|" . "CODE=" . $sCode));
			print("<br><br>\n");
			print("</div>\n");
					
					
					
					
					
					print("</div>\n");
					print("</div>\n");					
					print("</div>\n");
				}
			}				
			else {
				for ($iRecord = $sPositionPrev ; $iRecord < $sPositionNext; $iRecord++) {
					$sRecord = $aRecords[$iRecord];
					$aRecord = explode("^", $sRecord);
					if ($aRecord[4] == "historisch archief") {					
						print("<div class=\"nieuwsberichtenhist\">\n");
						print("<div class=\"geo\">\n");
						//type kaart checken
						if ($aRecord[5] == 3) {
							print("<A TARGET=\"_blank\" HREF=\"" . $aRecord[2] . "\" TITLE=\"Bekijk de gegevens op kaart.\">Bekijk kaartlaag</A>\n");
						}
						else {
							print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\">Bekijk kaartlaag</A>");
							//print("<A HREF=\"?e=@GBILEGEND&p=SEARCHLIST|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $iRecord . "|" . $aRecord[2] . "|" . $sWaarde . "|&laag=geoportaal&vis=" . $aRecord[2] . "&gdb=" . $aRecord[1] . "\" TITLE=\"Bekijk de gegevens op kaart.\">Bekijk kaartlaag</A>\n");
						}						
						print("</div>\n");
						print("<br>\n");
						print("<div class=\"nieuwsitem\">\n");
						print("<div class=\"inhoud\"><div class=\"kop\"><A HREF='?e=@GBI&p=DATASETEDIT|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $iRecord . "|" . $aRecord[2] . "|" . "' class=\"extlink1\" title=\"Bekijk de metagegevens van de kaartlaag.\">" . $aRecord[1] . " HISTORISCH ARCHIEF</A>\n");
						print("</div>\n");						
					}
					else {
						print("<div class=\"nieuwsberichten\">\n");
						print("<div class=\"geo\">\n");
						//type kaart checken
						if ($aRecord[5] == 3) {
							print("<A TARGET=\"_blank\" HREF=\"" . $aRecord[2] . "\" TITLE=\"Bekijk de gegevens op kaart.\">Bekijk kaartlaag</A>\n");
						}
						else {
							print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\">Bekijk kaartlaag</A>");
							//print("<A HREF=\"?e=@GBILEGEND&p=SEARCHLIST|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $iRecord . "|" . $aRecord[2] . "|" . $sWaarde . "|&laag=geoportaal&vis=" . $aRecord[2] . "&gdb=" . $aRecord[1] . "\" TITLE=\"Bekijk de gegevens op kaart.\">Bekijk kaartlaag</A>\n");
						}							
						print("</div>\n");
						print("<br>\n");
						print("<div class=\"nieuwsitem\">\n");
						print("<div class=\"inhoud\"><div class=\"kop\"><A HREF='?e=@GBI&p=DATASETEDIT|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $iRecord . "|" . $aRecord[2] . "|" . "' class=\"extlink1\" title=\"Bekijk de metagegevens van de kaartlaag.\">" . $aRecord[1] . "</A>\n");
						print("</div>\n");						
					}

			$sCode = F_SELECTRECORD("SELECT OMSCHRIJVING_CODE FROM DATASET WHERE DATACODE = " . $aRecord[0]);
			$sPlaattitel1 = F_SELECTRECORD("SELECT ALT_TITEL FROM DATASET WHERE DATACODE = " . $aRecord[0]);
			$sType = F_SELECTRECORD("SELECT TYPE FROM DATASET WHERE DATACODE = " . $aRecord[0]);
			$sImsid = explode(".", $sPlaattitel1);
			$sPlaattitel = $sImsid[1];	
			if ($sType == 3) {
				$sPlaattitel = "TYPE3";
			}
			print("<div class=\"tekstplaat\">\n");
			if ($sType == 3) {
			print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\"><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");
			//print ("<A HREF=\"" . $sPlaattitel1 . "\" TARGET=\"_blank\" BORDER=\"0\" TITLE=\"Bekijk de gegevens op kaart.\" ><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");						
			}
			else {
			print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\"><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");
			//print ("<A HREF=\"?e=@GBILEGEND&p=DATASETLIST|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $sLetter . "|" . $aRecord[2] . "\" BORDER=\"0\" TITLE=\"Bekijk de gegevens op kaart.\" ><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");						
			}
			print("</div>\n");
			print("<div class=\"tekst1\">\n");			
			print(F_GBIOMSCHRIJF("MEMOTABEL" . "|" . "TEKST" . "|" . "CODE=" . $sCode));
			print("<br><br>\n");
			print("</div>\n");
					
					
					print("</div>\n");
					print("</div>\n");					
					print("</div>\n");					
				}			
			}						
		}
		else {
			for ($iRecord = $sPosition; $iRecord < count($aRecords); $iRecord++) {
				$sRecord = $aRecords[$iRecord];
				$aRecord = explode("^", $sRecord);
				if ($aRecord[4] == "historisch archief") {					
					print("<div class=\"nieuwsberichtenhist\">\n");
					print("<div class=\"geo\">\n");
					//type kaart checken
					if ($aRecord[5] == 3) {
						print("<A TARGET=\"_blank\" HREF=\"" . $aRecord[2] . "\" TITLE=\"Bekijk de gegevens op kaart.\">Bekijk kaartlaag</A>\n");
					}
					else {
						print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\">Bekijk kaartlaag</A>");
						//print("<A HREF=\"?e=@GBILEGEND&p=SEARCHLIST|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $iRecord . "|" . $aRecord[2] . "|" . $sWaarde . "|&laag=geoportaal&vis=" . $aRecord[2] . "&gdb=" . $aRecord[1] . "\" TITLE=\"Bekijk de gegevens op kaart.\">Bekijk kaartlaag</A>\n");
					}						
					print("</div>\n");
					print("<br>\n");
					print("<div class=\"nieuwsitem\">\n");
					print("<div class=\"inhoud\"><div class=\"kop\"><A HREF='?e=@GBI&p=DATASETEDIT|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $iRecord . "|" . $aRecord[2] . "|" . "' class=\"extlink1\" title=\"Bekijk de metagegevens van de kaartlaag.\">" . $aRecord[1] . " HISTORISCH ARCHIEF</A>\n");
					print("</div>\n");					
				}
				else {
					print("<div class=\"nieuwsberichten\">\n");
					print("<div class=\"geo\">\n");
					//type kaart checken
					if ($aRecord[5] == 3) {
						print("<A TARGET=\"_blank\" HREF=\"" . $aRecord[2] . "\" TITLE=\"Bekijk de gegevens op kaart.\">Bekijk kaartlaag</A>\n");
					}
					else {
						print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\">Bekijk kaartlaag</A>");
						//print("<A HREF=\"?e=@GBILEGEND&p=SEARCHLIST|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $iRecord . "|" . $aRecord[2] . "|" . $sWaarde . "|&laag=geoportaal&vis=" . $aRecord[2] . "&gdb=" . $aRecord[1] . "\" TITLE=\"Bekijk de gegevens op kaart.\">Bekijk kaartlaag</A>\n");
					}						
					print("</div>\n");
					print("<br>\n");
					print("<div class=\"nieuwsitem\">\n");
					print("<div class=\"inhoud\"><div class=\"kop\"><A HREF='?e=@GBI&p=DATASETEDIT|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $iRecord . "|" . $aRecord[2] . "|" . "' class=\"extlink1\" title=\"Bekijk de metagegevens van de kaartlaag.\">" . $aRecord[1] . "</A>\n");
					print("</div>\n");					
				}

			$sCode = F_SELECTRECORD("SELECT OMSCHRIJVING_CODE FROM DATASET WHERE DATACODE = " . $aRecord[0]);
			$sPlaattitel1 = F_SELECTRECORD("SELECT ALT_TITEL FROM DATASET WHERE DATACODE = " . $aRecord[0]);
			$sType = F_SELECTRECORD("SELECT TYPE FROM DATASET WHERE DATACODE = " . $aRecord[0]);
			$sImsid = explode(".", $sPlaattitel1);
			$sPlaattitel = $sImsid[1];	
			if ($sType == 3) {
				$sPlaattitel = "TYPE3";
			}
			print("<div class=\"tekstplaat\">\n");
			if ($sType == 3) {
			print ("<A HREF=\"" . $sPlaattitel1 . "\" TARGET=\"_blank\" BORDER=\"0\" TITLE=\"Bekijk de gegevens op kaart.\" ><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");						
			}
			else {
			print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\"><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");
			//print ("<A HREF=\"?e=@GBILEGEND&p=DATASETLIST|" . $aRecord[0] . "|" . $aRecord[1] . "|" . $sLetter . "|" . $aRecord[2] . "\" BORDER=\"0\" TITLE=\"Bekijk de gegevens op kaart.\" ><img src=\"../metadata/thumbs/" . $sPlaattitel . ".jpg\" style=\"border: none;\"></A>");						
			}
			print("</div>\n");
			print("<div class=\"tekst1\">\n");			
			print(F_GBIOMSCHRIJF("MEMOTABEL" . "|" . "TEKST" . "|" . "CODE=" . $sCode));
			print("<br><br>\n");
			print("</div>\n");
				
				
				print("</div>\n");
				print("</div>\n");					
				print("</div>\n");					
			}			
		}
	}	
	
	print("<br>\n");
	if (count($aRecords) > 0) {
	
	if ($sPositionPrev == 0 and $aantal > 8) {
		print("<A HREF='?e=@GBI&p=SEARCHLIST|" . $sPositionNext .  "|next|" . $sWaarde . "' class=\"leesverder\">Volgende</A>\n");
	}
	elseif ($sPositionNext + 8 > $aantal) {
		print("<A HREF='?e=@GBI&p=SEARCHLIST|" . $sPositionPrev. "|prev|" . $sWaarde . "' class=\"leesterug\">Vorige</A>\n");
	}
	else {
		print("<A HREF='?e=@GBI&p=SEARCHLIST|" . $sPositionPrev. "|prev|" . $sWaarde . "' class=\"leesterug\">Vorige</A><span class=\"pijplijn\"> </span><A HREF='?e=@GBI&p=SEARCHLIST|" . $sPositionNext .  "|next|" . $sWaarde . "' class=\"leesverder\">Volgende</A>\n");
	}		
	
	}
	}
	F_ENDSTYLE();
} 

if ($sAction == "DATASETEDIT") {
	//$sDatacode = "-1";
	print("<link href=\"css/metadata.css\" rel=\"stylesheet\" type=\"text/css\"/>\n");
	//print("<link href=\"css/DynamischDrenthe.css\" rel=\"stylesheet\" type=\"text/css\"/>\n");
	//print("<link href=\"css/formulier.css\" rel=\"stylesheet\" type=\"text/css\"/>\n");
	//print("<link href=\"css/verticaleNav.css\" rel=\"stylesheet\" type=\"text/css\"/>\n");
	
	if (count($tokens) >= 2) {
		$sDatacode = $tokens[1];
		$sKaart = $tokens[2];
		$sPosition = $tokens[3];
		$sSpatial = $tokens[4];
		$sWaarde = $tokens[5];
	}
	
	F_LOG("Metagegevens : " . $sKaart);
	if (@$_SESSION["GBIMUTEREN"] == TRUE) {
		if (count($tokens) > 3) {
			$_SESSION["GBIPRO"] = $tokens[2];
		}
		else {
			$_SESSION["GBIPRO"] = "";
		}
	}
    else {
		$_SESSION["GBIPRO"] = "RO";
	}
	
		
	//<A HREF=\"?e=@GBI&p=DATASETLIST|DATASET.DATACODE\">PDF gegevens</A>       
	//<A HREF=\"?e=@GBI&p=DATASETLIST|DATASET.DATACODE\">XML gegevens</A>         
	//<A HREF=\"?e=@GBIKAART&laag=layer1&vis=$sDatacode\">Bekijk gegevens</A>
	
	
	
	$sPDF = F_SELECTRECORD("SELECT ALT_TITEL FROM DATASET WHERE DATACODE = " . $sDatacode);
	
	
	//print("<div class=\"linkerkolom\">\n");
	//print("<div class=\"nieuwsberichten\">\n");
    //print("<div class=\"nieuwsitem\">\n");    
    //print("<div class=\"inhoud\">\n");
    //print("<div class=\"tekst\">\n");
	
    print("<a href=\"../metadata/$sPDF.pdf\" class=\"lefturl\" target=\"_blank\" title=\"Een PDF bestand van de metadatagegevens.\">PDF gegevens</a>\n");	
	print("<div class=\"metawrapper\">\n");	
	print("<div class=\"lefttekst\">\n");	
	print("<table WIDTH=95% cellpadding=5 CELLSPACING=\"3\">\n");
	print("<TR><TD COLSPAN=\"2\"><B>Algemeen:</B></TD></TR>\n");
	print("<TR><TD WIDTH=25% VALIGN=top>Titel kaartlaag</TD><TD >\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "DATASET_TITEL" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Alternatieve titel</TD><TD>\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "ALT_TITEL" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Omschrijving kaartlaag</TD><TD>\n");
	$sCode = F_SELECTRECORD("SELECT OMSCHRIJVING_CODE FROM DATASET WHERE DATACODE = " . $sDatacode);
	print(F_GBIMEMOBOX("MEMOTABEL" . "|" . "TEKST" . "|" . "CODE=" . $sCode));
    
	print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Algemene opmerking</TD><TD>\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "OPMERKING" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Referentie datum</TD><TD>\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "BRONDATUM" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Brondatum</TD><TD>\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "OPBOUWDATUM" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Bronvermelding</TD><TD>\n");
	print(F_GBITEXTBOX("GEOGRAFISCH" . "|" . "BRONVERMELDING" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Opbouwmethode</TD><TD>\n");
	print(F_GBILISTBOXTYPE("GEOGRAFISCH" . "|" . "OPBOUWMETHODE" . "|" . "DATACODE=" . $sDatacode . "|" . "OPBOUWMETHODE"));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Gebeurtenis</TD><TD>\n");
	print(F_GBILISTBOXTYPE("DATASET" . "|" . "ACTIE" . "|" . "DATACODE=" . $sDatacode . "|" . "ACTIE"));
    print("</TD></TR>\n");
		
	print("<TR><TD WIDTH=25% VALIGN=top>Status</TD><TD>\n");
	print(F_GBILISTBOXTYPE("DATASET" . "|" . "STATUS" . "|" . "DATACODE=" . $sDatacode . "|" . "STATUS"));
    print("</TD></TR>\n");
	print("</table>\n");
	
	print("<table WIDTH=95% cellpadding=5>\n");
	print("<TR><TD WIDTH=\"25%\" COLSPAN=\"2\"><br></TD></TR>\n");
	print("</table>\n");
	
	print("<table WIDTH=95% cellpadding=5 CELLSPACING=\"3\">\n");
	print("<TR><TD COLSPAN=\"2\"><B>Inhoud:</B></TD></TR>\n");
		
	print("<TR><TD WIDTH=25% VALIGN=top>Contactpersoon inhoud</TD><TD>\n");
	print(F_GBILISTBOX("DATASET" . "|" . "CONTACTPERSOON" . "|" . "DATACODE=" . $sDatacode . "|" . "CONTACT"));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Beleidsterrein</TD><TD>\n");
	print(F_GBILISTBOXTYPE("DATASET" . "|" . "BELEIDSVELD" . "|" . "DATACODE=" . $sDatacode . "|" . "BELEIDSTERREIN"));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Team</TD><TD>\n");
	print(F_GBILISTBOXTYPE("DATASET" . "|" . "TEAM" . "|" . "DATACODE=" . $sDatacode . "|" . "TEAM"));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Thema</TD><TD>\n");
	print(F_GBILISTBOXTYPE("DATASET" . "|" . "THEMA" . "|" . "DATACODE=" . $sDatacode . "|" . "THEMA"));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Gebruiksbeperkingen</TD><TD>\n");
	print(F_GBILISTBOXTYPE("DATASET" . "|" . "GEBRUIKSBEPERKING" . "|" . "DATACODE=" . $sDatacode . "|" . "BEPERKING"));
    print("</TD></TR>\n");

	print("<TR><TD WIDTH=25% VALIGN=top>Veiligheidsrestricties</TD><TD>\n");
	print(F_GBILISTBOXTYPE("DATASET" . "|" . "VEILIGHEID" . "|" . "DATACODE=" . $sDatacode . "|" . "BEPERKING"));
    print("</TD></TR>\n");	
	
	print("<TR><TD WIDTH=25% VALIGN=top>Toegangsrestricties</TD><TD>\n");
	print(F_GBILISTBOXTYPE("DATASET" . "|" . "JURIDISCH" . "|" . "DATACODE=" . $sDatacode . "|" . "JURIDISCH"));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Copyright</TD><TD>\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "COPYRIGHT" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Herzienings frequentie</TD><TD>\n");
	print(F_GBILISTBOXTYPE("DATASET" . "|" . "BIJHOUDING" . "|" . "DATACODE=" . $sDatacode . "|" . "HERZIENING"));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Toepassingsschaal</TD><TD>\n");
	print(F_GBILISTBOXTYPE("GEOGRAFISCH" . "|" . "SCHAAL" . "|" . "DATACODE=" . $sDatacode . "|" . "SCHAAL"));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Contact leverancier</TD><TD>\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "CONTACT_LEVERANCIER" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Trefwoorden</TD><TD>\n");
	print("<table cellpadding=\"10\" width=\"100%\">\n");
	print("<TR><TD VALIGN=top></TD><TD VALIGN=top></TD></TR>\n");
	print("<TR><TD VALIGN=top>\n");
	$sTrefwoorden = F_SELECTRECORD("SELECT TREFTEXT.TREFCODE, TREFTEXT.TREFWOORD FROM TREFCODE INNER JOIN TREFTEXT ON TREFCODE.TREFCODE = TREFTEXT.TREFCODE WHERE TREFCODE.DATACODE=" . $sDatacode . " ORDER BY TREFTEXT.TREFWOORD");
	$aRecords = explode("|", $sTrefwoorden);
	for ($iRecord = 0; $iRecord < count($aRecords); $iRecord++) {
		$sRecord = $aRecords[$iRecord];
		$aRecord = explode("^", $sRecord);
		print($aRecord[1] . "<br>\n");		
	}	
	print("</TD></TR></table>\n");
    print("</TD></TR>\n");

	print("<TR><TD WIDTH=25% VALIGN=top>Temporele dekking (begin datum)</TD><TD>\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "DEKKING_BEGIN" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Temporele dekking (eind datum)</TD><TD>\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "DEKKING_EIND" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("</table>\n");
		
	print("<table WIDTH=95% cellpadding=5>\n");
	print("<TR><TD WIDTH=\"25%\" COLSPAN=\"2\"><br></TD></TR>\n");
	print("</table>\n");
	
	print("<table WIDTH=95% cellpadding=5 CELLSPACING=\"3\">\n");
	print("<TR><TD COLSPAN=\"2\"><B>Items:</B></TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Standaarditem</TD><TD>\n");
	print(F_GBITEXTBOX("GEOGRAFISCH" . "|" . "STD_ITEM" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	$sItem = F_SELECTRECORD( "SELECT STD_ITEM FROM GEOGRAFISCH WHERE DATACODE = "  . $sDatacode);
	$sCode = F_SELECTRECORD( "SELECT ITEMDEFINITIE FROM ITEMS WHERE ITEMS.DATACODE = " . $sDatacode . " AND ITEMS.ITEMNAAM = '" . $sItem . "'");
	
	
	print("<TR><TD WIDTH=25% VALIGN=top>Items</TD><TD>\n");
	$sRecords = F_SELECTRECORD("SELECT VOLGNR, ITEMNAAM, ITEMDEFINITIE FROM ITEMS WHERE DATACODE=" . $sDatacode . " ORDER BY VOLGNR");
    
	unset($aRecords);
	if ($sRecords <> "") {
		$aRecords = explode("|", $sRecords);
	}
	
	print("<table WIDTH=100% cellpadding=3>\n");
	print("<TR><TD>Volgnummer</TD><TD>Kolomnaam</TD><TD>Kolomdefinitie</TD></TR>\n");
	
	if (isset($aRecords)) {
		for ($iRecord = 0; $iRecord < count($aRecords); $iRecord++) {
			$sRecord = $aRecords[$iRecord];
			$aRecord = explode("^", $sRecord);
			$sVolgnr     = $aRecord[0];
			$sKolomnaam = $aRecord[1];
			$sKolomdef = $aRecord[2];
			
			print("<TR>\n");
			print("<TD VALIGN=top>" . F_GBITEXTBOX("ITEMS" . "|" . "VOLGNR" . "|" . "VOLGNR=" . $sVolgnr . " AND DATACODE=" . $sDatacode) . "</TD><TD VALIGN=top>" . F_GBITEXTBOX("ITEMS" ."|" . "ITEMNAAM" . "|" . "VOLGNR=" . $sVolgnr . " AND DATACODE=" . $sDatacode) . "</TD><TD VALIGN=top>" . F_GBITEXTBOX("ITEMS" . "|" . "ITEMDEFINITIE" . "|" . "VOLGNR=" . $sVolgnr . " AND DATACODE=" . $sDatacode) . "</TD><TD>\n");

			if (@$_SESSION["GBIPRO"] == "") {
				print("<A HREF='#' ONCLICK=\"execserver('?e=@GBIEXEC&p=DELETE" . "|" . "ITEMS" . "|" . "VOLGNR=" . $sVolgnr . " AND DATACODE=" . $sDatacode . "');\">");
				print("<IMG SRC='images\wissen1.png' ALT='Verwijderen' BORDER=0 HEIGHT=20 WIDTH=20></A>\n");
			}
			print("</TD>\n");
			print("</TR>\n");
			print("</TR>\n");
		}
	}
                                
	if (@$_SESSION["GBIPRO"] == "") {
		print ("<TR><TD>\n");
		print ("<A HREF='#' ONCLICK=" . "\"" . "execserver('?e=@GBIEXEC&p=INSERT" . "|" . "ITEMS" . "|" . "DATACODE" . "|" . $sDatacode . "'); document.location.reload();" . "\"" . ">\n");
		print ("<IMG SRC='images\maak.png' ALT='Toevoegen' BORDER=0 HEIGHT=20 WIDTH=20></A>\n");
		print ("</TD><TD></TD><TD></TD><TD></TD></TR>\n"); 
	}
	print("</table>\n");
	print("</TD></TR>\n");
	print("</table>\n");
		
	print("<table WIDTH=95% cellpadding=5>\n");
	print("<TR><TD WIDTH=\"25%\" COLSPAN=\"2\"><br></TD></TR>\n");
	print("</table>\n");
	
	print("<table WIDTH=95% cellpadding=5 CELLSPACING=\"3\">\n");
	print("<TR><TD COLSPAN=\"2\"><B>Metadata:</B></TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Contactpersoon metadata</TD><TD >\n");
	print(F_GBILISTBOXMETA("DATASET" . "|" . "METAPERSOON" . "|" . "DATACODE=" . $sDatacode . "|" . "CONTACT"));
	print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Metadata datum</TD><TD>\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "INVOERDATUM" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Taal kaartlaag</TD><TD>\n");
	print(F_GBITEXTBOXVAST("DATASET" . "|" . "TAAL" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Karakterset</TD><TD>\n");
	print(F_GBITEXTBOXVAST("DATASET" . "|" . "KARAKTERSET" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Metadatastandaard</TD><TD>\n");
	print(F_GBITEXTBOXVAST("DATASET" . "|" . "METADATASTD" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Versie metadatastandaard</TD><TD>\n");
	print(F_GBITEXTBOXVAST("DATASET" . "|" . "VERSIE_METASTD" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Code referentiesysteem</TD><TD>\n");
	print(F_GBITEXTBOXVAST("DATASET" . "|" . "CODE_REF" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Organisatie referentiesysteem</TD><TD>\n");
	print(F_GBITEXTBOXVAST("DATASET" . "|" . "ORG_NAMESPACE" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
		
	print("<TR><TD WIDTH=25% VALIGN=top>Contactpersoon ditributie</TD><TD>\n");
	print(F_GBILISTBOXGEO("DATASET" . "|" . "GEOLOKET" . "|" . "DATACODE=" . $sDatacode . "|" . "CONTACT"));
    print("</TD></TR>\n");
	print("</table>\n");
	
	print("<table WIDTH=95% cellpadding=5>\n");
	print("<TR><TD WIDTH=\"25%\" COLSPAN=\"2\"><br></TD></TR>\n");
	print("</table>\n");
	
	print("<table WIDTH=95% cellpadding=5 CELLSPACING=\"3\">\n");
	print("<TR><TD COLSPAN=\"2\"><B>Specifiek:</B></TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Geografisch gebied</TD><TD>\n");
	print(F_GBILISTBOXTYPE("GEOGRAFISCH" . "|" . "DEELGEBIED" . "|" . "DATACODE=" . $sDatacode . "|" . "GEBIED"));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Aanvullende informatie</TD><TD>\n");
	print(F_GBIMEMOBOX("DATASET" . "|" . "AANVUL_INFO" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Ruimtelijk schema</TD><TD>\n");
	print(F_GBILISTBOXTYPE("DATASET" . "|" . "RSCHEMA" . "|" . "DATACODE=" . $sDatacode . "|" . "RSCHEMA"));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Bestandsnaam</TD><TD>\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "NAAM" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Fysieke locatie</TD><TD>\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "FYSIEKE_LOCATIE" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Datatype</TD><TD>\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "DATATYPE" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Geometrie</TD><TD>\n");
	print(F_GBITEXTBOX("GEOGRAFISCH" . "|" . "GEOMETRIE" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");	
	
	print("<TR><TD WIDTH=25% VALIGN=top>Nauwkeurigheid</TD><TD>\n");
	print(F_GBITEXTBOX("GEOGRAFISCH" . "|" . "POS_NAUWKEURIGHEID" . "|" . "DATACODE=" . $sDatacode));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Hi&euml;rarchieniveau</TD><TD>\n");
	print(F_GBITEXTBOX("DATASET" . "|" . "KWALITEIT_BESCH" . "|" . "DATACODE=" . $sDatacode . "|" . "SCOPECODE"));
    print("</TD></TR>\n");
	
	$sGebied = F_SELECTRECORD( "SELECT DEELGEBIED FROM GEOGRAFISCH WHERE DATACODE = "  . $sDatacode);	
		
	print("<TR><TD WIDTH=25% VALIGN=top>Minimale x-co&ouml;rdinaat</TD><TD>\n");
	print(F_GBITEXTBOX("GEBIED" . "|" . "MIN_X" . "|" . "GEBIED='" . $sGebied . "'"));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Maximale x-co&ouml;rdinaat</TD><TD>\n");
	print(F_GBITEXTBOX("GEBIED" . "|" . "MAX_X" . "|" . "GEBIED='" . $sGebied . "'"));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Minimale y-co&ouml;rdinaat</TD><TD>\n");
	print(F_GBITEXTBOX("GEBIED" . "|" . "MIN_Y" . "|" . "GEBIED='" . $sGebied . "'"));
    print("</TD></TR>\n");
	
	print("<TR><TD WIDTH=25% VALIGN=top>Maximale y-co&ouml;rdinaat</TD><TD>\n");
	print(F_GBITEXTBOX("GEBIED" . "|" . "MAX_Y" . "|" . "GEBIED='" . $sGebied . "'"));
    print("</TD></TR>\n");
	
	print("</table>\n");	
	print("</center>\n");
	print("</div>\n");   
	print("</div>\n");   
	print("<br>\n");
}

if ($sAction == "MAAKKAART") {
	F_LOG("Maak een kaart");
	F_STYLE();
	print ("<Script Language=\"JavaScript\">");
	print ("function laadkaart() {");
	print ("waarde = document.forms[0]['kaart'].value;");
	print ("url = 'http://www.drenthe.info/kaarten/website/geoportaal/kaart.php?e=@KAART&title=Geoportaal | Provincie Drenthe&p=' + waarde;");
	//print ("$(\".extlink\").trigger('click');\n");
	print("$.fancybox({\n");
			print("'href' : url,\n");
        	//print("'autoDimensions'	: false,\n");
			print("'width'         		: '90%',\n");
			print("'height'        		: '90%',\n");
			print("'transitionIn'		: 'none',\n");
			print("'transitionOut'		: 'none',\n");
			print("'type' : 'iframe'\n");
			//print("'href' : 'http://www.drenthe.nl'\n");
		print("});\n");	
	print("return false;\n");		
	
	//print ("('body').append('<a class=\"extlink\" href=url></a>');");
	//print ("var load = window.open (url,'', 'left=0,top=0');" );
	//print ("var load = window.open('http://www.google.com','','scrollbars=no,menubar=no,height=600,width=800,resizable=yes,toolbar=no,location=no,status=no');");
	print ("}");
	
	
	print ("</Script>");	
	print("<script type=\"text/javascript\" src=\"js/selectbox.js\"></script>\n");
	print("<div class=\"linkerkolom\">\n");
	
	print("<div class=\"broodkruimel\">\n");
	print("<a href=\"http://www.provincie.drenthe.nl\">Home</a><span class=\"tussenteken\"> </span><a href=\"index.php\">Geoportaal</a><span class=\"tussenteken\"> </span>Maak een kaart");
	print("</div>\n");
	print("<br>\n");
	
	print("<br>\n");
    print("<h2>MAAK EEN KAART</h2>\n");    
	print("<div class=\"nieuwsberichten\">\n");
    print("<div class=\"nieuwsitem\">\n");
	print("<br>\n");
    print("<div class=\"inhoud\">\n");
    print("<div class=\"tekst1\">\n");
	print("Selecteer een kaartlaag en druk op de knop toevoegen. Doe dit voor alle kaartlagen die u wilt zien.<br>\n");
	print("Druk nu op de knop <STRONG>Maak een Kaart</STRONG> om uw kaart te maken.\n");      
	print("</div>\n");	
	print("</div>\n");	
	
	//print("<div class=\"formulier\">\n");
	print("<br>\n");
	print("<br>\n");
	print("<div class=\"invoer\">\n");
		
	print("<FORM METHOD=POST ACTION=\"kaart.php?e=@KAART&p=" . @$_POST["KAART"] . "\">");	
	//@$_POST["KAART"];	
	//print("<FORM METHOD=POST ACTION=\"?e=@GBILEGENDKAART\">\n");
		
	print("<table cellpadding=\"0\" width=\"100%\">\n");
	print("<TR><TD VALIGN=top></TD><TD VALIGN=top></TD><TD VALIGN=top></TD></TR>\n");
	print("<TR><TD VALIGN=top WIDTH=40%>\n");
	
	if (@$_SESSION["PROVINCIE"] == TRUE) {
		$sDatasets = F_SELECTRECORD("SELECT ALT_TITEL, DATASET_TITEL FROM DATASET WHERE TYPE = 1 ORDER BY DATASET_TITEL");
	}
	else {
		$sDatasets = F_SELECTRECORD("SELECT ALT_TITEL, DATASET_TITEL FROM DATASET WHERE TYPE = 1 AND VEILIGHEID = 'vrij toegankelijk' ORDER BY DATASET_TITEL");
	}

	
	$aRecords = explode("|", $sDatasets);
	print("<SELECT NAME=\"list1\" SIZE=5 ONDBLCLICK=\"moveSelectedOptions(this.form['list1'],this.form['list2'],true)\">\n");	
		
	for ($iRecord = 0; $iRecord < count($aRecords); $iRecord++) {
		$sRecord = $aRecords[$iRecord];
		$aRecord = explode("^", $sRecord); 
		print( "<OPTION VALUE=" . "\"" . $aRecord[0] . ";" . $aRecord[1] . "\"" . ">" . $aRecord[1] . "</OPTION>\n");		
	}                     
     
	print("</SELECT>\n");
	
	print("</TD>");
	
	print ("<TD ALIGN=\"center\" WIDTH=20% ID=\"kopnav\">");
	print ("<A HREF=\"#\" onClick=\"moveSelectedOptions(document.forms[0]['list1'],document.forms[0]['list2'],false);return false;\" class=\"leesverder\">Toevoegen</A><br><br>");
	print ("<A HREF=\#\" onClick=\"moveSelectedOptions(document.forms[0]['list2'],document.forms[0]['list1'],false); return false;\" class=\"leesterug\">Verwijderen</A><br><br>"); 
	
	print ("</TD>");
	
	print ("<TD VALIGN=top WIDTH=40%>\n");
	
	print ("<SELECT TYPE=\"TEXT\" NAME=\"list2\" SIZE=5 onDblClick=\"moveSelectedOptions(this.form['list2'],this.form['list1'],true)\">");
	print ("</SELECT>");

	
	//print("<SELECT STYLE=" . "\"" . "{width: 300px; height: 500px; font-family: Georgia, Times, serif;}" . "\"" . " NAME=" . "'PVVPS2'" . " SIZE=5 MULTIPLE ONDBLCLICK=" . "\"" . "if(this.selectedIndex >=0)  { var optObj = document.createElement('option'); optObj.text=this(selectedIndex).text; optObj.value=this(selectedIndex).value; PVVPS1.options.add(optObj); this.remove(this.selectedIndex);  } " . "\"" . ">\n");	
	
	//print("</SELECT>\n");
	print("</TD></TR></table>\n");
	print("<br>\n");
	print("<br>\n");
	
	print ("&nbsp;&nbsp;<INPUT TYPE=\"text\" STYLE=\"font-family: Georgia, Times, serif; width: 300px; visibility:hidden\" WIDTH=\"300px\"NAME=\"kaart\" VALUE=\"\">");
	
	
	print("</div>\n");
	print("<div class=\"knoppen\">\n");
	//print("<LI>Zoeken naar&nbsp;&nbsp;<INPUT SIZE=30 TYPE=TEXT NAME='KEYWORDS'>\n");
	//print("&nbsp;&nbsp;<input id=submit type=submit value=Zoek name=submit></input>\n");
	
	//print ("<A HREF=\"javascript:laadkaart();\">Maak een kaart</A>");
	
	print("&nbsp;&nbsp;<input type=button value='Maak een Kaart' onClick='laadkaart();'></input>\n");
	
	
	print("</FORM>\n");
	print("</div>\n");
	print("</div>\n");
	print("</div>\n");
	print("</div>\n");
	
	//print("</div>\n");
	print("<br>\n");	
	F_ENDSTYLE();
}


if ($sAction == "AFDRUK") {
	print("<br><br><br>\n");
	print("<center>\n");	
	
	print("<table BORDER=\"0\" CELLSPACING=\"10\">\n");
	print("<TR><TD VALIGN=\"MIDDLE\" WIDTH=\"600px\" class=\"kopbegin\">\n");
	print("<center>Afdrukopties</center>\n");
	print("</TD></TR></table></center>\n");
	print("<br>\n");	
	
	print("<center>\n");
	print("<table BORDER=\"0\" CELLSPACING=\"10\">\n");
	print("<TR><TD VALIGN=\"MIDDLE\" WIDTH=\"600px\">\n");
	if (@$_SESSION["GBIMUTEREN"] == TRUE) {
		print("<LI><A HREF='?e=@PRINT&p=TOTAL'>PDF met alle kaartlagen</A><br><br>\n");
		print("<FORM NAME=FRMCOPY METHOD=POST ACTION=\"?e=@PRINT&p=SUB\">\n");
		print("<LI>Selecteer een dataset &nbsp;&nbsp;<INPUT SIZE=20 TYPE=TEXT NAME='COPY'>\n");
		print("&nbsp;&nbsp;<input id=submit type=submit value=Kopieëren name=submit></input>\n");
		print("</FORM>\n");			
	}	
	
	print("<br>\n");
	print("<br>\n");
	print("<br>\n");
	print("<br>\n");
	print("<br>\n");
	print("<br>\n");
	print("<br>\n");
	print("<br>\n");
	print("<br>\n");
	print("<br>\n");
	print("<br>\n");
	print("<br>\n");
	print("<br>\n");
	
	print("</TD></TR></table>\n");
	print("</center>\n");	
}


if ($sAction == "ADMIN") {
	print("<br><br><br>\n");
	print("<center>\n");		
	print("<table BORDER=\"0\" CELLSPACING=\"10\">\n");
	print("<TR><TD VALIGN=\"MIDDLE\" WIDTH=\"800px\" class=\"kopbegin\">\n");
	print("<center>Administratieve taken</center>\n");
	print("</TD></TR></table></center>\n");
	print("<br>\n");	
}


}

function F_GBIPDFTEXT($p) {
	$tokens = explode("|", $p);
	$sTable = $tokens[0];
	$sField = $tokens[1];
	$sWhere = $tokens[2];
	
	$sRecordValue = F_SELECTRECORD("SELECT " . $sField . " FROM " . $sTable . " WHERE " . $sWhere);
			
	$sRoContent = str_replace("^", " ", $sRecordValue);	
	
	if ($sRoContent == "") {
		$sRoContent = "niet ingevuld";
	}
	
	if (strtotime($sRoContent) == TRUE)	{
		$dt = strtotime($sRoContent);
		$corrected_date=BST_finder($dt);	
		$sRoContent = date("d-m-Y", $corrected_date);
	}		
	return $sRoContent;	
}

function BST_finder ($dt) { 
    $BSTstart=strtotime("last Sunday",gmmktime(0,0,0,4,1)); // plus 3600 seconds to make it 1 a.m.
    $BSTend=strtotime("last Sunday",gmmktime(0,0,0,11,1));
    if ($dt>=$BSTstart and $dt<=$BSTend) {
        $dt=$dt;
    } 
    return $dt;
}

function F_GBITEXTBOX($p) {
	$tokens = explode("|", $p);
	$sTable = $tokens[0];
	$sField = $tokens[1];
	$sWhere = $tokens[2];
	
	$sRecordValue = F_SELECTRECORD("SELECT " . $sField . " FROM " . $sTable . " WHERE " . $sWhere);
	
	$sRoContent = str_replace("^", " ", $sRecordValue);
	
	if ($sRoContent == "") {
		$sRoContent = "niet ingevuld";
	}
	
	if (strtotime($sRoContent) == TRUE)	{
		$dt = strtotime($sRoContent);
		$corrected_date=BST_finder($dt);	
		$sRoContent = date("d-m-Y", $corrected_date);
	}	
	
	return $sRoContent;
}

function F_GBIMEMOBOX($p) {
	$tokens = explode("|", $p);
	$sTable = $tokens[0];
	$sField = $tokens[1];
	$sWhere = $tokens[2];
	
	$sRecordValue = F_SELECTRECORD( "SELECT " . $sField . " FROM " . $sTable . " WHERE " . $sWhere);
		
	$sRoContent = $sRecordValue;
	$sBoxCode = "<TEXTAREA STYLE=\"border; groove; border-style: solid; border-width: 1px; border-color: #000000; width: 100%\" TYPE=\"TEXT\" STYLE=\"{width: 200px}\" ID=txt" . $sField . " NAME=txt" . $sField . " COLS=\"22\" ROWS=\"5\" onchange=\"execserver('?e=@GBIEXEC&p=UPDATE" . "|" . $sTable . "|" . $sField . "|" . $sWhere . "|" . "' + this.value);\">" . $sRecordValue . "</TEXTAREA>";
	
	if (@$_SESSION["GBIPRO"] != "") {
		return $sRoContent;
	}
	else {
		return $sBoxCode;
	}
}

function F_GBIOMSCHRIJF($p) {
	$tokens = explode("|", $p);
	$sTable = $tokens[0];
	$sField = $tokens[1];
	$sWhere = $tokens[2];
	
	$sRecordValue = F_SELECTRECORD( "SELECT " . $sField . " FROM " . $sTable . " WHERE " . $sWhere);
		
	$sRoContent = $sRecordValue;
	
	return $sRoContent;
}

function F_GBIMEMOBOXMORE($p) {
	$tokens = explode("|", $p);
	$sTable = $tokens[0];
	$sField = $tokens[1];
	$sWhereOne = $tokens[2];
	$sWhereTwo = $tokens[3];
	
	
	
	$sRecordValue = F_SELECTRECORD( "SELECT " . $sField . " FROM " . $sTable . " WHERE DATACODE= " . $sWhereOne . " AND ITEMNAAM = '" . $sWhereTwo . "'");
		
	$sRoContent = $sRecordValue;
	$sBoxCode = "<TEXTAREA STYLE=\"border; groove; border-style: solid; border-width: 1px; border-color: #000000; width: 100%\" TYPE=\"TEXT\" STYLE=\"{width: 200px}\" ID=txt" . $sField . " NAME=txt" . $sField . " COLS=\"22\" ROWS=\"5\" onchange=\"execserver('?e=@GBIEXEC&p=UPDATEMORE" . "|" . $sTable . "|" . $sField . "|" . $sWhereOne . "|" . $sWhereTwo . "|" . "' + this.value);\">" . $sRecordValue . "</TEXTAREA>";
	
	if (@$_SESSION["GBIPRO"] != "") {
		return $sRoContent;
	}
	else {
		return $sBoxCode;
	}
}

function F_GBILISTBOXTYPE($p) {
	$tokens = explode("|", $p);
	$sTable = $tokens[0];
	$sField = $tokens[1];
	$sWhere = $tokens[2];
	$sLookup = $tokens[3];	
	
	$sRecordValue = F_SELECTRECORD( "SELECT " . $sField . " FROM " . $sTable . " WHERE " . $sWhere);
	
	if ($_SESSION["GBIPRO"] != "") {
		return $sRecordValue;
	}
	else {
		$sQuery = F_SELECTRECORD("SELECT * FROM " . $sLookup);
			
		$aRecords = explode("|", $sQuery);
		
		print ("<SELECT onchange=" . "\"" . "execserver('?e=@GBIEXEC&p=UPDATE" . "|" . $sTable . "|" . $sField . "|" . $sWhere . "|" . "' + this.value); document.location.reload();" . "\"" . ">\n");
		print ("<OPTION VALUE=" . "\"" . "\"" . "></OPTION>\n");
		
		for ($iRecord = 0; $iRecord < count($aRecords); $iRecord++) {
			$sRecord = $aRecords[$iRecord];
			$aRecord = explode("^", $sRecord);
			$sSoort = $aRecord[0];
			
			if ($sRecordValue == $sSoort) {
				print ("<OPTION VALUE=" . "\"" . $sSoort . "\"" . " SELECTED>" . $sSoort . "</OPTION>\n");
			}
			else { 
				print ("<OPTION VALUE=" . "\"" . $sSoort . "\"" . ">" . $sSoort . "</OPTION>\n");
			}			
		}
		print ("</SELECT>\n");
	}
}

function F_GBILISTBOX($p) {
	$tokens = explode("|", $p);
	$sTable = $tokens[0];
	$sField = $tokens[1];
	$sWhere = $tokens[2];
	$sLookup = $tokens[3];
	
	$sRecordValue = F_SELECTRECORD( "SELECT " . $sField . " FROM " . $sTable . " WHERE " . $sWhere);
	
	if (@$_SESSION["GBIPRO"] != "") {
		$sQuery = F_SELECTRECORD("SELECT CONTACTPERSOON FROM " . $sLookup . " WHERE CONTACT_ID = " . $sRecordValue);
		return $sQuery;
	}
	else {	
		$sQuery = F_SELECTRECORD("SELECT CONTACT_ID, CONTACTPERSOON FROM " . $sLookup);
		$aRecords = explode("|", $sQuery);
		
		print ("<SELECT onchange=" . "\"" . "execserver('?e=@GBIEXEC&p=UPDATE" . "|" . $sTable . "|" . $sField . "|" . $sWhere . "|" . "' + this.value); document.location.reload();" . "\"" . ">\n");
		print ("<OPTION VALUE=" . "\"" . "\"" . "></OPTION>\n");
		
		for ($iRecord = 0; $iRecord < count($aRecords); $iRecord++) {
			$sRecord = $aRecords[$iRecord];
			$aRecord = explode("^", $sRecord);
			$sId = $aRecord[0];
			$sSoort = $aRecord[1];
			
			if ($sRecordValue == $sId) {
				print ("<OPTION VALUE=" . "\"" . $sId . "\"" . " SELECTED>" . $sSoort . "</OPTION>\n"); 
			}
			else {
				print ("<OPTION VALUE=" . "\"" . $sId . "\"" . ">" . $sSoort . "</OPTION>\n");
			}			
		}
		print ("</SELECT>\n");
	}
}

function F_GBITEXTBOXVAST($p) {
	$tokens = explode("|", $p);
	$sTable = $tokens[0];
	$sField = $tokens[1];
	$sWhere = $tokens[2];
	
	$sRecordValue = F_SELECTRECORD("SELECT " . $sField . " FROM " . $sTable . " WHERE " . $sWhere);
	
	$sRoContent = $sRecordValue;
	$sBoxCode = "<INPUT STYLE='border; groove; border-style: solid; background-color: #FFFDE0; border-width: 1px; border-color: #000000; width: 100%' TYPE=TEXT STYLE=" . "\"" . "{width: 200px}" . "\"" . " ID=txt" . $sField . " NAME=fld" . $sTable . "|" . $sField . " VALUE=" . "\"" . $sRecordValue . "\"" . " onchange=" . "\"" . "execserver('?e=@GBIEXEC&p=UPDATE" . "|" . $sTable . "|" . $sField . "|" . $sWhere . "|" . "' + this.value);" . "\"" . ">";
	
	if (@$_SESSION["GBIPRO"] != "") {
		return $sRoContent;
	}
	else {
		return $sBoxCode;
	}
}

function F_GBILISTBOXMETA($p) {
	$tokens = explode("|", $p);
	$sTable = $tokens[0];
	$sField = $tokens[1];
	$sWhere = $tokens[2];
	$sLookup = $tokens[3];	
	
	$sRecordValue = F_SELECTRECORD( "SELECT " . $sField . " FROM " . $sTable . " WHERE " . $sWhere);

	if (@$_SESSION["GBIPRO"] != "") {
		if ($sRecordValue == "") {
			return "-";
		}
		else {
			$sQuery = F_SELECTRECORD("SELECT CONTACTPERSOON FROM " . $sLookup . " WHERE CONTACT_ID = " . $sRecordValue);
			return $sQuery;			
		}
	}
	else {	
		$sQuery = F_SELECTRECORD("SELECT CONTACT_ID, CONTACTPERSOON FROM " . $sLookup);
		$aRecords = explode("|", $sQuery);
		
		print ("<SELECT onchange=" . "\"" . "execserver('?e=@GBIEXEC&p=UPDATE" . "|" . $sTable . "|" . $sField . "|" . $sWhere . "|" . "' + this.value); document.location.reload();" . "\"" . ">\n");
		print ("<OPTION VALUE=" . "\"" . "\"" . "></OPTION>\n");
		
		for ($iRecord = 0; $iRecord < count($aRecords); $iRecord++) {
			
			$sRecord = $aRecords[$iRecord];
			$aRecord = explode("^", $sRecord);
			$sId = $aRecord[0];
			$sSoort = $aRecord[1];
			
			if ($sRecordValue == $sId) {
				print ("<OPTION VALUE=" . "\"" . $sId . "\"" . " SELECTED>" . $sSoort . "</OPTION>\n");
			}
			elseif ($sId == 26) {
				print ("<OPTION VALUE=" . "\"" . $sId . "\"" . ">" . $sSoort . "</OPTION>\n");
			}
			elseif ($sId == 15) {
				print ("<OPTION VALUE=" . "\"" . $sId . "\"" . ">" . $sSoort . "</OPTION>\n");
			}			
		}
		print ("</SELECT>\n");
	}
}

function F_GBILISTBOXGEO($p) {
	$tokens = explode("|", $p);
	$sTable = $tokens[0];
	$sField = $tokens[1];
	$sWhere = $tokens[2];
	$sLookup = $tokens[3];	
	
	$sRecordValue = F_SELECTRECORD( "SELECT " . $sField . " FROM " . $sTable . " WHERE " . $sWhere);
	
	if (@$_SESSION["GBIPRO"] != "") {
		if ($sRecordValue == "") {
			return "-";
		}
		else {
			$sQuery = F_SELECTRECORD("SELECT CONTACTPERSOON FROM " . $sLookup . " WHERE CONTACT_ID = " . $sRecordValue);
			return $sQuery;
		}
	}
	else {	
		$sQuery = F_SELECTRECORD("SELECT CONTACT_ID, CONTACTPERSOON FROM " . $sLookup);
		$aRecords = explode("|", $sQuery);
		
		print ("<SELECT onchange=" . "\"" . "execserver('?e=@GBIEXEC&p=UPDATE" . "|" . $sTable . "|" . $sField . "|" . $sWhere . "|" . "' + this.value); document.location.reload();" . "\"" . ">\n");
		print ("<OPTION VALUE=" . "\"" . "\"" . "></OPTION>\n");
		
		for ($iRecord = 0; $iRecord < count($aRecords); $iRecord++) {
			$sRecord = $aRecords[$iRecord];
			$aRecord = explode("^", $sRecord);
			$sId = $aRecord[0];
			$sSoort = $aRecord[1];
			
			if ($sRecordValue == $sId) {
				print ("<OPTION VALUE=" . "\"" . $sId . "\"" . " SELECTED>" . $sSoort . "</OPTION>\n");
			}
			elseif ($sId == 36) {
				print ("<OPTION VALUE=" . "\"" . $sId . "\"" . ">" . $sSoort . "</OPTION>\n");
			}
			elseif ($sId == 68) {
				print ("<OPTION VALUE=" . "\"" . $sId . "\"" . ">" . $sSoort . "</OPTION>\n");
			}			
		}
		print ("</SELECT>\n");
	}
}

function F_SELECTRECORD($p) {
//global $DBCON;
global $db, $dsn;

$db->Connect($dsn); 

//$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
$query = $p;
//print $query;

$result = $db->Execute($query);

if ($result == false) {		
	die ('error');
}
 	
$res = "";


if (!$result->EOF) {
		$iCount = $result->FieldCount();
		$iCountMin1 = $iCount - 1;
}
	
	
while (!$result->EOF) {
	for ($iCol = 0; $iCol <= $iCountMin1; $iCol++) {
		$res = $res . $result->fields[$iCol];
		if ($iCol < $iCountMin1) {
			$res = $res . "^";
		}
	}
	$result->MoveNext();
	
	if (!$result->EOF) {
		$res = $res . "|";
	}
}	

return $res;		
}

function F_ISWRITABLE($p) {
$tokens = explode("|", $p);       
if ($_SESSION["GBIMUTEREN"] == TRUE) {
	return TRUE;
}
else {
	return FALSE;
}
}

function F_GBILEGENDKAART() {
$sFilterValue = @$_POST["KAART"];
if ($sFilterValue <> "" AND $sFilterValue <> NULL) {
	//print($sFilterValue);	
	$tokens = explode("|", $sFilterValue);
	$sarxml = new ArcService("paros", "GDB_Geoportaal");
	for ($iRecord = 0; $iRecord < count($tokens); $iRecord++) {
		$sDataset = $tokens[$iRecord];
		$sImsid = explode(".", $sDataset);
		$sImglink = substr($sarxml->Legend($sImsid[1]), 39);
		if ($iRecord == (count($tokens) - 1)) {
			$Laag = $Laag . $sImsid[1];
			$sImglink1 = $sImglink1 . $sImglink;
		}
		else {
			$Laag = $Laag . $sImsid[1] . ",";
			$sImglink1 = $sImglink1 . $sImglink . ",";
		}		
	}
		
	print ("<SCRIPT>");
	//print("<A HREF=\"kaart.php?e=@KAART&p=" . $aRecord[2] . "\" TITLE=\"" . $aRecord[1]. "\" class=\"extlink\">Bekijk kaartlaag</A>");
	print ("self.location = \"kaart.php?e=@KAARTFULL&laag=geoportaal&title=\"Maak een kaart\"&vis=" . $Laag . "&gdb_legend=" . $sImglink1 . "\"");
	print ("</SCRIPT>");
		
	//print ("<SCRIPT>");
	//print ("self.location = \"?e=@GBIKAARTFULL&&laag=geoportaal&vis=" . $Laag . "&gdb=" . $Laag . "&gdb_legend=" . $sImglink1 . "\"");
	//print ("</SCRIPT>");
}
}

function F_BODEMKAART($p) {
if ($p <> "" AND $p <> NULL) {
	$tokens = explode("|", $p);
	$sarxml = new ArcService("paros", "GDB_Geoportaal");
	for ($iRecord = 0; $iRecord < count($tokens); $iRecord++) {
		$sDataset = $tokens[$iRecord];
		$sImsid = explode(".", $sDataset);
		$sImglink = substr($sarxml->Legend($sImsid[1]), 39);
		print $sImglink;
		if ($iRecord == (count($tokens) - 1)) {
			$Laag = $Laag . $sImsid[1];
			$sImglink1 = $sImglink1 . $sImglink;
			print $sImglink1;
		}
		else {
			$Laag = $Laag . $sImsid[1] . ",";
			$sImglink1 = $sImglink1 . $sImglink . ",";
			print $sImglink1;
		}		
	}
					
	//print ("hallo");				
	print ($sImglink1);
	//print ("<SCRIPT>");
	//print ("self.location = \"?e=@GBIKAARTFULL&laag=geoportaal&vis=" . $Laag . "&gdb_legend=" . $sImglink1 . "\"");
	//print ("</SCRIPT>");
}
}

function F_GBILEGEND($p) {
$tokens = explode("|", $p);
if (count($tokens) >= 2) {
		$sWaarde = $tokens[0];
		$sDatacode = $tokens[1];
		$sKaart = $tokens[2];
		$sPosition = $tokens[3];
		$sLaag = $tokens[4];
		$sSearch = $tokens[5];
}
$sarxml = new ArcService("paros", "GDB_Geoportaal");
$sImsid = explode(".", $sLaag);
$sLaag = $sImsid[1];
$sImglink = substr($sarxml->Legend($sLaag), 39);

if ($sWaarde == "DATASETLIST") {
	print ("<SCRIPT>");
	
	print ("self.location = \"?e=@GBIKAART&p=DATASETLIST|" . $sDatacode . "|" . $sKaart . "|" . $sPosition . "|" . $sLaag . "|" . $sImglink . "|" . $sSearch . "|&laag=geoportaal&vis=" . $sLaag . "&gdb=" . $sKaart . "&gdb_legend=" . $sImglink . "\"");
	print ("</SCRIPT>");
	}
else {
	print ("<SCRIPT>");
	print ("self.location = \"?e=@GBIKAART&p=SEARCHLIST| " . $sDatacode . "|" . $sKaart . "|" . $sPosition . "|" . $sLaag . "|" . $sImglink . "|" . $sSearch . "|&laag=geoportaal&vis=" . $sLaag . "&gdb=" . $sKaart . "&gdb_legend=" . $sImglink . "\"");
	print ("</SCRIPT>");		
}
}

function F_GBIKAART($p) {
$tokens = explode("|", $p);       
if (count($tokens) >= 2) {
		$sWaarde = $tokens[0];
		$sDatacode = $tokens[1];
		$sKaart = $tokens[2];
		$sPosition = $tokens[3];
		$sLaag = $tokens[4];
		$sImglink = $tokens[5];
		$sSearch = $tokens[6];
}
F_LOG("Bekijk kaart :" . $sKaart);

F_STYLE();
print("<div class=\"linkerkolom\">\n");
if ($sWaarde == "DATASETLIST") {
	print("<div class=\"broodkruimel\">\n");
	print("<a href=\"http://www.provincie.drenthe.nl\">Home</a><span class=\"tussenteken\"> </span><a href=\"index.php\">Geoportaal</a><span class=\"tussenteken\"> </span><a href=\"?e=@GBI&p=DATASETEDIT|" . $sDatacode . "|" . $sKaart . "|" . $sPosition . "|" . $sLaag . "|"  . "\">Gegevens kaartlaag</A><span class=\"tussenteken\"> </span>Kaart: $sKaart\n");
	print("</div>\n");
	print("<br>\n");	
	}
else {
	print("<div class=\"broodkruimel\">\n");
	print("<a href=\"http://www.provincie.drenthe.nl\">Home</a><span class=\"tussenteken\"> </span><a href=\"index.php\">Geoportaal</a><span class=\"tussenteken\"> </span><a href=\"?e=@GBI&p=SEARCHLIST|" . $sPosition . "|next|" . $sSearch . "\">Zoeken op trefwoord</a><span class=\"tussenteken\"> </span><a href=\"?e=@GBI&p=DATASETEDIT|" . $sDatacode . "|" . $sKaart . "|" . $sPosition . "|" . $sLaag . "|" . $sSearch . "\">Gegevens kaartlaag</A><span class=\"tussenteken\"> </span>Kaart: $sKaart\n");
	print("</div>\n");
	print("<br>\n");
}
print("</div>\n");

print("<script type=\"text/javascript\" src=\"js/swfobject.js\"></script>\n");
//print("<script type=\"text/javascript\" src=\"js/layers.js\"></script>\n");
print("<script type=\"text/javascript\" src=\"js/fscripts10.js\"></script>\n");
print("<style type=\"text/css\">\n");
print("#flashcontent { height: 100%; width: 100%; }\n");
print("</style>\n");
print("<body>\n");
print("<br>\n");
print("<center>\n");
print("<table class=\"tabelkader\">\n");
print("<tr>\n");
print("<td class=\"tabelkop\">\n");
$sImsid = explode(".", $sLaag);
print("<div class=\"nieuwsberichten\">\n");
print("<div class=\"geo\">\n");

$sAltTitel = F_SELECTRECORD("SELECT ALT_TITEL FROM DATASET WHERE DATACODE = " . $sDatacode);

print("<A HREF=\"kaart.php?e=@KAART&p=" . $sAltTitel . "\" TARGET=\"_blank\">Kaart in volledig scherm</A>");
//print("<A HREF=\"?e=@GBIKAARTFULL&laag=geoportaal&vis=" . $sImsid[1] . "&gdb=" . $sKaart . "&gdb_legend=" . $sImglink . "&full=true" . "\" TARGET=\"_blank\">Kaart in volledig scherm</A>");
print("</div>\n");
print("</td>\n");
print("</tr>\n");
print("<tr>\n");
print("<td class=\"tabelflash\">\n");
print("<div id=\"flashcontent\">\n");
print("</div>\n");
print("</td>\n");
print("</tr>\n");
print("</table>\n");
print("</center>\n");
print("<script type=\"text/javascript\">\n");
print("goFlamingo(\"../config\",\"geoportaal,locationfinder\");\n");
print("</script>");
//print("<script type=\"text/javascript\">\n");
//print("var so = new SWFObject(\"flamingo/flamingo.swf?config=../config/test.xml\", \"flamingo\", \"100%\", \"100%\", \"8\", \"#eaeaea\");\n");
//print("so.write(\"flashcontent\");\n");
//print("</script>\n");
print("<br>\n");
F_ENDSTYLE();

}

function F_GBIKAARTFULL () {
print("<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n");
print("<html>\n");
print("<head>\n");
print("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n");
print("<title>Provincie Drenthe - Kernen Check</title>\n");
print("<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/drenthe.css\">\n");
print("<script type=\"text/javascript\" src=\"js/swfobject.js\"></script>\n");
print("<script type=\"text/javascript\" src=\"js/fscripts_full.js\"></script>\n");
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
print("background-color: #ffffff;\n");
print("}\n");
print("</style>\n");
print("</head>\n");
print("<body style=\"background: white\">\n");
print("<div id=\"header2\">\n");
print("<div id=\"titlebar\">\n");
print("<div class=\"title\">\n");
print("<div id=\"subtitle\">\n");
	print("Geoportaal Drenthe\n");
print("</div>\n");
print("</div>\n");
print("<div class=\"provincies\">\n");
print("</div>\n");
print("</div>\n");
print("<div id=\"menu\">\n");
print("<div id=\"menuleft\">\n");
print("</div>\n");
print("<div id=\"menuright\">\n");
print("</div>\n");
print("</div>\n");
print("</div>\n");
print("<div id=\"kaart\">\n");
print("<div id=\"flashcontent\" style=\"width: 100%; height: 100%;\"></div>\n");
//print("<script type=\"text/javascript\">\n");
//print("var so = new SWFObject(\"flamingo/flamingo.swf?config=../config/geoportaal_maak_kaart.xml,../config/locationfinder.xml\", \"flamingo\", \"100%\", \"100%\", \"8\", \"#FFFFFF\");\n");
//print("so.addParam(\"wmode\", \"transparent\");\n");
//print("so.write(\"flashcontent\");\n");
//print("var flamingo = document.getElementById(\"flamingo\");\n");
//print("</script>\n");
print("<script type=\"text/javascript\">\n");
print("goFlamingo(\"../config\",\"geoportaal_maak_kaart,locationfinder\");\n");
print("</script>");
print("</div>\n");
print("</body>\n");
print("</html>\n");
}

function F_LOG($actie) {
if (@$_SESSION["PROVINCIE"] == TRUE) {
		$file = "../log/geoportaal_actie_intra.txt";
		$open = fopen( $file, "a" );
		fputs( $open, date("H:i:s, d-m-Y") . " | " . $actie . " | " . $_SERVER['HTTP_X_FORWARDED_FOR'] . " | "  . $_SERVER['REMOTE_ADDR'] . " | " . $_SERVER['HTTP_USER_AGENT'] . "\n" );
		fclose( $open );		
	}
	else {
		$file = "../log/geoportaal_actie_internet.txt";
		$open = fopen( $file, "a" );
		fputs( $open, date("H:i:s, d-m-Y") . " | " . $actie . " | " . $_SERVER['HTTP_X_FORWARDED_FOR'] . " | " . $_SERVER['REMOTE_ADDR'] . " | " . $_SERVER['HTTP_USER_AGENT'] . "\n" );
		fclose( $open );		
	}
}

function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}


function F_ENDSTYLE() {

//print("</div>\n");

print("<div class=\"watermerk\"><div class=\"wapen\"></div></div>\n");

print("</div>\n");
print("</div>\n");
print("</body>\n");
print("</html>\n");
}


function F_STYLE() {
//print("<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n");
print("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n");

print("<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"nl\" lang=\"nl\">\n");
//print("<style type=\"text/css\" media=\"all\">@import \"style/style.css\";</style>\n");
print("<head>\n");
header("Content-type: text/html; charset=iso-8859-1"); 
//header("Content-type: text/html; charset=utf-8"); 
print("<title>Geoportaal | Provincie Drenthe</title>\n");
//print("<link href=\"css/geoportaal.css\" rel=\"stylesheet\" type=\"text/css\"/>\n");
print("<link href=\"css/DrentheWebSite.css\" rel=\"stylesheet\" type=\"text/css\"/>\n");
print("<link href=\"css/DynamischDrenthe.css\" rel=\"stylesheet\" type=\"text/css\"/>\n");
//print("<link href=\"css/formulier.css\" rel=\"stylesheet\" type=\"text/css\"/>\n");
print("<link href=\"css/verticaleNav.css\" rel=\"stylesheet\" type=\"text/css\"/>\n");
//print("<!--[if IE 6]><link rel=\"stylesheet\" type=\"text/css\" href=\"/css/ie6.css\"/>\n");

//print("<link rel=\"stylesheet\" type=\"text/css\" href=\"js/shadowbox/shadowbox.css\">\n");
//print("<script type=\"text/javascript\" src=\"js/shadowbox/shadowbox.js\"></script>\n");

//print("<script type=\"text/javascript\" src=\"js/bumpbox/js/mootools.js\"></script>\n");
//print("<script type=\"text/javascript\" src=\"js/bumpbox/js/bumpbox.js\"></script>\n");
//print("<script type=\"text/javascript\" src=\"js/jwplayer.min.js\"></script>\n");

//print ("<script type=\"text/javascript\" src=\"js/fancybox/jquery.1.4.3.min.js\"></script>\n");
print ("<script type=\"text/javascript\" src=\"http://code.jquery.com/jquery-1.4.2.min.js\"></script>\n");
print ("<script type=\"text/javascript\" src=\"js/fancybox/jquery.fancybox-1.3.4.pack.js\"></script>\n");
//print ("<script type=\"text/javascript\" src=\"js/fancybox/jquery.easing-1.4.pack.js\"></script>\n");
//print ("<script type=\"text/javascript\" src=\"js/fancybox/jquery.mousewheel-3.0.4.pack.js\"></script>\n");

print ("<link rel=\"stylesheet\" href=\"js/fancybox/jquery.fancybox-1.3.4.css\" type=\"text/css\" media=\"screen\"/>\n");

//print ("<link media=\"screen\" rel=\"stylesheet\" href=\"js/colorbox/colorbox.css\" />\n");
//print ("<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js\"></script>\n");
//print ("<script src=\"js/colorbox/jquery.colorbox.js\"></script>\n");


//print("<!--[if lte IE 6]>\n");
//print("<link href=\"css/patches/patch_my_layout.css\" rel=\"stylesheet\" type=\"text/css\"/>\n");
//print("<![endif]-->\n");

print("<script type=\"text/javascript\">\n");
print("/* <![CDATA[ */\n");
print("function initPopup() {\n");
print(" var iframeEl = document.getElementById('WAITFRAME');\n");
print("iframeEl.style.display='none';\n");
print("}\n");
	 
print("function back(){\n");
print("var iframeEl = document.getElementById('WAITFRAME');\n");
print("iframeEl.style.display='none';\n");
print("}\n");

print("function execserver(ss) {\n");
print("WAITFRAME.document.location = ss;\n");
print("var iframeEl = document.getElementById('WAITFRAME');\n");
print("iframeEl.style.display='none';\n");
print("}\n");

//print("Shadowbox.init();\n");

print("$(document).ready(function(){ \n");
print("$(\".extlink\").fancybox({ \n");
print("'width' : '90%',\n");  
print("'height' : '90%', \n");
print("'autoScale' : true,  \n");
print("'transitionIn' : 'none',  \n");
print("'transitionOut' : 'none', \n");
print("'type' : 'iframe', \n");
print("'scrolling': 'no', \n");
//print("'titlePosition': 'outside', \n");
print("'cyclic': 'true', \n");
print("'centerOnScroll' : true");
print("});  \n");
print("});  \n");

print("$(document).ready(function(){ \n");
print("$(\".extlink1\").fancybox({ \n");
print("'width' : '60%',\n");  
print("'height' : '75%', \n");
print("'autoScale' : true,  \n");
print("'transitionIn' : 'none',  \n");
print("'transitionOut' : 'none', \n");
print("'type' : 'iframe', \n");
print("'scrolling': 'yes', \n");
print("'centerOnScroll' : true");
print("});  \n");
print("});  \n");

//print("$(document).ready(function(){\n");

//$("a[rel='example1']").colorbox();
//$("a[rel='example2']").colorbox({transition:"fade"});
//$("a[rel='example3']").colorbox({transition:"none", width:"75%", height:"75%"});
//$("a[rel='example4']").colorbox({slideshow:true});
//$(".example5").colorbox();

//print("$(\".example6\").colorbox({iframe:true, width:\"80%\", height:\"80%\"});\n");
//$(".example7").colorbox({width:"80%", height:"80%", iframe:true});
//$(".example8").colorbox({width:"50%", inline:true, href:"#inline_example1"});
//$(".example9").colorbox({
//print("onOpen:function(){ alert('onOpen: colorbox is about to open'); },\n");
//print("onLoad:function(){ alert('onLoad: colorbox has started to load the targeted content'); },\n");
//print("onComplete:function(){ alert('onComplete: colorbox has displayed the loaded content'); },\n");
//print("onCleanup:function(){ alert('onCleanup: colorbox has begun the close process'); },\n");
//print("onClosed:function(){ alert('onClosed: colorbox has completely closed'); }\n");
//print("});\n");
			
	//Example of preserving a JavaScript event for inline calls.
	//$("#click").click(function(){ 
	//$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
	//return false;
	//});
//print("});\n");

print("/* ]]> */\n");	 
print("</script>\n");

print("<style>\n");

// Change this to the total number of images in the folder
$total = "15";
// Change to the type of files to use eg. .jpg or .gif
$file_type = ".jpg";
// Change to the location of the folder containing the images
$image_folder = "headers";
// You do not need to edit below this line
$start = "1";
$random = mt_rand($start, $total);
$image_name = $random . $file_type;
//echo "<img src=\"$image_folder/$image_name\" alt=\"$image_name\" />";
print (".headerhouder .headerfoto {\n");
print ("background-image:url($image_folder/$image_name );\n");
print("</style>\n");

print("</head>\n");
print("<body>\n");
print("<div class=\"home\">\n");
print("<div id=\"omhulsel\" class=\"omhulsel\">\n");
print("<div class=\"headerhouder\" id=\"top\">\n");
print("<div class=\"headertop\">\n");
print("<div class=\"servicebalk\">\n");
print("<a href=\"index.php?e=@GBIEXEC&p=OVERPORTAAL\">Over het geoportaal</a>\n");
print("<span class=\"pijplijn\"> </span>\n");
print("<a href=\"index.php?e=@GBIEXEC&p=GEBRUIKSITE\">Over de kaarten</a>\n");
print("<span class=\"pijplijn\"> </span>\n");
print("<a href=\"index.php?e=@GBIEXEC&p=GEBRUIKVIEWER\">Uitleg</a>\n");
print("<span class=\"pijplijn\"> </span>\n");
print("<a href=\"index.php?e=@GBIEXEC&p=VRAGEN\">Veelgestelde vragen</a>\n");
print("<span class=\"pijplijn\"> </span>\n");
if (@$_SESSION["PROVINCIE"] == TRUE) {
	print("<a href=\"index.php?e=@GBIEXEC&p=GISPROV\">GIS provincie Drenthe</a>\n");
	print("<span class=\"pijplijn\"> </span>\n");
	print("<a href=\"http://www.drenthe.nl\">Drenthe.nl</a>\n");
}
else {
	print("<a href=\"http://www.drenthe.nl\">Drenthe.nl</a>\n");
}
print("</div>\n");
print("</div>\n");
print("<div class=\"headerfoto\">\n");
//print("<a href=\"http://www.provincie.drenthe.nl/\"><span class=\"opvulling\"></span></a>\n");
print("</div>\n");

print("<div class=\"headerbottom\">\n");
print("<div class=\"servicebalk\">\n");
print("<a href=\"index.php\">Zoeken</a>\n");
print("<span class=\"pijplijn\"> </span>\n");
print("<a href=\"index.php?e=@GBI&p=DATASETLIST\">Alfabetisch zoeken</a>\n");
//print("<span class=\"pijplijn\"> </span>\n");
//print("<a href=\"index.php?e=@GBI&p=DATASETSEARCH\">Zoeken op trefwoord</a>\n");
print("<span class=\"pijplijn\"> </span>\n");
print("<a href=\"index.php?e=@GBI&p=MAAKKAART\">Maak een kaart</a>\n");
print("<span class=\"pijplijn\"> </span>\n");
print("</div>\n");
print("</div>\n");
//print("<div id=\"nav_main\">\n");
//print("<a class=\"skip\" href=\"#navigation\" title=\"skip link\">Skip to the navigation</a><span class=\"hideme\">.</span>\n");
//print("<a class=\"skip\" href=\"#content\" title=\"skip link\">Skip to the content</a><span class=\"hideme\">.</span>\n");
//print("<span><a href=\"index.php?e=@GBI&p=DATASETLIST\">Alfabetisch zoeken</a> > <a href=\"index.php?e=@GBI&p=DATASETSEARCH\">Zoeken op trefwoord</a> > <a href=\"index.php?e=@GBI&p=SERVICELIST\">Thematische kaarten</a> > <a href=\"index.php?e=@GBI&p=MAAKKAART\">Maak een kaart</a> > </span>\n");
//print("</div>\n");
print("</div>\n");
}
?>
