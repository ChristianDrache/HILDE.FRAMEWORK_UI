<?php 	

										/********************************/
										/*		HILDE.XMLRPC.BROKER		*/
										/********************************/


	/************************************************************************************************/
	/* Autor: Christian Drache (christian.drache@gmx.de)											*/
	/* Version: 1.1																					*/
	/*																								*/
	/* Mit dem HILDE.XMLRPC.BROKER werden eingehende XML-RPC-Requests der HomeMatic-CCU2 			*/
	/* entgegengenommen.																			*/
	/* Bei den XML-RPC-Requests handelt es sich um Events der einzelnen HomeMatic-Aktoren.			*/
	/*																								*/
	/* Diese Events werden an eine festgelegte Websocket-Verbindung gesendet. 						*/
	/* An alle Client-Sesions, die mit dem Websocket verbunden sind, wird das Event gepusht.  		*/
	/*																								*/
	/* Version		Änderungsdatum		Bemerkung													*/
	/* -------------------------------------------------------------------------------------------- */
	/*   1.1.		29.05.2018			Ersterstellung												*/
	/*																								*/
	/*																								*/
	/************************************************************************************************/

	
	
	//Socket-Client laden
	require_once("hilde_socket_client.php");
	
	try{
		
		//XML-RPC-Serverinstanz initialisieren
		$xmlrpc_server = xmlrpc_server_create(); 
		
		//XML-RPC-Methode mit lokaler event()-Methode verknüpfen
		xmlrpc_server_register_method($xmlrpc_server, "system.multicall", "event");

		//HomeMatic-Request (XML-Format) in $request zwischenspeichern
		$request = file_get_contents('php://input');

		//XML-RPC-Options festlegen
		$options = array('output_type' => 'xml', 'version' => 'auto');
		
		//oben registrierte event()-Methode wird ausgeführt
		xmlrpc_server_call_method($xmlrpc_server, $request, null, $options);

		//HTTP-Header definieren
		header("Content-Length: " . strlen($request));

		//$request wird in Datei ausgegeben (zum Debugging einkommentieren)
		//file_put_contents(time()."_hilde_xmlrpc_broker_main_request.xml", $request);

		//die CCU2 erwartet immer einen leeren String
		$response = ''; 
	
		//XML-RPC-Serverinstanz zerstören
		xmlrpc_server_destroy($xmlrpc_server); 

	} catch(Exception $e) {

		//Fehler in Logdatei ausgeben
		file_put_contents("hilde_xmlrpc_broker_main_error.txt", $e->getMessage());
		
		//die CCU2 erwartet immer einen leeren String
		$response = ''; 
		
	}

	
	/********************************************************************************/
	/* Für die ankommenden HomeMatic-Events muss eine Event-Methode bereitgestellt	*/
	/* werden. 																		*/
	/* Die HomeMatic kann auch mehrere Events in einem Request schicken. In diesem	*/
	/* Fall handelt es sich dann um einen "system.multicall". 						*/
	/*																				*/
	/* $method:	Name der XML-RPC-Methode, i. d. R. ist der Wert "event".			*/
	/* $params: XML-Array, enthält Seriennummer, EventName und EventValue 			*/
	/********************************************************************************/
	function event($method, $params)
	{
		try{

			//Wurzelelement des XML-Arrays
			$root = $params[0];
			
			//alle Event-Knoten des XML-Arrays durchlaufen (mehrere Event-Knoten möglich -> system.multicall)
			for($i = 0; $i < sizeOf($root); $i++)
			{
				//aktuelles Event
				$eventArray 	= $root[$i]; 
				
				//Werte des aktuellen Events ermitteln
				$seriennummer 	= $eventArray["params"][1];
				$eventName 		= $eventArray["params"][2];
				$eventValue 	= $eventArray["params"][3];
				
				//Parameter aus dem Event werden in Datei gespeichert (Debugging)
				//file_put_contents("hilde_xmlrpc_broker_event_log.txt", 
				//				  $seriennummer . ", " . 
				//				  $eventName . ", " . 
				//				  $eventValue . PHP_EOL, FILE_APPEND);
				
				//Werte des aktuellen Events an Clients senden
				sendSocket($seriennummer, $eventName, $eventValue);
			}
			
			 return '';
		
		} catch(Exception $e) {			
		
			//Fehler in Logdatei ausgeben
			file_put_contents("hilde_xmlrpc_broker_event_error.txt", $e->getMessage());
			
			return '';
		}
	   
	} 
	
?> 