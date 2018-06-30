var strSeriennummer,
	strEventName,
	strEventValue;
	
function showMessage(messageHTML) {
	$('#events-box').append(messageHTML);
}

$(document).ready(function(){

	/************************************************************************************/
	/* HILDE.SOCKET.SERVER beendet														*/
	/************************************************************************************/
	websocket.onopen = function(event) { 
		showMessage("<b>Verbindung zum HILDE.SOCKET.SERVER hergestellt</b><br/>");
	}
	
	/************************************************************************************/
	/* Vom HILDE.SOCKET.SERVER eingehende JSON-Pakete werden hier entgegengenommen. 	*/
	/* Die Controls werden entsprechend aktualisiert.									*/
	/************************************************************************************/
	websocket.onmessage = function(event) {
		
		//Message-Objekt parsen
		var hmEventData = JSON.parse(event.data);		

		//Event-Informationen Variablen zuweisen
		ise_id			= hmEventData.message[0];
		strEventName	= hmEventData.message[1];
		strEventValue	= hmEventData.message[2];

		//Ausgabe der empfangenen Events
		$('#events-box').val(showMessage(ise_id + ' //' + strEventName + '// ' + strEventValue + '<br/>'));
		
		//Control aktualisieren
		if(strEventName == 'STATE'){
			
			$('#' + ise_id).attr("value", strEventValue);

			if(strEventValue)
				$('#' + ise_id).css("background-color", "red");
			else
				$('#' + ise_id).css("background-color", "green");
		}
	};		

	/************************************************************************************/
	/* Fehlermeldung vom HILDE.SOCKET.SERVER							 				*/
	/************************************************************************************/
	websocket.onerror = function(event){ 
		showMessage("<b>Bei der Verbindung zum HILDE.SOCKET.SERVER ist ein Fehler aufgetreten</b><br/>");
	};

	/************************************************************************************/
	/* HILDE.SOCKET.SERVER beendet														*/
	/************************************************************************************/
	websocket.onclose = function(event){
		showMessage("<b>Verbindung zum HILDE.SOCKET.SERVER beendet</b><br/>");
	}; 
 });


/************************************************************************************/
/* Nach Button-Click neues Value über XML-API an CCU2 senden						*/
/************************************************************************************/
function newValue(p_ise_id) {

	//Deklaration lokaler Variablen
	var current_value, 
	    new_value;

	current_value = document.getElementById(p_ise_id).value;
	
	//neuer Status ist Gegenteil von aktuellem Status
	if(current_value == 'true') new_value = 'false'; else new_value = 'true';

	//Status (im Attribut 'value') aktualisieren
	document.getElementById(p_ise_id).value = new_value;

	//XMLHttpRequest-Instanz erstellen
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			
			//Status aller Controls über XML-API aktualsieren
			refreshState(p_ise_id);			
		}
	};

	//in URL die ise_id des auslösenden Controls setzen
	var final_path = path_to_xmlapi_statuschange.replace('{ise_id}', p_ise_id);

	//XML-API aufrufen zur Statusänderung
	xhttp.open('GET', final_path + new_value, true);
	xhttp.send();

}

/************************************************************************************/
/* Die aktuellen Stati der angezeigten Controls	werden über die XML-API abgerufen.	*/
/************************************************************************************/
function refreshState(ise_id) {
	
	var xhttp = new XMLHttpRequest();
	
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {}
	};  
  xhttp.open('GET', 'http://localhost:81/HILDE.FRAMEWORK/hilde_xmlapi_broker.php', true);
  xhttp.send();
}