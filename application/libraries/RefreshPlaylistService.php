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
 */
class RefreshPlaylistService
{
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('SongModel');
        $this->CI->load->model('LogModel');
        $this->CI->load->model('PlaylistModel');
        $this->CI->load->library('FetchSongsService');
        $this->FetchSongsService = new FetchSongsService();
    }

    /**
     * Fetches all songs contained in the YouTube playlist
     * Then compares them with the locally saved copy and adds what is missing
     * A report is generated and attached if everything went well
     *
     * @param $listId int the RAPPAR playlist id
     * @return string An error message ("" means no errors)
     */
    public function refreshPlaylist(int $listId): string
    {
        //Fetch the YT playlist id based on the local playlist id
        $externalPlaylistId = $this->CI->PlaylistModel->GetListUrlById($listId);

        //Fetch the playlist videos from YT
        $songsJsonArray = $this->FetchSongsService->fetchPlaylistItemsFromYT($externalPlaylistId);

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
            else return "Nieznany błąd: ".$err['code']." - " .($err['displayMessage'] ?? '').".";
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