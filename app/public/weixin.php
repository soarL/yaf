<?php 
	file_put_contents('../../log/weixin.log',json_encode( $_GET));
	echo $_GET['echostr'];