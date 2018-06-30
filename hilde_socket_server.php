<?php

										/********************************/
										/*		HILDE.SOCKET.SERVER		*/
										/********************************/


	/************************************************************************************************/
	/* Autor: Christian Drache (christian.drache@gmx.de)											*/
	/* Version: 1.1																					*/
	/*																								*/
	/* Das hilde_socket_server.php ist das Hauptprogramm des SocketServer-Teils. Zusammen mit dem 	*/
	/* SocketHandler bildet das hilde_socket_server.php den SocketServer.							*/
	/*																								*/
	/* Version		Änderungsdatum		Bemerkung													*/
	/* -------------------------------------------------------------------------------------------- */
	/*   1.1.		29.05.2018			Ersterstellung												*/
	/*																								*/
	/*																								*/
	/************************************************************************************************/
	
	try{
		
		//Konfigurationsdaten laden
		require("config.php");
		
		//SocketHandler laden
		require_once("class.sockethandler.php");
		
		//HildeDb-Class laden
		require_once("class.hilde_db.php");

		define('HOST_NAME',$host_ip); 
		define('PORT',$socket_port);

		$null = NULL;

		//SocketHandler-Objekt instanzieren
		$socketHandler = new SocketHandler();

		$socketResource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_set_option($socketResource, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_bind($socketResource, 0, PORT);
		socket_listen($socketResource);

		$clientSocketArray = array($socketResource);
		while (true) {
			
			$newSocketArray = $clientSocketArray;
			socket_select($newSocketArray, $null, $null, 0, 10);
			
			if (in_array($socketResource, $newSocketArray)) {
				$newSocket = socket_accept($socketResource);
				$clientSocketArray[] = $newSocket;
				
				$header = socket_read($newSocket, 1024);
				$socketHandler->doHandshake($header, $newSocket, HOST_NAME, PORT);
				
				socket_getpeername($newSocket, $client_ip_address);
				
				$newSocketIndex = array_search($socketResource, $newSocketArray);
				unset($newSocketArray[$newSocketIndex]);
			}
			
			foreach ($newSocketArray as $newSocketArrayResource) {	
				while(socket_recv($newSocketArrayResource, $socketData, 1024, 0) >= 1){
					$socketMessage = $socketHandler->unseal($socketData);
					$messageObj = json_decode($socketMessage);

					$jsonSeriennummer = '';
					if (!empty($messageObj->jsonSeriennummer))
						$jsonSeriennummer = $messageObj->jsonSeriennummer;
					
					$jsonEventName = '';
					if (!empty($messageObj->jsonEventName))
						$jsonEventName = $messageObj->jsonEventName;

					$jsonEventValue = false;
					if (!empty($messageObj->jsonEventValue))
						$jsonEventValue = $messageObj->jsonEventValue;

					//ise_id auslesen
					$hildeDb = new HildeDb("");
					$ise_id = $hildeDb->getIseId($jsonSeriennummer, __FUNCTION__);
					
					//prüfen, ob es sich um ein Event handelt, das gepusht wird
					if($ise_id != 0){
						$message = $socketHandler->createMessage($ise_id, $jsonEventName, $jsonEventValue);
						$socketHandler->send($message);						
					}
					break 2;
				}
				
				$socketData = @socket_read($newSocketArrayResource, 1024, PHP_NORMAL_READ);
				if ($socketData === false) { 
					socket_getpeername($newSocketArrayResource, $client_ip_address);

					$newSocketIndex = array_search($newSocketArrayResource, $clientSocketArray);
					unset($clientSocketArray[$newSocketIndex]);			
				}
			}
		}
		socket_close($socketResource);
		
	} catch(Exception $e) {

		//Fehler in Logdatei ausgeben
		file_put_contents("hilde_php_socket_main_error.txt", $e->getMessage());
		
		return false;
	}	

?>