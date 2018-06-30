<?php

										/********************************/
										/*		HILDE.SOCKETHANDLER		*/
										/********************************/


	/************************************************************************************************/
	/* Autor: Christian Drache (christian.drache@gmx.de)											*/
	/* Version: 1.1																					*/
	/*																								*/
	/* Die Klasse SocketHandler verwaltet die einzelnen WebSocket-Verbindungen, den Handshake 		*/
	/* sowie die Nachrichtenzustellung an die Clients.												*/
	/*																								*/
	/*																								*/
	/* Version		Änderungsdatum		Bemerkung													*/
	/* -------------------------------------------------------------------------------------------- */
	/*   1.1.		29.05.2018			Ersterstellung												*/
	/*																								*/
	/*																								*/
	/************************************************************************************************/

	class SocketHandler {
		
		/********************************************************************************/
		/* Versenden einer Nachricht an alle registrierten Clients.						*/
		/********************************************************************************/
		function send($message) {
			
			try{ 
			
				global $clientSocketArray;
				$messageLength = strlen($message);
				foreach($clientSocketArray as $clientSocket)
				{
					@socket_write($clientSocket,$message,$messageLength);
				}
				return true;
			
			} catch(Exception $e) {

				//Fehler in Logdatei ausgeben
				file_put_contents("hilde_class_sockethandler_send_error.txt", $e->getMessage());
				
				return false;
			}			
		}

		
		/********************************************************************************/
		/* Nachricht entschlüsseln														*/
		/********************************************************************************/
		function unseal($socketData) {
			
			try{
				
				$length = ord($socketData[1]) & 127;
				if($length == 126) {
					$masks = substr($socketData, 4, 4);
					$data = substr($socketData, 8);
				}
				elseif($length == 127) {
					$masks = substr($socketData, 10, 4);
					$data = substr($socketData, 14);
				}
				else {
					$masks = substr($socketData, 2, 4);
					$data = substr($socketData, 6);
				}
				$socketData = "";
				for ($i = 0; $i < strlen($data); ++$i) {
					$socketData .= $data[$i] ^ $masks[$i%4];
				}
				
				return $socketData;
				
			} catch(Exception $e) {

				//Fehler in Logdatei ausgeben
				file_put_contents("hilde_class_sockethandler_unseal_error.txt", $e->getMessage());
				
				return '';
			}			
			
		}

		
		/********************************************************************************/
		/* Nachricht verschlüsseln														*/
		/********************************************************************************/
		function seal($socketData) {
			
			try{
				
				$b1 = 0x80 | (0x1 & 0x0f);
				$length = strlen($socketData);
				
				if($length <= 125)
					$header = pack('CC', $b1, $length);
				elseif($length > 125 && $length < 65536)
					$header = pack('CCn', $b1, 126, $length);
				elseif($length >= 65536)
					$header = pack('CCNN', $b1, 127, $length);
				return $header.$socketData;
				
			} catch(Exception $e) {

				//Fehler in Logdatei ausgeben
				file_put_contents("hilde_class_sockethandler_seal_error.txt", $e->getMessage());
				
				return '';
			}			
			
		}

		
		/********************************************************************************/
		/* Clients registrieren															*/
		/********************************************************************************/
		function doHandshake($received_header, $client_socket_resource, $host_ip, $port) {
			
			try{
				
				$headers = array();
				$lines = preg_split("/\r\n/", $received_header);
				foreach($lines as $line)
				{
					$line = chop($line);
					if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
					{
						$headers[$matches[1]] = $matches[2];
					}
				}

				$secKey = $headers['Sec-WebSocket-Key'];
				$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
				$buffer  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
				"Upgrade: websocket\r\n" .
				"Connection: Upgrade\r\n" .
				"WebSocket-Origin: $host_ip\r\n" .
				"WebSocket-Location: ws://$host_ip:$port/demo/shout.php\r\n".
				"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
				socket_write($client_socket_resource,$buffer,strlen($buffer));
			
			} catch(Exception $e) {

				//Fehler in Logdatei ausgeben
				file_put_contents("hilde_class_sockethandler_doHandshake_error.txt", $e->getMessage());
				
				return '';
			}		
			
		}

		
		
		/********************************************************************************/
		/* Nachricht wird an Clients geschickt. Die Parameter werden in $eventArray		*/
		/* geschrieben und als JSON-Paket der Nachricht übergeben.						*/
		/********************************************************************************/
		function createMessage($param1, $param2, $param3) {

			try{
				
				$eventArray 	= array($param1, $param2, $param3);
				$messageArray 	= array('message'=>$eventArray, 'message_type'=>'html');
				$returnMessage 	= $this->seal(json_encode($messageArray));
				
				return $returnMessage;
				
			} catch(Exception $e) {

				//Fehler in Logdatei ausgeben
				file_put_contents("hilde_class_sockethandler_doHandshake_error.txt", $e->getMessage());
				
				return '';
			}			
		}
	}
?>