<?php

require 'Unirest.php';
/**
 * Created by PhpStorm.
 * User: Lars
 * Date: 13.01.2016
 * Time: 08:52
 */

class gameTableFunctions {

    function extractGamesFromList($listName) {
        $file = fopen($listName, "r");
        $tempArray = array();

        while (!feof($file)) {
            $tempArray[] = fgets($file);
        }
        fclose($file);

        $ownedGamesArray = array();

        foreach($tempArray as $potentialGame) {
            if(substr($potentialGame,0,2) == "--"){
                $ownedGamesArray[] = substr($potentialGame, 3);
            }
        }
        foreach($ownedGamesArray as $ownedGame) {
            $this->addCustomGame($ownedGame);
        }
    }

    function addCustomGame($postName, $postGenre = "", $postMetacritic = "", $postTimetobeat = "") {
        $servername = "localhost:3306";
        $username = "root";
        $password = "";
        $dbname = "steamgames";

        $nameString = $postName;
        $type = "game";
        $genreString = $postGenre;
        $header_image = "";
        $metacritic = $postMetacritic;
        $recommendations = "manually added";
        $timetobeat = $this->getDataFromHowlongtobeat($nameString);

        if(strcmp($timetobeat, "no info")) {
            $timetobeat = $postTimetobeat;
        }

        if (!$this->checkDatabaseByName($nameString)) {
            $response = Unirest\Request::get("https://ahmedakhan-game-review-information-v1.p.mashape.com/api/v1/information?game_name=".$nameString,
                array(
                    "X-Mashape-Key" => "QyibUbgQ9zmshV0vBfcODcQk9x8yp1L7QdkjsnDlchW0tBoziv",
                    "Accept" => "application/json"
                )
            );
            print_r($response);
            if ($response->code == "200"){
                $json = $response->raw_body;
                $obj = json_decode($json, true);
                if (array_key_exists('result', $obj)){
                    $obj = $obj['result'];

                    $genreString = "";
                    foreach ($obj['genre'] as $genre) {
                        $genreString .= $genre.", ";
                    }
                    $genreString = substr($genreString, 0, strlen($genreString)-2);

                    $header_image = $obj['thumbnail'];

                    $metacritic = $obj['metacritic']['criticScore'];
                }
            }

            $conn = new mysqli($servername, $username, $password, $dbname);
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "INSERT INTO games (name, type, header_image, genres, recommendations, timetobeat, metacritic, played)
                VALUES ('".$nameString."', '".$type."', '".$header_image."', '".$genreString."', '".$recommendations."', '".$timetobeat."', '".$metacritic."', 'no')";
            if ($conn->query($sql) === TRUE) {
                echo "Record updated successfully";
            } else {
                echo "Error updating record: " . $conn->error;
            }

            $conn->close();
        } else {
            echo "already in database";
        }
    }

    function checkDatabaseByName($gameTitle) {
        $servername = "localhost:3306";
        $username = "root";
        $password = "";
        $dbname = "steamgames";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $gameTitle = str_replace("'", "''", $gameTitle);

        $query = "SELECT name from games WHERE name LIKE '$gameTitle'";

        //echo $query."<br>";

        $result = $conn->query($query);
        if (is_string($result) || is_bool($result)){
            print_r($result);
            $gameInDB = true;
        } else {
            $rowCount = mysqli_num_rows($result);
            if($rowCount > 0){
                $gameInDB = true;
            } else {
                $gameInDB = false;
            }
        }

        //echo $gameInDB."<br>";
        return $gameInDB;
    }

    function checkDatabaseByAppId($appId) {
        $servername = "localhost:3306";
        $username = "root";
        $password = "";
        $dbname = "steamgames";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        //echo "Checking if game is in database <br>";
        $query = "SELECT appid from games WHERE appid LIKE '$appId'";

        //echo $query."<br>";
        $result = $conn->query($query);
        if (is_string($result) || is_bool($result)){
            print_r($result);
            $gameInDB = true;
        } else {
            $rowCount = mysqli_num_rows($result);
            if($rowCount > 0){
                $gameInDB = true;
            } else {
                $gameInDB = false;
            }
        }
        //echo $gameInDB."<br>";
        return $gameInDB;
    }

    function updateDatabase($gameArray) {
        $servername = "localhost:3306";
        $username = "root";
        $password = "";
        $dbname = "steamgames";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $genreString = "";
        $recommendationString = "";
        $metacriticString = "";
        $timeToBeatString = "";
        $nameString = str_replace("'", "''", $gameArray['name']);

        if(array_key_exists('metacritic', $gameArray) && is_array($gameArray)) {
            $metacriticString = $gameArray['metacritic'];
        }

        if(array_key_exists('recommendations', $gameArray) && is_array($gameArray)) {
            $recommendationString = $gameArray['recommendations']['total'];
        }

        if(array_key_exists('genres', $gameArray) && is_array($gameArray)) {
            foreach ($gameArray['genres'] as $genre) {
                $genreString.= $genre['description'].", ";
            }
        $genreString = substr($genreString,0,strlen($genreString)-2);
        }

        $timeToBeatName = preg_replace("/(™|®|©|&trade;|&reg;|&copy;|&#8482;|&#174;|&#169;)/", "", $gameArray['name']);

        $timeToBeatString = $this->getDataFromHowlongtobeat($timeToBeatName);

        //echo "updating db with game: ".$nameString."<br>";

        $sql = "INSERT INTO games (name, type, header_image, genres, recommendations, timetobeat, metacritic, appid, played)
                VALUES ('".$nameString."', '".$gameArray['type']."', '".$gameArray['header_image']."', '".$genreString."', '".$recommendationString."', '".$timeToBeatString."', '".$metacriticString."', '".$gameArray['appId']."', 'no')";
        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully<br>";
        } else {
            echo "Error updating record: " . $conn->error;
        }

        $conn->close();
    }

    function deleteGameFromDb($gameName) {

        $servername = "localhost:3306";
        $username = "root";
        $password = "";
        $dbname = "steamgames";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $query = "DELETE FROM games WHERE name LIKE '$gameName'";

        //echo $query;
        if ($conn->query($query) === TRUE) {
            echo "Record deleted successfully";
        } else {
            echo "Error deleting record: " . $conn->error;
        }

        $conn->close();

    }

    function markAsPlayed($gameName) {

        $servername = "localhost:3306";
        $username = "root";
        $password = "";
        $dbname = "steamgames";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $query = "UPDATE games SET played='yes' WHERE name LIKE '$gameName'";

        if ($conn->query($query) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn->error;
        }

        $conn->close();
    }

    function getGameListFromApiKey($key) {

        $url = "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=".$key."&steamid=76561197960434622&include_appinfo=1&include_played_free_games=0&format=json";
        $json = file_get_contents($url);
        $obj = json_decode($json, true);
        $response = $obj['response'];
        $listOfGames = $response['games'];

        return $listOfGames;
    }

    function getGameCountFromApiKey($key) {

        $url = "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=".$key."&steamid=76561197960434622&include_appinfo=1&include_played_free_games=0&format=json";
        $json = file_get_contents($url);
        $obj = json_decode($json, true);
        $response = $obj['response'];
        $gameCount = $response['game_count'];

        return $gameCount;
    }

    function getDetailedGameInfo($appid) {

        $infoArray = array();

        $url = "http://store.steampowered.com/api/appdetails?appids=".$appid;
        $json = file_get_contents($url);
        $obj = json_decode($json, true);
        if (array_key_exists('data',$obj[$appid]) && is_array($obj[$appid])){
            $result = $obj[$appid]['data'];

            $infoArray['appId'] = $appid;
            $infoArray['name'] = $obj[$appid]['data']['name'];
            $infoArray['type'] = $obj[$appid]['data']['type'];
            $infoArray['header_image'] = $obj[$appid]['data']['header_image'];
            if (array_key_exists('metacritic',$obj[$appid]['data']) && is_array($obj[$appid]['data'])) {
                $infoArray['metacritic'] = $obj[$appid]['data']['metacritic']['score'];
            }
            if(array_key_exists('genres', $obj[$appid]['data']) && is_array($obj[$appid]['data'])) {
                $infoArray['genres'] = $obj[$appid]['data']['genres'];
            }

            if(array_key_exists('recommendations', $obj[$appid]['data']) && is_array($obj[$appid]['data'])) {
                $infoArray['recommendations'] = $obj[$appid]['data']['recommendations'];
            }

            echo "Info retrieved - checking if name is in db: ".$infoArray['name']. "<br>";

            if (!$this->checkDatabaseByName($infoArray['name'])) {
                $this->updateDatabase($infoArray);
            }
        }
        return $infoArray;
    }

    function updateSteamGameList($key) {

        $infoOnAllGames = array();
        //echo "Retrieving Game List from Steam Web API <br>";

        $games = $this->getGameListFromApiKey($key);

        //echo "Iterating over Game List <br>";
        $counter = 0;

        foreach($games as $game) {
           // echo "<br><br>***************************************************<br>";
           // echo "Checking Game Nr ".$counter." with appid: ".$game['appid']."<br>";

            if (!$this->checkDatabaseByAppId($game['appid'])){

                //echo "Game not in DB - retrieving info <br>";

                $infoOnAllGames[] = $this->getDetailedGameInfo($game['appid']);
                sleep(2);
            } else {
                //echo "Not updating, game is in db<br>";
            }

            $counter++;

        }

        return $infoOnAllGames;
    }

    function getDataFromHowlongtobeat($gameTitle) {
        $url = 'http://howlongtobeat.com/search_main.php?page=1';
        $data = array('queryString' => "".$gameTitle, 't' => 'games', 'sorthead' => 'popular', 'sortd' => 'Normal Order', 'plat' => '', 'detail' => '0');

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) {
            $timeToBeat = "no info";

        } else {
            $searchString = 'title="'.$gameTitle.'"';
            $gamePosition = strpos($result, $searchString);
            $timePositionStart = strpos($result, '100">', $gamePosition)+5;
            $timePositionEnd = strpos($result, '</div', $timePositionStart);

            if (strlen(substr($result,$timePositionStart, $timePositionEnd-$timePositionStart)) < 50 ) {
                $timeToBeat = substr($result,$timePositionStart, $timePositionEnd-$timePositionStart);
            } else {
                $timePositionStart = strpos($result, '40">', $gamePosition)+4;
                $timePositionEnd = strpos($result, '</div', $timePositionStart);
                if (strlen(substr($result,$timePositionStart, $timePositionEnd-$timePositionStart)) < 50 ) {
                    $timeToBeat = substr($result,$timePositionStart, $timePositionEnd-$timePositionStart);
                } else {
                    $timePositionStart = strpos($result, '60">', $gamePosition)+4;
                    $timePositionEnd = strpos($result, '</div', $timePositionStart);
                    if (strlen(substr($result,$timePositionStart, $timePositionEnd-$timePositionStart)) < 50 ) {
                        $timeToBeat = substr($result,$timePositionStart, $timePositionEnd-$timePositionStart);
                        } else {
                        $timePositionStart = strpos($result, '50">', $gamePosition)+4;
                        $timePositionEnd = strpos($result, '</div', $timePositionStart);
                        if (strlen(substr($result,$timePositionStart, $timePositionEnd-$timePositionStart)) < 50 ) {
                            $timeToBeat = substr($result,$timePositionStart, $timePositionEnd-$timePositionStart);
                            } else {
                            $timeToBeat = "no info";
                        }
                    }
                }
            }

        }
        return $timeToBeat;
    }

}

