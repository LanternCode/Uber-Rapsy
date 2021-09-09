<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION)){
	session_start();
}

class Playlist extends CI_Controller {

	public function __construct()
	{
        parent::__construct();
        $this->load->model( 'ListsModel' );
		$this->load->model( 'SongsModel' );
		$this->load->helper('cookie');
    }

	public function index()
	{

	}

	public function playlist()
	{
		$data = [];

		$data['ListId'] = isset( $_GET['ListId'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['ListId'] ) ) : 0;
		$data['Operation'] = isset( $_GET['Reviewer'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['Reviewer'] ) ) : 0;
		$data['Search'] = isset( $_GET['Search'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['Search'] ) ) : 0;
		$data['gradesToDisplay'] = [];
		$data['body'] = 'playlist';
		$data['title'] = "Playlist selected!";

		//There are 3 available choices: filter by grade (tierlist), search by title, display all
		if($data['Operation'])
		{
			$data['songs'] = $this->SongsModel->GetTopSongsFromList($data['ListId'], $data['Operation']);
			$data['body'] = 'tierlist';

			foreach($data['songs'] as $song)
			{
				$gradeA = $song->SongGradeAdam;
				if($gradeA != NULL && !in_array($gradeA, $data['gradesToDisplay']) && $data['Operation'] == "Adam")
					array_push($data['gradesToDisplay'], $gradeA);

				$gradeB = $song->SongGradeChurchie;
				if($gradeB != NULL && !in_array($gradeB, $data['gradesToDisplay']) && $data['Operation'] == "Churchie")
					array_push($data['gradesToDisplay'], $gradeB);

				$gradeC = bcdiv(($song->SongGradeAdam+$song->SongGradeChurchie)/2, 1, 2);
				if($gradeC != NULL && !in_array($gradeC, $data['gradesToDisplay']) && $data['Operation'] == "Average")
					array_push($data['gradesToDisplay'], $gradeC);
			}
			rsort($data['gradesToDisplay']);
		}
		else if ($data['Search'])
		{
			$data['songs'] = $this->SongsModel->GetSongsFromList($data['ListId'], $data['Search']);
		}
		else
		{
			$data['songs'] = $this->SongsModel->GetSongsFromList($data['ListId']);
			$data['lists'] = $this->ListsModel->GetListsIdsAndNames();
		}

		$this->load->view( 'templates/main', $data );
	}

	public function update()
	{
        $data = [];
        $data['playlistId'] = $_POST['playlistId'] ?? "invalid";

        //Check if this request comes from a valid playlist
        if($data['playlistId'] === "invalid")
        {
            $data['body']  = 'invalidAction';
            $data['title'] = "Błąd akcji!";
            $data['errorMessage'] = "Nie podano numeru playlisty podczas aktualizacji!";
        } //Now check if the user performing this action is allowed to do it
        else if(isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn'] && isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer")
        {
            $data['body']  = 'update';
            $data['title'] = "Oceny Zapisane!";
            $i = 0;

            //separate results into key and value respectively
            foreach ($_POST as $key => $value) {
                $ratings[$i] = $key;
                $ratings[$i+1] = $value;
                $i += 2;
            }

            //pair keys and values to update the score
            $i = 0;
            $songId = "";
            $oldAdamRating = 0;
            $oldKoscielnyRating = 0;
            $adamRating = 0;
            $koscielnyRating = 0;

            foreach($ratings as $rating)
            {
                if($i == 1) $oldAdamRating = $rating;
                else if($i == 2) $songId = $rating;
                else if($i == 3) $adamRating = $rating;
                else if($i == 5) $oldKoscielnyRating = $rating;
                else if($i == 7)
                {
                    $koscielnyRating = $rating;
                    $songId = substr($songId, 2);

                    if($oldAdamRating != $adamRating || $oldKoscielnyRating != $koscielnyRating)
                        $this->SongsModel->UpdateSongWithScores($songId, $adamRating, $koscielnyRating);
                }
                else if($i == 9)
                {
                    //0 means we do not update the playlist
                    if($rating > 0)
                    {
                        //include google library
                        $library_included = true;
                        try {
                            $myPath = $_SERVER['DOCUMENT_ROOT'] . '/Dev/Uber-Rapsy/';
                            require_once $myPath . 'application/libraries/Google/vendor/autoload.php';
                            $client = new Google\Client();
                            $client->setAuthConfig($myPath . 'application/api/client_secret.json');
                        } catch(Exception $e) {
                            //The library or the client could not be initiated
                            $library_included = false;
                        }

                        //only proceed when the library was successfully included
                        if($library_included)
                        {
                            //get the currently saved token from the cookie
                            $accessToken = get_cookie("UberRapsyToken");
                            //Check if the cookie contained the token
                            $token_expired = false;
                            if (!is_null($accessToken)) {
                                try {
                                    //If yes, check if it is valid and not expired
                                    $client->setAccessToken($accessToken);
                                    $token_expired = $client->isAccessTokenExpired();
                                } catch (Exception $e) {
                                    //exception raised means the format is invalid
                                    $token_expired = true;
                                }
                            } else {
                                //cookie did not exist or returned null
                                $token_expired = true;
                            }

                            //if the token expired, fetch the refresh token and attempt a refresh
                            if($token_expired)
                            {
                                //first fetch the refresh token from api/refresh_token.txt
                                if($refresh_token = file_get_contents("application/api/refresh_token.txt")) {
                                    //get a new token
                                    $client->refreshToken($refresh_token);
                                    //save the new token
                                    $accessToken = $client->getAccessToken();
                                    //save the token to the google client library
                                    $client->setAccessToken($accessToken);
                                    //run JSON encode to store the token in a cookie
                                    $accessToken = json_encode($accessToken);
                                    //delete the old cookie with the expired token
                                    delete_cookie("UberRapsyToken");
                                    //set a new cookie with the new token
                                    set_cookie("UberRapsyToken", $accessToken, 86400);
                                    //set token_expired to false and proceed
                                    $token_expired = false;
                                } else {
                                    //refresh token not found - display refresh instructions
                                    $data['body']  = 'refreshToken';
                                    $data['title'] = "Błąd autoryzacji tokenu!";
                                }
                            }

                            //Direct API interaction
                            if(!$token_expired)
                            {
                                $newPlaylistId = $rating;

                                //fetch the playlist URL from the database using the id
                                $playlistURL = $this->ListsModel->getListUrlById($newPlaylistId);

                                //fetch the song URL and PlaylistItemId from the database using the local id
                                $songDetails = $this->SongsModel->GetSongDetailsForMoving($songId);

                                // Define service object for making API requests.
                                $service = new Google_Service_YouTube($client);

                                // Define the $playlistItem object, which will be uploaded as the request body.
                                $playlistItem = new Google_Service_YouTube_PlaylistItem();

                                // Add 'snippet' object to the $playlistItem object.
                                $playlistItemSnippet = new Google_Service_YouTube_PlaylistItemSnippet();
                                $playlistItemSnippet->setPlaylistId($playlistURL);
                                //with a large enough number, last position available will be taken
                                $playlistItemSnippet->setPosition(1000);
                                $resourceId = new Google_Service_YouTube_ResourceId();
                                $resourceId->setKind('youtube#video');
                                $resourceId->setVideoId($songDetails[0]->SongURL);
                                $playlistItemSnippet->setResourceId($resourceId);
                                $playlistItem->setSnippet($playlistItemSnippet);

                                //add the song to the playlist
                                $response = $service->playlistItems->insert('snippet', $playlistItem);
                                //New PlaylistItemsId is generated, so we need to update it in the db
                                $newSongPlaylistItemsId = $response->id;

                                //delete the song from the earlier playlist
                                $response = $service->playlistItems->delete($songDetails[0]->SongPlaylistItemsId);

                                //move the song in the database
                                $this->SongsModel->UpdateSongPlaylist($songId, $newPlaylistId, $newSongPlaylistItemsId);
                            }
                        }
                        else
                        {
                            //could not load the library
                            $data['body']  = 'invalidAction';
                            $data['title'] = "Wystąpił Błąd!";
                            $data['errorMessage'] = "Nie znaleziono biblioteki google!";
                        }
                    }
                }

                $i++;
                if($i > 9) $i = 0;
            }
        }
        else
        {
            //The user is not allowed to update anything in the system
            $data['body']  = 'invalidAction';
            $data['title'] = "Wystąpił Błąd!";
            $data['errorMessage'] = "Nie posiadasz uprawnień do wykonywania tej akcji.";
        }

		$this->load->view( 'templates/main', $data );
	}

	public function downloadSongs()
	{
		//id of the list to reload
		$listId = isset( $_GET['ListId'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['ListId'] ) ) : 0;

		//declare default variables
		$data = array(
			'body' => 'downloadSongs',
			'title' => 'Reloading songs!',
			'songsJsonArray' => array(),
			'ListId' => $listId,
			'success' => true
		);

		//Check if the user is allowed to do this action
        if(isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn'] && isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer")
        {
            //parameters for the api call
            $host = "https://youtube.googleapis.com/youtube/v3/playlistItems";
            $part = "snippet";
            $maxResults = 50; //50 is the most you can get on one page
            $playlistId = $this->ListsModel->getListUrlById($listId);

            //fetch the api key from the api_key file
            if($apiKey = file_get_contents("application/api/api_key.txt"))
            {
                //load songs for the first time
                if($playlistId != "")
                {
                    $firstCall = file_get_contents($host.'?part='.$part.'&maxResults='.$maxResults.'&playlistId='.$playlistId.'&key='.$apiKey);
                    $downloadedSongs = json_decode($firstCall, true);
                    array_push($data['songsJsonArray'], $downloadedSongs);
                }

                //how many songs total - returns 0 if null
                $allResults = $downloadedSongs['pageInfo']['totalResults'] ?? 0;

                if($allResults > 0)
                {
                    //keep loading songs until all are loaded
                    for($scannedResults = $downloadedSongs['pageInfo']['resultsPerPage']; $scannedResults < $allResults; $scannedResults += $downloadedSongs['pageInfo']['resultsPerPage'])
                    {
                        //get the token of the next page
                        $pageToken = $downloadedSongs['nextPageToken'];
                        //perform the api call
                        $nextCall = file_get_contents($host.'?part='.$part.'&maxResults='.$maxResults.'&pageToken='.$pageToken.'&playlistId='.$playlistId.'&key='.$apiKey);
                        //decode the result from json to array
                        $downloadedSongs = json_decode($nextCall, true);
                        //save the songs into the array
                        array_push($data['songsJsonArray'], $downloadedSongs);
                    }

                    //get all songs that are already in the list, only urls
                    $songURLs = $this->SongsModel->GetURLsOfAllSongsInList($listId);
                    $songURLsArray = [];
                    foreach($songURLs as $songURL)
                    {
                        array_push($songURLsArray, $songURL->SongURL);
                    }

                    //perform the reloading process
                    //the main array is composed of parsed data arrays
                    foreach($data['songsJsonArray'] as $songarrays)
                    {
                        //each of these arrays contains a list of songs
                        foreach($songarrays as $songlist)
                        {
                            //some data is not in an array and is unnecessary for this process
                            if(is_array($songlist))
                            {
                                //each song is an array itself
                                foreach($songlist as $song)
                                {
                                    //data that is not a song array can be dropped
                                    if(is_array($song))
                                    {
                                        //get all required data to save a song in the database
                                        $songURL = $song['snippet']['resourceId']['videoId'];
                                        $songThumbnailURL = $song['snippet']['thumbnails']['medium']['url'];
                                        $songTitle = mysqli_real_escape_string( $this->db->conn_id, $song['snippet']['title'] );
                                        $songPlaylistItemsId = $song['id'];

                                        //if something goes wrong any incorrect entries will be discarded
                                        if(isset($songURL) && isset($songThumbnailURL) && isset($songTitle) && isset($songPlaylistItemsId))
                                        {
                                            //check if the song already exists in the database
                                            if(in_array($songURL, $songURLsArray))
                                            {
                                                echo $songTitle . " - ⏸<br />";
                                            } //attempt to insert the song to the database
                                            else if($this->SongsModel->InsertSong($listId, $songURL, $songThumbnailURL, $songTitle, $songPlaylistItemsId))
                                            {
                                                echo $songTitle . " - ✔<br />";
                                            } //if insertion failed
                                            else
                                            {
                                                echo $songTitle . " - ❌<br />";
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    $data['success'] = false;
                }
            }
            else
            {
                //API key not found
                $data['body']  = 'invalidAction';
                $data['title'] = "Nie znaleziono klucza API!";
                $data['errorMessage'] = "Nie znaleziono klucza API.";
            }
        }
        else
        {
            //The user is not allowed to update anything in the system
            $data['body']  = 'invalidAction';
            $data['title'] = "Wystąpił Błąd!";
            $data['errorMessage'] = "Nie posiadasz uprawnień do wykonywania tej akcji.";
        }

		$this->load->view( 'templates/main', $data );
	}

	public function newPlaylist()
	{
	    $data = [];
        //Check if the user is allowed to do this action
        if(isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn'] && isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer")
        {
            $data['body'] = 'addPlaylist';
            $data['title'] = 'Dodaj nową playlistę!';
        }
        else
        {
            //The user is not allowed to update anything in the system
            $data['body']  = 'invalidAction';
            $data['title'] = "Wystąpił Błąd!";
            $data['errorMessage'] = "Nie posiadasz uprawnień do wykonywania tej akcji.";
        }
		$this->load->view( 'templates/main', $data );
	}

	public function addPlaylist()
	{
	    $data = [];
        //Check if the user is allowed to do this action
        if(isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn'] && isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer")
        {
            //include google library
            $library_included = true;
            try {
                $myPath = $_SERVER['DOCUMENT_ROOT'] . '/Dev/Uber-Rapsy/';
                require_once $myPath . 'application/libraries/Google/vendor/autoload.php';
                $client = new Google\Client();
                $client->setAuthConfig($myPath . 'application/api/client_secret.json');
            } catch(Exception $e) {
                //The library or the client could not be initiated
                $library_included = false;
            }

            //only proceed when the library was successfully included
            if($library_included)
            {
                //get the currently saved token from the cookie
                $accessToken = get_cookie("UberRapsyToken");
                //Check if the cookie contained the token
                $token_expired = false;
                if (!is_null($accessToken)) {
                    try {
                        //If yes, check if it is valid and not expired
                        $client->setAccessToken($accessToken);
                        $token_expired = $client->isAccessTokenExpired();
                    } catch (Exception $e) {
                        //exception raised means the format is invalid
                        $token_expired = true;
                    }
                } else {
                    //cookie did not exist or returned null
                    $token_expired = true;
                }

                //if the token expired, fetch the refresh token and attempt a refresh
                if($token_expired)
                {
                    //first fetch the refresh token from api/refresh_token.txt
                    if($refresh_token = file_get_contents("application/api/refresh_token.txt")) {
                        //get a new token
                        $client->refreshToken($refresh_token);
                        //save the new token
                        $accessToken = $client->getAccessToken();
                        //save the token to the google client library
                        $client->setAccessToken($accessToken);
                        //run JSON encode to store the token in a cookie
                        $accessToken = json_encode($accessToken);
                        //delete the old cookie with the expired token
                        delete_cookie("UberRapsyToken");
                        //set a new cookie with the new token
                        set_cookie("UberRapsyToken", $accessToken, 86400);
                        //set token_expired to false and proceed
                        $token_expired = false;
                    } else {
                        //refresh token not found - display refresh instructions
                        $data['body']  = 'refreshToken';
                        $data['title'] = "Błąd autoryzacji tokenu!";
                    }
                }

                //main functionality of this method
                if(!$token_expired)
                {
                    $data = array(
                        'body' => 'addPlaylist',
                        'title' => isset( $_POST['playlistName'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_POST['playlistName'] ) ) : "",
                        'description' => isset( $_POST['playlistDesc'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_POST['playlistDesc'] ) ) : "",
                        'link' => '',
                        'resultMessage' => ''
                    );

                    //validate the form
                    if($data['title'] != "" && $data['description'] != "")
                    {
                        // Define service object for making API requests.
                        $service = new Google_Service_YouTube($client);

                        // Define the $playlist object, which will be uploaded as the request body.
                        $playlist = new Google_Service_YouTube_Playlist();

                        // Add 'snippet' object to the $playlist object.
                        $playlistSnippet = new Google_Service_YouTube_PlaylistSnippet();
                        $playlistSnippet->setDefaultLanguage('en');
                        $playlistSnippet->setDescription($data['description']);
                        $playlistSnippet->setTags(['Uber Rapsy', 'API call']);
                        $playlistSnippet->setTitle($data['title']);
                        $playlist->setSnippet($playlistSnippet);

                        // Add 'status' object to the $playlist object.
                        $playlistStatus = new Google_Service_YouTube_PlaylistStatus();
                        $playlistStatus->setPrivacyStatus('public');
                        $playlist->setStatus($playlistStatus);

                        //save the api call response
                        $response = $service->playlists->insert('snippet,status', $playlist);

                        //get the unique id of a playlist from the response
                        $data['link'] = $response->id;

                        //save the playlist into the database
                        $this->ListsModel->insertPlaylist($data);

                        $data['resultMessage'] = "Playlista zapisana!";
                    } else {
                        $data['resultMessage'] = "Proszę wyślij formularz ponownie, wprowadzone dane są niepoprawne.";
                    }
                }
            }
            else
            {
                //could not load the library
                $data['body']  = 'invalidAction';
                $data['title'] = "Wystąpił Błąd!";
                $data['errorMessage'] = "Nie znaleziono biblioteki google!";
            }
        }
        else
        {
            //The user is not allowed to update anything in the system
            $data['body']  = 'invalidAction';
            $data['title'] = "Wystąpił Błąd!";
            $data['errorMessage'] = "Nie posiadasz uprawnień do wykonywania tej akcji.";
        }

        $this->load->view( 'templates/main', $data );
	}

}
