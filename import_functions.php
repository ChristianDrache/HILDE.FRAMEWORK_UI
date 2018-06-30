<?php


										/********************************/
										/*		HILDE.DATAIMPORT		*/
										/********************************/


	/****************************************************************************************************/
	/* Autor: Christian Drache (christian.drache@gmx.de)												*/
	/* Version: 1.1																						*/
	/*																									*/
	/* Die Channel- und Gewerkeinformationen werden über die XML-API bezogen und in die Tabellen 		*/
	/* des DB-Servers importiert. Als DB-Server wird hier MySQL bzw. MariaDB verwendet.					*/
	/*																									*/
	/* Auf dem DB-Server werden existiert die Tabelle 'kategorie'. In dieser Tabelle werden die 		*/
	/* verfügbaren Gewerke der CCU manuell angelegt. Über das Feld 'ShowUI' wird gesteuert				*/
	/* welche Gewerke und damit Channels in der ControlUI angezeigt werden (kategorie.showUI = 1).		*/
	/* Die beiden Tabellen 'channel' und 'channel_kategorie' werden über dieses Import-Skript befüllt.	*/
	/*																									*/
	/* In 'channel' werde die alle auf der CCU2 verfügbaren Channels (adresse, name und ise_id) 		*/
	/* improtiert.																						*/
	/*																									*/
	/* Die Tabelle 'channel_kategorie' verknüpft anhand der ise_id die Gewerke mit den enthaltenen 		*/
	/* Channels.																						*/
	/*																									*/
	/* Es empfiehlt sich dieses Import-Skript immer auszuführen, soferen sich Änderungen auf der CCU 	*/
	/* ergeben haben, also neue Aktoren gelöscht oder hinzugekommen sind bzw. sich etwas an den 		*/
	/* Gewerken geändert hat.																			*/
	/* Es sollten keine manuellen Datenmanipulationen in den Tabellen 'channel' und 'channel_kategorie' */
	/* durchgeführt werden, da alle Daten dieser beiden Tabellen vor jedem Datenimport vollständig 		*/
	/* gelöscht werden.																					*/
	/* Nur damit ist sichergestellt, dass alle gewünschten Aktoren (kategorie.showUI = 1) in der 		*/
	/* ControlUI angezeigt werden.																		*/
	/*																									*/
	/* Version		Änderungsdatum		Bemerkung														*/
	/* ------------------------------------------------------------------------------------------------ */
	/*   1.1.		29.05.2018			Ersterstellung													*/
	/*																									*/
	/*																									*/
	/****************************************************************************************************/

	//HildeDb-Class laden
	require_once("class.hilde_db.php");

	$hildeDb = new HildeDb();

	//Tabellen leeren	
	$hildeDb->deleteTable('channel_kategorie', __FUNCTION__);
	$hildeDb->deleteTable('channel', __FUNCTION__);

	//Tabellen füllen
	importChannel($hildeDb);
	importChannelGewerk($hildeDb);
	

	/********************************************************************************/
	/* HomeMatic-Channels über XML-API importieren									*/
	/********************************************************************************/
	function importChannel($p_hildeDb)
	{
		try{
			
			//Konfigurationsdaten laden
			require("config.php");

			$return = $p_hildeDb->doImportChannels(__FUNCTION__);	

		} catch(Exception $e) {			
		
			//Fehler in Logdatei ausgeben
			file_put_contents("hilde_import_functions_importChannel_error.txt", $e->getMessage());
		}
		
		return '';
	}
	
	
	/********************************************************************************/
	/* HomeMatic-Channel/Gewerk-Verknüpfung über XML-API importieren				*/
	/********************************************************************************/
	function importChannelGewerk($p_hildeDb)
	{
		try{
					
			//Konfigurationsdaten laden
			require("config.php");

			$return = $p_hildeDb->doImportGewerke(__FUNCTION__);	

		} catch(Exception $e) {			
		
			//Fehler in Logdatei ausgeben
			file_put_contents("hilde_import_functions_importChannelGewerk_error.txt", $e->getMessage());
		}
		
		return '';
	}

?>