<?php 


class Curl
{

	public function __construct(){}	
	
	/*********************************************************************************************************************/
	/*********************************************************************************************************************/	
	public function getXML($p_url, $p_caller, $p_post_fields = "")
	{
		$result = "";
		
		try{

			//CURL-Aufruf initialisieren
			$ch = curl_init();

			//HTTP-Header erstellen und SOAP-Request anhängen
			$headers = array(
				"Content-type: text/xml;",
				"Accept: text/xml",
				"Cache-Control: no-cache",
				"Pragma: no-cache",
				"Content-length: ".strlen($p_post_fields),
				); 
			
			//Parameter für CURL-Objekt setzen
			curl_setopt($ch, CURLOPT_URL, $p_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $p_post_fields);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			//SOAP-Request ausführen und SOAP-Response in $response speichern
			$response = curl_exec($ch); 
			
			//CURL-Objekt zerstören
			curl_close($ch);
			
			//XML-Objekt instanzieren und Channel-Liste selektieren
			$result = new SimpleXMLElement($response);
						
		} catch(Exception $e) {
			
			//Fehler in Logdatei ausgeben
			file_put_contents("class_curl_error.txt", $e->getMessage());
			
		}			
	 				
		return $result;
	}
}
?>