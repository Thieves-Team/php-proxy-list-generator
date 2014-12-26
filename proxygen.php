<?php

$act = (isset($_GET['act']) && $_GET['act'] == "check") ? "check" : "none";


function checkSpan($input){
	// Save style classes
	$match_exc_style = preg_replace("/.*([<]style\s*?[>])|([<]\W+style\s*?[>]).*/","", $input);
	$match_exc = preg_replace("/\s+/","", $match_exc_style);

	$match_exc = explode(".", $match_exc, 2);
	$match_exc = $match_exc[1];

	$newSplit = explode(".", $match_exc);
	$arrClass = array();
	foreach($newSplit as $class) {
		$class_exc = preg_replace("/{.*?}/","", $class);
		$class_val = preg_replace("/.*?{(.*?)}/","$1", $class);
		$arrClass[$class_exc] = $class_val;
	}

	//continue triming
	$match = str_replace($match_exc_style,"", $input);
	$match = preg_replace("/\s*[<]style[>]\s*?[<]\W+style\s*?[>]\s*/","", $match);
	$match = preg_replace("/[<]div\s+style\s*?\W+display\s*?\W\s*?none\s*?\W+?[>].*?[<].*?[>]/", "",$match);
	$match = preg_replace("/[<]span\s+style\s*?\W+display\s*?\W\s*?none\s*?\W+?[>].*?[<].*?[>]/", "",$match);
	$match = preg_replace("/[<]img.*?[>]/", "", $match);
	$match = preg_replace("/([<]span\s+class\s*?\W+\s*?country\s*?\W+?[>]\s+)(.*?)([<].*?[>])/", "$2",$match);
	$match = preg_replace("/([<]span\s+class\s*?\W+\s*?[0-9]+\s*?\W+?[>])(.*?)([<].*?[>])/", "$2",$match);
	$match = preg_replace("/([<]span\s+style\s*?\W+display\s*?\W\s*?inline\s*?\W+?[>])(.*?)([<].*?[>])/", "$2",$match);

	foreach($arrClass as $key => $class) {
		if($class == "display:none")
			$match=preg_replace("/([<]span\s+class\s*?\W+\s*?".$key."\s*?\W+?[>])(.*?)([<].*?[>])/","",$match);
		if($class == "display:inline")
			$match=preg_replace("/([<]span\s+class\s*?\W+\s*?".$key."\s*?\W+?[>])(.*?)([<].*?[>])/","$2",$match);
	}
	
	$match = preg_replace("/[<]span\s*?[>]\s*?[<]\W+.*?[>]/", "", $match);
	$match = preg_replace("/([<]span\s*?[>])(.*?)([<].*?[>])/", "$2",$match);
	
	return $match ;
}

function getContents() {
	$response = "";
	$mama = file_get_contents("http://hidemyass.com/proxy-list/");
	$mama = preg_replace("/(.*)?[<]\W+thead\s*?[>]/", "", $mama);
	$mama = preg_replace("/[<]thead\s*?[>]\s*/", "|+|+|+|", $mama);
	$mama = explode("|+|+|+|",$mama);
	$mama = $mama[1];
	$mama = preg_replace("/\s*[<]\W+table\s*?[>]\s*/", "|+|+|+|", $mama);
	$mama = explode("|+|+|+|",$mama);
	$mama = $mama[0];

	$mama = preg_replace("/\s*[<]\W+?tr\s*?[>]\s*?[<]tr.*?[>]\s*/","|+|+|+|", $mama);
	$mama = preg_replace("/\s*[<]\W+?tr\s*?[>]s*|s*[<]tr.*?[>]\s*/","", $mama);	
	$mama = explode("|+|+|+|",$mama,2);
	$mama = $mama[1];
	$newSplit = explode("|+|+|+|", $mama);
	
	$response .='<table style="border: 1px solid;" cellpadding=3 align=center WIDTH=60%>
					<tr align="center" style="border: 1px solid;" >
					<td style="border: 1px solid;">IpAddress</td>
					<td style="border: 1px solid;">Port</td>
					<td style="border: 1px solid;">Country</td>				
				</tr>';

	foreach($newSplit as $line) {
		$line = preg_replace("/\s*[<]\W+?td\s*?[>]\s*?[<]td.*?[>]\s*/","|+|+|+|", $line);
		$newSplit = explode("|+|+|+|",$line);
		$line = $newSplit[1]."|".$newSplit[2]."|".$newSplit[3];
		$arrayView = checkSpan($line);
			
		$newline = explode("|",$arrayView,3);
		$response .='<tr align="center" style="border: 1px solid;" >';
		$response .= "<td style='border: 1px solid;'><center>".$newline[0]."</td>";
		$response .= "<td style='border: 1px solid;'><center>".$newline[1]."</td>";
		$response .= "<td style='border: 1px solid;'><center>".$newline[2]."</td></tr>";
	}

	$response .=  "</table>";

	return $response;
}

switch($act) {
	case "check": {
		echo getContents();
	}break;

	default: {
		echo '<title>Proxy List @Crisalixx</title>
			<link rel="stylesheet" type=text/css href=./css/style.css />
			<div align=center><img src=http://s23.postimg.org/5lmp7adbv/Untitled_1.png></img></div><center>
			<script type="text/JavaScript">
				function xhr() {
					var xhr;
					
					if(window.XMLHttpRequest) { xhr = new XMLHttpRequest(); }else { xhr = new ActiveXObject("Microsoft.XMLHTTP");	}
				
					xhr.onreadystatechange = function() {
						if(xhr.readyState == 4 && xhr.status == 200) { document.getElementById("table").innerHTML = xhr.responseText;}			
					}
					
					xhr.open("GET", "?act=check", false); 
					xhr.send(null);
				}

				function timedRefresh() { xhr(); setTimeout(function(){timedRefresh();}, 10000); }
			</script>
			<body onload="timedRefresh();"><div id="table"></div></body>';
	}
}


?>
