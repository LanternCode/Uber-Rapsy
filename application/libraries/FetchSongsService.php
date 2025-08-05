<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A service retrieving songs from YouTube.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 *
 * @property SecurityModel $SecurityModel
 */
class FetchSongsService
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /**
     * Set up the service object required for YouTube API requests.
     *
     * @return array|Google_Service_YouTube the service object or an array describing the error
     */
    public function setupGoogleService(): array|Google_Service_YouTube
    {
        //Include the YT API library
        $client = $this->CI->SecurityModel->initialiseLibrary();

        //Only proceed if the library was successfully loaded
        if ($client !== false) {
            //Validate the access token required for the api call
            $tokenExpired = $this->CI->SecurityModel->validateAuthToken($client);

            //Check if a correct token was found
            if ($tokenExpired) {
                $err = array(
                    'code' => "TNF",
                    'displayMessage' => "Odświeżenie tokenu autoryzującego się nie powiodło!"
                );
            }
            else {
                //Define the service object for making YT API requests
                return new Google_Service_YouTube($client);
            }
        }
        else {
            $err = array(
                'code' => "LNF",
                'displayMessage' => "Nie znaleziono biblioteki YT API podczas odświeżania playlisty!"
            );
        }

        return $err;
    }

    /**
     * Fetch all requested videos from YouTube.
     *
     * @param array $songIds an array of YouTube video IDs
     * @return array|bool the videos found or false if the request failed
     */
    public function fetchVideoItemsFromYT(array $songIds): array|bool
    {
        //Connect to the YT API
        $service = $this->setupGoogleService();
        if (is_array($service) && isset($service['code']) && isset($service['displayMessage']))
            return $service;

        //Start fetching videos
        $videosFetched = [];
        $allVideosToFetch = count($songIds);
        for ($i = 0; $i < $allVideosToFetch; $i += 50) {
            $videosToFetch = min($allVideosToFetch - $i, 50);
            $songIdsToFetch = array_slice($songIds, $i, $videosToFetch);
            $queryParams['id'] = $songIdsToFetch;

            try {
                $response = $service->videos->listVideos('snippet', $queryParams);
            }
            catch (Throwable $e) {
                return false;
            }

            if (isset($response['items']))
                $videosFetched[] = $response['items'];
            else return false;
        }

        if (count($videosFetched) > 0)
            return $videosFetched;
        else return [];
    }

    /**
     * Fetch all songs from a YouTube playlist.
     *
     * @param string $playlistId the remote playlist id
     * @return array|bool|Google_Service_YouTube the items found, the error code and message array, or a bool
     */
    public function fetchPlaylistItemsFromYT(string $playlistId): array|bool|Google_Service_YouTube
    {
        //Connect to the YT API
        $service = $this->setupGoogleService();
        if (is_array($service) && isset($service['code']) && isset($service['displayMessage']))
            return $service;

        //Define the API call params
        $queryParams = [
            'maxResults' => 50,
            'playlistId' => $playlistId
        ];

        //Load songs for the first time. If the request fails, return the error
        $songsJsonArray = [];
        try {
            $response = $service->playlistItems->listPlaylistItems('snippet', $queryParams);
        }
        catch (Throwable $e) {
            return array(
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
            else return [];

            return $songsJsonArray;
        }
        else return false;
    }
}