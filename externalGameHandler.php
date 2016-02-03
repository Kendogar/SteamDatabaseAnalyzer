<?php

/**
 * Created by PhpStorm.
 * User: Lars
 * Date: 03.02.2016
 * Time: 13:50
 */
require 'gameTableFunctions.php';

$externalGameHandler = new gameTableFunctions();

if (isset($_POST['filename'])){
    if ($_POST['filename'] != ""){
        $externalGameHandler->extractGamesFromList($_POST['filename']);
    }
}