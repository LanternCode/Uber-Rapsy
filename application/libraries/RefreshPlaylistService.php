<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A service refreshing playlists.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 *
 * @property PlaylistModel $PlaylistModel
 * @property SongModel $SongModel
 * @property PlaylistSongModel $PlaylistSongModel
 * @property LogModel $LogModel
 * @property AccountModel $AccountModel
 */
class RefreshPlaylistService
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('PlaylistSongModel');
        $this->CI->load->model('SongModel');
        $this->CI->load->model('PlaylistModel');
        $this->CI->load->model('AccountModel');
        $this->CI->load->library('FetchSongsService');
        $this->FetchSongsService = new FetchSongsService();
    }

    /**
     * Fetch all songs in a YouTube playlist, compares them with the locally saved copy
     *  of that playlist and add what's missing. If everything goes well,
     *  generate and attach a report.
     *
     * @param $listId int the RAPPAR playlist id
     * @return string An error message ("" means no errors)
     */
    public function refreshPlaylist(int $listId, $userId): string
    {
        //Fetch the YT playlist id based on the local playlist id
        $externalPlaylistId = $this->CI->PlaylistModel->getListUrlById($listId);

        //Fetch the playlist videos from YT
        $songsJsonArray = $this->FetchSongsService->fetchPlaylistItemsFromYT($externalPlaylistId);

        //Return an error message based on the content fetched - "" means everything went well
        if ($songsJsonArray === false) {
            return "Wskazana na YT playlista jest pusta! Sprawdź w ustawieniach czy podano poprawny link!";
        }
        elseif (isset($songsJsonArray['code'])) {
            if (in_array($songsJsonArray['code'], ["LNF", "TNF"])) {
                return $songsJsonArray['displayMessage'];
            }
            elseif ($songsJsonArray['code'] === "RF") {
                return $songsJsonArray['displayMessage'];
            }
            else return "Nieznany błąd: ".$songsJsonArray['code']." - " .($songsJsonArray['displayMessage'] ?? '').".";
        }
        else {
            //Perform the reloading process - The main array is composed of parsed song arrays
            $refreshReport = "<pre>";
            $refreshReport .= "<h3>Legenda:</h3>❌ - nie znaleziono utworu<br>⏸ - utwór jest już na playliście<br>✔ - pobrano i dodano utwór na playlistę!<br><br><h3>Utwory:</h3>";

            //Save only the relevant information from the retrieved object for easy processing
            $songItems = [];
            foreach ($songsJsonArray as $playlistItemsArray) {
                foreach ($playlistItemsArray as $playlistItem) {
                    $songPublic = isset($playlistItem['snippet']['thumbnails']['medium']['url']);
                    $songItems[] = array(
                        'externalSongId' => $playlistItem['snippet']['resourceId']['videoId'],
                        'songTitle' => $playlistItem['snippet']['title'],
                        'songChannelName' => str_ends_with($playlistItem['snippet']['videoOwnerChannelTitle'], " - Topic") ? substr($playlistItem['snippet']['videoOwnerChannelTitle'], 0, -strlen(" - Topic")) : $playlistItem['snippet']['videoOwnerChannelTitle'],
                        'songThumbnailLink' => $songPublic ? $playlistItem['snippet']['thumbnails']['medium']['url'] : false,
                        'songPlaylistItemsId' => $playlistItem['id'],
                        'songPublic' => $songPublic
                    );
                }
            }

            //Check which song items already exist so not to waste resources on querying them again
            $newSongItems = [];
            foreach ($songItems as &$song) {
                if ($song['songPublic']) {
                    //Save the existing id (or 0) to later link the song to the playlist_song
                    $existingSongId = $this->CI->SongModel->songExists($song['externalSongId'], $song['songTitle'], $song['songChannelName']);
                    $song['existingSongId'] = $existingSongId;
                    if ($existingSongId == 0) {
                        //Song does not exist in the local database
                        $newSongItems[] = $song;
                    }
                }
            }
            unset($song);

            //For each new song item, fetch the corresponding video item for its publishedAt date
            $i = 0;
            $videoItems = $this->FetchSongsService->fetchVideoItemsFromYT(array_column($newSongItems, 'externalSongId'));
            if (is_array($videoItems)) {
                foreach ($videoItems as $videoItemsArray) {
                    foreach ($videoItemsArray as $videoItem) {
                        $newSongItems[$i]['songPublishedAt'] = substr($videoItem['snippet']['publishedAt'], 0, 4);
                        $i++;
                    }
                }
            }
            else return "Udało się pobrać playlistę, ale nie udało się pobrać piosenek z YouTube. Spróbuj ponownie za jakiś czas.";

            //Insert every new song into the database
            foreach ($newSongItems as &$songToInsert) {
                $songId = $this->CI->SongModel->insertSong($songToInsert['externalSongId'], $userId, $songToInsert['songThumbnailLink'], $songToInsert['songTitle'], $songToInsert['songChannelName'], $songToInsert['songPublishedAt']);
                $songToInsert['existingSongId'] = $songId;
                $this->CI->AccountModel->addUserScore($userId, 'song');
            }
            unset($songToInsert);

            //To preserve YouTube playlist ordering, insert every song into the playlist in the order it was fetched (if it's not in it yet)
            $i = 0;
            foreach ($songItems as $song) {
                if (!$song['songPublic']) {
                    //The song is private or was removed from YT
                    $refreshReport .= $song['externalSongId'] . " jest prywatna - ❌<br />";
                }
                elseif ($song['existingSongId'] > 0) {
                    //Check if the song already exists in this playlist
                    $existingPlaylistSongId = $this->CI->PlaylistSongModel->playlistSongExists($listId, $song['existingSongId']);
                    if ($existingPlaylistSongId > 0) {
                        //The song already exists in this playlist
                        $refreshReport .= $song['songTitle'] . " - ⏸<br />";
                    }
                    else {
                        //The song does not exist in this playlist
                        $playlistSongId = $this->CI->PlaylistSongModel->insertPlaylistSong($listId, $song['existingSongId'], $song['songPlaylistItemsId']);
                        $refreshReport .= $song['songTitle'] . " - ✔<br />";
                    }
                }
                else {
                    //New song altogether - insert with the previously created id
                    $playlistSongId = $this->CI->PlaylistSongModel->insertPlaylistSong($listId, $newSongItems[$i]['existingSongId'], $song['songPlaylistItemsId']);
                    $refreshReport .= $song['songTitle'] . " - ✔<br />";
                    $i++;
                }
            }

            //Songs were loaded correctly - submit a report
            $refreshReport .= "</pre>";
            $newReportId = $this->CI->LogModel->submitReport(htmlspecialchars($refreshReport));

            //Create a log with the report
            $reportSuccessful = $newReportId ? " i dołączono raport." : ", nie udało się zapisać raportu.";
            $logMessage = "Załadowano nowe nuty na playlistę" . $reportSuccessful;
            $this->CI->LogModel->createLog('playlist', $listId, $logMessage, $newReportId);

            return "";
        }
    }
}