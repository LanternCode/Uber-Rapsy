<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION)){
	session_start();
}

/**
 * Controller responsible for handling views related with playlists.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class Playlist extends CI_Controller {

	public function __construct()
	{
        parent::__construct();
        $this->load->model('PlaylistModel');
		$this->load->model('SongModel');
    }

    /**
     * This function is used to handle:
     * Global search queries
     * Local search queries
     * Tierlists
     * Playlists
     *
     * Whichever is queued is presented to the user. Playlists can
     * use filters to give more specific results, see below.
     *
     * The allowed filters are:
     * repeat - only show songs with the repeat status true
     * unrated - only show songs that are unrated
     * adam - show a tierlist based on adam's scores, high to low
     * churchie - show a tierlist based on kościelny's scores, high to low
     * average - show a tierlist based on the average song score, high to low
     * none - show the playlist as-is
     *
     * @return void
     */
    public function playlist()
    {
        $data = [];
        $data['ListId'] = isset($_GET['ListId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['ListId'])) : 0;
        $data['SearchQuery'] = isset($_GET['SearchQuery']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['SearchQuery'])) : 0;
        $data['GlobalSearch'] = isset($_GET['GlobalSearch']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['GlobalSearch'])) : 0;
        $data['Reviewer'] = isset($_SESSION['userRole']) && $_SESSION['userRole'] === "reviewer";
        $data['lists'] = $this->PlaylistModel->GetListsIdsAndNames();
        $data['body'] = 'playlist/insidePlaylist/playlist';

        //Handle global search if queued, otherwise check for valid playlist id
        if($data['GlobalSearch'] !== 0 && $data['SearchQuery'] !== 0) {
            $data['songPlaylistNames'] = [];
            $data['playlist'] = [];
            $data['body'] = 'playlist/insidePlaylist/playlistSearch';
            $data['title'] = "Wyniki Wyszukiwania | Uber Rapsy";
            $data['songs'] = $this->SongModel->GetSongsFromSearch($data['SearchQuery']);
            foreach($data['songs'] as $i => $song)
            {
                //Display values without decimals at the end if the decimals are only 0
                if(is_numeric($song->SongGradeAdam)) $song->SongGradeAdam = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeAdam);
                if(is_numeric($song->SongGradeChurchie)) $song->SongGradeChurchie = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeChurchie);
                //Get song button information
                $data['playlist'][] = $this->PlaylistModel->FetchPlaylistById($song->ListId);
            }
        }
        else if (is_numeric($data['ListId'])) {
            //Fetch the playlist, queue for local search, then filters
            $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);
            $data['ListName'] = $data['playlist']->ListName;
            $data['ListUrl'] = $data['playlist']->ListUrl;
            $data['title'] = $data['ListName']." | Playlista Uber Rapsy";

            //Check if search was used
            $data['Filter'] = isset($_GET['Filter']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['Filter'])) : "none";
            $data['Filter'] = ($data['Filter'] === "none" && $data['SearchQuery'] !== 0) ? "Search" : $data['Filter'];

            //Filter is in use, queue for the right filter or none to just fetch the playlist
            switch($data['Filter']) {
                case "Search": {
                    //Local search was selected
                    $data['songs'] = $this->SongModel->GetSongsFromList($data['ListId'], $data['SearchQuery']);
                    break;
                }
                case "Repeat": {
                    $data['songs'] = $this->SongModel->FilterByRepeat(true, $data['ListId']);
                    break;
                }
                case "Unrated": {
                    $data['songs'] = $this->SongModel->FilterUnrated($data['ListId']);
                    break;
                }
                case "Adam":
                case "Churchie":
                case "Average": {
                    $data['gradesToDisplay'] = [];
                    $data['body'] = 'playlist/insidePlaylist/tierlist';
                    $data['songs'] = $this->SongModel->GetTopSongsFromList($data['ListId'], $data['Filter']);
                    $propName = $data['Filter'] === "Adam" ? "SongGradeAdam" : ($data['Filter'] === "Churchie" ? "SongGradeChurchie" : "Average");
                    foreach ($data['songs'] as $song) {
                        //Display values without decimals at the end if the decimals are only 0
                        if(is_numeric($song->SongGradeAdam)) $song->SongGradeAdam = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeAdam);
                        if(is_numeric($song->SongGradeChurchie)) $song->SongGradeChurchie = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeChurchie);
                        $grade = $propName !== "Average" ? $song->$propName : bcdiv(($song->SongGradeAdam+$song->SongGradeChurchie)/2, 1, 2);
                        if(!in_array($grade, $data['gradesToDisplay']))
                            $data['gradesToDisplay'][] = $grade;
                    }
                    rsort($data['gradesToDisplay']);
                    break;
                }
                default:
                case "none": {
                    //No filter in use - just load the playlist
                    $data['songs'] = $this->SongModel->GetSongsFromList($data['ListId']);
                    break;
                }
            }
            //Calculate playlist averages for each reviewer and the overall average - not required in tierlists
            if (!in_array($data['Filter'], ["Adam", "Churchie", "Average"])) {
                $avgOverall = 0;
                $avgAdam = 0;
                $avgChurchie = 0;
                $ratedCount = 0;
                $ratedAdam = 0;
                $ratedChurchie = 0;
                foreach($data['songs'] as $song)
                {
                    //Display values without decimals at the end if the decimals are only 0
                    if(is_numeric($song->SongGradeAdam)) $song->SongGradeAdam = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeAdam);
                    if(is_numeric($song->SongGradeChurchie)) $song->SongGradeChurchie = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeChurchie);
                    $avgOverall += ($song->SongGradeAdam + $song->SongGradeChurchie) / 2;
                    $avgAdam += $song->SongGradeAdam;
                    $avgChurchie += $song->SongGradeChurchie;
                    $ratedCount += $song->SongGradeAdam > 0 || $song->SongGradeChurchie > 0 ? 1 : 0;
                    $ratedAdam += $song->SongGradeAdam > 0 ? 1 : 0;
                    $ratedChurchie += $song->SongGradeChurchie > 0 ? 1 : 0;
                }
                $data['avgOverall'] = $ratedCount > 0 ? $avgOverall/$ratedCount : 0;
                $data['avgAdam'] = $ratedAdam > 0 ? $avgAdam/$ratedAdam : 0;
                $data['avgChurchie'] = $ratedChurchie > 0 ? $avgChurchie/$ratedChurchie : 0;
                $data['ratedOverall'] = $ratedCount;
                $data['ratedAdam'] = $ratedAdam;
                $data['ratedChurchie'] = $ratedChurchie;
                $data['ListIntegrated'] = $data['playlist']->ListIntegrated;
            }
        }
        else {
            $data['body'] = 'invalidAction';
            $data['title'] = "Błąd akcji!";
            $data['errorMessage'] = "Wskazana playlista nie istnieje lub jest prywatna.";
        }

        $this->load->view('templates/main', $data);
    }

    /**
     * Opens the playlist dashboard.
     *
     * @return void
     */
    public function dashboard()
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'playlist/playlistDashboard';
            $data['title'] = "Uber Rapsy | Zarządzaj playlistami!";
            $data['playlists'] = $this->PlaylistModel->GetAllLists();

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Opens the playlist details page.
     *
     * @return void
     */
    public function details()
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        if($userAuthenticated) {
            $data = [];
            $data['body']  = 'playlist/details';
            $data['title'] = "Uber Rapsy | Zarządzaj playlistą!";
            $data['ListId'] = isset( $_GET['id'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['id'] ) ) : 0;

            if($data['ListId'] && is_numeric($data['ListId'])) {
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);

                if($data['playlist'] === false) {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono playlisty o podanym numerze id!";
                }
                else {
                    $data['songs'] = $this->SongModel->GetSongsFromList($data['ListId']);
                }
            }
            else {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id playlisty lub nie podano go wcale!";
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Opens the playlist edit page and processes the form.
     *
     * @return void
     */
    public function edit()
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'playlist/edit';
            $data['title'] = "Uber Rapsy | Edytuj playlistę!";
            $data['ListId'] = isset( $_GET['id'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['id'] ) ) : 0;

            if($data['ListId'] && is_numeric($data['ListId']))
            {
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);

                if($data['playlist'] === false)
                {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono playlisty o podanym numerze id!";
                }
                else if(isset($_POST['playlistFormSubmitted']))
                {
                    $queryData = [];
                    $queryData['ListId'] = $data['ListId'];
                    $queryData['ListUrl'] = isset($_POST['playlistId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistId'])) : "";
                    $queryData['ListName'] = isset($_POST['playlistName']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistName'])) : "";
                    $queryData['ListDesc'] = isset($_POST['playlistDesc']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistDesc'])) : "";
                    $queryData['ListCreatedAt'] = isset($_POST['playlistDate']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistDate'])) : "";
                    $queryData['ListActive'] = isset($_POST['playlistVisibility']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistVisibility'])) : "";
                    $queryData['btnRehearsal'] = isset($_POST["btnRehearsal"]) ? 1 : 0;
                    $queryData['btnDistinction'] = isset($_POST["btnDistinction"]) ? 1 : 0;
                    $queryData['btnMemorial'] = isset($_POST["btnMemorial"]) ? 1 : 0;
                    $queryData['btnXD'] = isset($_POST["btnXD"]) ? 1 : 0;
                    $queryData['btnNotRap'] = isset($_POST["btnNotRap"]) ? 1 : 0;
                    $queryData['btnDiscomfort'] = isset($_POST["btnDiscomfort"]) ? 1 : 0;
                    $queryData['btnTop'] = isset($_POST["btnTop"]) ? 1 : 0;
                    $queryData['btnNoGrade'] = isset($_POST["btnNoGrade"]) ? 1 : 0;
                    $queryData['btnUber'] = isset($_POST["btnUber"]) ? 1 : 0;
                    $queryData['btnBelowSeven'] = isset($_POST["btnBelowSeven"]) ? 1 : 0;
                    $queryData['btnBelowTen'] = isset($_POST["btnBelowTen"]) ? 1 : 0;
                    $queryData['btnBelowNine'] = isset($_POST["btnBelowNine"]) ? 1 : 0;
                    $queryData['btnBelowEight'] = isset($_POST["btnBelowEight"]) ? 1 : 0;
                    $queryData['btnBelowFour'] = isset($_POST["btnBelowFour"]) ? 1 : 0;
                    $queryData['btnDuoTen'] = isset($_POST["btnDuoTen"]) ? 1 : 0;
                    $queryData['btnVeto'] = isset($_POST["btnVeto"]) ? 1 : 0;

                    if($queryData['ListName'] && $queryData['ListDesc'] && $queryData['ListCreatedAt'] && $queryData['ListActive'] != "")
                    {
                        $this->PlaylistModel->UpdatePlaylist($queryData);
                        $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);
                        $data['resultMessage'] = "Pomyślnie zaktualizowano playlistę!";
                    }
                    else
                    {
                        $data['resultMessage'] = "";
                        $data['resultMessage'] .= $queryData['ListName'] == "" ? "Nazwa Playlisty jest wymagana!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListDesc'] == "" ? "Opis Playlisty jest wymagany!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListCreatedAt'] == "" ? "Data Stworzenia Playlisty jest wymagana!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListActive'] == "" ? "Status Playlisty jest wymagany!</br>" : '';
                    }
                }
            }
            else
            {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id playlisty lub nie podano go wcale!";
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Allows the user to set a playlist as (in)active.
     *
     * @return void
     */
    public function hidePlaylist()
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'playlist/hidePlaylist';
            $data['title'] = "Uber Rapsy | Ukryj playlistę";
            $data['ListId'] = isset($_GET['id']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['id'])) : 0;
            $data['HideList'] = isset($_GET['hide']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['hide'])) : false;

            if($data['ListId'] && is_numeric($data['ListId']))
            {
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);

                if($data['playlist'] === false)
                {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono playlisty o podanym numerze id!";
                }
                else if($data['HideList'] === "true")
                {
                    $this->PlaylistModel->SetPlaylistActiveProperty($data['playlist']->ListActive, $data['ListId']);
                    redirect('playlist/details?id='.$data['ListId']);
                }
            }
            else
            {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id playlisty lub nie podano go wcale!";
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * The function takes the form inputs with song details and compares them with the actual song
     * If the two overlap, the song is identical and an empty array is returned
     * If even one of the values is different, the songs are different, and an array
     * of the differing properties is returned
     *
     * @param $currentSong object the current state of the song
     * @param $formInput array the state of the song after the form is submitted
     * @return array array of property names to update
     */
    function FlagSongDataToUpdate(object $currentSong, array $formInput): array
    {
        //Set update flags
        $flags['SongGradeAdam'] = $currentSong->SongGradeAdam != $formInput['SongGradeAdam'];
        $flags['SongGradeChurchie'] = $currentSong->SongGradeChurchie != $formInput['SongGradeChurchie'];
        $flags['SongRehearsal'] = $currentSong->SongRehearsal != $formInput['SongRehearsal'];
        $flags['SongDistinction'] = $currentSong->SongDistinction != $formInput['SongDistinction'];
        $flags['SongMemorial'] = $currentSong->SongMemorial != $formInput['SongMemorial'];
        $flags['SongXD'] = $currentSong->SongXD != $formInput['SongXD'];
        $flags['SongNotRap'] = $currentSong->SongNotRap != $formInput['SongNotRap'];
        $flags['SongDiscomfort'] = $currentSong->SongDiscomfort != $formInput['SongDiscomfort'];
        $flags['SongTop'] = $currentSong->SongTop != $formInput['SongTop'];
        $flags['SongNoGrade'] = $currentSong->SongNoGrade != $formInput['SongNoGrade'];
        $flags['SongUber'] = $currentSong->SongUber != $formInput['SongUber'];
        $flags['SongBelow'] = $currentSong->SongBelow != $formInput['SongBelow'];
        $flags['SongBelTen'] = $currentSong->SongBelTen != $formInput['SongBelTen'];
        $flags['SongBelNine'] = $currentSong->SongBelNine != $formInput['SongBelNine'];
        $flags['SongBelEight'] = $currentSong->SongBelEight != $formInput['SongBelEight'];
        $flags['SongBelFour'] = $currentSong->SongBelFour != $formInput['SongBelFour'];
        $flags['SongDuoTen'] = $currentSong->SongDuoTen != $formInput['SongDuoTen'];
        $flags['SongVeto'] = $currentSong->SongVeto != $formInput['SongVeto'];
        $flags['SongComment'] = $currentSong->SongComment != $formInput['SongComment'];

        //If any of the flags is set (true), add it to the list
        if(in_array(true, $flags, true)) {
            $triggeredFlags = [];
            foreach ($flags as $key => $flag) {
                if($flag)
                    $triggeredFlags[] = $key;
            }
            return $triggeredFlags;
        }
        else return [];
    }

    /**
     * Given an internal property name, returns a custom display name
     * Applies only to checkbox properties
     *
     * @param $propertyName string the internal name of the property
     * @return string the custom display name of the property
     */
    function GetPropertyDisplayName(string $propertyName): string
    {
        return match ($propertyName) {
            'SongRehearsal' => "ponowny odsłuch",
            'SongDistinction' => "wyróżnienie",
            'SongMemorial' => "10*",
            'SongXD' => "XD",
            'SongNotRap' => "to nie rapsik",
            'SongDiscomfort' => "strefa dyskomfortu",
            'SongTop' => "X15",
            'SongNoGrade' => "nie oceniam",
            'SongUber' => "Uber",
            'SongBelow' => "< 7",
            'SongBelTen' => "< 10",
            'SongBelNine' => "< 9",
            'SongBelEight' => "< 8",
            'SongBelFour' => "< 4",
            'SongDuoTen' => '"10"',
            'SongVeto' => "VETO",
            default => ""
        };
    }

    /**
     * The Update and UpdateSelection methods were merged into one - UpdateSongsInPlaylist
     * The new method introduced new optimisations, merged the identical components of both methods
     * And fixed inconsistencies, as well as introduced new security checks to make sure all cases are handled
     *
     * @return void
     */
    public function updateSongsInPlaylist(): void
    {
        $data = [];
        $data['playlistId'] = $_POST['playlistId'] ?? "invalid";
        $userAuthenticated = $this->SecurityModel->authenticateUser();

        //Check if this request comes from a valid playlist
        if ($data['playlistId'] === "invalid" || ($data['playlistId'] !== "search" && !is_numeric($data['playlistId'])) || $data['playlistId'] == 0 ) {
            $data['body']  = 'invalidAction';
            $data['title'] = "Błąd akcji!";
            $data['errorMessage'] = "Nie podano numeru playlisty podczas aktualizacji!";
        }
        else if ($userAuthenticated) {
            $data['body']  = 'update';
            $data['title'] = "Oceny Zapisane!";
            $resultMessage = "<pre>";

            //Fetch the playlist to access its settings
            $playlist = false;
            if($data['playlistId'] !== "search")
                $playlist = $this->PlaylistModel->FetchPlaylistById($data['playlistId']);

            //Process each song separately
            for ($i = 0; $i < count($_POST)-1; $i+=23) {
                //Only process songs that were actually updated
                $songUpdated = isset($_POST["songUpdated-" . $i+21]) && $_POST["songUpdated-".$i+21];
                if ($songUpdated) {
                    //Create a variable for the song's update message
                    $localResultMessage = "";

                    //Save the form data to a temp variable
                    $formInput['songId'] = $_POST["songId-" . $i];
                    $formInput['SongGradeAdam'] = $_POST["nwGradeA-" . $i + 1];
                    $formInput['SongGradeChurchie'] = $_POST["nwGradeC-" . $i + 2];
                    $formInput['SongRehearsal'] = $_POST["songRehearsal-" . $i + 4];
                    $formInput['SongDistinction'] = $_POST["songDistinction-" . $i + 5];
                    $formInput['SongMemorial'] = $_POST["songMemorial-" . $i + 6];
                    $formInput['SongXD'] = $_POST["songXD-" . $i + 7];
                    $formInput['SongNotRap'] = $_POST["songNotRap-" . $i + 8];
                    $formInput['SongDiscomfort'] = $_POST["songDiscomfort-" . $i + 9];
                    $formInput['SongTop'] = $_POST["songTop-" . $i + 10];
                    $formInput['SongNoGrade'] = $_POST["songNoGrade-" . $i + 11];
                    $formInput['SongUber'] = $_POST["songUber-" . $i + 12];
                    $formInput['SongBelow'] = $_POST["songBelow-" . $i + 13];
                    $formInput['SongBelTen'] = $_POST["songBelTen-" . $i + 14];
                    $formInput['SongBelNine'] = $_POST["songBelNine-" . $i + 15];
                    $formInput['SongBelEight'] = $_POST["songBelEight-" . $i + 16];
                    $formInput['SongBelFour'] = $_POST["songBelFour-" . $i + 17];
                    $formInput['SongDuoTen'] = $_POST["songDuoTen-" . $i + 18];
                    $formInput['SongVeto'] = $_POST["songVeto-" . $i + 19];
                    $formInput['newPlaylistId'] = $_POST["nwPlistId-" . $i + 3];
                    $formInput['copyToPlaylist'] = $_POST["copyPlistId-" . $i + 20];
                    $formInput['SongComment'] = $_POST["songComment-" . $i + 22];

                    //Fetch the song-to-update
                    $currentSong = $this->SongModel->GetSongById($formInput['songId']);

                    //Fatal Error - if song was not fetched, note this and continue to the next song.
                    if($currentSong === false) {
                        $resultMessage .= "<br><br>\tNie znaleziono utworu o ID ".$formInput['songId']."<br><br>";
                        continue;
                    }

                    //Fetch the song properties that need to be updated by comparing the input data with the song data
                    $elementsToUpdate = $this->FlagSongDataToUpdate($currentSong, $formInput);

                    //Update song grades and the comment
                    $createUpdateLog = false;
                    $adamGradeUpdated = in_array("SongGradeAdam", $elementsToUpdate);
                    $churchieGradeUpdated = in_array("SongGradeChurchie", $elementsToUpdate);
                    $commentUpdated = in_array("SongComment", $elementsToUpdate);
                    if ($adamGradeUpdated || $churchieGradeUpdated) {
                        $newAdamRating = str_replace(',', '.', $formInput['SongGradeAdam']);
                        $newChurchieRating = str_replace(',', '.', $formInput['SongGradeChurchie']);
                        //Ensure the ratings are valid decimal numbers (full or .5), are in the range "1-15" and are separated with dots and not commas
                        if (strlen($formInput['SongGradeAdam']) > 0 && strlen($formInput['SongGradeChurchie']) > 0
                            && is_numeric($formInput['SongGradeAdam']) && is_numeric($formInput['SongGradeChurchie'])
                            && $this->UtilityModel->InRange($formInput['SongGradeAdam'], 0, 15) && $this->UtilityModel->InRange($formInput['SongGradeChurchie'], 0, 15)
                            && fmod($formInput['SongGradeAdam'], 0.5) == 0 && fmod($formInput['SongGradeChurchie'], 0.5) == 0) {
                            $scoresSaved = $this->SongModel->UpdateSongScores($currentSong->SongId, $newAdamRating, $newChurchieRating);
                            if (!$scoresSaved) {
                                //Fatal Error - if grades were not saved, note this and continue to the next song.
                                $resultMessage .= "<br><br>\tNie udało się zapisać ocen dla utworu ".$currentSong->SongTitle."<br><br>";
                                continue;
                            }
                            else if ($adamGradeUpdated && $churchieGradeUpdated) {
                                $localResultMessage .= "\tOcena Adama: " . $currentSong->SongGradeAdam . " -> " . $newAdamRating . "<br>";
                                $localResultMessage .= "\tOcena Kościelnego: " . $currentSong->SongGradeChurchie . " -> " . $newChurchieRating;
                            } else if ($adamGradeUpdated)
                                $localResultMessage .= "\tOcena Adama: " . $currentSong->SongGradeAdam . " -> " . $newAdamRating;
                            else
                                $localResultMessage .= "\tOcena Kościelnego: " . $currentSong->SongGradeChurchie . " -> " . $newChurchieRating;
                            $createUpdateLog = true;
                        }
                    }
                    if($commentUpdated) {
                        $commentSaved = $this->SongModel->UpdateSongComment($currentSong->SongId, $formInput['SongComment']);
                        if (!$commentSaved) {
                            //Fatal Error - if comment was not saved, note this and continue to the next song.
                            $resultMessage .= "<br><br>\tNie udało się zapisać komentarza do utworu ".$currentSong->SongTitle."<br><br>";
                            continue;
                        }
                        else {
                            $localResultMessage .= ($localResultMessage == "" ? "\t" : "<br>\t");
                            $localResultMessage .= "Komentarz: " . $currentSong->SongComment . " -> " . $formInput['SongComment'];
                            $createUpdateLog = true;
                        }
                    }

                    //Update Song Checkbox Properties
                    foreach ($elementsToUpdate as $prop) {
                        if (!in_array($prop, ["SongGradeAdam", "SongGradeChurchie", "SongComment"])) {
                            $propertyDisplayName = $this->GetPropertyDisplayName($prop);
                            $updateSuccess = $this->SongModel->UpdateSongCheckboxProperty($currentSong->SongId, $prop, $formInput[$prop]);
                            $localResultMessage .= ($localResultMessage == "" ? "\t" : "<br>\t");
                            if($updateSuccess) {
                                $localResultMessage .= ($formInput[$prop] ? "Zaznaczono " : "Odznaczono ") . $propertyDisplayName;
                            }
                            else {
                                $localResultMessage .= "Nie udało się zmienić wartości przycisku " . $propertyDisplayName;
                            }
                            $createUpdateLog = true;
                        }
                    }

                    //Create a song update log if changes were made
                    if ($createUpdateLog)
                        $this->LogModel->CreateLog('song', $currentSong->SongId, "Zapisano oceny nuty z " . ($data['playlistId'] === "search" ? "wyszukiwarki" : "playlisty"));

                    //Copy the song to a different playlist and add a log explaining where the song came from
                    $newSongId = 0;
                    if ($formInput['copyToPlaylist']) {
                        $copySuccessful = $this->SongModel->CopySongToPlaylist($currentSong->SongId, $formInput['copyToPlaylist']);
                        if($copySuccessful) {
                            $newSongId = $this->SongModel->GetSongIdByNameAndPlaylist($currentSong->SongTitle, $formInput['copyToPlaylist']);
                            $sourceName = $data['playlistId'] === "search" ? "wyszukiwarki" : "playlisty " . $playlist->ListName;
                            $targetName = $this->PlaylistModel->GetPlaylistNameById($formInput['copyToPlaylist']);
                            $localResultMessage .= "\tSkopiowano do: ".$targetName;
                            $this->LogModel->CreateLog("song", $newSongId, "Nuta skopiowana z " . $sourceName . " do " . $targetName);
                        }
                        else {
                            $resultMessage .= "<br><br>\tNie udało się skopiować utworu ".$currentSong->SongTitle.", przeskoczono do następnego.<br><br>";
                            continue;
                        }
                    }

                    //Check if the song needs to be moved, or copied to an integrated playlist
                    $moveRequired = $formInput['newPlaylistId'] != $data['playlistId'] && $formInput['newPlaylistId'] != 0;
                    $copyRequired = $formInput['copyToPlaylist'] != $data['playlistId'] && $formInput['copyToPlaylist'] != 0;
                    $copyToIntegratedRequired = $copyRequired && $this->PlaylistModel->GetPlaylistIntegratedById($formInput['copyToPlaylist']);
                    if($moveRequired || $copyToIntegratedRequired) {
                        //Include google library
                        $client = $this->SecurityModel->initialiseLibrary();

                        //Only proceed when the library was successfully included
                        if($client !== false) {
                            //Validate the access token required for the api call
                            $tokenExpired = $this->SecurityModel->validateAuthToken($client);

                            //Perform the api call or refresh the auth token
                            if ($tokenExpired) {
                                $data['body'] = 'invalidAction';
                                $data['title'] = "Błąd autoryzacji tokenu!";
                                $data['errorMessage'] = "Odświeżenie tokenu autoryzującego nie powiodło się.</br>
                                Zapisano wszystkie oceny, nie przeniesiono żadnej piosenki.";
                            }
                            else {
                                //Define service object for making API requests.
                                $service = new Google_Service_YouTube($client);
                                //Define the $playlistItem object, which will be uploaded as the request body.
                                $playlistItem = new Google_Service_YouTube_PlaylistItem();

                                //First handle the moving aspect - copying is next
                                $newSongPlaylistItemsId = '';
                                if($moveRequired) {
                                    //Get current and new playlist data for moving and copying
                                    $oldPlaylistDetails = $data['playlistId'] !== "search" ? $playlist : $this->PlaylistModel->FetchPlaylistById($currentSong->ListId);
                                    $newPlaylistDetails = $this->PlaylistModel->FetchPlaylistById($formInput['newPlaylistId']);

                                    //Add 'snippet' object to the $playlistItem object.
                                    $playlistItemSnippet = new Google_Service_YouTube_PlaylistItemSnippet();
                                    $playlistItemSnippet->setPlaylistId($newPlaylistDetails->ListUrl);

                                    //Set the resources
                                    $resourceId = new Google_Service_YouTube_ResourceId();
                                    $resourceId->setKind('youtube#video');
                                    $resourceId->setVideoId($currentSong->SongURL);
                                    $playlistItemSnippet->setResourceId($resourceId);
                                    $playlistItem->setSnippet($playlistItemSnippet);

                                    //Perform the YT side of the move - only if either playlist is integrated
                                    if(!$oldPlaylistDetails->ListIntegrated && $newPlaylistDetails->ListIntegrated)
                                    {
                                        //This playlist is local and target playlist is integrated with yt so add the song to the integrated playlist
                                        $response = $service->playlistItems->insert('snippet', $playlistItem);
                                        //New PlaylistItemsId is generated, so we need to capture it to update it in the db
                                        $newSongPlaylistItemsId = $response->id;
                                        //Create a log for the playlist
                                        $this->LogModel->CreateLog("playlist", $newPlaylistDetails->ListId, "Nuta ".$currentSong->SongTitle." dodana do zintegrowanej playlisty w wyniku przeniesienia z ".$oldPlaylistDetails->ListName);
                                    }
                                    else if($oldPlaylistDetails->ListIntegrated && !$newPlaylistDetails->ListIntegrated)
                                    {
                                        //This playlist is integrated with yt and target playlist is local so delete the song from the integrated playlist
                                        $response = $service->playlistItems->delete($currentSong->SongPlaylistItemsId);
                                        //Create a log for the playlist
                                        $this->LogModel->CreateLog("playlist", $oldPlaylistDetails->ListId, "Nuta ".$currentSong->SongTitle." usunięta z zintegrowanej playlisty w wyniku przeniesienia do ".$newPlaylistDetails->ListName);
                                    }
                                    else if ($oldPlaylistDetails->ListIntegrated && $newPlaylistDetails->ListIntegrated)
                                    {
                                        //Both playlists are integrated with yt so add the song to the new playlist
                                        $response = $service->playlistItems->insert('snippet', $playlistItem);
                                        //Create a log of the song being added in the playlist's record
                                        $this->LogModel->CreateLog("playlist", $newPlaylistDetails->ListId, "Nuta ".$currentSong->SongTitle." dodana do zintegrowanej playlisty w wyniku przeniesienia z ".$oldPlaylistDetails->ListName);
                                        //New PlaylistItemsId is generated, so we need to capture it to update it in the db
                                        $newSongPlaylistItemsId = $response->id;
                                        //Both playlists are integrated with yt so delete the song from the old playlist
                                        $response = $service->playlistItems->delete($currentSong->SongPlaylistItemsId);
                                        //Create a log of this deletion in the playlist's record
                                        $this->LogModel->CreateLog("playlist", $oldPlaylistDetails->ListId, "Nuta ".$currentSong->SongTitle." usunięta z zintegrowanej playlisty w wyniku przeniesienia do ".$newPlaylistDetails->ListName);
                                    }

                                    //Based on the target playlist status, the update is different
                                    if(!$newPlaylistDetails->ListIntegrated)
                                    {
                                        //Target playlist is local - move the song in the local database
                                        $updateSuccess = $this->SongModel->UpdateLocalSongPlaylist($currentSong->SongId, $formInput['newPlaylistId']);
                                    }
                                    else
                                    {
                                        //Target playlist is integrated
                                        $updateSuccess = $this->SongModel->UpdateIntegratedSongPlaylist($currentSong->SongId, $formInput['newPlaylistId'], $newSongPlaylistItemsId);
                                    }

                                    //Log the move in the particular song's record
                                    $localResultMessage .= ($localResultMessage == "" ? "\t" : "<br>\t");
                                    if($updateSuccess) {
                                        $localResultMessage .= "Przeniesiono z playlisty ".$oldPlaylistDetails->ListName." do ".$newPlaylistDetails->ListName;
                                        $this->LogModel->CreateLog("song", $currentSong->SongId, "Nuta przeniesiona z playlisty ".$oldPlaylistDetails->ListName." do ".$newPlaylistDetails->ListName);
                                    }
                                    else {
                                        $localResultMessage .= "Nie udało się przenieść do ".$newPlaylistDetails->ListName;
                                        $this->LogModel->CreateLog("song", $currentSong->SongId, "Nie udało się przenieść z ".$oldPlaylistDetails->ListName." do ".$newPlaylistDetails->ListName);
                                    }
                                }
                                //Next handle the copy aspect - both moving and copying may happen for the same song!
                                if($copyToIntegratedRequired) {
                                    $copyPlaylistDetails = $this->PlaylistModel->FetchPlaylistById($formInput['copyToPlaylist']);

                                    //Add 'snippet' object to the $playlistItem object.
                                    $playlistItemSnippet = new Google_Service_YouTube_PlaylistItemSnippet();
                                    $playlistItemSnippet->setPlaylistId($copyPlaylistDetails->ListUrl);

                                    //Set the resources
                                    $resourceId = new Google_Service_YouTube_ResourceId();
                                    $resourceId->setKind('youtube#video');
                                    $resourceId->setVideoId($currentSong->SongURL);
                                    $playlistItemSnippet->setResourceId($resourceId);
                                    $playlistItem->setSnippet($playlistItemSnippet);

                                    //Add the song to the integrated playlist
                                    $response = $service->playlistItems->insert('snippet', $playlistItem);

                                    //New PlaylistItemsId is generated, so we need to capture it to update it in the db
                                    $newSongPlaylistItemsId = $response->id;

                                    //Target playlist is integrated
                                    $updateSuccess = $this->SongModel->UpdateCopiedSongItemsId($newSongId, $newSongPlaylistItemsId);

                                    //Create a log for the playlist
                                    $localResultMessage .= ($localResultMessage == "" ? "\t" : "<br>\t");
                                    if($updateSuccess) {
                                        $localResultMessage .= "Dodano do zintegrowanej playlisty ".$newPlaylistDetails->ListName;
                                        $this->LogModel->CreateLog("playlist", $copyPlaylistDetails->ListId, "Nuta dodana do zintegrowanej playlisty w wyniku skopiowania z ".$playlist->ListName);
                                    }
                                    else {
                                        $localResultMessage .= "Nie udało się dodać do zintegrowanej playlisty ".$newPlaylistDetails->ListName;
                                        $this->LogModel->CreateLog("playlist", $copyPlaylistDetails->ListId, "Nie udało się dodać utworu o ID ".$currentSong->SongId." do zintegrowanej playlisty");
                                    }
                                }
                            }
                        }
                        else
                        {
                            //Could not load the YouTube api
                            $data['body']  = 'invalidAction';
                            $data['title'] = "Wystąpił Błąd!";
                            $data['errorMessage'] = "Nie znaleziono biblioteki google!";
                        }
                    }
                    //Save the result message and pass it to the report
                    $finalResultMessage = $localResultMessage != "" ? ("<br><br>Utwór " . $currentSong->SongTitle . ":<br><br>" . $localResultMessage) : "";
                    $resultMessage .= $finalResultMessage;
                }
            }
            //Finalise the result message
            $data['resultMessage'] = $resultMessage . "</pre>";
            //Submit a report
            $newReportId = $this->LogModel->SubmitReport(htmlspecialchars($data['resultMessage']));
            //Create a log
            $where = $data['playlistId'] === "search" ? "z wyszukiwarki" : "z playlisty";
            $reportSuccessful = $newReportId ? " i dołączono raport." : ", nie udało się zapisać raportu.";
            $logMessage = "Zapisano oceny ".$where.$reportSuccessful;
            if(is_numeric($data['playlistId']))
                $this->LogModel->CreateLog('playlist', $data['playlistId'], $logMessage, $newReportId);
        }
        else {
            //The user is not allowed to update anything in the system
            $data['body']  = 'invalidAction';
            $data['title'] = "Wystąpił Błąd!";
            $data['errorMessage'] = "Nie posiadasz uprawnień do wykonywania tej akcji.";
        }
        $this->load->view('templates/main', $data);
    }

    /**
     * Updates the playlist with new songs added to it on YouTube.
     *
     * @return void
     */
	public function downloadSongs()
	{
		$listId = isset( $_GET['ListId'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['ListId'] ) ) : 0;
		$data = array(
			'body' => 'downloadSongs',
			'title' => 'Aktualizacja listy!',
			'songsJsonArray' => array(),
			'ListId' => $listId,
			'success' => true
		);

		//Check if the user is allowed to do this action
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        if($userAuthenticated)
        {
            //Parameters for the api call
            $host = "https://youtube.googleapis.com/youtube/v3/playlistItems";
            $part = "snippet";
            $maxResults = 50; //50 is the most you can get on one page
            $playlistId = $this->PlaylistModel->GetListUrlById($listId);

            //Create a log
            $this->LogModel->CreateLog('playlist', $listId, "Załadowano nowe nuty na playlistę");

            //Fetch the api key from the api_key file
            if($apiKey = file_get_contents("application/api/api_key.txt"))
            {
                //Load songs for the first time
                if($playlistId != "")
                {
					$url = $host.'?part='.$part.'&maxResults='.$maxResults.'&playlistId='.urlencode($playlistId).'&key='.urlencode($apiKey);
                    $firstCall = file_get_contents($url);
                    $downloadedSongs = json_decode($firstCall, true);
                    $data['songsJsonArray'][] = $downloadedSongs;
                }

                //How many songs total - returns 0 if null
                $allResults = $downloadedSongs['pageInfo']['totalResults'] ?? 0;

                if($allResults > 0)
                {
                    //Keep loading songs until all are loaded
                    for($scannedResults = $downloadedSongs['pageInfo']['resultsPerPage'] ?? 150000;
                        $scannedResults < $allResults;
                        $scannedResults += $downloadedSongs['pageInfo']['resultsPerPage'])
                    {
                        //Get the token of the next page
                        $pageToken = $downloadedSongs['nextPageToken'];
                        //Perform the api call
                        $nextCall = file_get_contents($host.'?part='.$part.'&maxResults='.$maxResults.'&pageToken='.$pageToken.'&playlistId='.urlencode($playlistId).'&key='.urlencode($apiKey));
                        //Decode the result from json to array
                        $downloadedSongs = json_decode($nextCall, true);
                        //Save the songs into the array
                        $data['songsJsonArray'][] = $downloadedSongs;
                    }

                    //Get all songs that are already in the list, only urls
                    $songURLs = $this->SongModel->GetURLsOfAllSongsInList($listId);
                    $songURLsArray = [];
                    foreach($songURLs as $songURL)
                    {
                        $songURLsArray[] = $songURL->SongURL;
                    }

                    //Perform the reloading process
                    //The main array is composed of parsed data arrays
                    foreach($data['songsJsonArray'] as $songarrays)
                    {
                        //Each of these arrays contains a list of songs
                        foreach($songarrays as $songlist)
                        {
                            //Some data is not in an array and is unnecessary for this process
                            if(is_array($songlist))
                            {
                                //Each song is an array itself
                                foreach($songlist as $song)
                                {
                                    //Data that is not a song array can be dropped
                                    if(is_array($song))
                                    {
                                        //Get all required data to save a song in the database
                                        $songURL = $song['snippet']['resourceId']['videoId'];
										$songPublic = isset($song['snippet']['thumbnails']['medium']['url']);
                                        $songThumbnailURL = $songPublic ? $song['snippet']['thumbnails']['medium']['url'] : false;
                                        $songTitle = mysqli_real_escape_string( $this->db->conn_id, $song['snippet']['title'] );
                                        $songPlaylistItemsId = $song['id'];

                                        //If something goes wrong any incorrect entries will be discarded
                                        if(isset($songURL) && isset($songThumbnailURL) && isset($songTitle) && isset($songPlaylistItemsId))
                                        {
                                            //Check if the song already exists in the database
                                            if(in_array($songURL, $songURLsArray))
                                            {
                                                echo $songTitle . " - ⏸<br />";
                                            } //Attempt to insert the song to the database
                                            else if($songPublic && $this->SongModel->InsertSong($listId, $songURL, $songThumbnailURL, $songTitle, $songPlaylistItemsId))
                                            {
                                                echo $songTitle . " - ✔<br />";
                                            } //If insertion failed
                                            else
                                            {
                                                echo $songURL . " is private - ❌<br />";
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    $data['success'] = false;
                }
            }
            else
            {
                //API key not found
                $data['body']  = 'invalidAction';
                $data['title'] = "Nie znaleziono klucza API!";
                $data['errorMessage'] = "Nie znaleziono klucza API.";
            }
        }
        else
        {
            //The user is not allowed to update anything in the system
            $data['body']  = 'invalidAction';
            $data['title'] = "Wystąpił Błąd!";
            $data['errorMessage'] = "Nie posiadasz uprawnień do wykonywania tej akcji.";
        }

		$this->load->view( 'templates/main', $data );
	}

    /**
     * Opens the new playlist form.
     *
     * @return void
     */
	public function newPlaylist()
	{
        $userAuthenticated = $this->SecurityModel->authenticateUser();

        if($userAuthenticated)
        {
            $data = array(
                'body' => 'playlist/addPlaylist',
                'title' => 'Uber Rapsy | Dodaj nową playlistę!'
            );
            $this->load->view('templates/main', $data);
        }
        else redirect('logout');
	}

    /**
     * Processes the Add Playlist form.
     *
     * @return void
     */
    public function addPlaylist(): void
    {
        $data = [];
        $userAuthenticated = $this->SecurityModel->authenticateUser();

        if($userAuthenticated)
        {
            //Include google library
            $client = $this->SecurityModel->initialiseLibrary();

            if($client !== false) {
                //Validate the access token required for an api call
                $tokenExpired = $this->SecurityModel->validateAuthToken($client);

                if($tokenExpired) {
                    //Refresh token not found
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd autoryzacji tokenu!";
                    $data['errorMessage'] = "Odświeżenie tokenu autoryzującego nie powiodło się.</br> Nie stworzono playlisty.";
                }
                else {
                    $data = array(
                        'body' => 'playlist/addPlaylist',
                        'title' => isset($_POST['playlistName']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistName'])) : "",
                        'description' => $_POST['playlistDesc'] ?? "",
                        'visibility' => isset($_POST['playlistVisibility']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistVisibility'])) : "",
                        'link' => '',
                        'resultMessage' => ''
                    );

                    //Validate the form
                    if($data['title'] != "" && in_array($data['visibility'], ["public", "unlisted", "private"]) )
                    {
                        //Update the description new-line characters
                        $data['description'] = trim(str_replace(["\r\n", "\r"], "\n", $data['description']));

                        //Define service object for making API requests.
                        $service = new Google_Service_YouTube($client);

                        //Define the $playlist object, which will be uploaded as the request body.
                        $playlist = new Google_Service_YouTube_Playlist();

                        //Add 'snippet' object to the $playlist object.
                        $playlistSnippet = new Google_Service_YouTube_PlaylistSnippet();
                        $playlistSnippet->setDefaultLanguage('en');
                        $playlistSnippet->setDescription($data['description']);
                        $playlistSnippet->setTitle($data['title']);
                        $playlist->setSnippet($playlistSnippet);

                        //Add 'status' object to the $playlist object.
                        $playlistStatus = new Google_Service_YouTube_PlaylistStatus();
                        $playlistStatus->setPrivacyStatus($data['visibility']);
                        $playlist->setStatus($playlistStatus);

                        //Save the api call response
                        $response = $service->playlists->insert('snippet, status', $playlist);

                        //Get the unique id of a playlist from the response
                        $data['link'] = $response->id;

                        //Save the playlist into the database
                        $this->PlaylistModel->InsertPlaylist($data);

                        //Fetch the local id of the newly created playlist
                        $listId = $this->PlaylistModel->GetListIdByUrl($data['link']);

                        //Create a log
                        $this->LogModel->CreateLog('playlist', $listId, "Stworzono zintegrowaną playlistę");

                        $data['resultMessage'] = "Playlista zapisana!";
                    } else {
                        $data['resultMessage'] = "Proszę wyślij formularz ponownie, wprowadzone dane są niepoprawne.";
                    }
                }
            }
            else
            {
                //Could not load the library
                $data['body']  = 'invalidAction';
                $data['title'] = "Wystąpił Błąd!";
                $data['errorMessage'] = "Nie znaleziono biblioteki google!";
            }
        }
        else redirect('logout');

        $this->load->view( 'templates/main', $data );
    }

    /**
     * Shows and validates the form to add a local playlist.
     *
     * @return void
     */
    public function addLocal()
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'playlist/addLocalPlaylist';
            $data['title'] = "Uber Rapsy | Dodaj lokalnie playlistę!";

            if(isset($_POST['playlistFormSubmitted']))
            {
                $queryData = [];
                $queryData['ListUrl'] = isset($_POST['playlistId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistId'])) : "";
                $queryData['ListName'] = isset($_POST['playlistName']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistName'])) : "";
                $queryData['ListDesc'] = isset($_POST['playlistDesc']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistDesc'])) : "";
                $queryData['ListCreatedAt'] = isset($_POST['playlistDate']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistDate'])) : "";
                $queryData['ListActive'] = isset($_POST['playlistVisibility']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistVisibility'])) : "";

                //obtain the unique playlist ID from the url given
                $listPos = strpos($queryData['ListUrl'], "list=");
                if($listPos > 0)
                {
                    $indexPos = strpos($queryData['ListUrl'], "&index=");
                    $indexLength = strlen(substr($queryData['ListUrl'], $indexPos));
                    if($indexPos > 0) $queryData['ListUrl'] = substr($queryData['ListUrl'], $listPos+5, -$indexLength);
                    else $queryData['ListUrl'] = substr($queryData['ListUrl'], $listPos+5);
                }

                if($queryData['ListName'] && $queryData['ListDesc'] && $queryData['ListCreatedAt'] && $queryData['ListActive'] != "")
                {
                    $this->PlaylistModel->InsertLocalPlaylist($queryData);
                    $data['resultMessage'] = "Pomyślnie dodano playlistę!";

                    //fetch the newly created playlist to obtain the id and create a log
                    $playlistId = $this->PlaylistModel->GetPlaylistIdByTimestamp($queryData['ListCreatedAt']);
                    $this->LogModel->CreateLog('playlist', $playlistId, "Stworzono lokalną playlistę");
                }
                else
                {
                    $data['resultMessage'] = "";
                    $data['resultMessage'] .= $queryData['ListName'] == "" ? "Nazwa Playlisty jest wymagana!</br>" : '';
                    $data['resultMessage'] .= $queryData['ListDesc'] == "" ? "Opis Playlisty jest wymagany!</br>" : '';
                    $data['resultMessage'] .= $queryData['ListCreatedAt'] == "" ? "Data Stworzenia Playlisty jest wymagana!</br>" : '';
                    $data['resultMessage'] .= $queryData['ListActive'] == "" ? "Status Playlisty jest wymagany!</br>" : '';
                }
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Allows the user to delete a local playlist.
     *
     * @return void
     */
    public function deleteLocal()
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'playlist/deleteLocal';
            $data['title'] = "Uber Rapsy | Usuń playlistę";
            $data['ListId'] = isset($_GET['id']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['id'])) : 0;
            $data['DeleteList'] = isset($_GET['delete']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['delete'])) : false;

            if($data['ListId'] && is_numeric($data['ListId']))
            {
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);

                if($data['playlist'] === false)
                {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono playlisty o podanym numerze id!";
                }
                else if($data['DeleteList'] === "true")
                {
                    $this->PlaylistModel->DeleteLocalPlaylist($data['ListId']);
                    redirect('playlistDashboard');
                }
            }
            else
            {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id playlisty lub nie podano go wcale!";
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Allows the user to delete a song from a playlist.
     *
     * @return void
     */
    public function delSong()
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'song/delSong';
            $data['title'] = "Uber Rapsy | Usuń piosenkę";
            $data['SongId'] = isset($_GET['id']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['id'])) : 0;
            $data['DeleteSong'] = isset($_GET['delete']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['delete'])) : false;

            if($data['SongId'] && is_numeric($data['SongId']))
            {
                $data['song'] = $this->SongModel->GetSongById($data['SongId']);

                if($data['song'] === false)
                {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono piosenki o podanym numerze id!";
                }
                else if($data['DeleteSong'] === "true")
                {
                    $this->SongModel->DeleteSong($data['SongId']);
                    redirect('playlistDashboard');
                }
                else
                {
                    $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['song']->ListId);
                }
            }
            else
            {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id piosenki lub nie podano go wcale!";
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Allows the user to switch the integration status of a playlist
     *
     * @return void
     */
    public function integrate()
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'playlist/integrate';
            $data['title'] = "Uber Rapsy | Zintegruj playlistę";
            $data['playlistId'] = isset($_GET['id']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['id'])) : 0;
            $data['status'] = isset($_GET['status']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['status'])) : 0;

            if($data['playlistId'] && is_numeric($data['playlistId']))
            {
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['playlistId']);

                if($data['playlist'] === false)
                {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono playlisty o podanym numerze id!";
                }

                //status is passed when the form is submitted, otherwise we open the form
                if($data['status'] == "confirm")
                {
                    $updatedIntegrationStatus = $data['playlist']->ListIntegrated ? "0" : "1";
                    $updatedLink = isset($_POST['nlink']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['nlink'])) : 0;
                    $data['playlistUpdatedMessage'] = "<h2>Playlista została zaktualizowana!</h2>";
                    $data['playlistUpdatedStatus'] = true;

                    //check if there is a valid link existing or entered when integrating the playlist with YT
                    $validLink = !$updatedIntegrationStatus || (strlen($data['playlist']->ListUrl) > 10 || strlen($updatedLink) > 10);
                    if($validLink)
                    {
                        $this->PlaylistModel->UpdatePlaylistIntegrationStatus($data['playlistId'], $updatedIntegrationStatus, $updatedLink);
                        $this->LogModel->CreateLog('playlist', $data['playlistId'],
                            $updatedIntegrationStatus ? "Playlista została zintegrowana z YT" : "Usunięto integrację playlisty z YT");
                    }
                    else
                    {
                        //the playlist does not have a link when one is required to integrate with youtube
                        $data['playlistUpdatedMessage'] = "<h2>Zintegrowana plalista musi posiadać swój link na YouTube!</h2>";
                        $data['playlistUpdatedStatus'] = false;
                    }
                }
            }
            else
            {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id playlisty lub nie podano go wcale!";
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

	/**
     * Allows the user to see the logs of the playlist
     *
     * @return void
     */
    public function showLog()
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        if($userAuthenticated) {
            $data = [];
            $data['body']  = 'playlist/showLog';
            $data['title'] = "Uber Rapsy | Historia playlisty";
            $data['playlistId'] = isset($_GET['id']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['id'])) : 0;

            if($data['playlistId'] && is_numeric($data['playlistId'])) {
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['playlistId']);
				$data['playlistLog'] = $this->LogModel->GetPlaylistLog($data['playlistId']);

                if($data['playlist'] === false) {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono playlisty o podanym numerze id!";
                }
            }
            else {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id playlisty lub nie podano go wcale!";
            }
            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

}
