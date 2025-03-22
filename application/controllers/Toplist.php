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
    }

    /**
     * Opens the song toplist.
     *
     * @return void
     */
    public function songsToplist(): void
    {

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
     * This method handles the normal loading of playlist items
     * By default all songs in the playlists are loaded
     * If filters are used, only the songs that match the filter are shown
     * Search query is a text-based filter that is empty by default and not
     *      included as a separate filter (those are based on checkboxes)
     *
     * The specific filters are:
     * repeat - only show songs with the repeat checkbox checked
     * unrated - only show songs that are unrated by at least one reviewer
     * checkbox property - only show songs with the specified checkbox property checked
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

}