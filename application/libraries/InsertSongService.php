<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A service dedicated to inserting songs to YT playlists
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
 */
class InsertSongService
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('SongModel');
        $this->CI->load->model('SecurityModel');
        $this->CI->load->model('LogModel');
        $this->CI->load->model('PlaylistModel');
    }

    /**
     * Load the YT API library and validate the auth token
     *
     * @param $newPlaylistDetails object target playlist for moving or copying
     * @param $currentSong object a song object being moved or copied
     * @return array [false, error message] or objects required to make YT api requests
     */
    private function preliminaryChecks(object $newPlaylistDetails, object $currentSong): array
    {
        //Include google library
        $client = $this->CI->SecurityModel->initialiseLibrary();

        //Only proceed when the library was successfully included
        if ($client !== false) {
            //Validate the access token required for the api call
            $tokenExpired = $this->CI->SecurityModel->validateAuthToken($client);
            if (!$tokenExpired) {
                //Define service object for making API requests
                $service = new Google_Service_YouTube($client);

                //Define the $playlistItem object, which will be uploaded as the request body
                $playlistItem = new Google_Service_YouTube_PlaylistItem();

                //Add 'snippet' object to the $playlistItem object
                $playlistItemSnippet = new Google_Service_YouTube_PlaylistItemSnippet();
                $playlistItemSnippet->setPlaylistId($newPlaylistDetails->ListUrl);

                //Set the resources
                $resourceId = new Google_Service_YouTube_ResourceId();
                $resourceId->setKind('youtube#video');
                $resourceId->setVideoId($currentSong->SongURL);
                $playlistItemSnippet->setResourceId($resourceId);
                $playlistItem->setSnippet($playlistItemSnippet);

                return [$service, $playlistItem];
            }
            else return [false, "Odświeżenie tokenu autoryzującego nie powiodło się.</br>Zapisano wszystkie oceny, nie przeniesiono żadnej piosenki."];
        }
        else return [false, "Nie znaleziono biblioteki Youtube API!"];
    }

    /**
     * Move a song from one playlist to another where at least one of them is integrated with YT
     *
     * @param $playlist object source playlist object
     * @param $currentSong object the song object being moved
     * @param $newPlaylistId int target playlist id
     * @param $localResultMessage string a string holding the song update display text
     * @return string an error message (or empty string if all went well)
     */
    public function moveSongBetweenIntegratedPlaylists(object $playlist, object $currentSong, int $newPlaylistId, string &$localResultMessage): string
    {
        //Check whether the API was loaded correctly
        $newPlaylistDetails = $this->CI->PlaylistModel->fetchPlaylistById($newPlaylistId);
        [$service, $playlistItem] = $this->preliminaryChecks($newPlaylistDetails, $currentSong);
        if ($service === false) {
            return $playlistItem;
        }

        //Establish the source playlist details
        $newSongPlaylistItemsId = '';
        $oldPlaylistDetails = $playlist !== false ? $playlist : $this->CI->PlaylistModel->fetchPlaylistById($currentSong->ListId);

        //Perform the YT side of the move - only if either playlist is integrated
        if (!$oldPlaylistDetails->ListIntegrated && $newPlaylistDetails->ListIntegrated) {
            //This playlist is local and target playlist is integrated with yt so add the song to the integrated playlist
            $response = $service->playlistItems->insert('snippet', $playlistItem);
            //New PlaylistItemsId is generated, so we need to capture it to update it in the db
            $newSongPlaylistItemsId = $response->id;
            //Create a log for the playlist
            $this->CI->LogModel->createLog("playlist", $newPlaylistDetails->ListId, "Nuta ".$currentSong->SongTitle." dodana do zintegrowanej playlisty w wyniku przeniesienia z ".$oldPlaylistDetails->ListName);
        }
        elseif ($oldPlaylistDetails->ListIntegrated && !$newPlaylistDetails->ListIntegrated) {
            //This playlist is integrated with yt, and the target playlist is local - delete the song from the integrated playlist
            $response = $service->playlistItems->delete($currentSong->SongPlaylistItemsId);
            //Create a log for the playlist
            $this->CI->LogModel->createLog("playlist", $oldPlaylistDetails->ListId, "Nuta ".$currentSong->SongTitle." usunięta z zintegrowanej playlisty w wyniku przeniesienia do ".$newPlaylistDetails->ListName);
        }
        elseif ($oldPlaylistDetails->ListIntegrated && $newPlaylistDetails->ListIntegrated) {
            //Both playlists are integrated with yt so add the song to the new playlist
            $response = $service->playlistItems->insert('snippet', $playlistItem);
            //Create a log of the song being added in the playlist's record
            $this->CI->LogModel->createLog("playlist", $newPlaylistDetails->ListId, "Nuta ".$currentSong->SongTitle." dodana do zintegrowanej playlisty w wyniku przeniesienia z ".$oldPlaylistDetails->ListName);
            //New PlaylistItemsId is generated, so we need to capture it to update it in the db
            $newSongPlaylistItemsId = $response->id;
            //Both playlists are integrated with yt so delete the song from the old playlist
            $response = $service->playlistItems->delete($currentSong->SongPlaylistItemsId);
            //Create a log of this deletion in the playlist's record
            $this->CI->LogModel->createLog("playlist", $oldPlaylistDetails->ListId, "Nuta ".$currentSong->SongTitle." usunięta z zintegrowanej playlisty w wyniku przeniesienia do ".$newPlaylistDetails->ListName);
        }

        //Based on the target playlist status, the update is different
        if (!$newPlaylistDetails->ListIntegrated) {
            //Target playlist is local - move the song in the local database
            $updateSuccess = $this->CI->SongModel->updateLocalSongPlaylist($currentSong->SongId, $newPlaylistId);
        }
        else {
            //Target playlist is integrated
            $updateSuccess = $this->CI->SongModel->updateIntegratedSongPlaylist($currentSong->SongId, $newPlaylistId, $newSongPlaylistItemsId);
        }

        //Log the move in the particular song's record
        $localResultMessage .= ($localResultMessage == "" ? "\t" : "<br>\t");
        if ($updateSuccess) {
            $localResultMessage .= "Przeniesiono z playlisty ".$oldPlaylistDetails->ListName." do ".$newPlaylistDetails->ListName;
            $this->CI->LogModel->createLog("song", $currentSong->SongId, "Nuta przeniesiona z playlisty ".$oldPlaylistDetails->ListName." do ".$newPlaylistDetails->ListName);
        }
        else {
            $localResultMessage .= "Nie udało się przenieść do ".$newPlaylistDetails->ListName;
            $this->CI->LogModel->createLog("song", $currentSong->SongId, "Nie udało się przenieść z ".$oldPlaylistDetails->ListName." do ".$newPlaylistDetails->ListName);
        }

        return "";
    }

    /**
     * Copy a song to a playlist integrated with YT
     *
     * @param $currentSong object the song object being moved
     * @param $newPlaylistId int target playlist id
     * @param $newSongId int copied song's id
     * @param $localResultMessage string a string holding the song update display text
     * @return string an error message (or empty string if all went well)
     */
    public function copySongToIntegratedPlaylist(object $currentSong, int $newPlaylistId, int $newSongId, string &$localResultMessage): string
    {
        //Check whether the API was loaded correctly
        $newPlaylistDetails = $this->CI->PlaylistModel->fetchPlaylistById($newPlaylistId);
        [$service, $playlistItem] = $this->preliminaryChecks($newPlaylistDetails, $currentSong);
        if ($service === false) {
            return $playlistItem;
        }

        //Add the song to the integrated playlist
        $response = $service->playlistItems->insert('snippet', $playlistItem);

        //New PlaylistItemsId is generated, so we need to capture it to update it in the db
        $newSongPlaylistItemsId = $response->id;

        //Target playlist is integrated
        $updateSuccess = $this->CI->SongModel->updateCopiedSongItemsId($newSongId, $newSongPlaylistItemsId);

        //Create a log for the playlist
        $localResultMessage .= ($localResultMessage == "" ? "\t" : "<br>\t");
        if ($updateSuccess) {
            $sourcePlaylist = $this->CI->PlaylistModel->fetchPlaylistById($currentSong->ListId);
            $localResultMessage .= "Dodano do zintegrowanej playlisty ".$newPlaylistDetails->ListName;
            $this->CI->LogModel->createLog("playlist", $newPlaylistDetails->ListId, "Nuta dodana do zintegrowanej playlisty w wyniku skopiowania z ".$sourcePlaylist->ListName);
        }
        else {
            $localResultMessage .= "Nie udało się dodać do zintegrowanej playlisty ".$newPlaylistDetails->ListName;
            $this->CI->LogModel->createLog("playlist", $newPlaylistDetails->ListId, "Nie udało się dodać utworu o ID ".$currentSong->SongId." do zintegrowanej playlisty");
        }

        return "";
    }
}