<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A service dedicated to refreshing playlist contents
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 *
 * @property PlaylistModel $PlaylistModel
 * @property SongModel $SongModel
 * @property LogModel $LogModel
 * @property SecurityModel $SecurityModel
 * @property CI_DB_mysqli_driver $db
 */
class RefreshPlaylistService
{
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();

        // Load models
        $this->CI->load->model('SongModel');
        $this->CI->load->model('SecurityModel');
        $this->CI->load->model('LogModel');
        $this->CI->load->model('PlaylistModel');
    }

    /**
     * Fetches all the songs contained in the live playlist with the given id from YouTube
     * Then compares them with the locally saved copy and adds what is missing
     * A report is generated and returned if everything went well
     * A false is returned if the api call failed to return anything
     * -1 is returned if the api key is not found
     * -2 is returned if the YouTube playlist is not found
     *
     * @param $listId
     * @return bool|int|void|string|string[]
     */
    public function refreshPlaylist($listId)
    {
        //Include the YT API library
        $client = $this->CI->SecurityModel->initialiseLibrary();

        //Only proceed if the library was successfully loaded
        if ($client !== false) {
            //Validate the access token required for the api call
            $tokenExpired = $this->CI->SecurityModel->validateAuthToken($client);

            //Continue to the api call or refresh the auth token
            if ($tokenExpired) {
                $err = array(
                    'code' => "TNF",
                    'displayMessage' => "Odświeżenie tokenu autoryzującego się nie powiodło!"
                );
            }
            else {
                //Define the service object for making YT API requests
                $service = new Google_Service_YouTube($client);
                $queryParams = [
                    'maxResults' => 50,
                    'playlistId' => $this->CI->PlaylistModel->GetListUrlById($listId)
                ];

                //Load songs for the first time. If the request fails, return with an error
                $songsJsonArray = [];
                try {
                    $response = $service->playlistItems->listPlaylistItems('snippet', $queryParams);
                }
                catch (Google_Service_Exception | Throwable $e) {
                    $err = array(
                        'code' => "RF",
                        'displayMessage' => "Wskazana playlista jest prywatna lub podano niepoprawny link. Zaktualizuj go i spróbuj jeszcze raz!",
                        'errorType' => json_decode($e->getMessage())->error->errors[0]->reason ?? 'unknown',
                        'errorObject' => json_decode($e) ?? 'unknown',
                        'errorMessage' => json_decode($e->getMessage()) ?? 'unknown'
                    );
                }

                //How many songs total - assign 0 if null
                $songsJsonArray[] = $response['items'];
                $allResults = $response['pageInfo']['totalResults'] ?? 0;

                //If results were returned, continue the process
                if ($allResults > 0) {
                    //Keep loading songs until all are loaded
                    for ($scannedResults = $response['pageInfo']['resultsPerPage'] ?? 150000;
                         $scannedResults < $allResults;
                         $scannedResults += $response['pageInfo']['resultsPerPage']) {
                        //Get the token of the next page
                        $pageToken = $response['nextPageToken'];
                        //Perform calls to the PlaylistItems API until all items are retrieved
                        $queryParams = [
                            'maxResults' => 50,
                            'pageToken' => $pageToken,
                            'playlistId' => $this->CI->PlaylistModel->GetListUrlById($listId)
                        ];
                        $response = $service->playlistItems->listPlaylistItems('snippet', $queryParams);
                        //Save the songs into the local array
                        $songsJsonArray[] = $response['items'];
                    }

                    //Get the URLs of all songs that are currently in the playlist
                    $songURLs = $this->CI->SongModel->getURLsOfPlaylistSongs($listId);
                    $songURLsArray = [];
                    foreach ($songURLs as $songURL) {
                        $songURLsArray[] = $songURL->SongURL;
                    }

                    print('<pre>');
                    print($songsJsonArray);
                    print("</pre>");
                    die();

                    //Perform the reloading process - The main array is composed of parsed song arrays
                    $refreshReport = "<pre>";
                    foreach ($songsJsonArray as $songArrays) {
                        //Each of these arrays contains a song object
                        foreach ($songArrays as $song) {
                            //Get all required data to save a song in the database
                            $songURL = $song['snippet']['resourceId']['videoId'];
                            $songPublic = isset($song['snippet']['thumbnails']['medium']['url']);
                            $songThumbnailURL = $songPublic ? $song['snippet']['thumbnails']['medium']['url'] : false;
                            $songChannelName = '';
                            $songTitle = mysqli_real_escape_string($this->CI->db->conn_id, $song['snippet']['title']);
                            $songPlaylistItemsId = $song['id'];

                            //Discard incorrect and empty entries
                            if (isset($songURL) && isset($songThumbnailURL) && isset($songChannelName) && strlen($songTitle) > 0 && isset($songPlaylistItemsId)) {
                                //Check if the song is public in YouTube
                                if (!$songPublic) {
                                    //the entry is private/deleted from YT
                                    $refreshReport .= $songURL . " jest prywatna - ❌<br />";
                                    continue;
                                }

                                //Check if the song already exists in the database
                                $existingSongId = $this->CI->SongModel->songExists($songURL, $songTitle, $songChannelName);
                                if ($existingSongId) {
                                    //Check if the playlist_song already exists in the database
                                    $existingPlaylistSongId = $this->CI->SongModel->playlistSongExists($listId, $existingSongId);
                                    if ($existingPlaylistSongId <= 0) {
                                        //Insert the song into the playlist
                                        $playlistSongId = $this->CI->SongModel->insertPlaylistSong($listId, $existingSongId);
                                        $refreshReport .= $songTitle . " - ✔<br />";
                                    }
                                    else {
                                        $refreshReport .= $songTitle . " - ⏸<br />";
                                    }
                                }
                                else {
                                    //Insert the song into the database
                                    $songId = $this->CI->SongModel->insertSong($songURL, $songThumbnailURL, $songTitle, $songPlaylistItemsId, $songChannelName);

                                    //Insert the song into the playlist
                                    $playlistSongId = $this->CI->SongModel->insertPlaylistSong($listId, $songId);
                                    $refreshReport .= $songTitle . " - ✔<br />";
                                }
                            }
                        }
                    }

                    //Songs were loaded correctly - submit a report
                    $refreshReport .= "</pre>";
                    $newReportId = $this->CI->LogModel->SubmitReport(htmlspecialchars($refreshReport));
                    //Create a log with the report
                    $reportSuccessful = $newReportId ? " i dołączono raport." : ", nie udało się zapisać raportu.";
                    $logMessage = "Załadowano nowe nuty na playlistę" . $reportSuccessful;
                    $this->CI->LogModel->createLog('playlist', $listId, $logMessage, $newReportId);
                    $err = true;
                } else $err = false;
            }
        }
        else {
            $err = array(
                'code' => "LNF",
                'displayMessage' => "Nie znaleziono biblioteki YT API podczas odświeżania playlisty!"
            );
        }

        //Return an error message based on how the refreshing went - "" means everything went well
        if ($err === false) {
            return "Wskazana na YT playlista jest pusta! Sprawdź w ustawieniach czy podano poprawny link!";
        }
        elseif (isset($err['code'])) {
            if (in_array($err['code'], ["LNF", "TNF"])) {
                return $err['displayMessage'];
            }
            elseif ($err['code'] === "RF") {
                return $err['displayMessage'];
            }
        }
        else return "";
    }
}