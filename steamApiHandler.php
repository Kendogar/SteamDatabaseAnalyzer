<?php

/**
 * Created by PhpStorm.
 * User: Lars
 * Date: 18.01.2016
 * Time: 13:40
 */
require 'gameTableFunctions.php';

$requestHandler = new gameTableFunctions();

if (isset($_POST['webAPI'])){
    if ($_POST['webAPI'] != ""){
        $requestHandler->updateSteamGameList($_POST['webAPI']);
        print_r($_POST['webAPI']);
    }
}

