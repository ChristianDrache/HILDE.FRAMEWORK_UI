<?php 


class HildeDb
{
	private $hildeDb;
	

	/*********************************************************************************************************************/
	/*********************************************************************************************************************/
	public function __construct()
	{		
		require("config.php");
		
		$this->hildeDb = new mysqli($hildeDb_server, 
									$hildeDb_user, 
									$hildeDb_password, 
									$hildeDb_name);
			
	}
	
	
	/*********************************************************************************************************************/
	/*********************************************************************************************************************/	
	private function execQuery($p_sql, $p_caller)
	{
		$result = "";
		
		try{

			//prüfen ob Verbindung zu hildeDb-Server hergestellt wurde
			if ($this->hildeDb->connect_error) {
				
				//Verbindung beenden
				die($this->hildeDb->connect_error);
				
				//Fehler in Logdatei ausgeben
				file_put_contents(  "hildeDb.log", 
									date_format(date_create(), 'd-m-Y H:i:s') . ": " .
									"Aufrufer: " . $p_caller  . " // " . 
									"Datei: "    . __FILE__   . " // " . 
									"Zeile: "    . __LINE__   . " // " .
									"Methode: "  . __METHOD__ . " // " .
									"Fehler: "   . $this->hildeDb->connect_error .
									PHP_EOL, FILE_APPEND
								 );

			} else {

				$result = $this->hildeDb->query($p_sql);

				//prüfen, ob Datensatz zurückgegeben wurde
				if ($result->num_rows <= 0) {

					//Fehler in Logdatei ausgeben
					file_put_contents(  "hildeDb.log", 
										date_format(date_create(), 'd-m-Y H:i:s') . ": " .
										"Aufrufer: " . $p_caller  . " // " . 
										"Datei: "    . __FILE__   . " // " . 
										"Zeile: "    . __LINE__   . " // " .
										"Methode: "  . __METHOD__ . " // " .
										"Fehler: "   . $this->hildeDb->connect_error .
										PHP_EOL, FILE_APPEND
									 );
				
				}
			}

		} catch(Exception $e) {

			//Fehler in Logdatei ausgeben
			file_put_contents(  "hildeDb.log", 
								date_format(date_create(), 'd-m-Y H:i:s') . ": " .
								"Aufrufer: " . $p_caller  . " // " . 
								"Datei: "    . __FILE__   . " // " . 
								"Zeile: "    . __LINE__   . " // " .
								"Methode: "  . __METHOD__ . " // " .
								"Fehler: "   . $e->getMessage() .
								PHP_EOL, FILE_APPEND
							 );
		
		}
		
	 				
		return $result;
	}

	
	/*********************************************************************************************************************/
	/*********************************************************************************************************************/
	public function getChannels($p_caller)
	{
		require("config.php");
		$result = "";
		
		try{

			//SQL-Statement zusammenbauen
			$sql = 	"SELECT channel.ise_id, " .
					"		kategorie.ise_id AS 'ise_id_gewerk', " .
					"		kategorie.bezeichnung AS 'gewerk', " .
					"		channel.bezeichnung AS 'channel' " .
					"FROM   channel_kategorie, " . 
					"       channel, " .
					"       kategorie " . 
					"WHERE  channel_kategorie.id_channel = channel.ise_id " .
					"AND    channel_kategorie.id_kategorie = kategorie.ise_id ".
					"AND    kategorie.showUI = 1 " .
					"ORDER BY kategorie.bezeichnung";

			$result = $this->execQuery($sql, __METHOD__);

		} catch(Exception $e) {
	
			//Fehler in Logdatei ausgeben
			file_put_contents(  "hildeDb.log", 
								date_format(date_create(), 'd-m-Y H:i:s') . ": " .
								"Aufrufer: " . $p_caller  . " // " . 
								"Datei: "    . __FILE__   . " // " . 
								"Zeile: "    . __LINE__   . " // " .
								"Methode: "  . __METHOD__ . " // " .
								"Fehler: "   . $e->getMessage() .
								PHP_EOL, FILE_APPEND
							 );
			
			$result = $e->getMessage();
		}
		

		return $result;
	}
	
	
	/*********************************************************************************************************************/
	/*********************************************************************************************************************/
	public function doImportChannels($p_caller)
	{
		$error = 0;
		
		try{

			require("config.php");
			require_once("class.curl.php");
		
			$curl = new Curl();
			$xml = $curl->getXML($path_to_xmlapi_devices, __FUNCTION__);

			$channels = $xml->xpath('//deviceList/device/channel');
			
			//Attribute von Channels auswählen
			$attr_name 		= 'name';
			$attr_address 	= 'address';
			$attr_ise_id 	= 'ise_id';
			
			//alle Channels durchlaufen
			while(list( , $channel) = each($channels)) {
			
				//SQL-Statement zusammenbauen
				$sql = "INSERT INTO channel 
							(
							 ise_id, 
							 seriennummer, 
							 bezeichnung
							)
							VALUES (
							 '" . $channel->attributes()->$attr_ise_id . "', 
							 '" . $channel->attributes()->$attr_address . "', 
							 '" . $channel->attributes()->$attr_name . "'
							)";

				//SQL-Statement ausführen und auf Fehler prüfen
				if ($this->hildeDb->query($sql) === false) {

					//Fehler in Logdatei ausgeben
					file_put_contents(  "hildeDb.log", 
										date_format(date_create(), 'd-m-Y H:i:s') . ": " .
										"Aufrufer: " . $p_caller  . " // " . 
										"Datei: "    . __FILE__   . " // " . 
										"Zeile: "    . __LINE__   . " // " .
										"Methode: "  . __METHOD__ . " // " .
										"Fehler: "   . $this->hildeDb->connect_error .
										PHP_EOL, FILE_APPEND
									 );

					$error = 1;
				}					
			}			
			
		} catch(Exception $e) {
			
			//Fehler in Logdatei ausgeben
			file_put_contents(  "hildeDb.log", 
								date_format(date_create(), 'd-m-Y H:i:s') . ": " .
								"Aufrufer: " . $p_caller  . " // " . 
								"Datei: "    . __FILE__   . " // " . 
								"Zeile: "    . __LINE__   . " // " .
								"Methode: "  . __METHOD__ . " // " .
								"Fehler: "   . $e->getMessage() .
								PHP_EOL, FILE_APPEND
							 );
			
			$error = 1;
		}	
		
		return $error;
	}
	
	
	/*********************************************************************************************************************/
	/*********************************************************************************************************************/
	public function doImportGewerke($p_caller)
	{
		$error = 0;
		
		try{

			require("config.php");
			require_once("class.curl.php");
		
			$curl = new Curl();
			$xml = $curl->getXML($path_to_xmlapi_channel_gewerk, __FUNCTION__);

			$gewerke = $xml->xpath('//functionList/function');
			
			$attr_ise_id 	= 'ise_id';
			
			//alle Gewerke durchlaufen
			while(list( , $gewerk) = each($gewerke)) {
				
				//Channels des aktuellen Gewerks
				$channels = $gewerk->xpath('.//channel');
				
				//Channels des aktuellen Gewerks durchlaufen
				while(list( , $channel) = each($channels)) {
				
					//SQL-Statement zusammenbauen
					$sql = "INSERT INTO channel_kategorie 
								(
								 id_channel, 
								 id_kategorie
								)
								VALUES (
								 '" . $channel->attributes()->$attr_ise_id . "', 
								 '" . $gewerk->attributes()->$attr_ise_id . "'
								)";

					//SQL-Statement ausführen und auf Fehler prüfen
					if ($this->hildeDb->query($sql) === false) {
						
						//Fehler in Logdatei ausgeben
						file_put_contents(  "hildeDb.log", 
											date_format(date_create(), 'd-m-Y H:i:s') . ": " .
											"Aufrufer: " . $p_caller  . " // " . 
											"Datei: "    . __FILE__   . " // " . 
											"Zeile: "    . __LINE__   . " // " .
											"Methode: "  . __METHOD__ . " // " .
											"Fehler: "   . $this->hildeDb->connect_error .
											PHP_EOL, FILE_APPEND
										 );
						
						$error = 1;
					}					
				}				
			}			
			
		} catch(Exception $e) {
			
			//Fehler in Logdatei ausgeben
			file_put_contents(  "hildeDb.log", 
								date_format(date_create(), 'd-m-Y H:i:s') . ": " .
								"Aufrufer: " . $p_caller  . " // " . 
								"Datei: "    . __FILE__   . " // " . 
								"Zeile: "    . __LINE__   . " // " .
								"Methode: "  . __METHOD__ . " // " .
								"Fehler: "   . $this->hildeDb->connect_error .
								PHP_EOL, FILE_APPEND
							 );
			
			$error = 1;
		}	
		
		return $error;
	}
		
	
	/*********************************************************************************************************************/
	/*********************************************************************************************************************/
	public function deleteTable($p_table, $p_caller)
	{
		$error = 0;
		
		try{
			
			//SQL-Statement zusammenbauen
			$sql = "DELETE FROM " . $p_table;

			//SQL-Statement ausführen und auf Fehler prüfen
			if ($this->hildeDb->query($sql) === false) {
				
				//Fehler in Logdatei ausgeben
				file_put_contents(  "hildeDb.log", 
									date_format(date_create(), 'd-m-Y H:i:s') . ": " .
									"Aufrufer: " . $p_caller  . " // " . 
									"Datei: "    . __FILE__   . " // " . 
									"Zeile: "    . __LINE__   . " // " .
									"Methode: "  . __METHOD__ . " // " .
									"Fehler: "   . $this->hildeDb->connect_error .
									PHP_EOL, FILE_APPEND
								 );
				
				$error = 1;
			}			
						
		} catch(Exception $e) {			
		
			//Fehler in Logdatei ausgeben
			file_put_contents(  "hildeDb.log", 
								date_format(date_create(), 'd-m-Y H:i:s') . ": " .
								"Aufrufer: " . $p_caller  . " // " . 
								"Datei: "    . __FILE__   . " // " . 
								"Zeile: "    . __LINE__   . " // " .
								"Methode: "  . __METHOD__ . " // " .
								"Fehler: "   . $e->getMessage() .
								PHP_EOL, FILE_APPEND
							 );
			
			//Fehler in Logdatei ausgeben
			file_put_contents("hildeDb.log", "deleteTable(" . $p_caller . ")-2: " . $e->getMessage());
			
			$error = 1;
		}
		
		return $error;
	}	


	/*********************************************************************************************************************/
	/*********************************************************************************************************************/
	public function getIseId($p_seriennummer, $p_caller)
	{
	
		$result = "";
		
		try{

			//SQL-Statement zusammenbauen
			$sql = 	"SELECT channel.ise_id " .
					"FROM   channel_kategorie, " . 
					"       channel, " .
					"       kategorie " . 
					"WHERE  channel_kategorie.id_channel = channel.ise_id " .
					"AND    channel_kategorie.id_kategorie = kategorie.ise_id ".
					"AND    channel.seriennummer = '" . $p_seriennummer . "' " .
					"AND    kategorie.showUI = 1";

			$rs = $this->execQuery($sql, __METHOD__);
			
			while($row = $rs->fetch_assoc()) {
				$result = $row["ise_id"];
			}
			
		} catch(Exception $e) {
	
			//Fehler in Logdatei ausgeben
			file_put_contents(  "hildeDb.log", 
								date_format(date_create(), 'd-m-Y H:i:s') . ": " .
								"Aufrufer: " . $p_caller  . " // " . 
								"Datei: "    . __FILE__   . " // " . 
								"Zeile: "    . __LINE__   . " // " .
								"Methode: "  . __METHOD__ . " // " .
								"Fehler: "   . $e->getMessage() .
								PHP_EOL, FILE_APPEND
							 );
			
			$result = $e->getMessage();
		}
		

		return $result;
	}	
	
	/*********************************************************************************************************************/
	/*********************************************************************************************************************/
	public function getListOfChannelIseId($p_caller)
	{
	
		$result = "";
		
		try{

			//SQL-Statement zusammenbauen
			$sql = 	"SELECT channel.ise_id, " .
					"		channel.seriennummer " .
					"FROM   channel_kategorie, " . 
					"       channel, " .
					"       kategorie " . 
					"WHERE  channel_kategorie.id_channel = channel.ise_id " .
					"AND    channel_kategorie.id_kategorie = kategorie.ise_id ".
					"AND    kategorie.showUI = 1 " .
					"ORDER BY kategorie.bezeichnung";
		
			$result = $this->execQuery($sql, __METHOD__);
			/*
			while($row = $rs->fetch_assoc()) {
				$result = $row["ise_id"];
			}
			*/
		} catch(Exception $e) {
	
			//Fehler in Logdatei ausgeben
			file_put_contents(  "hildeDb.log", 
								date_format(date_create(), 'd-m-Y H:i:s') . ": " .
								"Aufrufer: " . $p_caller  . " // " . 
								"Datei: "    . __FILE__   . " // " . 
								"Zeile: "    . __LINE__   . " // " .
								"Methode: "  . __METHOD__ . " // " .
								"Fehler: "   . $e->getMessage() .
								PHP_EOL, FILE_APPEND
							 );
			
			$result = $e->getMessage();
		}
		

		return $result;
	}	
	
	/*********************************************************************************************************************/
	/*********************************************************************************************************************/
	public function __destruct()
	{
		try{
			
			//Verbindung zu SQL-Server beenden
			$this->hildeDb->close();		
			
		} catch(Exception $e) {
			
			//Fehler in Logdatei ausgeben
			file_put_contents(  "hildeDb.log", 
								date_format(date_create(), 'd-m-Y H:i:s') . ": " .
								"Aufrufer: " . $p_caller  . " // " . 
								"Datei: "    . __FILE__   . " // " . 
								"Zeile: "    . __LINE__   . " // " .
								"Methode: "  . __METHOD__ . " // " .
								"Fehler: "   . $e->getMessage() .
								PHP_EOL, FILE_APPEND
							 );
			
		}
		
	}	
}

////////////////////////////////////////////////
/*
$hildeDb = new HildeDb("");
echo $hildeDb->getIseId("","OEQ1398339:1");
*/
////////////////////////////////////////////////
/*
$hildeDb = new HildeDb();
echo $hildeDb->deleteTable("todo", "channel");
*/
////////////////////////////////////////////////
/*
//Konfigurationsdaten laden
require("config.php");

//CURL-Aufruf initialisieren
$ch = curl_init();

//Parameter für CURL-Objekt setzen
curl_setopt($ch, CURLOPT_URL, $path_to_xmlapi_channel_gewerk);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, '');

//SOAP-Request ausführen und SOAP-Response in $response speichern
$response = curl_exec($ch); 

//CURL-Objekt zerstören
curl_close($ch);

$hildeDb = new HildeDb();
echo $hildeDb->doImportGewerke("todo", $response);
*/
////////////////////////////////////////////////
/*
//Konfigurationsdaten laden
require("config.php");

//CURL-Aufruf initialisieren
$ch = curl_init();

//Parameter für CURL-Objekt setzen
curl_setopt($ch, CURLOPT_URL, $path_to_xmlapi_devices);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, '');

//SOAP-Request ausführen und SOAP-Response in $response speichern
$response = curl_exec($ch); 

//CURL-Objekt zerstören
curl_close($ch);

$hildeDb = new HildeDb();
echo $hildeDb->doImportChannels("todo", $response);
*/
////////////////////////////////////////////////
/*
$hildeDb = new HildeDb();
$rs = $hildeDb->getChannels("todo");
while($row = $rs->fetch_assoc()) {
	echo $row["gewerk"];
}
*/
?>