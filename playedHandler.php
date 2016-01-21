<?php
/**
 * Created by PhpStorm.
 * User: Lars
 * Date: 21.01.2016
 * Time: 13:55
 */
require 'gameTableFunctions.php';

$playedHandler = new gameTableFunctions();

if (isset($_POST['action'])){
    if ($_POST['action'] == "mark"){
        $playedHandler->markAsPlayed($_POST['game']);
    }
}

header("Refresh:0");