var rect;
var mousedown;

function loadConfig(file){
   var app = getMovie("flamingo");
   app.call("flamingo","loadConfig",file);
}

function flamingo_onInit() {
   //at this moment the flamingo.swf is up and running

   var app = getMovie("flamingo");

   //map
   var map = getURLParam("map");
   if (map.length == 0) {
      map="1";
   }
	//var config = "../config/template1.xml,../streekplan/config/kaart" + map + ".xml";
    //var config = "../config/test.xml";

   //lang
   var lang= getURLParam("lang");
   if (lang.length > 0) {   
       app.call("flamingo" , "setLanguage", lang);
   }else{
       app.call("flamingo" , "setLanguage", "nl");
   }

	var laag = getURLParam("laag");	
   //extent or locationfinder
   var ext = getURLParam("ext");
   var loc = getURLParam("loc");
   if (ext.length > 0) {
      app.call("flamingo" , "setArgument", "map" , "extent" , ext);
   } else if  (loc.length > 0) {
      app.call("flamingo" , "setArgument", "locationfinder" , "find" , loc);
   }

      
   var gegevens = getURLParam("gegevens");
   if (gegevens.length > 0) {
		
   }
   
      //visible
   var vis = getURLParam("vis");
   if (vis.length > 0) {
      app.call("flamingo" , "setArgument", "map_" + laag, "visible" , vis);
   }
   
   var hid = getURLParam("hid");
   if (hid.length > 0) {
      app.call("flamingo" , "setArgument", "map_" + laag, "hidden" , hid);  	
   }
   
   //var test = app.call("flamingo", "getArgument", "layer1", "hiddenids");
   

   //if (config.length > 0) {
   //   alert("map:"+config);
   //   loadConfig(config);
   //}
   
   

}



function getMovie(movieName, framename) {
  if (framename != undefined){
    if (navigator.appName.indexOf("Microsoft") != -1) {
       return frames[framename].window[movieName];
    }else {
       return frames[framename].document[movieName];
    }
  }else{
    if (navigator.appName.indexOf("Microsoft") != -1) {
        return window[movieName];
    }else {
        return document[movieName];
    }
  }
}


function flamingo_getExtent(mapid){
   var app =getMovie("flamingo");
   var ext = app.call(mapid, "getCurrentExtent");
   return ext.minx + "," + ext.miny + "," + ext.maxx + "," + ext.maxy;
}

function flamingo_toggleLayers(mapid, layerid, sublayers){
   var app =getMovie("flamingo");
   var visible= app.call(mapid + "_" + layerid, "getVisible", sublayers);
   if (visible > 0){
      app.call(mapid + "_" + layerid, "setVisible", false, sublayers);
   }else{
      app.call(mapid + "_" + layerid, "setVisible", true, sublayers);
   }
   app.call(mapid + "_" + layerid, "update");
}


function flamingo_hideLayers(mapid, layerid, sublayers){
   var app =getMovie("flamingo");
   app.call(mapid + "_" + layerid, "setVisible", false, sublayers);
   app.call(mapid + "_" + layerid, "update");
}

function flamingo_showLayers(mapid, layerid, sublayers){
   var app =getMovie("flamingo");
   app.call(mapid + "_" + layerid, "setVisible", true, sublayers);
   app.call(mapid + "_" + layerid, "update");
}

function flamingo_moveToExtent(mapid, minx, miny, maxx, maxy, name){
    var extent = new Object();
    extent.minx = minx;
    extent.miny = miny;
    extent.maxx = maxx;
    extent.maxy = maxy;
    if (name == undefined){
      extent.name = name;
    }
    var app =getMovie("flamingo");
    app.call("map", "moveToExtent", extent, 0);
}

function flamingo_setLanguage(language){
   var app =getMovie("flamingo");
   app.call("flamingo", "setLanguage", language);
}


function getURLParam(strParamName){
      var strReturn = "";
      var strHref = window.location.href;
      if ( strHref.indexOf("?") > -1 ){
        var strQueryString = strHref.substr(strHref.indexOf("?"))
        var aQueryString = strQueryString.split("&");
        for ( var iParam = 0; iParam < aQueryString.length; iParam++ ){
          if (
            aQueryString[iParam].toLowerCase().indexOf(strParamName.toLowerCase() + "=") > -1 ){
            var aParam = aQueryString[iParam].split("=");
            strReturn = aParam[1];
            break;
          }
        }
      }
      return unescape(strReturn);
} 

