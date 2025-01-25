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
        else if (is_numeric($data['ListId']) && $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId'])) {
            $data['ListName'] = $data['playlist']->ListName;
            $data['ListUrl'] = $data['playlist']->ListUrl;
            $data['title'] = $data['ListName']." | Playlista Uber Rapsy";

            //Check if search was used
            $data['Filter'] = isset($_GET['Filter']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['Filter'])) : "none";
            $data['Filter'] = ($data['Filter'] === "none" && $data['SearchQuery'] !== 0) ? "Search" : $data['Filter'];

            //Define various checkbox-related properties in one place - first the db name, then button name, then display name
            $data['CheckboxPropertiesDetails'] = [
                ['SongDistinction', 'btnDistinction', 'Wyróżnienie'],
                ['SongMemorial', 'btnMemorial', '10*'],
                ['SongXD', 'btnXD', 'XD'],
                ['SongNotRap', 'btnNotRap', 'To Nie Rapsik'],
                ['SongDiscomfort', 'btnDiscomfort', 'Strefa Dyskomfortu'],
                ['SongTop', 'btnTop', 'X15'],
                ['SongNoGrade', 'btnNoGrade', 'Nie Oceniam'],
                ['SongUber', 'btnUber', 'Uber'],
                ['SongBelow', 'btnBelowSeven', '< 7'],
                ['SongBelTen', 'btnBelowTen', '< 10'],
                ['SongBelNine', 'btnBelowNine', '< 9'],
                ['SongBelEight', 'btnBelowEight', '< 8'],
                ['SongBelFour', 'btnBelowFour', '< 4'],
                ['SongDuoTen', 'btnDuoTen', '"10"'],
                ['SongVeto', 'btnVeto', 'VETO'],
                ['SongBelHalfSeven', 'btnBelowHalfSeven', '< 7.5'],
                ['SongBelHalfEight', 'btnBelowHalfEight', '< 8.5'],
                ['SongBelHalfNine', 'btnBelowHalfNine', '< 9.5'],
                ['SongRehearsal', 'btnRehearsal', 'Ponowny Odsłuch']
            ];

            //Filter is in use, queue for the right filter or none to just fetch the playlist
            switch($data['Filter']) {
                case "Search": {
                    //Local search was selected
                    $data['songs'] = $this->SongModel->GetSongsFromList($data['ListId'], $data['SearchQuery']);
                    break;
                }
                case "Checkbox": {
                    $checkboxProperty = isset($_GET['Prop']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['Prop'])) : "none";
                    $data['songs'] = [];
                    foreach($data['CheckboxPropertiesDetails'] as $CheckboxProperties) {
                        if (in_array($checkboxProperty, $CheckboxProperties)) {
                            $data['songs'] = $this->PlaylistModel->FilterSongsByCheckboxProperty($data['ListId'], $checkboxProperty);
                        }
                    }

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
                    //Check if the playlist has a YouTube link. If so, compare with the YT list and refresh
                    if(!empty($data['playlist']->ListUrl)) {
                        //Based on the current playlist Etag check if an update is required
                        $playlistNeedsUpdate = $this->playlistEtagsMismatch($data['playlist']);
                        if($playlistNeedsUpdate !== false) {
                            //Fetch new songs and update the playlist
                            $refreshReturnCode = $this->RefreshPlaylist($data['ListId']);
                            if($refreshReturnCode === true) {
                                //Update the locally stored playlist Etag
                                $this->PlaylistModel->UpdatePlaylistEtag($data['ListId'], $playlistNeedsUpdate);
                            }
                            else if($refreshReturnCode === -1) {
                                //API key not found
                                $data['body']  = 'invalidAction';
                                $data['title'] = "Nie znaleziono klucza API!";
                                $data['errorMessage'] = "Nie znaleziono klucza API.";
                            }
                            else if ($refreshReturnCode === false || $refreshReturnCode === -2) {
                                //Response returned empty (false) or response could not be reached (-2)
                                $data['refreshSuccess'] = false;
                            }
                        }
                    }

                    //No filter in use - just load the playlist
                    $data['songs'] = $this->SongModel->GetSongsFromList($data['ListId']);
                    break;
                }
            }

            //Calculate playlist averages for each reviewer and the overall average - not required for tierlists
            if (!in_array($data['Filter'], ["Adam", "Churchie", "Average"])) {
                $avgOverall = 0;
                $avgAdam = 0;
                $avgChurchie = 0;
                $ratedCount = 0;
                $ratedAdam = 0;
                $ratedChurchie = 0;
                foreach($data['songs'] as $song) {
                    //Display values without decimals at the end if the decimals are only 0
                    if(is_numeric($song->SongGradeAdam)) $song->SongGradeAdam = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeAdam);
                    if(is_numeric($song->SongGradeChurchie)) $song->SongGradeChurchie = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeChurchie);
                    //Do not include songs graded "10" in the average
                    if(!$song->SongDuoTen) {
                        $avgOverall += ($song->SongGradeAdam + $song->SongGradeChurchie) / 2;
                        $avgAdam += $song->SongGradeAdam;
                        $avgChurchie += $song->SongGradeChurchie;
                        $ratedCount += $song->SongGradeAdam > 0 || $song->SongGradeChurchie > 0 ? 1 : 0;
                        $ratedAdam += $song->SongGradeAdam > 0 ? 1 : 0;
                        $ratedChurchie += $song->SongGradeChurchie > 0 ? 1 : 0;
                    }
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

        $this->load->view('templates/customNav', $data);
    }

    /**
     * Makes an API call to fetch a playlist's current Etag and compares it with the locally saved Etag
     *
     * @param object $playlist the playlist to compare the etags for
     * @return bool|string an etag if the etags mismatch, true if a failure occurred along the way, false if the etags match
     */
    function playlistEtagsMismatch(object $playlist): bool|string
    {
        //Include google library
        $client = $this->SecurityModel->initialiseLibrary();

        //Only proceed when the library was successfully included
        if($client !== false) {
            //Validate the access token required for the api call
            $tokenExpired = $this->SecurityModel->validateAuthToken($client);

            //Continue to the api call or refresh the auth token
            if ($tokenExpired) {
                $data['body'] = 'invalidAction';
                $data['title'] = "Błąd autoryzacji tokenu!";
                $data['errorMessage'] = "Odświeżenie tokenu autoryzującego nie powiodło się.</br>
                            Zapisano wszystkie oceny, nie przeniesiono żadnej piosenki.";
            }
            else {
                //Define service object for making API requests.
                $service = new Google_Service_YouTube($client);
                $queryParams = [
                    'id' => $playlist->ListUrl
                ];

                //Make the request
                $response = $service->playlists->listPlaylists('contentDetails', $queryParams);

                //Fetch the etag if it was returned
                $etag = $response['etag'] ?? 0;
                if ($etag !== 0 && strlen($etag) > 1) {
                    //Compare the current Etag with the locally saved Etag
                    return $etag === $playlist->ListEtag ? false : $etag;
                }
            }
        }
        else {
            //Could not load the YouTube api
            $data['body']  = 'invalidAction';
            $data['title'] = "Wystąpił Błąd!";
            $data['errorMessage'] = "Nie znaleziono biblioteki google!";

            $this->load->view( 'templates/main', $data );
        }
    }

    /**
     * Opens the playlist dashboard.
     *
     * @return void
     */
    public function dashboard()
    {
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
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
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
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
                    $data['songs'] = $this->SongModel->GetAllSongsFromList($data['ListId']);
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
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'playlist/edit';
            $data['title'] = "Uber Rapsy | Edytuj playlistę!";
            $data['ListId'] = isset( $_GET['id'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['id'] ) ) : 0;

            if($data['ListId'] && is_numeric($data['ListId'])) {
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);

                if($data['playlist'] === false) {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono playlisty o podanym numerze id!";
                }
                else if(isset($_POST['playlistFormSubmitted'])) {
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
                    $queryData['btnBelowHalfSeven'] = isset($_POST["btnBelowHalfSeven"]) ? 1 : 0;
                    $queryData['btnBelowHalfEight'] = isset($_POST["btnBelowHalfEight"]) ? 1 : 0;
                    $queryData['btnBelowHalfNine'] = isset($_POST["btnBelowHalfNine"]) ? 1 : 0;

                    if($queryData['ListName'] && $queryData['ListDesc'] && $queryData['ListCreatedAt'] && $queryData['ListActive'] != "") {
                        $this->PlaylistModel->UpdatePlaylist($queryData);
                        $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);
                        $data['resultMessage'] = "Pomyślnie zaktualizowano playlistę!";
                    }
                    else {
                        $data['resultMessage'] = "";
                        $data['resultMessage'] .= $queryData['ListName'] == "" ? "Nazwa Playlisty jest wymagana!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListDesc'] == "" ? "Opis Playlisty jest wymagany!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListCreatedAt'] == "" ? "Data Stworzenia Playlisty jest wymagana!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListActive'] == "" ? "Status Playlisty jest wymagany!</br>" : '';
                    }
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
     * Allows the user to set a playlist as (in)active.
     *
     * @return void
     */
    public function hidePlaylist()
    {
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();

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
        $flags['SongDepA'] = $currentSong->SongDepA != $formInput['SongDepA'];
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
        $flags['SongBelHalfSeven'] = $currentSong->SongBelHalfSeven != $formInput['SongBelHalfSeven'];
        $flags['SongBelHalfEight'] = $currentSong->SongBelHalfEight != $formInput['SongBelHalfEight'];
        $flags['SongBelHalfNine'] = $currentSong->SongBelHalfNine != $formInput['SongBelHalfNine'];

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
            'SongDepA' => "department abroad",
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
            'SongBelHalfSeven' => "< 7.5",
            'SongBelHalfEight' => "< 8.5",
            'SongBelHalfNine' => "< 9.5",
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
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();

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
            if($data['playlistId'] !== "search") {
                $playlist = $this->PlaylistModel->FetchPlaylistById($data['playlistId']);
                $songCount = $this->PlaylistModel->GetPlaylistSongCount($playlist->ListId);
            }

            //Process each song separately
            $i = 0;
            $data['processed'] = 0;
            $data['processedAndUpdated'] = 0;
            while (isset($_POST["songUpdated-" . $i+21])) {
                $i += ($data['processed'] == 0) ? 0 : 27;
                $data['processed'] += 1;
                //Only process songs that were actually updated
                $songUpdated = isset($_POST["songUpdated-" . $i+21]) && $_POST["songUpdated-".$i+21];
                if ($songUpdated) {
                    $data['processedAndUpdated'] += 1;
                    //Create a variable for the song's update message
                    $localResultMessage = "";

                    //Save the form data to a temp variable
                    $formInput['songId'] = $_POST["songId-" . $i];
                    $formInput['SongGradeAdam'] = $_POST["nwGradeA-" . $i + 1];
                    $formInput['SongGradeChurchie'] = $_POST["nwGradeC-" . $i + 2];
                    $formInput['SongRehearsal'] = isset($_POST["songRehearsal-" . $i + 4]) && $_POST["songRehearsal-" . $i + 4] == "on" ? 1 : 0;
                    $formInput['SongDistinction'] = isset($_POST["songDistinction-" . $i + 5]) && $_POST["songDistinction-" . $i + 5] == "on" ? 1 : 0;
                    $formInput['SongMemorial'] = isset($_POST["songMemorial-" . $i + 6]) && $_POST["songMemorial-" . $i + 6] == "on" ? 1 : 0;
                    $formInput['SongXD'] = isset($_POST["songXD-" . $i + 7]) && $_POST["songXD-" . $i + 7] == "on" ? 1 : 0;
                    $formInput['SongNotRap'] = isset($_POST["songNotRap-" . $i + 8]) && $_POST["songNotRap-" . $i + 8] == "on" ? 1 : 0;
                    $formInput['SongDiscomfort'] = isset($_POST["songDiscomfort-" . $i + 9]) && $_POST["songDiscomfort-" . $i + 9] == "on" ? 1 : 0;
                    $formInput['SongDepA'] = isset($_POST["songDepA-" . $i + 26]) && $_POST["songDepA-" . $i + 26] == "on" ? 1 : 0;
                    $formInput['SongTop'] = isset($_POST["songTop-" . $i + 10]) && $_POST["songTop-" . $i + 10] == "on" ? 1 : 0;
                    $formInput['SongNoGrade'] = isset($_POST["songNoGrade-" . $i + 11]) && $_POST["songNoGrade-" . $i + 11] == "on" ? 1 : 0;
                    $formInput['SongUber'] = isset($_POST["songUber-" . $i + 12]) && $_POST["songUber-" . $i + 12] == "on" ? 1 : 0;
                    $formInput['SongBelow'] = isset($_POST["songBelow-" . $i + 13]) && $_POST["songBelow-" . $i + 13] == "on" ? 1 : 0;
                    $formInput['SongBelTen'] = isset($_POST["songBelTen-" . $i + 14]) && $_POST["songBelTen-" . $i + 14] == "on" ? 1 : 0;
                    $formInput['SongBelNine'] = isset($_POST["songBelNine-" . $i + 15]) && $_POST["songBelNine-" . $i + 15] == "on" ? 1 : 0;
                    $formInput['SongBelEight'] = isset($_POST["songBelEight-" . $i + 16]) && $_POST["songBelEight-" . $i + 16] == "on" ? 1 : 0;
                    $formInput['SongBelFour'] = isset($_POST["songBelFour-" . $i + 17]) && $_POST["songBelFour-" . $i + 17] == "on" ? 1 : 0;
                    $formInput['SongDuoTen'] = isset($_POST["songDuoTen-" . $i + 18]) && $_POST["songDuoTen-" . $i + 18] == "on" ? 1 : 0;
                    $formInput['SongVeto'] = isset($_POST["songVeto-" . $i + 19]) && $_POST["songVeto-" . $i + 19] == "on" ? 1 : 0;
                    $formInput['newPlaylistId'] = $_POST["nwPlistId-" . $i + 3];
                    $formInput['copyToPlaylist'] = $_POST["copyPlistId-" . $i + 20];
                    $formInput['SongComment'] = $_POST["songComment-" . $i + 22];
                    $formInput['SongBelHalfSeven'] = isset($_POST["SongBelHalfSeven-" . $i + 23]) && $_POST["SongBelHalfSeven-" . $i + 23] == "on" ? 1 : 0;
                    $formInput['SongBelHalfEight'] = isset($_POST["SongBelHalfEight-" . $i + 24]) && $_POST["SongBelHalfEight-" . $i + 24] == "on" ? 1 : 0;
                    $formInput['SongBelHalfNine'] = isset($_POST["SongBelHalfNine-" . $i + 25]) && $_POST["SongBelHalfNine-" . $i + 25] == "on" ? 1 : 0;

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
                        if (strlen($newAdamRating) > 0 && strlen($newChurchieRating) > 0
                            && is_numeric($newAdamRating) && is_numeric($newChurchieRating)
                            && $this->UtilityModel->InRange($newAdamRating, 0, 15) && $this->UtilityModel->InRange($newChurchieRating, 0, 15)
                            && fmod($newAdamRating, 0.5) == 0 && fmod($newChurchieRating, 0.5) == 0) {
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
                            $localResultMessage .= ($localResultMessage == "" ? "\t" : "<br>\t");
                            $localResultMessage .= "Skopiowano do: ".$targetName;
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
                                    if(!$oldPlaylistDetails->ListIntegrated && $newPlaylistDetails->ListIntegrated) {
                                        //This playlist is local and target playlist is integrated with yt so add the song to the integrated playlist
                                        $response = $service->playlistItems->insert('snippet', $playlistItem);
                                        //New PlaylistItemsId is generated, so we need to capture it to update it in the db
                                        $newSongPlaylistItemsId = $response->id;
                                        //Create a log for the playlist
                                        $this->LogModel->CreateLog("playlist", $newPlaylistDetails->ListId, "Nuta ".$currentSong->SongTitle." dodana do zintegrowanej playlisty w wyniku przeniesienia z ".$oldPlaylistDetails->ListName);
                                    }
                                    else if($oldPlaylistDetails->ListIntegrated && !$newPlaylistDetails->ListIntegrated) {
                                        //This playlist is integrated with yt and target playlist is local so delete the song from the integrated playlist
                                        $response = $service->playlistItems->delete($currentSong->SongPlaylistItemsId);
                                        //Create a log for the playlist
                                        $this->LogModel->CreateLog("playlist", $oldPlaylistDetails->ListId, "Nuta ".$currentSong->SongTitle." usunięta z zintegrowanej playlisty w wyniku przeniesienia do ".$newPlaylistDetails->ListName);
                                    }
                                    else if ($oldPlaylistDetails->ListIntegrated && $newPlaylistDetails->ListIntegrated) {
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
                                    if(!$newPlaylistDetails->ListIntegrated) {
                                        //Target playlist is local - move the song in the local database
                                        $updateSuccess = $this->SongModel->UpdateLocalSongPlaylist($currentSong->SongId, $formInput['newPlaylistId']);
                                    }
                                    else {
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
                        else {
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
			'ListId' => $listId,
			'refreshSuccess' => true
		);

		//Check if the user is allowed to do this action
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        if($userAuthenticated) {
            //Check if the playlist has a YouTube link. If so, compare with the YT list and refresh
            $playlistUrl = $this->PlaylistModel->GetListUrlById($data['ListId']);
            if(!empty($playlistUrl)) {
                $refreshReturnCode = $this->RefreshPlaylist($data['ListId']);
                if($refreshReturnCode === -1) {
                    //API key not found
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Nie znaleziono klucza API!";
                    $data['errorMessage'] = "Nie znaleziono klucza API.";
                }
                else if ($refreshReturnCode === false || $refreshReturnCode === -2) {
                    //Response returned empty (false) or response could not be reached (-2)
                    $data['refreshSuccess'] = false;
                }
            }
            else {
                //Playlist has no YouTube URL
                $data['body']  = 'invalidAction';
                $data['title'] = "Nie znaleziono linku do playlisty na YT!";
                $data['errorMessage'] = "Nie znaleziono linku do playlisty na YT.";
            }
        }
        else {
            //The user is not allowed to update anything in the system
            $data['body']  = 'invalidAction';
            $data['title'] = "Wystąpił Błąd!";
            $data['errorMessage'] = "Nie posiadasz uprawnień do wykonywania tej akcji.";
        }

		$this->load->view( 'templates/main', $data );
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
    function RefreshPlaylist($listId) {
        //Include google library
        $client = $this->SecurityModel->initialiseLibrary();

        //Only proceed when the library was successfully included
        if($client !== false) {
            //Validate the access token required for the api call
            $tokenExpired = $this->SecurityModel->validateAuthToken($client);

            //Continue to the api call or refresh the auth token
            if ($tokenExpired) {
                $data['body'] = 'invalidAction';
                $data['title'] = "Błąd autoryzacji tokenu!";
                $data['errorMessage'] = "Odświeżenie tokenu autoryzującego się nie powiodło.</br>
                            Zapisano wszystkie oceny, nie przeniesiono żadnej piosenki.";
            }
            else {
                //Define service object for making API requests.
                $service = new Google_Service_YouTube($client);
                $queryParams = [
                    'maxResults' => 50,
                    'playlistId' => $this->PlaylistModel->GetListUrlById($listId)
                ];

                //Load songs for the first time. If the request fails, return -2
                $songsJsonArray = [];
                try {
                    $response = $service->playlistItems->listPlaylistItems('snippet', $queryParams);
                }
                catch (Google_Service_Exception $e) {
                    $errorType = json_decode($e->getMessage())->error->errors[0]->reason;
                    if($errorType == "playlistNotFound") {
                        return -2;
                    }
                }

                //How many songs total - assign 0 if null
                $songsJsonArray[] = $response['items'];
                $allResults = $response['pageInfo']['totalResults'] ?? 0;

                //If results were returned, continue the process
                if($allResults > 0) {
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
                            'playlistId' => $this->PlaylistModel->GetListUrlById($listId)
                        ];

                        $response = $service->playlistItems->listPlaylistItems('snippet', $queryParams);
                        //Save the songs into the array
                        $songsJsonArray[] = $response['items'];
                    }

                    //Get all songs that are already in the list, only urls
                    $songURLs = $this->SongModel->GetURLsOfAllSongsInList($listId);
                    $songURLsArray = [];
                    foreach($songURLs as $songURL) {
                        $songURLsArray[] = $songURL->SongURL;
                    }

                    //Perform the reloading process - The main array is composed of parsed song arrays
                    $refreshReport = "<pre>";
                    foreach($songsJsonArray as $songarrays) {
                        //Each of these arrays contains a song object
                        foreach($songarrays as $song) {
                            //Get all required data to save a song in the database
                            $songURL = $song['snippet']['resourceId']['videoId'];
                            $songPublic = isset($song['snippet']['thumbnails']['medium']['url']);
                            $songThumbnailURL = $songPublic ? $song['snippet']['thumbnails']['medium']['url'] : false;
                            $songTitle = mysqli_real_escape_string($this->db->conn_id, $song['snippet']['title']);
                            $songPlaylistItemsId = $song['id'];

                            //If something goes wrong any incorrect entries will be discarded
                            if(isset($songURL) && isset($songThumbnailURL) && strlen($songTitle) > 0 && isset($songPlaylistItemsId)) {
                                //Check if the song already exists in the database
                                if(in_array($songURL, $songURLsArray)) {
                                    $refreshReport .= $songTitle . " - ⏸<br />";
                                }
                                else if($songPublic && $this->SongModel->InsertSong($listId, $songURL, $songThumbnailURL, $songTitle, $songPlaylistItemsId)) {
                                    //Attempt to insert the song to the database
                                    $refreshReport .= $songTitle . " - ✔<br />";
                                }
                                else {
                                    //If insertion failed
                                    $refreshReport .= $songURL . " is private - ❌<br />";
                                }
                            }
                        }
                    }

                    //Songs were loaded correctly - Submit a report
                    $refreshReport .= "</pre>";
                    $newReportId = $this->LogModel->SubmitReport(htmlspecialchars($refreshReport));
                    //Create a log
                    $reportSuccessful = $newReportId ? " i dołączono raport." : ", nie udało się zapisać raportu.";
                    $logMessage = "Załadowano nowe nuty na playlistę".$reportSuccessful;
                    $this->LogModel->CreateLog('playlist', $listId, $logMessage, $newReportId);
                    return true;
                }
                else return false;
            }
        }
        else {
            //Could not load the YouTube api
            $data['body']  = 'invalidAction';
            $data['title'] = "Wystąpił Błąd!";
            $data['errorMessage'] = "Nie znaleziono biblioteki Google!";

            $this->load->view( 'templates/main', $data );
        }
    }
    
    /**
     * Opens the new playlist form.
     *
     * @return void
     */
	public function newPlaylist()
	{
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();

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
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();

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
                        'link' => '',
                        'resultMessage' => '',
                        'ListPrivacyStatus' => isset($_POST['playlistVisibilityYT']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistVisibilityYT'])) : ""
                    );

                    $playlistData = array(
                        'ListName' => isset($_POST['playlistName']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistName'])) : "",
                        'ListDesc' => $_POST['playlistDesc'] ?? "",
                        'ListIntegrated' => 1,
                        'ListActive' => isset($_POST['playlistVisibility']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistVisibility'])) : "",
                        'ListOwnerId' => $_SESSION['userId']
                    );

                    //Validate the form
                    if($playlistData['ListName'] != "" && in_array($data['ListPrivacyStatus'], ["public", "unlisted", "private"]) ) {
                        //Update the description new-line characters
                        $playlistData['ListDesc'] = trim(str_replace(["\r\n", "\r"], "\n", $playlistData['ListDesc']));

                        //Define service object for making API requests.
                        $service = new Google_Service_YouTube($client);

                        //Define the $playlist object, which will be uploaded as the request body.
                        $playlist = new Google_Service_YouTube_Playlist();

                        //Add 'snippet' object to the $playlist object.
                        $playlistSnippet = new Google_Service_YouTube_PlaylistSnippet();
                        $playlistSnippet->setDefaultLanguage('en');
                        $playlistSnippet->setDescription($playlistData['ListDesc']);
                        $playlistSnippet->setTitle($playlistData['ListName']);
                        $playlist->setSnippet($playlistSnippet);

                        //Add 'status' object to the $playlist object.
                        $playlistStatus = new Google_Service_YouTube_PlaylistStatus();
                        $playlistStatus->setPrivacyStatus($data['ListPrivacyStatus']);
                        $playlist->setStatus($playlistStatus);

                        //Save the api call response
                        $response = $service->playlists->insert('snippet, status', $playlist);

                        //Get the unique id of a playlist from the response
                        $playlistData['ListUrl'] = $response->id;

                        //Save the playlist into the database
                        $this->PlaylistModel->InsertPlaylist($playlistData);

                        //Fetch the local id of the newly created playlist
                        $listId = $this->PlaylistModel->GetListIdByUrl($playlistData['ListUrl']);

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
                $queryData['ListOwnerId'] = $_SESSION['userId'];

                //Obtain the unique playlist ID from the url given
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
                    //Insert the playlist to the local db
                    $newListId = $this->PlaylistModel->InsertPlaylist($queryData);
                    $data['resultMessage'] = "Pomyślnie dodano playlistę!";

                    //Refresh the playlist so to fetch the songs available on YT
                    $refreshReturnCode = $this->RefreshPlaylist($newListId);

                    //Create a log
                    $this->LogModel->CreateLog('playlist', $newListId, "Stworzono lokalną playlistę");
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
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();

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
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if($userAuthenticated) {
            $data = [];
            $data['body']  = 'song/delSong';
            $data['title'] = "Uber Rapsy | Usuń piosenkę";
            $data['SongId'] = isset($_GET['id']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['id'])) : 0;
            $data['DeleteSong'] = isset($_GET['delete']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['delete'])) : false;

            if($data['SongId'] && is_numeric($data['SongId'])) {
                $data['song'] = $this->SongModel->GetSongById($data['SongId']);

                if($data['song'] === false) {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono piosenki o podanym numerze id!";
                }
                else if($data['DeleteSong'] === "true") {
                    $this->LogModel->CreateLog('song', $data['SongId'], "Permanentnie usunięto nutę z plejki...");
                    $this->LogModel->CreateLog('playlist', $data['song']->ListId, "Permanentnie usunięto nutę ".$data['song']->SongTitle." z plejki.");
                    $this->SongModel->DeleteSong($data['SongId']);
                    redirect('playlistDashboard');
                }
                else
                    $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['song']->ListId);
            }
            else {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id piosenki lub nie podano go wcale!";
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Allows the user to hide a song in a playlist.
     *
     * @return void
     */
    public function updateSongVisibility()
    {
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if($userAuthenticated) {
            $data = [];
            $data['SongId'] = isset($_GET['id']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['id'])) : 0;

            if($data['SongId'] && is_numeric($data['SongId'])) {
                $data['song'] = $this->SongModel->GetSongById($data['SongId']);

                if($data['song'] === false) {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono piosenki o podanym numerze id!";
                }
                else {
                    $currentVisibility = $data['song']->SongVisible;
                    $newVisibility = $currentVisibility == 1 ? 0 : 1;
                    $this->SongModel->UpdateSongVisibility($data['SongId'], $newVisibility);
                    $this->LogModel->CreateLog('song', $data['SongId'], ($newVisibility ? "Upubliczniono" : "Ukryto") . " nutę na playliście");
                    redirect('playlistDashboard');
                }
            }
            else {
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
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();

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
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
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

    /**
     * Opens the user's playlist dashboard
     *
     * @return void
     */
    public function myPlaylists()
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        if($userAuthenticated) {
            $data = [];
            $data['body']  = 'playlist/myPlaylists';
            $data['title'] = "Uber Rapsy | Moje playlisty";
            $data['playlists'] = $this->PlaylistModel->FetchUserPlaylists($_SESSION['userId']);
            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

}
