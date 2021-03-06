<?php

											/********************************/
											/*		GLOBALE VARIABLEN		*/
											/********************************/
			
	
	/********************************************************************************************************************/
	/* Autor: Christian Drache (christian.drache@gmx.de)																*/
	/* Version: 1.1																										*/
	/*																												 	*/
	/* In diesem Skript werden die globalen Variablen zusammengefasst. 												 	*/
	/* Im Wesentlichen handelt es sich dabei um URLs, Servernamen, IP-Adressen und Ports.							 	*/
	/*																												 	*/
	/*																													*/
	/* Version		Änderungsdatum			Bemerkung																	*/
	/* ---------------------------------------------------------------------------------------------------------------- */
	/*   1.1.		29.05.2018				Ersterstellung																*/
	/*																													*/
	/*																													*/
	/********************************************************************************************************************/


	
	/********************************************************************************************************************/
	/* IP des WebServers (IPv4) auf dem sich das HILDE-Framework befindet												*/
	/********************************************************************************************************************/	
	$host_ip = "192.168.188.57";

	
	/********************************************************************************************************************/
	/* Port unter dem der Webserver (aus $host_ip) erreichbar ist														*/
	/********************************************************************************************************************/
	$http_port = "81";


	/********************************************************************************************************************/
	/* Pfad zur ControlUI (index.php)																					*/
	/********************************************************************************************************************/
	$path_to_index = "http://" . $host_ip . ":" . $http_port   . dirname($_SERVER['PHP_SELF']) . "/index.php";


	/********************************************************************************************************************/
	/* Port zur WebSocket-Kommunikation																					*/
	/********************************************************************************************************************/
	$socket_port = "9000";
	
	
	/********************************************************************************************************************/
	/* Adresse unter der ein WebSocket eingerichtet wird																*/
	/********************************************************************************************************************/
	$path_to_hilde_server_socket = "ws://" 	. $host_ip . ":" . $socket_port . dirname($_SERVER['PHP_SELF']) . "/hilde_server_socket.php";
	
	
	/********************************************************************************************************************/
	/* Skript an das die CCU2 die Events zustellen soll																	*/
	/********************************************************************************************************************/
	$path_to_hilde_xmlrpc_broker = "http://" . $host_ip 	 . ":" . $http_port . dirname($_SERVER['PHP_SELF']). "/hilde_xmlrpc_broker.php";
	
	
	/********************************************************************************************************************/
	/* Identifikations-ID unter der sich die CCU2 ($ccu2_host) am WebServer ($host_ip) legitimiert						*/
	/********************************************************************************************************************/
	$identifier	= "8403608712321";
		
	
	/********************************************************************************************************************/
	/* IP der CCU2 (IPv4)																								*/
	/********************************************************************************************************************/
	$ccu2_host = "http://192.168.188.75";
	
	
	/********************************************************************************************************************/
	/* Port zur XML-RPC-Kommunikation 																					*/
	/********************************************************************************************************************/
	$xmlrpc_port = "2001";
	
	
	/********************************************************************************************************************/
	/* Adresse zum Abonieren von XML-RPC-Events																			*/
	/********************************************************************************************************************/
	$path_to_xmlrpc = $ccu2_host . ":" . $xmlrpc_port;
	
	
	/********************************************************************************************************************/
	/* URL zum XML-API-Interface																						*/
	/********************************************************************************************************************/
	$path_to_xmlapi = $ccu2_host . "/addons/xmlapi/";
	
	
	/********************************************************************************************************************/
	/* URL zur XML-API: Deviceliste																						*/
	/********************************************************************************************************************/
	$path_to_xmlapi_devices = $path_to_xmlapi . "devicelist.cgi";
	
	
	/********************************************************************************************************************/
	/* URL zur XML-API: Device/Gewerkliste																				*/
	/********************************************************************************************************************/
	$path_to_xmlapi_channel_gewerk = $path_to_xmlapi . "functionlist.cgi";
	
	
	/********************************************************************************************************************/
	/* URL zur XML-API: Statusänderung																					*/
	/********************************************************************************************************************/
	$path_to_xmlapi_statuschange = $path_to_xmlapi . "statechange.cgi?ise_id={ise_id}&new_value=";
	
	
	/********************************************************************************************************************/
	/* Verbindungs- und Anmeldeinformationen am DB-Servernamen															*/
	/********************************************************************************************************************/
	
	$hildeDb_server 	= "localhost";
	$hildeDb_user 		= "abc";
	$hildeDb_password 	= "";
	$hildeDb_name 		= "homematic";
	
	
?>
