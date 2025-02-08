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
     * @return bool|int|void|string
     */
    public function RefreshPlaylist($listId)
    {
        //Include google library
        $client = $this->CI->SecurityModel->initialiseLibrary();

        //Only proceed when the library was successfully included
        if ($client !== false) {
            //Validate the access token required for the api call
            $tokenExpired = $this->CI->SecurityModel->validateAuthToken($client);

            //Continue to the api call or refresh the auth token
            if ($tokenExpired) {
                $data['body'] = 'invalidAction';
                $data['title'] = "Błąd autoryzacji tokenu!";
                $data['errorMessage'] = "Odświeżenie tokenu autoryzującego się nie powiodło.</br>
    Zapisano wszystkie oceny, nie przeniesiono żadnej piosenki.";
            } else {
                //Define service object for making API requests.
                $service = new Google_Service_YouTube($client);
                $queryParams = [
                    'maxResults' => 50,
                    'playlistId' => $this->CI->PlaylistModel->GetListUrlById($listId)
                ];

                //Load songs for the first time. If the request fails, return -2
                $songsJsonArray = [];
                try {
                    $response = $service->playlistItems->listPlaylistItems('snippet', $queryParams);
                } catch (Google_Service_Exception $e) {
                    $errorType = json_decode($e->getMessage())->error->errors[0]->reason;
                    if ($errorType == "playlistNotFound") {
                        return -2;
                    }
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
                        //Perform the api call
                        $queryParams = [
                            'maxResults' => 50,
                            'pageToken' => $pageToken,
                            'playlistId' => $this->CI->PlaylistModel->GetListUrlById($listId)
                        ];

                        $response = $service->playlistItems->listPlaylistItems('snippet', $queryParams);
                        //Save the songs into the array
                        $songsJsonArray[] = $response['items'];
                    }

                    //Get all songs that are already in the list, only urls
                    $songURLs = $this->CI->SongModel->GetURLsOfAllSongsInList($listId);
                    $songURLsArray = [];
                    foreach ($songURLs as $songURL) {
                        $songURLsArray[] = $songURL->SongURL;
                    }

                    //Perform the reloading process - The main array is composed of parsed song arrays
                    $refreshReport = "<pre>";
                    foreach ($songsJsonArray as $songarrays) {
                        //Each of these arrays contains a song object
                        foreach ($songarrays as $song) {
                            //Get all required data to save a song in the database
                            $songURL = $song['snippet']['resourceId']['videoId'];
                            $songPublic = isset($song['snippet']['thumbnails']['medium']['url']);
                            $songThumbnailURL = $songPublic ? $song['snippet']['thumbnails']['medium']['url'] : false;
                            $songTitle = mysqli_real_escape_string($this->CI->db->conn_id, $song['snippet']['title']);
                            $songPlaylistItemsId = $song['id'];

                            //If something goes wrong any incorrect entries will be discarded
                            if (isset($songURL) && isset($songThumbnailURL) && strlen($songTitle) > 0 && isset($songPlaylistItemsId)) {
                                //Check if the song already exists in the database
                                if (in_array($songURL, $songURLsArray)) {
                                    $refreshReport .= $songTitle . " - ⏸<br />";
                                } else if ($songPublic && $this->CI->SongModel->InsertSong($listId, $songURL, $songThumbnailURL, $songTitle, $songPlaylistItemsId)) {
                                    //Attempt to insert the song to the database
                                    $refreshReport .= $songTitle . " - ✔<br />";
                                } else {
                                    //If insertion failed
                                    $refreshReport .= $songURL . " is private - ❌<br />";
                                }
                            }
                        }
                    }

                    //Songs were loaded correctly - Submit a report
                    $refreshReport .= "</pre>";
                    $newReportId = $this->CI->LogModel->SubmitReport(htmlspecialchars($refreshReport));
                    //Create a log
                    $reportSuccessful = $newReportId ? " i dołączono raport." : ", nie udało się zapisać raportu.";
                    $logMessage = "Załadowano nowe nuty na playlistę" . $reportSuccessful;
                    $this->CI->LogModel->CreateLog('playlist', $listId, $logMessage, $newReportId);
                    return true;
                } else return false;
            }
        } else {
            //Could not load the YouTube api
            $data['body'] = 'invalidAction';
            $data['title'] = "Wystąpił Błąd!";
            $data['errorMessage'] = "Nie znaleziono biblioteki Google!";

            $this->load->view('templates/main', $data);
        }
    }
}