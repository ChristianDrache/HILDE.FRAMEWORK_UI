<html>
<head>
	<title>HILDE.ControlUI</title>
	<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
	<script src="hilde.socket.server-1.1.js"></script>
	</head>
	<body id="idbody">
	
		<!-- BEGIN: serverseitiges Initialisieren der Controls -->
		
			<?php require_once('ui_initialize-1.1.php'); ?>
		
		<!-- END: serverseitiges Initialisieren der Controls -->
		
		
		<div id="events-box"></div>
		<input type="submit" id="btnSend" name="send-chat-message" value="Send" >
	</body>
</html>