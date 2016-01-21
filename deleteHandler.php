<?php
/**
 * Created by PhpStorm.
 * User: Lars
 * Date: 19.01.2016
 * Time: 08:53
 */
require 'gameTableFunctions.php';

$deleteHandler = new gameTableFunctions();


if (isset($_POST['action'])){
    if ($_POST['action'] == "delete"){
        $deleteHandler->deleteGameFromDb($_POST['game']);
    }
}

header("Refresh:0");

