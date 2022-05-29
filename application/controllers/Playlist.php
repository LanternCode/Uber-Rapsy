<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION)){
	session_start();
}

/**
 * Controller responsible for handling views related with playlists.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class Playlist extends CI_Controller {

	public function __construct()
	{
        parent::__construct();
        $this->load->model( 'PlaylistModel' );
		$this->load->model( 'SongsModel' );
		$this->load->helper('cookie');
    }

    /**
     * Opens the playlist dashboard.
     *
     * @return void
     */
    public function dashboard()
    {
        $userAuthenticated = $this->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'playlist/playlistDashboard';
            $data['title'] = "Uber Rapsy | Zarządzaj playlistami!";
            $data['playlists'] = $this->PlaylistModel->GetAllLists();

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Opens the playlist details page.
     *
     * @return void
     */
    public function details()
    {
        $userAuthenticated = $this->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'playlist/details';
            $data['title'] = "Uber Rapsy | Zarządzaj playlistą!";
            $data['ListId'] = isset( $_GET['id'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['id'] ) ) : 0;

            if($data['ListId'] && is_numeric($data['ListId']))
            {
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);

                if($data['playlist'] === false)
                {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono playlisty o podanym numerze id!";
                }
                else
                {
                    $data['songs'] = $this->SongsModel->GetSongsFromList($data['ListId']);
                }
            }
            else
            {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id playlisty lub nie podano go wcale!";
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Opens the playlist quick edit page and processes the form.
     *
     * @return void
     */
    public function quickEdit()
    {
        $userAuthenticated = $this->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'playlist/quickEdit';
            $data['title'] = "Uber Rapsy | Edytuj playlistę!";
            $data['ListId'] = isset( $_GET['id'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['id'] ) ) : 0;

            if($data['ListId'] && is_numeric($data['ListId']))
            {
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);

                if($data['playlist'] === false)
                {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono playlisty o podanym numerze id!";
                }
                else if(isset($_POST['playlistFormSubmitted']))
                {
                    $queryData = [];
                    $queryData['ListId'] = $data['ListId'];
                    $queryData['ListUrl'] = isset($_POST['playlistId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistId'])) : "";
                    $queryData['ListName'] = isset($_POST['playlistName']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistName'])) : "";
                    $queryData['ListDesc'] = isset($_POST['playlistDesc']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistDesc'])) : "";
                    $queryData['ListCreatedAt'] = isset($_POST['playlistDate']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistDate'])) : "";
                    $queryData['ListActive'] = isset($_POST['playlistVisibility']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistVisibility'])) : "";

                    if($queryData['ListUrl'] && $queryData['ListName'] && $queryData['ListDesc'] && $queryData['ListCreatedAt'] && $queryData['ListActive'] != "")
                    {
						print_r($queryData['ListActive']);
                        $this->PlaylistModel->UpdatePlaylist($queryData);
                        $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);
                        $data['resultMessage'] = "Pomyślnie zaktualizowano playlistę!";
                    }
                    else
                    {
                        $data['resultMessage'] = $queryData['ListUrl'] == "" ? "ID Playlisty jest wymagane!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListName'] == "" ? "Nazwa Playlisty jest wymagana!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListDesc'] == "" ? "Opis Playlisty jest wymagany!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListCreatedAt'] == "" ? "Data Stworzenia Playlisty jest wymagana!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListActive'] == "" ? "Status Playlisty jest wymagany!</br>" : '';
                    }
                }
            }
            else
            {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id playlisty lub nie podano go wcale!";
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Allows the user to set a playlist as (in)active.
     *
     * @return void
     */
    public function hidePlaylist()
    {
        $userAuthenticated = $this->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'playlist/hidePlaylist';
            $data['title'] = "Uber Rapsy | Ukryj playlistę";
            $data['ListId'] = isset($_GET['id']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['id'])) : 0;
            $data['HideList'] = isset($_GET['hide']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['hide'])) : false;

            if($data['ListId'] && is_numeric($data['ListId']))
            {
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);

                if($data['playlist'] === false)
                {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono playlisty o podanym numerze id!";
                }
                else if($data['HideList'] === "true")
                {
                    $this->PlaylistModel->SetPlaylistActiveProperty($data['playlist']->ListActive, $data['ListId']);
                    redirect('playlist/details?id='.$data['ListId']);
                }
            }
            else
            {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id playlisty lub nie podano go wcale!";
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Opens a playlist with the required filters and settings.
     *
     * @return void
     */
	public function playlist()
	{
		$data = [];

		$data['ListId'] = isset( $_GET['ListId'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['ListId'] ) ) : 0;
		$data['ListName'] = $this->PlaylistModel->GetPlaylistNameById($data['ListId']);
		$data['Operation'] = isset( $_GET['Reviewer'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['Reviewer'] ) ) : 0;
		$data['Search'] = isset( $_GET['Search'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['Search'] ) ) : 0;
		$data['gradesToDisplay'] = [];
		$data['body'] = 'playlistTest';

		//confirm the playlist exists
		if($data['ListName'] != false)
		{
			$data['title'] = $data['ListName']." | Playlista Uber Rapsy";

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
	            $data['lists'] = $this->PlaylistModel->GetListsIdsAndNames();
			}
			else
			{
				$data['songs'] = $this->SongsModel->GetSongsFromList($data['ListId']);
				$data['lists'] = $this->PlaylistModel->GetListsIdsAndNames();
			}

	        foreach($data['songs'] as $song)
	        {
	            //Display values without decimals at the end if the decimals are only 0
	            $song->SongGradeAdam = $this->TrimTrailingZeroes($song->SongGradeAdam);
	            $song->SongGradeChurchie = $this->TrimTrailingZeroes($song->SongGradeChurchie);
	        }
		}
		else
		{
			$data['body'] = 'invalidAction';
			$data['title'] = "Błąd akcji!";
			$data['errorMessage'] = "Wskazana playlista nie istnieje lub jest prywatna.";
		}

		$this->load->view( 'templates/main', $data );
	}

    /**
     * Updates reviewer grades in a playlist and moves songs between playlists.
     *
     * @return void
     */
	public function update()
	{
        $data = [];
        $data['playlistId'] = $_POST['playlistId'] ?? "invalid";
        $userAuthenticated = $this->authenticateUser();

        //Check if this request comes from a valid playlist
        if($data['playlistId'] === "invalid")
        {
            $data['body']  = 'invalidAction';
            $data['title'] = "Błąd akcji!";
            $data['errorMessage'] = "Nie podano numeru playlisty podczas aktualizacji!";
        }
        else if($userAuthenticated)
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

			echo("<pre>");
			print_r($ratings);
			echo("</pre>");

            foreach($ratings as $rating)
            {
                if($i == 1) $oldAdamRating = floatval($rating);
                else if($i == 2) $songId = $rating;
                else if($i == 3) $adamRating = floatval($rating);
                else if($i == 5) $oldKoscielnyRating = floatval($rating);
                else if($i == 7)
                {
                    $koscielnyRating = floatval($rating);
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
                            $myPath = $_SERVER['DOCUMENT_ROOT'] . (ENVIRONMENT !== 'production' ? '/Dev' : '') . '/Uber-Rapsy/';
                            require_once $myPath . 'vendor/autoload.php';
                            $client = new Google\Client();
                            $client->setAuthConfig($myPath . 'application/api/client_secret.json');
                        } catch(Exception $e) {
                            //The library or the client could not be initiated
                            $library_included = false;
                        }

                        //only proceed when the library was successfully included
                        if($library_included)
                        {
                            //validate the access token required for an api call
                            $tokenExpired = $this->validateAuthToken($client);

                            if($tokenExpired)
                            {
                                //refresh token not found
                                $data['body']  = 'invalidAction';
                                $data['title'] = "Błąd autoryzacji tokenu!";
                                $data['errorMessage'] = "Odświeżenie tokenu autoryzującego nie powiodło się.</br>
                                    Zapisano wszystkie oceny, nie przeniesiono żadnej piosenki.";
                            }
                            else //perform the api call
                            {
                                $newPlaylistId = $rating;

                                //fetch the playlist URL from the database using the id
                                $playlistURL = $this->PlaylistModel->GetListUrlById($newPlaylistId);

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
                                $resourceId->setVideoId($songDetails->SongURL);
                                $playlistItemSnippet->setResourceId($resourceId);
                                $playlistItem->setSnippet($playlistItemSnippet);

                                //add the song to the playlist
                                $response = $service->playlistItems->insert('snippet', $playlistItem);
                                //New PlaylistItemsId is generated, so we need to update it in the db
                                $newSongPlaylistItemsId = $response->id;

                                //delete the song from the earlier playlist
                                $response = $service->playlistItems->delete($songDetails->SongPlaylistItemsId);

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

    /**
     * Updates the playlist with new songs added to it on YouTube.
     *
     * @return void
     */
	public function downloadSongs()
	{
		//id of the list to reload
		$listId = isset( $_GET['ListId'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['ListId'] ) ) : 0;
        $userAuthenticated = $this->authenticateUser();

		//declare default variables
		$data = array(
			'body' => 'downloadSongs',
			'title' => 'Aktualizacja listy!',
			'songsJsonArray' => array(),
			'ListId' => $listId,
			'success' => true
		);

		//Check if the user is allowed to do this action
        if($userAuthenticated)
        {
            //parameters for the api call
            $host = "https://youtube.googleapis.com/youtube/v3/playlistItems";
            $part = "snippet";
            $maxResults = 50; //50 is the most you can get on one page
            $playlistId = $this->PlaylistModel->GetListUrlById($listId);

            //fetch the api key from the api_key file
            if($apiKey = file_get_contents("application/api/api_key.txt"))
            {
                //load songs for the first time
                if($playlistId != "")
                {
										$url = $host.'?part='.$part.'&maxResults='.$maxResults.'&playlistId='.urlencode($playlistId).'&key='.urlencode($apiKey);
                    $firstCall = file_get_contents($url);
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
																				$songPublic = isset($song['snippet']['thumbnails']['medium']['url']) ? true : false;
                                        $songThumbnailURL = $songPublic ? $song['snippet']['thumbnails']['medium']['url'] : false;
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
                                            else if($songPublic && $this->SongsModel->InsertSong($listId, $songURL, $songThumbnailURL, $songTitle, $songPlaylistItemsId))
                                            {
                                                echo $songTitle . " - ✔<br />";
                                            } //if insertion failed
                                            else
                                            {
                                                echo $songURL . " is private - ❌<br />";
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

    /**
     * Opens the new playlist form.
     *
     * @return void
     */
	public function newPlaylist()
	{
        $userAuthenticated = $this->authenticateUser();

        if($userAuthenticated)
        {
            $data = array(
                'body' => 'playlist/addPlaylist',
                'title' => 'Uber Rapsy | Dodaj nową playlistę!'
            );
            $this->load->view('templates/main', $data);
        }
        else redirect('logout');
	}

    /**
     * Processes the Add Playlist form.
     *
     * @return void
     */
    public function addPlaylist()
    {
        $data = [];
        $userAuthenticated = $this->authenticateUser();

        if($userAuthenticated)
        {
            $client = '';
            //include google library
            $library_included = true;
            try {
                $myPath = $_SERVER['DOCUMENT_ROOT'] . (ENVIRONMENT !== 'production' ? '/Dev' : '') . '/Uber-Rapsy/';
                require_once $myPath . 'vendor/autoload.php';
                $client = new Google\Client();
                $client->setAuthConfig($myPath . 'application/api/client_secret.json');
            } catch(Exception $e) {
                //The library or the client could not be initiated
                $library_included = false;
            }

            if($library_included)
            {
                //validate the access token required for an api call
                $tokenExpired = $this->validateAuthToken($client);

                if($tokenExpired)
                {
                    //refresh token not found
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd autoryzacji tokenu!";
                    $data['errorMessage'] = "Odświeżenie tokenu autoryzującego nie powiodło się.</br>
                                    Zapisano wszystkie oceny, nie przeniesiono żadnej piosenki.";
                }
                else
                {
                    $data = array(
                        'body' => 'playlist/addPlaylist',
                        'title' => isset($_POST['playlistName']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistName'])) : "",
                        'description' => isset($_POST['playlistDesc']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistDesc'])) : "",
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
                        $this->PlaylistModel->InsertPlaylist($data);

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
        else redirect('logout');

        $this->load->view( 'templates/main', $data );
    }

    /**
     * Shows and validates the form to add a local playlist.
     *
     * @return void
     */
    public function addLocal()
    {
        $userAuthenticated = $this->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'playlist/addLocalPlaylist';
            $data['title'] = "Uber Rapsy | Dodaj lokalnie playlistę!";

            if(isset($_POST['playlistFormSubmitted']))
            {
                $queryData = [];
                $queryData['ListUrl'] = isset($_POST['playlistId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistId'])) : "";
                $queryData['ListName'] = isset($_POST['playlistName']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistName'])) : "";
                $queryData['ListDesc'] = isset($_POST['playlistDesc']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistDesc'])) : "";
                $queryData['ListCreatedAt'] = isset($_POST['playlistDate']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistDate'])) : "";
                $queryData['ListActive'] = isset($_POST['playlistVisibility']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistVisibility'])) : "";

                if($queryData['ListUrl'] && $queryData['ListName'] && $queryData['ListDesc'] && $queryData['ListCreatedAt'] && $queryData['ListActive'] != "")
                {
                    $this->PlaylistModel->InsertLocalPlaylist($queryData);
                    $data['resultMessage'] = "Pomyślnie dodano playlistę!";
                }
                else
                {
                    $data['resultMessage'] = $queryData['ListUrl'] == "" ? "ID Playlisty jest wymagane!</br>" : '';
                    $data['resultMessage'] .= $queryData['ListName'] == "" ? "Nazwa Playlisty jest wymagana!</br>" : '';
                    $data['resultMessage'] .= $queryData['ListDesc'] == "" ? "Opis Playlisty jest wymagany!</br>" : '';
                    $data['resultMessage'] .= $queryData['ListCreatedAt'] == "" ? "Data Stworzenia Playlisty jest wymagana!</br>" : '';
                    $data['resultMessage'] .= $queryData['ListActive'] == "" ? "Status Playlisty jest wymagany!</br>" : '';
                }
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Allows the user to delete a local playlist.
     *
     * @return void
     */
    public function deleteLocal()
    {
        $userAuthenticated = $this->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'playlist/deleteLocal';
            $data['title'] = "Uber Rapsy | Usuń playlistę";
            $data['ListId'] = isset($_GET['id']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['id'])) : 0;
            $data['DeleteList'] = isset($_GET['delete']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['delete'])) : false;

            if($data['ListId'] && is_numeric($data['ListId']))
            {
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);

                if($data['playlist'] === false)
                {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono playlisty o podanym numerze id!";
                }
                else if($data['DeleteList'] === "true")
                {
                    $this->PlaylistModel->DeleteLocalPlaylist($data['ListId']);
                    redirect('playlistDashboard');
                }
            }
            else
            {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id playlisty lub nie podano go wcale!";
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Allows the user to delete a song from a playlist.
     *
     * @return void
     */
    public function delSong()
    {
        $userAuthenticated = $this->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'song/delSong';
            $data['title'] = "Uber Rapsy | Usuń piosenkę";
            $data['SongId'] = isset($_GET['id']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['id'])) : 0;
            $data['DeleteSong'] = isset($_GET['delete']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['delete'])) : false;

            if($data['SongId'] && is_numeric($data['SongId']))
            {
                $data['song'] = $this->SongsModel->GetSongById($data['SongId']);

                if($data['song'] === false)
                {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono piosenki o podanym numerze id!";
                }
                else if($data['DeleteSong'] === "true")
                {
                    $this->SongsModel->DeleteSong($data['SongId']);
                    redirect('playlistDashboard');
                }
                else
                {
                    $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['song']->ListId);
                }
            }
            else
            {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id piosenki lub nie podano go wcale!";
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Checks whether the user is logged in and has the appropriate role.
     *
     * @return boolean     true if authenticated, false if not
     */
    function authenticateUser(): bool
    {
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? 0;
        $userRole = $_SESSION['userRole'] ?? 0;

        if($userLoggedIn === 1 && $userRole === 'reviewer')
        {
            return true;
        }
        else return false;
    }

    /**
     * Validates the google oauth2 token.
     *
     * @return bool true if token is valid, false if expired.
     */
    function validateAuthToken($client): bool
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
                //run JSON encode to store the token in a cookie
                $accessToken = json_encode($accessToken);
                //delete the old cookie with the expired token
                delete_cookie("UberRapsyToken");
                //set a new cookie with the new token
                set_cookie("UberRapsyToken", $accessToken, 86400);
                //set token_expired to false and proceed
                $token_expired = false;
            }
        }

        return $token_expired;
    }

    /**
     * Trims trailing zeroes from a given number.
     *
     * @param float $nbr number to trim
     * @return float trimmed number
     */
    function TrimTrailingZeroes(float $nbr): float
    {
        return strpos($nbr,'.')!==false ? rtrim(rtrim($nbr,'0'),'.') : $nbr;
    }

}
