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
		else $data['songs'] = $this->SongsModel->GetSongsFromList($data['ListId']);

		$this->load->view( 'templates/main', $data );
	}

	public function update()
	{
		$data = [];
		$ratings = [];
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

			$i++;
			if($i > 7) $i = 0;
		}

		$data['body']  = 'update';
		$data['title'] = "Oceny Zapisane!";
		$data['playlistId'] = $_POST['playlistId'];

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

		//parameters for the api call
		$host = "https://youtube.googleapis.com/youtube/v3/playlistItems";
		$part = "snippet";
		$maxResults = 50;
		$playlistId = $this->ListsModel->getListUrlById($listId);
		//TODO: Validate this url belongs to Uber Rapsy otherwise deny connection, for now will deny on empty
		$apiKey = "AIzaSyDEd8FFVtvzEa83qE6tzKoinP3B-Ef96Og";

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

			//now that we have all songs from the playlist we can start reloading

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
							//data that is not a song arrat can be dropped
							if(is_array($song))
							{
								//get all required data to save a song in the database
								$songURL = "youtu.be/" . $song['snippet']['resourceId']['videoId'];
								$songThumbnailURL = $song['snippet']['thumbnails']['medium']['url'];
								$songTitle = mysqli_real_escape_string( $this->db->conn_id, $song['snippet']['title'] );

								//if something goes wrong any incorrect entries will be discarded
								if(isset($songURL) && isset($songThumbnailURL) && isset($songTitle))
								{
									//check if the song already exists in the database
									if(in_array($songURL, $songURLsArray))
									{
										echo $songTitle . " - ⏸<br />";
									} //attempt to insert the song to the database
									else if($this->SongsModel->InsertSong($listId, $songURL, $songThumbnailURL, $songTitle))
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

		$this->load->view( 'templates/main', $data );
	}

	public function newPlaylist()
	{
		$data = array(
			'body' => 'addPlaylist'
		);

		$this->load->view( 'templates/main', $data );
	}

	public function addPlaylist()
	{
		$data = array(
			'body' => 'addPlaylist',
			'title' => isset( $_POST['playlistName'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_POST['playlistName'] ) ) : null,
			'description' => isset( $_POST['playlistDesc'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_POST['playlistDesc'] ) ) : null,
			'link' => '',
			'resultMessage' => ''
		);

		$myPath = $_SERVER['DOCUMENT_ROOT'] . '/Dev/Uber-Rapsy/';
		require_once $myPath . 'application/libraries/Google/vendor/autoload.php';

		//validate the form
		if (
			$data['title'] != null &&
			$data['description'] != null
		) {

			$token_expired = false;
			//validate yt authentication

			if (true) {
				$myPath = $_SERVER['DOCUMENT_ROOT'] . '/Dev/UberRapsy/';
				require_once $myPath . 'application/libraries/Google/vendor/autoload.php';

				$client = new Google\Client();

				$client->setAuthConfig($myPath . 'application/api/client_secret.json');

				// Exchange authorization code for an access token.
				$accessToken = $_SESSION['token'];
				$client->setAccessToken($accessToken);

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

				//save the playlist into database
				$this->ListsModel->insertPlaylist($data);

				$data['resultMessage'] = "Playlista zapisana!";
			} else {
				$data['resultMessage'] = "Nie udało się połączyć z YT, zaloguj się do YT ponownie.";
			}
		} else {
			$data['resultMessage'] = "Proszę wyślij formularz ponownie, wysłane dane są niepoprawne.";
		}

		$this->load->view( 'templates/main', $data );
	}

}
