<?php 	

										/********************************/
										/*		HILDE.XMLAPI.BROKER		*/
										/********************************/


	/************************************************************************************************/
	/* Autor: Christian Drache (christian.drache@gmx.de)											*/
	/* Version: 1.1																					*/
	/*																								*/
	/*																								*/
	/* Version		Änderungsdatum		Bemerkung													*/
	/* -------------------------------------------------------------------------------------------- */
	/*   1.1.		19.06.2018			Ersterstellung												*/
	/*																								*/
	/*																								*/
	/************************************************************************************************/

	
	require_once("config.php");
	require_once("hilde_socket_client.php");
	require_once("class.hilde_db.php");
	require_once("class.curl.php");

	try{

		$hildeDb = new HildeDb();
		$result = $hildeDb->getListOfChannelIseId(__FUNCTION__);

		$curl = new Curl();

		//Datensätze durchlaufen
		while($row = $result->fetch_assoc()) {

			$xml = $curl->getXML('http://192.168.188.75/config/xmlapi/state.cgi?channel_id=' . $row["ise_id"], __FUNCTION__);
			
			//Datenpunkt selektieren, der den State des aktuellen Channels enthält
			$datenpunkte = $xml->xpath("//state/device/channel[@ise_id='" . $row["ise_id"] . "']/datapoint[@type='STATE']");
			
			//Zustand auslesen
			$attr_state = 'value';
			$state = $datenpunkte[0]->attributes()->$attr_state;

			if((string)$state == 'true') $b_State = true; else $b_State = false;
			
			//Werte des aktuellen Events an Clients senden
			sendSocket($row["seriennummer"], "STATE", $b_State);

		}			

	} catch(Exception $e) {

		//Fehler in Logdatei ausgeben
		file_put_contents("hilde_xmlapi_broker_main_error.txt", $e->getMessage());
		
	}
	
?> 