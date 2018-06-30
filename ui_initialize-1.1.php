<?php 
	
	try{
		
		require("config.php");
		require_once("class.hilde_db.php");
		require_once("class.curl.php");
		
		$hildeDb = new HildeDb();
		$rs = $hildeDb->getChannels(__FUNCTION__);
		
		$curl = new Curl();
		
		$gewerk = "";
		
		while($row = $rs->fetch_assoc()) {

			//Gruppenüberschrift
			if($row["gewerk"] != $gewerk){
				$gewerk = $row["gewerk"];
				echo "<br>" . $gewerk . "<br>";
			}
			
			//aktuellen Status für aktuellen Aktor auslesen
			$xml = $curl->getXML('http://192.168.188.75/config/xmlapi/state.cgi?channel_id=' . $row["ise_id"], __FUNCTION__);

			//Datenpunkt selektieren, der den State des aktuellen Channels enthält
			$datenpunkte = $xml->xpath("//state/device/channel[@ise_id='" . $row["ise_id"] . "']/datapoint[@type='STATE']");
			
			//Zustand auslesen
			$attr_state = 'value';
			$state = $datenpunkte[0]->attributes()->$attr_state;

			$bgcolor = 'red';					
			if($state == 'false')
				$bgcolor = 'green';
			
			//prüfen, ob Aktor zum Gewerk 'Steckdosen' (= 1052) gehört
			if($row["ise_id_gewerk"] == 1052){
				echo "<button id='" . $row["ise_id"] . "' value='" . $state . "' onClick='newValue(" . $row["ise_id"] . ");' style='background-color:" . $bgcolor . ";'>" . $row["channel"] . "</button>";
				echo "<br>";						
			}
			
			//prüfen, ob Aktor zum Gewerk 'Kontakte' (= 1058) gehört
			if($row["ise_id_gewerk"] == 1058){
				echo "<data id='" . $row["ise_id"] . "' value='" . $state . "' style='background-color:" . $bgcolor . ";'>" . $row["channel"] . "</data>";
				echo "<br>";						
			}

		}			
	
	} catch(Exception $e) {

		//Fehler in Logdatei ausgeben
		file_put_contents("hilde_php_socket_filterEvent_error.txt", $e->getMessage());
		
	}	
		

	/************************************/
	/*	Definition HILDE.SOCKET.SERVER	*/
	/************************************/

	//Konfigurationsdaten laden
	require_once("config.php");
	
	//Initialisierung WebSocket
	echo 
	 "<script>" .
 	  "var path_to_xmlapi_statuschange = '" . $path_to_xmlapi_statuschange . "';" .
	  "var websocket = new WebSocket('" . $path_to_hilde_server_socket . "'); " .
	 "</script>";
	 
?>