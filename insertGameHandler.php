<?php

/**
 * Created by PhpStorm.
 * User: Lars
 * Date: 14.01.2016
 * Time: 16:04
 */
require 'gameTableFunctions.php';

$requestHandler = new gameTableFunctions();

$requestHandler->addCustomGame($_POST['name'], $_POST['genre'], $_POST['metacritic'], $_POST['timetobeat']);

header("Location: gameTable.html");