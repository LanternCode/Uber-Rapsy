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
        $this->CI->load->model('SongModel');
        $this->CI->load->model('SecurityModel');
        $this->CI->load->model('LogModel');
        $this->CI->load->model('PlaylistModel');
    }

    public function fetchVideoItemsFromYT(array $songIds): mixed
    {
        //Fetch playlist items and video items to add new songs
        //Fetch video items directly to add new songs
        //For playlists, fetch playlist items specifically for playlistItemsId but also video items to add new songs


        //Include the YT API library
        $client = $this->CI->SecurityModel->initialiseLibrary();
        //Define the service object for making YT API requests
        $tokenExpired = $this->CI->SecurityModel->validateAuthToken($client);
        $service = new Google_Service_YouTube($client);
        $queryParams['id'] = $songIds;
        $response = $service->videos->listVideos('snippet', $queryParams);
        return $response ?? [];
    }

    /**
     * Fetches all songs from a youtube playlist
     *
     * @param string $resourceId the local playlist id / remote playlist id, or video id
     * @param bool $detailedReporting whether to return detailed error messages, or just an empty array of items []
     * @return mixed the items found, the error code and message array, or a bool
     */
    public function fetchPlaylistItemsFromYT(string $resourceId, bool $detailedReporting = false): mixed
    {
        //Include the YT API library
        $client = $this->CI->SecurityModel->initialiseLibrary();

        //Only proceed if the library was successfully loaded
        if ($client !== false) {
            //Validate the access token required for the api call
            $tokenExpired = $this->CI->SecurityModel->validateAuthToken($client);

            //Continue to the api call or return an error
            if ($tokenExpired) {
                $err = array(
                    'code' => "TNF",
                    'displayMessage' => "Odświeżenie tokenu autoryzującego się nie powiodło!"
                );
            }
            else {
                //Define the service object for making YT API requests
                $service = new Google_Service_YouTube($client);

                //When done for a playlist, the url must be fetched first
                $resourceId = $detailedReporting ? $this->CI->PlaylistModel->GetListUrlById($resourceId) : $resourceId;
                $queryParams = [
                    'maxResults' => 50,
                    'playlistId' => $resourceId
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

                if (isset($response)) {
                    //How many songs total - assign 0 if null
                    $songsJsonArray[] = $response['items'];
                    $allResults = $response['pageInfo']['totalResults'] ?? 0;

                    //If results were returned, continue the process
                    if ($allResults > 0) {
                        //Keep loading songs until all are loaded
                        for (
                            $scannedResults = $response['pageInfo']['resultsPerPage'] ?? 150000;
                            $scannedResults < $allResults;
                            $scannedResults += $response['pageInfo']['resultsPerPage']
                        ) {
                            //Get the token of the next page
                            $pageToken = $response['nextPageToken'];
                            //Perform calls to the PlaylistItems API until all items are retrieved
                            $queryParams['pageToken'] = $pageToken;
                            $response = $service->playlistItems->listPlaylistItems('snippet', $queryParams);
                            //Save the songs into the local array
                            $songsJsonArray[] = $response['items'];
                        }
                    }
                    else return false;

                    return $songsJsonArray;
                }
            }
        }
        else {
            $err = array(
                'code' => "LNF",
                'displayMessage' => "Nie znaleziono biblioteki YT API podczas odświeżania playlisty!"
            );
        }

        if ($detailedReporting)
            return $err;
        else return [];
    }

    /**
     * Fetches all the songs contained in the live playlist with the given id from YouTube
     * Then compares them with the locally saved copy and adds what is missing
     * A report is generated and attached if everything went well
     * An error message is returned ("" means no errors)
     *
     * @param $listId int
     * @return string
     */
    public function refreshPlaylist(int $listId): string
    {
        //Fetch the playlist videos from YT
        $songsJsonArray = $this->fetchPlaylistItemsFromYT($listId, "playlist", true);

        //Return an error message based on the content fetched - "" means everything went well
        if ($songsJsonArray === false) {
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
        else {
            //Perform the reloading process - The main array is composed of parsed song arrays
            $refreshReport = "<pre>";
            foreach ($songsJsonArray as $songArrays) {
                //Each of these arrays contains a song object
                foreach ($songArrays as $song) {
                    //Get all required data to save a song in the database
                    $songURL = $song['snippet']['resourceId']['videoId'];
                    $songPublic = isset($song['snippet']['thumbnails']['medium']['url']);
                    $songThumbnailURL = $songPublic ? $song['snippet']['thumbnails']['medium']['url'] : false;
                    $songChannelName = $song['snippet']['videoOwnerChannelTitle'];
                    $songTitle = $song['snippet']['title'];
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
                                $playlistSongId = $this->CI->SongModel->insertPlaylistSong($listId, $existingSongId, $songPlaylistItemsId);
                                $refreshReport .= $songTitle . " - ✔<br />";
                            }
                            else {
                                $refreshReport .= $songTitle . " - ⏸<br />";
                            }
                        }
                        else {
                            //Insert the song into the database
                            $songId = $this->CI->SongModel->insertSong($songURL, $songThumbnailURL, $songTitle, $songChannelName);

                            //Insert the song into the playlist
                            $playlistSongId = $this->CI->SongModel->insertPlaylistSong($listId, $songId, $songPlaylistItemsId);
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

            return "";
        }
    }
}