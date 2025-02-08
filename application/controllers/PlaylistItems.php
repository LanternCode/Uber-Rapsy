<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!isset($_SESSION)) {
    session_start();
}

/**
 * Controller responsible for handling items inside a playlist.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 *
 * @property PlaylistModel $PlaylistModel
 * @property SongModel $SongModel
 * @property UtilityModel $UtilityModel
 * @property LogModel $LogModel
 * @property SecurityModel $SecurityModel
 * @property RefreshPlaylistService $RefreshPlaylistService
 * @property CI_DB_mysqli_driver $db
 */
class PlaylistItems extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('PlaylistModel');
        $this->load->model('SongModel');
        $this->load->model('UtilityModel');
        $this->load->model('LogModel');
        $this->load->model('SecurityModel');
        $this->load->library('RefreshPlaylistService');
        $this->RefreshPlaylistService = new RefreshPlaylistService();
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
    public function loadPlaylist()
    {
        $data = [];
        $data['listId'] = isset($_GET['listId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['listId'])) : 0;
        $data['listId'] = is_numeric($data['listId']) ? $data['listId'] : 0;
        if ($data['listId']) {
            $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['listId']);
            $data['owner'] = isset($_SESSION['userId']) && $this->PlaylistModel->GetListOwnerById($data['listId']) == $_SESSION['userId'];
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $data['owner'];
            $userAuthorised = $userAuthorised || $data['playlist']->ListActive;
            if ($userAuthorised) {
                $data['title'] = $data['playlist']->ListName." | Playlista Uber Rapsy";
                $data['searchQuery'] = isset($_GET['SearchQuery']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['SearchQuery'])) : "";
                $data['reviewer'] = isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer";
                $data['allPlaylists'] = $this->PlaylistModel->GetListsIdsAndNames();
                $data['body'] = 'playlist/insidePlaylist/playlist';
                $data['songs'] = [];

                //Define various checkbox-related properties in one place - first the db name, then the button name, then the display name
                $data['checkboxPropertiesDetails'] = [
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

                //Apply a filter if one was selected by the user (proceed to the default case if not)
                $filter = isset($_GET['filter']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['filter'])) : "";
                switch ($filter) {
                    case "Checkbox": {
                        //A 'checkbox selected' filter is in use
                        $checkboxProperty = isset($_GET['prop']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['prop'])) : "none";
                        foreach($data['checkboxPropertiesDetails'] as $checkboxProperties) {
                            if (in_array($checkboxProperty, $checkboxProperties)) {
                                $data['songs'] = $this->PlaylistModel->FilterSongsByCheckboxProperty($data['listId'], $checkboxProperty);
                                break;
                            }
                        }
                        break;
                    }
                    case "Unrated": {
                        //The 'unrated' filter is in use
                        $data['songs'] = $this->SongModel->FilterUnrated($data['listId']);
                        break;
                    }
                    default: {
                        //Fetch songs and filter by the search query if one was used
                        $data['songs'] = $this->SongModel->GetSongsFromList($data['listId'], $data['searchQuery']);
                        break;
                    }
                }

                //Initialise the variables to store the average grades
                $avgOverall = 0;
                $avgAdam = 0;
                $avgChurchie = 0;
                $ratedCount = 0;
                $ratedAdam = 0;
                $ratedChurchie = 0;

                //Compute the average grades song-by-song
                foreach($data['songs'] as $song) {
                    //Display values without decimals at the end if the decimals are only 0
                    if(is_numeric($song->SongGradeAdam)) $song->SongGradeAdam = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeAdam);
                    if(is_numeric($song->SongGradeChurchie)) $song->SongGradeChurchie = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeChurchie);
                    if(is_numeric($song->SongGradeOwner)) $song->SongGradeOwner = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeOwner);

                    //Do not include songs graded "10" by both reviewers in the average
                    if(!($song->SongDuoTen && $song->SongGradeAdam == 10 && $song->SongGradeChurchie == 10)) {
                        $avgOverall += ($song->SongGradeAdam + $song->SongGradeChurchie) / 2;
                        $avgAdam += ($song->SongDuoTen && $song->SongGradeAdam == 10) ? 0 : $song->SongGradeAdam;
                        $avgChurchie += ($song->SongDuoTen && $song->SongGradeChurchie == 10) ? 0 : $song->SongGradeChurchie;
                        $ratedCount += $song->SongGradeAdam > 0 || $song->SongGradeChurchie > 0 ? 1 : 0;
                        $ratedAdam += $song->SongGradeAdam > 0 && !($song->SongDuoTen && $song->SongGradeAdam == 10) ? 1 : 0;
                        $ratedChurchie += $song->SongGradeChurchie > 0 && !($song->SongDuoTen && $song->SongGradeChurchie == 10) ? 1 : 0;
                    }
                }

                //Calculate playlist averages for each reviewer
                $data['avgOverall'] = $ratedCount > 0 ? $avgOverall/$ratedCount : 0;
                $data['avgAdam'] = $ratedAdam > 0 ? $avgAdam/$ratedAdam : 0;
                $data['avgChurchie'] = $ratedChurchie > 0 ? $avgChurchie/$ratedChurchie : 0;
                $data['ratedOverall'] = $ratedCount;
                $data['ratedAdam'] = $ratedAdam;
                $data['ratedChurchie'] = $ratedChurchie;
            }
            else redirect('logout');
        }
        else redirect('logout');

        $this->load->view('templates/customNav', $data);
    }

    /**
     * This method handles global search queries
     * Global search only works on the homepage
     * Global search looks into public playlists and your playlists
     *
     * @return void
     */
    public function globalSearch(): void
    {
        $data = [];
        $data['searchQuery'] = isset($_GET['SearchQuery']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['SearchQuery'])) : 0;
        $data['body'] = 'playlist/insidePlaylist/searchResults';
        $data['title'] = "Wyniki Wyszukiwania | Uber Rapsy";

        //Handle global search
        if (strlen($data['searchQuery']) > 0) {
            $data['playlist'] = [];
            $data['songs'] = $this->SongModel->GetSongsFromSearch($data['searchQuery']);
            if (count($data['songs']) > 0 && count($data['songs']) < 301) {
                $data['lists'] = $this->PlaylistModel->GetListsIdsAndNames();
                $data['reviewer'] = isset($_SESSION['userRole']) && $_SESSION['userRole'] === "reviewer";
                foreach ($data['songs'] as $song) {
                    //Display values without decimals at the end if the decimals are only 0's (ex. 5.50 -> 5.5)
                    if(is_numeric($song->SongGradeAdam)) $song->SongGradeAdam = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeAdam);
                    if(is_numeric($song->SongGradeChurchie)) $song->SongGradeChurchie = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeChurchie);
                    if(is_numeric($song->SongGradeOwner)) $song->SongGradeOwner = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeOwner);

                    //Get song button information
                    $data['playlist'][] = $this->PlaylistModel->FetchPlaylistById($song->ListId);
                }
            }
        }

        $this->load->view('templates/customNav', $data);
    }

    /**
     * This method handles tierlists
     * Tierlists can be accessed from playlists
     * Tierlists can be generated based on the reviewers' scores, your scores, or average scores
     *
     * @return void
     */
    public function tierlist(): void
    {
        $data = [];
        $data['listId'] = isset($_GET['listId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['listId'])) : 0;
        $data['listId'] = is_numeric($data['listId']) ? $data['listId'] : 0;
        if ($data['listId']) {
            //Fetch the playlist and set the page name
            $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['listId']);
            $data['owner'] = isset($_SESSION['userId']) && $this->PlaylistModel->GetListOwnerById($data['listId']) == $_SESSION['userId'];

            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $data['owner'];
            $userAuthorised = $userAuthorised || $data['playlist']->ListActive;
            if ($userAuthorised) {
                $data['title'] = $data['playlist']->ListName . " | Playlista Uber Rapsy";
                $data['reviewer'] = isset($_SESSION['userRole']) && $_SESSION['userRole'] === "reviewer";
                $data['body'] = 'playlist/insidePlaylist/tierlist';
                $data['gradesToDisplay'] = [];

                //Define the tierlist owner
                $data['filter'] = isset($_GET['filter']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['filter'])) : "none";
                $propName = $data['filter'] === "Adam" ? "SongGradeAdam" : ($data['filter'] === "Churchie" ? "SongGradeChurchie" : ($data['filter'] === "Owner" ? "SongGradeOwner" : "Average"));

                //Fetch other relevant data to complete the tierlist
                $data['songs'] = $this->SongModel->GetTopSongsFromList($data['listId'], $data['filter']);
                $data['lists'] = $this->PlaylistModel->GetListsIdsAndNames();

                //Construct tierlist tiers
                foreach ($data['songs'] as $song) {
                    //Display values without decimals at the end if the decimals are only 0
                    if(is_numeric($song->SongGradeAdam)) $song->SongGradeAdam = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeAdam);
                    if(is_numeric($song->SongGradeChurchie)) $song->SongGradeChurchie = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeChurchie);
                    if(is_numeric($song->SongGradeOwner)) $song->SongGradeOwner = $this->UtilityModel->TrimTrailingZeroes($song->SongGradeOwner);
                    $grade = $propName !== "Average" ? $song->$propName : bcdiv(($song->SongGradeAdam+$song->SongGradeChurchie+$song->SongGradeOwner)/3, 1, 2);
                    if(!in_array($grade, $data['gradesToDisplay']))
                        $data['gradesToDisplay'][] = $grade;
                }

                //Sort the tierlist tiers from highest to lowest
                rsort($data['gradesToDisplay']);
            }
            else redirect('logout');
        }
        else redirect('logout');

        $this->load->view('templates/customNav', $data);
    }

    /**
     * This method updates song ratings inside a playlist
     * Song ratings include: reviewer grades, personal grades, checkbox checks, and review notes
     * If 'transfer song' or 'copy song to another playlist' were queued, they will be
     *  processed here
     *
     * This method is called when the user clicks the 'Save Grades' button
     * By default this worked inside a playlist, and was then extended to work with Global Search
     *
     * Since each song, of which there may be hundreds, has almost 30 individual features
     *  the script checks if any updates were made before processing the features
     * This is done through JS on the client-side updating a hidden field
     *
     * @return void
     */
    public function updateSongRatingsInPlaylist(): void
    {
        $playlistId = isset($_POST['playlistId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistId'])) : 0;
        $playlistId = is_numeric($playlistId) || $playlistId == "search" ? $playlistId : 0;
        if ($playlistId) {
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->GetListOwnerById($playlistId) == $_SESSION['userId'];
            $reviewerAuthenticated = $this->SecurityModel->authenticateReviewer();
            $reviewerAuthorised = $reviewerAuthenticated && true; //Later replace with a dedicated property
            if ($userAuthorised || $reviewerAuthorised) {
                $data = [];
                $data['body']  = 'update';
                $data['title'] = "Oceny Zapisane!";
                $resultMessage = "<pre>";

                //Fetch the playlist to access its settings
                $playlist = false;
                if($playlistId !== "search") {
                    $playlist = $this->PlaylistModel->FetchPlaylistById($playlistId);
                    $data['playlistId'] = $playlist->ListId;
                }

                //Process each song separately
                $i = 0;
                $data['processedSongsCount'] = 0;
                $data['processedAndUpdatedSongsCount'] = 0;
                while (isset($_POST["songUpdated-" . $i+21])) {
                    $i += ($data['processedSongsCount'] == 0) ? 0 : 28;
                    $data['processedSongsCount'] += 1;
                    //Only process songs that were actually updated
                    $songUpdated = isset($_POST["songUpdated-" . $i+21]) && $_POST["songUpdated-".$i+21];
                    if ($songUpdated) {
                        $data['processedAndUpdatedSongsCount'] += 1;
                        //Create a variable for the song's update message
                        $localResultMessage = "";

                        //Fetch the song-to-update to compare the submission
                        $formInput['songId'] = $_POST["songId-" . $i];
                        $currentSong = $this->SongModel->GetSongById($formInput['songId']);

                        //Save the form data to a temp variable for easy db update - grades
                        $formInput['SongGradeAdam'] = $_POST["nwGradeA-" . $i + 1] ?? $currentSong->SongGradeAdam;
                        $formInput['SongGradeChurchie'] = $_POST["nwGradeC-" . $i + 2] ?? $currentSong->SongGradeChurchie;
                        $formInput['SongGradeOwner'] = $_POST["nwGradeM-" . $i + 27] ?? $currentSong->SongGradeOwner;

                        //Buttons (checkboxes)
                        $formInput['SongRehearsal'] = $_POST["songRehearsal-" . $i + 4] ?? $currentSong->SongRehearsal;
                        $formInput['SongDistinction'] = $_POST["songDistinction-" . $i + 5] ?? $currentSong->SongDistinction;
                        $formInput['SongMemorial'] = $_POST["songMemorial-" . $i + 6] ?? $currentSong->SongMemorial;
                        $formInput['SongXD'] = $_POST["songXD-" . $i + 7] ?? $currentSong->SongXD;
                        $formInput['SongNotRap'] = $_POST["songNotRap-" . $i + 8] ?? $currentSong->SongNotRap;
                        $formInput['SongDiscomfort'] = $_POST["songDiscomfort-" . $i + 9] ?? $currentSong->SongDiscomfort;
                        $formInput['SongDepA'] = $_POST["songDepA-" . $i + 26] ?? $currentSong->SongDepA;
                        $formInput['SongTop'] = $_POST["songTop-" . $i + 10] ?? $currentSong->SongTop;
                        $formInput['SongNoGrade'] = $_POST["songNoGrade-" . $i + 11] ?? $currentSong->SongNoGrade;
                        $formInput['SongUber'] = $_POST["songUber-" . $i + 12] ?? $currentSong->SongUber;
                        $formInput['SongBelow'] = $_POST["songBelow-" . $i + 13] ?? $currentSong->SongBelow;
                        $formInput['SongBelTen'] = $_POST["songBelTen-" . $i + 14] ?? $currentSong->SongBelTen;
                        $formInput['SongBelNine'] = $_POST["songBelNine-" . $i + 15] ?? $currentSong->SongBelNine;
                        $formInput['SongBelEight'] = $_POST["songBelEight-" . $i + 16] ?? $currentSong->SongBelEight;
                        $formInput['SongBelFour'] = $_POST["songBelFour-" . $i + 17] ?? $currentSong->SongBelFour;
                        $formInput['SongDuoTen'] = $_POST["songDuoTen-" . $i + 18] ?? $currentSong->SongDuoTen;
                        $formInput['SongVeto'] = $_POST["songVeto-" . $i + 19] ?? $currentSong->SongVeto;
                        $formInput['SongBelHalfSeven'] = $_POST["SongBelHalfSeven-" . $i + 23] ?? $currentSong->SongBelHalfSeven;
                        $formInput['SongBelHalfEight'] = $_POST["SongBelHalfEight-" . $i + 24] ?? $currentSong->SongBelHalfEight;
                        $formInput['SongBelHalfNine'] = $_POST["SongBelHalfNine-" . $i + 25] ?? $currentSong->SongBelHalfNine;

                        //Move and copy song select boxes
                        $formInput['newPlaylistId'] = $_POST["nwPlistId-" . $i + 3];
                        $formInput['copyToPlaylist'] = $_POST["copyPlistId-" . $i + 20];

                        //Song comment textarea
                        $formInput['SongComment'] = $_POST["songComment-" . $i + 22] ?? $currentSong->SongComment;

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
                        $ownerGradeUpdated = in_array("SongGradeOwner", $elementsToUpdate);
                        $commentUpdated = in_array("SongComment", $elementsToUpdate);

                        if ($adamGradeUpdated || $churchieGradeUpdated || $ownerGradeUpdated) {
                            $newAdamRating = str_replace(',', '.', $formInput['SongGradeAdam']);
                            $newChurchieRating = str_replace(',', '.', $formInput['SongGradeChurchie']);
                            $newOwnerRating = str_replace(',', '.', $formInput['SongGradeOwner']);

                            //Ensure the ratings are valid decimal numbers (full or .5), are in the range "1-15", and are separated with dots and not commas
                            if (strlen($newAdamRating) > 0 && strlen($newChurchieRating) > 0 && strlen($newOwnerRating) > 0
                                && is_numeric($newAdamRating) && is_numeric($newChurchieRating) && is_numeric($newOwnerRating)
                                && $this->UtilityModel->InRange($newAdamRating, 0, 15) && $this->UtilityModel->InRange($newChurchieRating, 0, 15) && $this->UtilityModel->InRange($newOwnerRating, 0, 15)
                                && fmod($newAdamRating, 0.5) == 0 && fmod($newChurchieRating, 0.5) == 0 && fmod($newOwnerRating, 0.5) == 0) {
                                $scoresSaved = $this->SongModel->UpdateSongScores($currentSong->SongId, $newAdamRating, $newChurchieRating, $newOwnerRating);
                                if (!$scoresSaved) {
                                    //Fatal Error - if grades were not saved, note this and continue to the next song.
                                    $resultMessage .= "<br><br>\tNie udało się zapisać ocen dla utworu ".$currentSong->SongTitle."<br><br>";
                                    continue;
                                }

                                if ($adamGradeUpdated)
                                    $localResultMessage .= "\tOcena Adama: " . $currentSong->SongGradeAdam . " -> " . $newAdamRating . "<br>";
                                if ($churchieGradeUpdated)
                                    $localResultMessage .= "\tOcena Kościelnego: " . $currentSong->SongGradeChurchie . " -> " . $newChurchieRating . "<br>";
                                if ($ownerGradeUpdated)
                                    $localResultMessage .= "\tOcena Właściciela: " . $currentSong->SongGradeOwner . " -> " . $newOwnerRating;

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
                            if (!in_array($prop, ["SongGradeAdam", "SongGradeChurchie", "SongGradeOwner", "SongComment"])) {
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
                            $this->LogModel->CreateLog('song', $currentSong->SongId, "Zapisano oceny nuty z " . ($playlistId === "search" ? "wyszukiwarki" : "playlisty"));

                        //Copy the song to a different playlist and add a log explaining where the song came from
                        $newSongId = 0;
                        if ($formInput['copyToPlaylist']) {
                            $copySuccessful = $this->SongModel->CopySongToPlaylist($currentSong->SongId, $formInput['copyToPlaylist']);
                            if($copySuccessful) {
                                $newSongId = $this->SongModel->GetSongIdByNameAndPlaylist($currentSong->SongTitle, $formInput['copyToPlaylist']);
                                $sourceName = $playlistId === "search" ? "wyszukiwarki" : "playlisty " . $playlist->ListName;
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
                        $moveRequired = $formInput['newPlaylistId'] != $playlistId && $formInput['newPlaylistId'] != 0;
                        $copyRequired = $formInput['copyToPlaylist'] != $playlistId && $formInput['copyToPlaylist'] != 0;
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
                                        $oldPlaylistDetails = $playlistId !== "search" ? $playlist : $this->PlaylistModel->FetchPlaylistById($currentSong->ListId);
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
                $where = $playlistId === "search" ? "z wyszukiwarki" : "z playlisty";
                $reportSuccessful = $newReportId ? " i dołączono raport." : ", nie udało się zapisać raportu.";
                $logMessage = "Zapisano oceny ".$where.$reportSuccessful;
                if(is_numeric($playlistId))
                    $this->LogModel->CreateLog('playlist', $playlistId, $logMessage, $newReportId);

                $this->load->view('templates/main', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }

    /**
     * Updates the playlist with new songs added to it on YouTube.
     *
     * @return void
     */
    public function downloadSongs()
    {
        $listId = isset($_GET['ListId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['ListId'])) : 0;
        $listId = is_numeric($listId) ? $listId : 0;
        $data = array(
            'body' => 'downloadSongs',
            'title' => 'Aktualizacja listy!',
            'ListId' => $listId,
            'refreshSuccess' => true
        );

        //Check if the user is logged in and has the required permissions
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        $userAuthorised = $userAuthenticated && $this->PlaylistModel->GetListOwnerById($data['ListId']) == $_SESSION['userId'];
        if($userAuthorised) {
            //Check if the playlist has a YouTube link.
            $playlistUrl = $this->PlaylistModel->GetListUrlById($data['ListId']);
            if(!empty($playlistUrl)) {
                //Refresh the playlist and see if any errors occurred
                $refreshReturnCode = $this->RefreshPlaylistService->RefreshPlaylist($data['ListId']);
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
                //TODO: NIE POKAZYWAĆ OPCJI BEZ LINKU
                $data['body']  = 'invalidAction';
                $data['title'] = "Nie znaleziono linku do playlisty na YT!";
                $data['errorMessage'] = "Nie znaleziono linku do playlisty na YT.";
            }
        }
        else redirect('logout');

        $this->load->view('templates/main', $data);
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
        $flags['SongGradeOwner'] = $currentSong->SongGradeOwner != $formInput['SongGradeOwner'];
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
}