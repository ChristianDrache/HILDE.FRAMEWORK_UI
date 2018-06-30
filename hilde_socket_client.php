<?php

										/********************************/
										/*		HILDE.SOCKET.CLIENT		*/
										/********************************/


	/************************************************************************************************/
	/* Autor: Christian Drache (christian.drache@gmx.de)											*/
	/* Version: 1.1																					*/
	/*																								*/
	/* Client-Komponente die Events vom XML-RPC-Broker oder dem XML-API-Broker entgegennimmt und	*/
	/* und als JSON-Paket an den Socket-Server sendent.												*/
	/*																								*/
	/* Version		Änderungsdatum		Bemerkung													*/
	/* -------------------------------------------------------------------------------------------- */
	/*   1.1.		19.06.2018			Ersterstellung												*/
	/*																								*/
	/*																								*/
	/************************************************************************************************/

	
	
		
	/********************************************************************************/
	/* Event wird in JSON-Paket gepackt und an den HILDE.SOCKET.SERVER geschickt.	*/
	/********************************************************************************/
	function sendSocket($p_seriennummer, $p_eventName, $p_eventValue)
	{	
		try{

			//Konfigurationsdaten laden
			require("config.php");

			//Klasse für HomeMatic-Event erstellen
			$hmEvent = new stdClass();
			$hmEvent->jsonSeriennummer 	= $p_seriennummer;
			$hmEvent->jsonEventName 	= $p_eventName;
			$hmEvent->jsonEventValue 	= $p_eventValue;

			//Event-Klasse in JSON verpacken
			$jsonEvent = json_encode($hmEvent);

			//HTTP-Header erstellen
			$head = "GET / HTTP/1.1"."\r\n".
					"Upgrade: WebSocket"."\r\n".
					"Connection: Upgrade"."\r\n".
					"Origin: $path_to_index"."\r\n".
					"Host: $host_ip"."\r\n".
					"Sec-WebSocket-Key: asdasdaas76da7sd6asd6as7d"."\r\n".
					"Content-Length: ".strlen($jsonEvent)."\r\n"."\r\n";
					
			//WebSocket Handshake durchführen
			$sock = fsockopen($host_ip, $socket_port, $errno, $errstr, 2);
			fwrite($sock, $head) or die('error:'.$errno.':'.$errstr);
			$headers = fread($sock, 2000);
			
			//Ausgabe Header-Daten
			echo $headers;
			
			//JSON-Paket verschlüsseln und an Socket schicken
			fwrite($sock, socketEncode($jsonEvent)) or die('error:'.$errno.':'.$errstr);
			$wsdata = fread($sock, 2000);
			
			//Ausgabe entschlüsseln
			var_dump(socketDecode($wsdata));
			
			//Socket schliessen
			fclose($sock);
			
			return '';
		
		} catch(Exception $e) {			
		
			//Fehler in Logdatei ausgeben
			file_put_contents("hilde_xmlrpc_broker_sendSocket_error.txt", $e->getMessage());
			
			return '';
		}	
	}

	
	/********************************************************************************/
	/* Datentransfer verschlüsseln													*/
	/********************************************************************************/
	function socketDecode($data)
	{
		try{
			
			$bytes = $data;
			$dataLength = '';
			$mask = '';
			$coded_data = '';
			$decodedData = '';
			$secondByte = sprintf('%08b', ord($bytes[1]));
			$masked = ($secondByte[0] == '1') ? true : false;
			$dataLength = ($masked === true) ? ord($bytes[1]) & 127 : ord($bytes[1]);

			if($masked === true)
			{
				if($dataLength === 126)
				{
				   $mask = substr($bytes, 4, 4);
				   $coded_data = substr($bytes, 8);
				}
				elseif($dataLength === 127)
				{
					$mask = substr($bytes, 10, 4);
					$coded_data = substr($bytes, 14);
				}
				else
				{
					$mask = substr($bytes, 2, 4);       
					$coded_data = substr($bytes, 6);        
				}   
				for($i = 0; $i < strlen($coded_data); $i++)
				{       
					$decodedData .= $coded_data[$i] ^ $mask[$i % 4];
				}
			}
			else
			{
				if($dataLength === 126)
				{          
				   $decodedData = substr($bytes, 4);
				}
				elseif($dataLength === 127)
				{           
					$decodedData = substr($bytes, 10);
				}
				else
				{               
					$decodedData = substr($bytes, 2);       
				}       
			}   

			return $decodedData;
			
		} catch(Exception $e) {			
		
			//Fehler in Logdatei ausgeben
			file_put_contents("hilde_xmlrpc_broker_socketDecode_error.txt", $e->getMessage());
			
			return '';
		}	
					
	}


	/********************************************************************************/
	/* Datentransfer entschlüsseln													*/
	/********************************************************************************/
	function socketEncode($payload, $type = 'text', $masked = true) {
		
		try{
			
			$frameHead = array();
			$frame = '';
			$payloadLength = strlen($payload);

			switch ($type) {
				case 'text':
					// first byte indicates FIN, Text-Frame (10000001):
					$frameHead[0] = 129;
					break;

				case 'close':
					// first byte indicates FIN, Close Frame(10001000):
					$frameHead[0] = 136;
					break;

				case 'ping':
					// first byte indicates FIN, Ping frame (10001001):
					$frameHead[0] = 137;
					break;

				case 'pong':
					// first byte indicates FIN, Pong frame (10001010):
					$frameHead[0] = 138;
					break;
			}

			// set mask and payload length (using 1, 3 or 9 bytes)
			if ($payloadLength > 65535) {
				$payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
				$frameHead[1] = ($masked === true) ? 255 : 127;
				for ($i = 0; $i < 8; $i++) {
					$frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
				}

				// most significant bit MUST be 0 (close connection if frame too big)
				if ($frameHead[2] > 127) {
					$this->close(1004);
					return false;
				}
			} elseif ($payloadLength > 125) {
				$payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
				$frameHead[1] = ($masked === true) ? 254 : 126;
				$frameHead[2] = bindec($payloadLengthBin[0]);
				$frameHead[3] = bindec($payloadLengthBin[1]);
			} else {
				$frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
			}

			// convert frame-head to string:
			foreach (array_keys($frameHead) as $i) {
				$frameHead[$i] = chr($frameHead[$i]);
			}

			if ($masked === true) {
				// generate a random mask:
				$mask = array();
				for ($i = 0; $i < 4; $i++) {
					$mask[$i] = chr(rand(0, 255));
				}

				$frameHead = array_merge($frameHead, $mask);
			}
			$frame = implode('', $frameHead);
			// append payload to frame:
			for ($i = 0; $i < $payloadLength; $i++) {
				$frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
			}

			return $frame;
			
		} catch(Exception $e) {			
		
			//Fehler in Logdatei ausgeben
			file_put_contents("hilde_xmlrpc_broker_socketEncode_error.txt", $e->getMessage());
			
			return '';
		}	
										
	}	
	
?>