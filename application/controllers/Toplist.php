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
        $this->load->library('RefreshPlaylistService');
        $this->RefreshPlaylistService = new RefreshPlaylistService();
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
            //Set a manual verification page for the author to review the contents
            $data['body'] = 'song/verifySongImport';

            //Fetch the item(s) at the links
            if ($data['playlistLink']) {
                //Fetch the playlist items
                $videoIds = [];
                $remotePlaylistId = $this->UtilityModel->extractPlaylistIdFromLink($data['playlistLink']);
                $data['playlistItems'] = $this->RefreshPlaylistService->fetchPlaylistItemsFromYT($remotePlaylistId);
                foreach ($data['playlistItems'] as $playlistItemsArray) {
                    foreach ($playlistItemsArray as $playlistItem) {
                        $videoIds[] = $playlistItem['snippet']['resourceId']['videoId'] ?? "";
                    }
                }

                //For each playlist item, fetch the corresponding video item for the publishedAt date
                $data['videoItems'] = $this->RefreshPlaylistService->fetchVideoItemsFromYT($videoIds);
                foreach ($data['playlistItems'] as $groupIndex => &$playlistItemsGroup) {
                    foreach ($playlistItemsGroup as $itemIndex => &$playlistItem) {
                        //Access the corresponding video item at the same group and item index
                        $playlistItem['videoPublishedAt'] = substr($data['videoItems']['items'][$itemIndex]['snippet']['publishedAt'], 0, 4);
                    }
                }
            }
            if ($data['songLink']) {
                $data['remoteVideoId'] = $this->UtilityModel->extractVideoIdFromLink($data['songLink']);
                $data['video'] = $this->RefreshPlaylistService->fetchVideoItemsFromYT([$data['remoteVideoId']]);
                $data['playlistItems'][] = $data['video']['items'];
            }

            //Save the videos fetched for 24 hours so the user can make changes
            $this->session->set_tempdata('playlistItems', $data['playlistItems'], 86400);
        }

        $this->load->view('templates/toplist', $data);
    }

    public function approveSongImport()
    {
        $data['body'] = 'song/approveSongImport';
        $songItems = $this->session->tempdata('playlistItems');

        print_r("<pre>");
        print_r($songItems);
        
        //$this->input->post("songChannelName-".$i);

        //$this->session->unset_tempdata('playlistItems');

        $this->load->view('templates/toplist', $data);
    }

}