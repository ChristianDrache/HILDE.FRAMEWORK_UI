<?php 

								/****************************************/
								/*		INITIAL CCU2-SUBSCRIPTION		*/
								/****************************************/


	/************************************************************************************************/
	/* Autor: Christian Drache (christian.drache@gmx.de)											*/
	/* Version: 1.1																					*/
	/*																								*/
	/* Mit der init.php wird der CCU2 mitgeteilt, an welche Adresse sie die Events zustellen soll.	*/
	/* Leider merkt sich die CCU2 diese Adresse nur für ca 55 Sekunden. Danach werden keine	evtl.	*/
	/* Events mehr zugestellt. Treten während der 55 Sekunden wieder Events auf, werden die 		*/
	/* 55 Sekunden ab dem letzten Event neu gesetzt.												*/
	/*																								*/
	/* EMPFEHLUNG:																					*/
	/* Da man nicht weiss wann das nächste Event auftritt, empfielt es sich zyklisch nach    		*/
	/* spätestens 50 Sekunden die init.php via CRON-Job erneut aufzurufen.							*/
	/*																								*/
	/* Version		Änderungsdatum		Bemerkung													*/
	/* -------------------------------------------------------------------------------------------- */
	/*   1.1.		29.05.2018			Ersterstellung												*/
	/*																								*/
	/*																								*/
	/************************************************************************************************/
	
	try{
		
		//Konfigurationsdaten laden
		require_once("config.php");
		require_once("class.curl.php");


		//Request zusammenbauen
		$xml_post_string = '
			<?xml version="1.0"?>
			<methodCall>
			   <methodName>init</methodName>
			   <params>
				  <param><value><string>' . $path_to_hilde_xmlrpc_broker . '</string></value></param>
				  <param><value><string>' . $identifier . '</string></value></param>
			   </params>
			</methodCall>
			';

		$curl = new Curl();
		$xml = $curl->getXML($path_to_xmlrpc, __FUNCTION__, $xml_post_string);

		
	} catch(Exception $e) {

		//Fehler in Logdatei ausgeben
		file_put_contents("hilde_init_error.txt", $e->getMessage());		
	}
 
?>