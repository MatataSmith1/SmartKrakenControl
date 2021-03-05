<?php
//error_reporting(0);
require('redbean.php');
require('config.php');

global $masterPassword;
global $mysqlHost;
global $mysqlDbName;
global $mysqlUserName;
global $mysqlPassword;

R::setup('mysql:host='.$mysqlHost.
         ';dbname='.$mysqlDbName, 
         $mysqlUsername, 
         $mysqlPassword);
		 
if (isset($_POST['password'])) {
	if ($_POST['password'] == $masterPassword) {
		foreach(getClients() as $client) {
			echo(
				'<id>'.$client->id.'</id>'.
				'<machine>'.$client->machine.'</machine>'.
				'<user>'.$client->user.'</user>'.
				'<os>'.$client->os.'</os>'.
				'<cpu>'.$client->cpu.'</cpu>'.
				'<gpu>'.$client->gpu.'</gpu>'.
				'<ip>'.$client->ip.'</ip>'.
				'<country>'.$client->country.'</country>'.
				'<region>'.$client->region.'</region>'.
				'<status>'.$client->status.'</status>'.
				'<screens>'.$client->screens.'</screens><br>'
			);
		}
	} else {
		die();
	}
} else {
	die();
}

function getClients() {
	$clients = R::findAll('clients', 'minute = ?', 
							[
								date('i')
							]);
	return $clients;
}
?>