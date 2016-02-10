<?php

/**
 * Created by PhpStorm.
 * User: Lars
 * Date: 14.01.2016
 * Time: 16:03
 */
require 'gameTableFunctions.php';

class gameListHandler
{
    function buildTable($sortBy = null, $filters = array(), $random = false , $played = "") {
        $querystring = "SELECT * FROM games";

        if(sizeof($filters) > 0) {
            $count = 0;

            foreach ($filters as $key => $value) {
                if ($count == 0){
                    switch ($key) {
                        case 'name':
                            $querystring .= " WHERE $key LIKE '$value' OR $key LIKE '%$value' OR $key LIKE '$value%' OR $key LIKE '%$value%'";
                            break;
                        case 'genres':
                            $querystring .= " WHERE $key LIKE '$value' OR $key LIKE '%$value' OR $key LIKE '$value%' OR $key LIKE '%$value%'";
                            break;
                        case 'metacritic':
                            $querystring .= " WHERE $key > $value";
                            break;
                        case 'recommendations':
                            $querystring .= " WHERE $key > $value";
                            break;
                    }
                } else {
                    switch ($key) {
                        case 'name':
                            $querystring .= " AND WHERE $key LIKE '$value' OR $key LIKE '%$value' OR $key LIKE '$value%' OR $key LIKE '%$value%'";
                            break;
                        case 'genre':
                            $querystring .= " AND WHERE $key LIKE '$value' OR $key LIKE '%$value' OR $key LIKE '$value%' OR $key LIKE '%$value%'";
                            break;
                        case 'metacritic':
                            $querystring .= " AND WHERE $key > $value";
                            break;
                        case 'recommendations':
                            $querystring .= " AND WHERE $key > $value";
                            break;
                    }
                }
                $count++;
            }
        }

        if ($random) {
            if($played != "" && sizeof($filters) > 0) {
                $querystring .= " AND played LIKE 'no'";
            }
            if($played != "" && !(sizeof($filters) > 0)) {
                $querystring .= " WHERE played LIKE 'no'";
            }

            $querystring = "SELECT * FROM
                           (".$querystring.") AS temp
                            ORDER BY RAND() LIMIT 1";
        } else {

            switch ($sortBy) {
                case 'name':
                    $querystring .= " ORDER BY name ASC";
                    break;
                case 'genre':
                    $querystring .= " ORDER BY genres ASC";
                    break;
                case 'metacritic':
                    $querystring .= " ORDER BY metacritic DESC";
                    break;
                case 'recommendations':
                    $querystring .= " ORDER BY recommendations DESC";
                    break;
                case null:
                    break;
            }
        }


        $resultGames = $this->getGamesFromDb($querystring);

        $htmlTable = "";

        foreach ($resultGames as $resultGame) {
            $htmlTable .= "<tr><td><img style='height:75px' src='".$resultGame['header_image']."'</img></td>
                                <td>".$resultGame['name']."</td>
                                <td>".$resultGame['genres']."</td>
                                <td>".$resultGame['metacritic']."</td>
                                <td>".$resultGame['timetobeat']."</td>
                                <td>".$resultGame['recommendations']."</td>
                                <td><button type='button' class='myButton deleteButton' name='".$resultGame['name']."'>Delete</button></td>";
            if(!$random) {
                if($resultGame['played'] == "no") {
                    $htmlTable .= "<td><button type='button' class='myButton playedButton' name='".$resultGame['name']."'>Done</button></td><td>".$resultGame['doskir']."</td></tr>";
                } else {
                    $htmlTable .= "<td>Yes</td><td>".$resultGame['doskir']."</td></tr>";
                }
            } else {
                $htmlTable .= "<td>".$resultGame['doskir']."</td></tr>";
            }
        }

        echo $htmlTable;
        return $htmlTable;


    }

    function getGamesFromDb($query) {
        $servername = "localhost:3306";
        $username = "root";
        $password = "";
        $dbname = "steamgames";

        // Create connection
        $conn = mysqli_connect($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $tempResult = mysqli_query($conn, $query);
        $result = mysqli_fetch_all($tempResult, MYSQLI_ASSOC);

        return $result;
    }
}

$handler = new gameListHandler();
$sorting = null;
$filters = array();
$random = null;
$played = "";

if (isset($_POST['name'])){
    if ($_POST['name'] != "") {
        $filters['name'] = $_POST['name'];
    }
}
if (isset($_POST['metacritic'])){
    if ($_POST['metacritic'] != ""){
        $filters['metacritic'] = $_POST['metacritic'];
    }
}
if (isset($_POST['recommendations'])){
    if ($_POST['recommendations'] != ""){
        $filters['recommendations'] = $_POST['recommendations'];
    }
}
if (isset($_POST['genre'])){
    if ($_POST['genre'] != ""){
        $filters['genres'] = $_POST['genre'];
    }
}
if (isset($_POST['random'])){
    if ($_POST['random'] != ""){
        $random = true;
        $played = "no";
    }
}

$handler->buildTable($sorting, $filters, $random, $played);