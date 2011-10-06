<?php
	/* 
		ArcService Class Developed by Daniele Simoncini
		Centro di GeoTecnologie - Siena University
		simoncini5@unisi.it
	*/
	
	class ArcService{
		var $parser; //Parser XML
		var $ultimo; //Last Field
		var $lastact; //Actual Opration
		
		var $server; //ArcIMS Server
		var $service; //ArcIMS Service
		
		var $servicetype; //Type of the Service
		
		
		var $minx; //Envelope of the service
		var $miny;
		var $maxx;
		var $maxy;
		
		
		var $unit; //Measure Unit of the Service
		
		
		var $Layers; //Layers
		
		
		var $Thumb; //Image
		
		var $Legend; //Legenda
		var $laag;
		
		
		
		//Init Class
		function ArcService($server, $service) {
			$this->server = $server;
			$this->service = $service;
			$this->Service_Info();
		}
	
		//Parsing XML Stream
		function parse($data) { 
			$this->parser = xml_parser_create();
			xml_set_object($this->parser, &$this);
			xml_set_element_handler($this->parser, "tag_open", "tag_close");
			xml_parse($this->parser, $data);
			xml_parser_free($this->parser);
		}
	
		//Opening TAG
		function tag_open($parser, $tag, $attributes) { 
			switch ($this->lastact){
				case "SERVICE_INFO":
					switch ($tag){					
						case "MAPUNITS":
							$this->unit = $attributes["UNITS"];
						break;
					
						case "ENVELOPE":
							$this->minx = $attributes["MINX"];
							$this->maxx = $attributes["MAXX"];
							$this->miny = $attributes["MINY"];
							$this->maxy = $attributes["MAXY"];
						break;
						
						case "CAPABILITIES":
							$this->servicetype = $attributes["SERVERTYPE"];							
						break;
						
						case "LAYERINFO":
							array_push($this->Layers, array("name" => $attributes["NAME"], "type" => $attributes["TYPE"], "id" => $attributes["ID"], "visible" => $attributes["VISIBLE"], "minscale" => $attributes["MINSCALE"], "maxscale" => $attributes["MAXSCALE"], "fields" => array()));
							$this->ultimo = count($this->Layers) - 1;
						break;
						
						case "FIELD":
							array_push($this->Layers[$this->ultimo]["fields"], array("name" => $attributes["NAME"], "type" => $attributes["TYPE"], "size" => $attributes["SIZE"], "precision" => $attributes["PRECISION"]));
						break;
					}
				break;	
				
				case "IMAGE":
					switch ($tag){
						case "OUTPUT":
							$this->Thumb = $attributes["URL"];
						break;	
						
						case "LEGEND":
							$this->Legend = $attributes["URL"];
						break;											
					}
				break;
			}	
		}
		
		function tag_close($parser, $tag) {
		}
		
		function ExecArcXML($string){
			$connessione = fsockopen($this->server, 80, $errno, $errstr, 120);
				if(!$connessione){
					return false;
				} else {
					fwrite($connessione, "POST /ARCIMS/ims?ServiceName=".$this->service." HTTP/1.0\n");
					fwrite($connessione, "Accept: */*\n");
					fwrite($connessione, "Content-type: application/x-www-form-urlencoded\n");
					fwrite($connessione, "Content-length: ".strlen($string)."\n\n");
					fwrite($connessione, $string."\n");
					fwrite($connessione, "\n" , 1);
					
					$head = "";
					while ($str = trim(fgets($connessione, 4096))){
						$head .= $str."\r\n";
					}
					
					$corpo = "";
					
					while(!feof($connessione)){
						$corpo .= fgets($connessione, 4096);
					}
				}
			fclose($connessione);
			return $corpo;
		}
		
		//richiesta di info sul service
		function Service_Info(){
			$this->lastact = "SERVICE_INFO";
			$this->Layers = array();
						
			$dati = "<ARCXML version='1.1'><REQUEST><GET_SERVICE_INFO renderer='false' fields='true' envelope='false' extensions='false' /></REQUEST></ARCXML>";
			
			$this->parse($this->ExecArcXML($dati));
			$this->Layers = array_reverse($this->Layers);
		}
		
		//richiesta di info sul service
		function Image($minx, $miny, $maxx, $maxy, $width = 300, $height = 300){
			$this->lastact = "IMAGE";
			$this->Thumb = "";
			
			$dati = "<ARCXML version='1.1'><REQUEST><GET_IMAGE><PROPERTIES><BACKGROUND color=\"255,255,255\" transcolor=\"255,255,255\"/><OUTPUT type=\"png\"/><ENVELOPE minx='".$minx."' miny='".$miny."' maxx='".$maxx."' maxy='".$maxy."'/><IMAGESIZE width='".$width."' height='".$height."' />";
			$dati .= "<LAYERLIST>";
			foreach($this->Layers as $livello){
				$dati .= "<LAYERDEF id='".$livello["id"]."' visible='".$livello["visible"]."' />";
			}	
			$dati .= "</LAYERLIST></PROPERTIES></GET_IMAGE></REQUEST></ARCXML>";
			
			$this->parse($this->ExecArcXML($dati));
			return $this->Thumb;
		}
		
		function ImageLaag($laag, $minx, $miny, $maxx, $maxy, $width = 125, $height = 125){
			$this->lastact = "IMAGE";
			$this->Thumb = "";
			
			$dati = "<ARCXML version='1.1'><REQUEST><GET_IMAGE><PROPERTIES><BACKGROUND color=\"255,255,255\" transcolor=\"255,255,255\"/><OUTPUT type=\"png\"/><ENVELOPE minx='".$minx."' miny='".$miny."' maxx='".$maxx."' maxy='".$maxy."'/><IMAGESIZE width='".$width."' height='".$height."' />";
			$dati .= "<LAYERLIST>";
			$dati .= "<LAYERDEF id='AB_GEMEENTENDRENTHE_V' visible='TRUE' />";
			$dati .= "<LAYERDEF id='" . $laag . "' visible='TRUE' />";
			$dati .= "</LAYERLIST></PROPERTIES></GET_IMAGE></REQUEST></ARCXML>";
			
			$this->parse($this->ExecArcXML($dati));
			return $this->Thumb;
		}
		
		function Legend($laag) {
			$this->lastact = "IMAGE";
			$this->Legend = "";
		
			$dati = "<ARCXML version='1.1'><REQUEST><GET_IMAGE type=\"PNG\"><PROPERTIES>";
			$dati .= "<BACKGROUND color=\"255,255,255\" transcolor=\"255,255,255\"/><OUTPUT type=\"png\"/><LEGEND type=\"png\" autoextend=\"true\" font=\"Arial\" width=\"150\" height=\"50\" titlefontsize=\"10\" valuefontsize=\"10\" layerfontsize=\"10\" />";
			$dati .= "<DRAW map=\"false\" />";	
			$dati .= "<LAYERLIST nodefault=\"true\">";
			$dati .= "<LAYERDEF id='". $laag . "' visible='TRUE' />";
			$dati .= "</LAYERLIST></PROPERTIES></GET_IMAGE></REQUEST></ARCXML>";
			
			$this->parse($this->ExecArcXML($dati));
			return $this->Legend;		
		}
		
		function LegendKaart($laag) {
			$this->lastact = "IMAGE";
			$this->Legend = "";
		
			$dati = "<ARCXML version='1.1'><REQUEST><GET_IMAGE type=\"PNG\"><PROPERTIES>";
			$dati .= "<BACKGROUND color=\"255,255,255\" transcolor=\"255,255,255\"/><OUTPUT type=\"png\"/><LEGEND type=\"png\" autoextend=\"true\" font=\"Arial\" width=\"150\" titlefontsize=\"10\" valuefontsize=\"10\" layerfontsize=\"10\" z/>";
			$dati .= "<DRAW map=\"false\" />";	
			$dati .= "<LAYERLIST>";
			$tokens = explode(",", $laag);
			for ($iRecord = 0; $iRecord < count($tokens); $iRecord++) {
				$dati .= "<LAYERDEF id='". $tokens[$iRecord] . "' visible='TRUE' />";				
			}			
			$dati .= "</LAYERLIST></PROPERTIES></GET_IMAGE></REQUEST></ARCXML>";
			
			$this->parse($this->ExecArcXML($dati));
			return $this->Legend;


			
		}		
	}
?>