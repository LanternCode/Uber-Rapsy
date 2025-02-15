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
 *
 * @property RefreshPlaylistService $RefreshPlaylistService
 */
class Playlist extends CI_Controller {

	public function __construct()
	{
        parent::__construct();
        $this->load->model('PlaylistModel');
		$this->load->model('SongModel');
		$this->load->model('AccountModel');
		$this->load->library('RefreshPlaylistService');
        $this->RefreshPlaylistService = new RefreshPlaylistService();
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
        $listId = isset($_GET['listId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['listId'])) : 0;
        $listId = is_numeric($listId) ? $listId : 0;
        if ($listId) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->GetListOwnerById($listId) == $_SESSION['userId'];
            if ($userAuthenticated && $userAuthorised) {
                $data = [];
                $data['body']  = 'playlist/details';
                $data['title'] = "Uber Rapsy | Zarządzaj playlistą!";
                $data['songs'] = $this->SongModel->GetAllSongsFromList($listId);
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($listId);
                $data['isReviewer'] = $this->SecurityModel->authenticateReviewer();
                $data['playlistOwnerUsername'] = $this->AccountModel->FetchUsernameById($data['playlist']->ListOwnerId);
                $data['redirectSource'] = isset($_GET['src']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['src'])) : "";

                $this->load->view('templates/main', $data);
            }
            else redirect('logout');
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
        $data = [];
        $data['ListId'] = isset($_GET['listId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['listId'])) : 0;
        $data['ListId'] = is_numeric($data['ListId']) ? $data['ListId'] : 0;
        if ($data['ListId']) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->GetListOwnerById($data['ListId']) == $_SESSION['userId'];
            if ($userAuthorised) {
                $data['body']  = 'playlist/edit';
                $data['title'] = "Uber Rapsy | Edytuj playlistę!";
                $data['redirectSource'] = isset($_GET['src']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['src'])) : "";

                //Process the edit playlist form if it was submitted
                if(isset($_POST['playlistFormSubmitted'])) {
                    $queryData = [];
                    $queryData['ListId'] = $data['ListId'];
                    $queryData['ListOwnerId'] = $_SESSION['userId'];
                    $queryData['ListUrl'] = isset($_POST['playlistId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistId'])) : "";
                    $queryData['ListName'] = isset($_POST['playlistName']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistName'])) : "";
                    $queryData['ListDesc'] = isset($_POST['playlistDesc']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistDesc'])) : "";
                    $queryData['ListCreatedAt'] = isset($_POST['playlistDate']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistDate'])) : "";
                    $queryData['ListPublic'] = isset($_POST['playlistVisibility']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistVisibility'])) : "";
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

                    if($queryData['ListName'] && $queryData['ListDesc'] && $queryData['ListCreatedAt'] && $queryData['ListPublic'] != "") {
                        $this->PlaylistModel->UpdatePlaylist($queryData);
                        $data['resultMessage'] = "Pomyślnie zaktualizowano playlistę!";
                    }
                    else {
                        $data['resultMessage'] = "";
                        $data['resultMessage'] .= $queryData['ListName'] == "" ? "Nazwa Playlisty jest wymagana!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListDesc'] == "" ? "Opis Playlisty jest wymagany!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListCreatedAt'] == "" ? "Data Stworzenia Playlisty jest wymagana!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListPublic'] == "" ? "Status Widoczności Playlisty jest wymagany!</br>" : '';
                    }
                }
                //Fetch the (possibly updated) playlist settings
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['ListId']);
            }
            else redirect('logout');
        }
        else redirect('logout');

        $this->load->view( 'templates/main', $data );
    }

    /**
     * Allows the user to set a playlist as (in)active.
     * Inactive playlist will not show up in search results, and
     *  will be moved to the 'archived' section of the playlist dashboard
     *
     * @return void
     */
    public function switchPlaylistPublicStatus(): void
    {
        $playlistId = isset($_GET['playlistId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['playlistId'])) : 0;
        $playlistId = is_numeric($playlistId) ? $playlistId : 0;
        if ($playlistId) {
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->GetListOwnerById($playlistId) == $_SESSION['userId'];
            if($userAuthorised) {
                $data = [];
                $data['body']  = 'playlist/hidePlaylist';
                $data['title'] = "Uber Rapsy | Ukryj playlistę";
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($playlistId);
                $data['redirectSource'] = isset($_GET['src']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['src'])) : "";

                //If the user pressed yes, reverse the current ListPublic status (to hide or show the playlist)
                $hidePlaylist = isset($_GET['switch']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['switch'])) : false;
                if($hidePlaylist === "true") {
                    //Fetch the playlist to show it to the user after making changes
                    $this->PlaylistModel->SetPlaylistPublicProperty($data['playlist']->ListPublic, $playlistId);
                    $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($playlistId);
                }

                $this->load->view('templates/main', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
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
                'title' => 'Uber Rapsy | Dodaj nową playlistę!',
                'redirectSource' => isset($_GET['src']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['src'])) : ""
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
                        'redirectSource' => isset($_GET['src']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['src'])) : "",
                        'ListPrivacyStatus' => isset($_POST['playlistVisibilityYT']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistVisibilityYT'])) : ""
                    );

                    $playlistData = array(
                        'ListName' => isset($_POST['playlistName']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistName'])) : "",
                        'ListDesc' => $_POST['playlistDesc'] ?? "",
                        'ListIntegrated' => 1,
                        'ListPublic' => isset($_POST['playlistVisibility']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistVisibility'])) : "",
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

        $this->load->view('templates/main', $data);
    }

    /**
     * Shows and validates the form to add a local playlist.
     *
     * @return void
     */
    public function addLocal()
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        if($userAuthenticated) {
            $data = [];
            $data['body']  = 'playlist/addLocalPlaylist';
            $data['title'] = "Uber Rapsy | Dodaj lokalnie playlistę!";
            $data['redirectSource'] = isset($_GET['src']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['src'])) : "";

            if(isset($_POST['playlistFormSubmitted'])) {
                $queryData = [];
                $queryData['ListUrl'] = isset($_POST['playlistId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistId'])) : "";
                $queryData['ListName'] = isset($_POST['playlistName']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistName'])) : "";
                $queryData['ListDesc'] = isset($_POST['playlistDesc']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistDesc'])) : "";
                $queryData['ListCreatedAt'] = isset($_POST['playlistDate']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistDate'])) : "";
                $queryData['ListPublic'] = isset($_POST['playlistVisibility']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['playlistVisibility'])) : "";
                $queryData['ListOwnerId'] = $_SESSION['userId'];

                //Obtain the unique playlist ID from the url given
                $listPos = strpos($queryData['ListUrl'], "list=");
                if($listPos > 0) {
                    $indexPos = strpos($queryData['ListUrl'], "&index=");
                    $indexLength = strlen(substr($queryData['ListUrl'], $indexPos));
                    if($indexPos > 0) $queryData['ListUrl'] = substr($queryData['ListUrl'], $listPos+5, -$indexLength);
                    else $queryData['ListUrl'] = substr($queryData['ListUrl'], $listPos+5);
                }

                if($queryData['ListName'] && $queryData['ListDesc'] && $queryData['ListCreatedAt'] && $queryData['ListPublic'] != "") {
                    //Insert the playlist to the local db
                    $newListId = $this->PlaylistModel->InsertPlaylist($queryData);
                    $data['resultMessage'] = "Pomyślnie dodano playlistę!";

                    //If a YT URL was provided, fetch the songs and refresh the playlist
                    if(!empty($queryData['ListUrl'])) {
                        //Refresh the playlist - if everything went well, the message will be empty
                        $data['displayErrorMessage'] = $this->RefreshPlaylistService->refreshPlaylist($newListId);
                    }

                    //Create a log
                    $this->LogModel->CreateLog('playlist', $newListId, "Stworzono lokalną playlistę");
                }
                else {
                    $data['resultMessage'] = "";
                    $data['resultMessage'] .= $queryData['ListName'] == "" ? "Nazwa Playlisty jest wymagana!</br>" : '';
                    $data['resultMessage'] .= $queryData['ListDesc'] == "" ? "Opis Playlisty jest wymagany!</br>" : '';
                    $data['resultMessage'] .= $queryData['ListCreatedAt'] == "" ? "Data Stworzenia Playlisty jest wymagana!</br>" : '';
                    $data['resultMessage'] .= $queryData['ListPublic'] == "" ? "Status Playlisty jest wymagany!</br>" : '';
                }
            }

            $this->load->view('templates/main', $data);
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
        $playlistId = isset($_GET['playlistId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['playlistId'])) : 0;
        $playlistId = is_numeric($playlistId) ? $playlistId : 0;
        if ($playlistId) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->GetListOwnerById($playlistId) == $_SESSION['userId'];
            if ($userAuthorised) {
                $data = [];
                $data['body']  = 'playlist/deleteLocal';
                $data['title'] = "Uber Rapsy | Usuń playlistę";
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($playlistId);
                $data['redirectSource'] = isset($_GET['src']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['src'])) : "";

                //Delete the local playlist if selected by the user
                $deleteLocal = isset($_GET['del']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['del'])) : false;
                if($deleteLocal === "true") {
                    $this->PlaylistModel->DeleteLocalPlaylist($playlistId);
                    redirect('myPlaylists');
                }

                $this->load->view('templates/main', $data);
            }
            else redirect('logout');
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
        //Validate the submitted song id
        $data = [];
        $songId = isset($_GET['songId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['songId'])) : 0;
        $data['song'] = is_numeric($songId) ? $this->SongModel->GetSongById($songId) : false;
        if ($data['song'] !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->GetListOwnerById($data['song']->ListId) == $_SESSION['userId'];
            if ($userAuthenticated && $userAuthorised) {
                $data['body']  = 'song/delSong';
                $data['title'] = "Uber Rapsy | Usuń piosenkę z playlisty";
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($data['song']->ListId);
                $data['redirectSource'] = isset($_GET['src']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['src'])) : 0;

                //Delete the song if the form was submitted
                $delSong = isset($_GET['delete']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['delete'])) : false;
                if($delSong) {
                    $this->LogModel->CreateLog('song', $songId, "Permanentnie usunięto nutę z plejki...");
                    $this->LogModel->CreateLog('playlist', $data['song']->ListId, "Permanentnie usunięto nutę ".$data['song']->SongTitle." z plejki.");
                    $this->SongModel->DeleteSong($songId);
                    if ($redirectSource == 'pd')
                        redirect('playlist/details?listId='.$data['song']->ListId.'&src=pd');
                    else redirect('playlist/details?listId='.$data['song']->ListId.'&src=mp');
                }

                $this->load->view( 'templates/main', $data );
            }
            else redirect('logout');
        }
        else redirect('logout');
    }

    /**
     * Allows the user to switch the integration status of a playlist
     * An integrated playlist reflects changes made to it between platforms
     *
     * @return void
     */
    public function integrate()
    {
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if ($userAuthenticated) {
            $data = [];
            $data['body']  = 'playlist/integrate';
            $data['title'] = "Uber Rapsy | Zintegruj playlistę";
            $data['redirectSource'] = isset($_GET['src']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['src'])) : "";
            $playlistId = isset($_GET['playlistId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['playlistId'])) : 0;
            $playlistId = is_numeric($playlistId) ? $playlistId : false;
            $status = isset($_GET['status']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['status'])) : 0;

            //Validate the provided playlist id
            if ($playlistId) {
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($playlistId);
                if ($data['playlist'] !== false) {
                    //Integrate if the form was submitted, otherwise open the form
                    if ($status == "confirm") {
                        $updatedIntegrationStatus = $data['playlist']->ListIntegrated ? "0" : "1";
                        $updatedLink = isset($_POST['nlink']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['nlink'])) : 0;

                        //Check if a valid link exists in the db or was entered when integrating the playlist with YT
                        $linkValid = !$updatedIntegrationStatus || (strlen($data['playlist']->ListUrl) > 10 || strlen($updatedLink) > 10);
                        if ($linkValid) {
                            $data['playlistUpdatedMessage'] = "<h2>Playlista została zaktualizowana!</h2>";
                            $data['playlistUpdatedStatus'] = true;
                            $this->PlaylistModel->UpdatePlaylistIntegrationStatus($playlistId, $updatedIntegrationStatus, $updatedLink);
                            $this->LogModel->CreateLog('playlist', $playlistId,
                                $updatedIntegrationStatus ? "Playlista została zintegrowana z YT" : "Usunięto integrację playlisty z YT");
                        }
                        else {
                            //This local playlist does not have a link required to integrate it with an existing YT playlist
                            $data['playlistUpdatedMessage'] = "<h2>Zintegrowana plalista musi posiadać swój link na YouTube!</h2>";
                            $data['playlistUpdatedStatus'] = false;
                        }
                    }

                    $this->load->view('templates/main', $data);
                }
                else redirect('logout');
            }
            else redirect('logout');
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
        $playlistId = isset($_GET['playlistId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['playlistId'])) : 0;
        $playlistId = is_numeric($playlistId) ? $playlistId : 0;
        if ($playlistId) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->GetListOwnerById($playlistId) == $_SESSION['userId'];
            if ($userAuthorised) {
                $data = [];
                $data['body']  = 'playlist/showLog';
                $data['title'] = "Uber Rapsy | Historia playlisty";
                $data['redirectSource'] = isset($_GET['src']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['src'])) : "";
                $data['playlist'] = $this->PlaylistModel->FetchPlaylistById($playlistId);
                $data['playlistLog'] = $this->LogModel->GetPlaylistLog($playlistId);

                $this->load->view( 'templates/main', $data );
            }
            else redirect('logout');
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
            $this->load->view('templates/main', $data);
        }
        else redirect('logout');
    }

}
