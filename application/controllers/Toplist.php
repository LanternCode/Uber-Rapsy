<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!isset($_SESSION)) {
    session_start();
}

/**
 * Controller responsible for handling toplist-related views
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 *
 * @property SongModel $SongModel
 * @property SecurityModel $SecurityModel
 * @property PlaylistModel $PlaylistModel
 * @property UtilityModel $UtilityModel
 * @property CI_Input $input
 */
class Toplist extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('SongModel');
        $this->load->model('SecurityModel');
        $this->load->model('PlaylistModel');
        $this->load->model('UtilityModel');
        $this->load->library('FetchSongsService');
        $this->FetchSongsService = new FetchSongsService();
    }

    /**
     * Opens the song toplist.
     *
     * @return void
     */
    public function frontpage(): void
    {
        $data = array(
            'body' => 'toplist/frontpage',
            'title' => 'Nasze toplisty | Uber Rapsy',
            'songs' => $this->SongModel->fetchTopSongs()
        );
        foreach ($data['songs'] as $song) {
            $song->myRating = isset($_SESSION['userId']) ? $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongRating($song->SongId, $_SESSION['userId'])) : 0;
            $song->communityAverage = $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongAverage($song->SongId));
            $song->awards = $this->SongModel->fetchSongAwards($song->SongId);
        }
        $this->load->view('templates/toplist', $data);
    }

    /**
     * Opens the individual song's page
     *
     * @return void
     */
    public function songPage(): void
    {
        $songId = filter_var($this->input->get('songId'), FILTER_VALIDATE_INT);
        if ($songId) {
            $data = array(
                'body' => 'song/songPage',
                'title' => 'Uber Rapsy | Oceń nutę',
                'song' => $this->SongModel->GetSongById($songId),
                'myRating' => isset($_SESSION['userId']) ? $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongRating($songId, $_SESSION['userId'])) : 0,
                'communityAverage' => $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongAverage($songId)),
                'songAwards' => $this->SongModel->fetchSongAwards($songId)
            );
            $data['song']->SongGradeAdam = $this->UtilityModel->trimTrailingZeroes($data['song']->SongGradeAdam ?? 0);
            $data['song']->SongGradeChurchie = $this->UtilityModel->trimTrailingZeroes($data['song']->SongGradeChurchie ?? 0);

            $this->load->view('templates/toplist', $data);
        }
        else redirect('logout');
    }

    /**
     * Saves user grades from toplists.
     *
     * @return void
     */
    public function saveGradesFromToplist(): void
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        if ($userAuthenticated) {
            //Fetch the new ratings
            $queryData['userId'] = $_SESSION['userId'];
            $queryData['songGrade'] = filter_var($this->input->post('songGrade'), FILTER_VALIDATE_FLOAT);
            $queryData['songId'] = filter_var($this->input->post('songId'), FILTER_VALIDATE_INT);

            //Check if the user already rated the song
            if($queryData['songGrade'] && $queryData['songId']) {
                $songUnrated = !$this->SongModel->checkSongRatingExists($queryData['songId'], $queryData['userId']);
                if ($songUnrated)
                    $this->SongModel->addSongRating($queryData);
                else
                    $this->SongModel->updateSongRating($queryData);
            }
            redirect('songsToplist');
        }
        else redirect('logout');
    }

    /**
     * This method filters visible songs to be displayed as search results
     *
     * @return void
     */
    public function songSearch(): void
    {
        $data = array(
            'body' => 'toplist/songSearch',
            'title' => 'Wyniki Wyszukiwania Nut | Uber Rapsy',
            'songs' => array(),
            'searchQuery' => trim($this->input->get('searchQuery') ?? '')
        );

        //Fetch songs filtered by a valid search query
        if (strlen($data['searchQuery']) >= 1) {
            //Fetch per-song properties if there were 300 or less songs returned
            $data['songs'] = $this->SongModel->searchSongs($data['searchQuery']);
            if (count($data['songs']) <= 300) {
                foreach ($data['songs'] as $song) {
                    $song->myGrade = isset($_SESSION['userId']) ? $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongRating($song->SongId, $_SESSION['userId'])) : 0;
                    $song->communityAverage = $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongAverage($song->SongId));
                    $song->awards = $this->SongModel->fetchSongAwards($song->SongId);
                }
            }
        }

        $this->load->view('templates/toplist', $data);
    }

    public function importSongs(): void
    {
        $data = array(
            'body' => 'song/importSongs',
            'title' => 'Dodaj nowe utwory | Uber Rapsy',
            'playlistLink' => $this->input->post('playlistLink'),
            'songLink' => $this->input->post('songLink')
        );

        //The form is submitted when a link to a playlist or a song is supplied
        if ($data['playlistLink'] || $data['songLink']) {
            //First process the playlist link
            if ($data['playlistLink']) {
                //Fetch the playlist items and extract the required information into an array
                $remotePlaylistId = $this->UtilityModel->extractPlaylistIdFromLink($data['playlistLink']);
                $playlistItems = $this->FetchSongsService->fetchPlaylistItemsFromYT($remotePlaylistId);
                $songItems = [];
                foreach ($playlistItems as $playlistItemsArray) {
                    foreach ($playlistItemsArray as $playlistItem) {
                        $songItems[] = array(
                            'externalSongId' => $playlistItem['snippet']['resourceId']['videoId'],
                            'songTitle' => $playlistItem['snippet']['title'],
                            'songChannelName' => str_ends_with($playlistItem['snippet']['videoOwnerChannelTitle'], " - Topic") ? substr($playlistItem['snippet']['videoOwnerChannelTitle'], 0, -strlen(" - Topic")) : $playlistItem['snippet']['videoOwnerChannelTitle'],
                            'songThumbnailLink' => $playlistItem['snippet']['thumbnails']['medium']['url']
                        );
                    }
                }

                //For each playlist item, fetch the corresponding video item for its publishedAt date
                $i = 0;
                $data['videoItems'] = $this->FetchSongsService->fetchVideoItemsFromYT(array_column($songItems, 'externalSongId'));
                foreach ($data['videoItems'] as $videoItemsArray) {
                    foreach ($videoItemsArray as $videoItem) {
                        $songItems[$i]['songPublishedAt'] = substr($videoItem['snippet']['publishedAt'], 0, 4);
                        $i++;
                    }
                }
            }

            //Next, process the individual video link
            if ($data['songLink']) {
                $remoteVideoId = $this->UtilityModel->extractVideoIdFromLink($data['songLink']);
                $video = $this->FetchSongsService->fetchVideoItemsFromYT([$remoteVideoId]);

                $songItems[] = array(
                    'externalSongId' => $remoteVideoId,
                    'songTitle' => $video[0][0]['snippet']['title'],
                    'songChannelName' => str_ends_with($video[0][0]['snippet']['channelTitle'], " - Topic") ? substr($video[0][0]['snippet']['channelTitle'], 0, -strlen(" - Topic")) : $video[0][0]['snippet']['channelTitle'],
                    'songThumbnailLink' => $video[0][0]['snippet']['thumbnails']['medium']['url'],
                    'songPublishedAt' => substr($video[0][0]['snippet']['publishedAt'], 0, 4)
                );
            }

            //Check if any items were imported
            if (count($songItems) > 0) {
                //Save the songs fetched for 24 hours so the user can make changes
                $data['songItems'] = $songItems;
                $this->session->set_tempdata('playlistItems', ($songItems), 86400);
                //Set a manual verification page for the author to review the contents
                $data['body'] = 'song/verifySongImport';
            }
            else {
                //If no items were found, show an error
                $data['error'] = '<h3>Nie znaleziono żadnych utworów. Upewnij się, że playlista i utwór są publiczne.</h3><br>';
            }
        }

        $this->load->view('templates/toplist', $data);
    }

    public function approveSongImport()
    {
        $i = 0;
        $added = 0;
        $data['report'] = "";

        //Load the previously fetched songs array
        $songItems = $this->session->tempdata('playlistItems');
        if (count($songItems) > 0) {
            //Import each song if it does not exist yet
            foreach ($songItems as $song) {
                $songChannelName = $this->input->post("songChannelName-".$i) != $song['songChannelName'] ? $this->input->post("songChannelName-".$i) : $song['songChannelName'];
                $existingSongId = $this->SongModel->songExists($song['externalSongId'], $song['songTitle'], $songChannelName);
                if ($existingSongId == 0) {
                    $songId = $this->SongModel->insertSong($song['externalSongId'], $song['songThumbnailLink'], $song['songTitle'], $songChannelName, $song['songPublishedAt']);
                    $data['report'] .= "<h4>Utwór ".$song['songTitle']." został dodany do bazy danych Rappar!</h4><br>";
                    $added++;
                }
                else
                    $data['report'] .= "<h4>Utwór ".$song['songTitle']." już istnieje w bazie danych Rappar!</h4><br>";
                $i++;
            }
        }

        //Complete the report
        $word = $added === 1 ? 'utwór' : ($added === 2 || $added === 3 || $added === 4 ? 'utwory' : 'utworów');
        $data['report'] .= "<h2>Łącznie dodano ".$added." ".$word." do bazy danych RAPPAR!</h2>";
        $this->session->unset_tempdata('playlistItems');

        $data['body'] = 'song/approveSongImport';
        $this->load->view('templates/toplist', $data);
    }

}