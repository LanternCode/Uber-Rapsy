<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!isset($_SESSION))
	session_start();

/**
 * Controller responsible for handling views related with playlists.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 *
 * @property PlaylistModel $PlaylistModel
 * @property PlaylistSongModel $PlaylistSongModel
 * @property UtilityModel $UtilityModel
 * @property LogModel $LogModel
 * @property SecurityModel $SecurityModel
 * @property AccountModel $AccountModel
 * @property CI_Input $input
 * @property CI_DB_mysqli_driver $db
 * @property RefreshPlaylistService $RefreshPlaylistService
 * @property HTMLSanitiser $htmlsanitiser
 */
class Playlist extends CI_Controller
{
	public function __construct()
	{
        parent::__construct();
        $this->load->model('PlaylistModel');
		$this->load->model('PlaylistSongModel');
		$this->load->model('AccountModel');
		$this->load->library('RefreshPlaylistService');
        $this->load->library('htmlsanitiser');
        $this->RefreshPlaylistService = new RefreshPlaylistService();
    }

    /**
     * Opens the administrator playlist dashboard.
     *
     * @return void
     */
    public function playlistDashboard(): void
    {
        //Ensure a rappar staff member is logged in
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if (!$userAuthenticated)
            redirect('errors/403-404');

        $data = array(
            'body' => 'playlist/playlistDashboard',
            'title' => 'Uber Rapsy | Zarządzaj playlistami!',
            'playlists' => $this->PlaylistModel->getAllPlaylists()
        );
        $this->load->view('templates/main', $data);
    }

    /**
     * Opens the user playlist dashboard
     *
     * @return void
     */
    public function myPlaylists(): void
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        if ($userAuthenticated) {
            $data = array(
                'body' => 'playlist/myPlaylists',
                'title' => 'Uber Rapsy | Moje playlisty',
                'playlists' => $this->PlaylistModel->fetchUserPlaylists($_SESSION['userId'])
            );
            $this->load->view('templates/main', $data);
        }
        else redirect('errors/403-404');
    }

    /**
     * Opens the playlist's details page.
     *
     * @return void
     */
    public function playlistDetails(): void
    {
        //Validate the provided playlist id
        $listId = filter_var($this->input->get('playlistId'), FILTER_VALIDATE_INT);
        if (!$listId)
            redirect('errors/403-404');

        //Validate user permissions
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        $userId = $this->SecurityModel->getCurrentUserId();
        $playlistOwnerId = $this->PlaylistModel->getListOwnerById($listId);
        $userAuthorised = $userAuthenticated && $playlistOwnerId == $userId;
        if (!$userAuthorised)
            redirect('errors/403-404');

        //Fetch playlist details
        $data = array(
            'body' => 'playlist/details',
            'title' => 'Uber Rapsy | Zarządzaj playlistą!',
            'songs' => $this->PlaylistSongModel->getPlaylistSongs($listId, "", true),
            'playlist' => $this->PlaylistModel->fetchPlaylistById($listId),
            'isReviewer' => $this->SecurityModel->authenticateReviewer(),
            'redirectSource' => $this->input->get('src'),
            'isRapparManaged' => $playlistOwnerId == 1
        );
        $data['playlistOwnerUsername'] = $this->AccountModel->fetchUsernameById($data['playlist']->ListOwnerId);

        //Display values without decimals at the end if the decimals are zeros
        foreach ($data['songs'] as $song) {
            $song->SongGradeAdam = $this->UtilityModel->trimTrailingZeroes($song->SongGradeAdam);
            $song->SongGradeChurchie = $this->UtilityModel->trimTrailingZeroes($song->SongGradeChurchie);
            $song->SongGradeOwner = $this->UtilityModel->trimTrailingZeroes($song->SongGradeOwner);
        }

        $this->load->view('templates/main', $data);
    }

    /**
     * Opens the 'edit playlist' page and if submitted, processes the editing form.
     *
     * @return void
     */
    public function editPlaylist(): void
    {
        $listId = filter_var($this->input->get('playlistId'), FILTER_VALIDATE_INT);
        if ($listId) {
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->getListOwnerById($listId) == $_SESSION['userId'];
            if ($userAuthorised) {
                $data = array(
                    'body' => 'playlist/edit',
                    'title' => 'Uber Rapsy | Edytuj playlistę!',
                    'redirectSource' => $this->input->get('src')
                );

                //Process the form if it was submitted
                if ($this->input->post('playlistFormSubmitted')) {
                    $queryData = array(
                        'ListId' => $listId,
                        'ListOwnerId' => $_SESSION['userId'],
                        'ListUrl' => $this->input->post('playlistUrl'),
                        'ListName' => $this->input->post('playlistName'),
                        'ListDesc' => $this->input->post('playlistDesc'),
                        'ListCreatedAt' => $this->input->post('playlistDate'),
                        'ListPublic' => $this->input->post('playlistVisibility'),
                        'btnRehearsal' => $this->input->post('btnRehearsal') ?: 0,
                        'btnDistinction' => $this->input->post('btnDistinction') ?: 0,
                        'btnMemorial' => $this->input->post('btnMemorial') ?: 0,
                        'btnXD' => $this->input->post('btnXD') ?: 0,
                        'btnNotRap' => $this->input->post('btnNotRap') ?: 0,
                        'btnDiscomfort' => $this->input->post('btnDiscomfort') ?: 0,
                        'btnTop' => $this->input->post('btnTop') ?: 0,
                        'btnNoGrade' => $this->input->post('btnNoGrade') ?: 0,
                        'btnUber' => $this->input->post('btnUber') ?: 0,
                        'btnBelowSeven' => $this->input->post('btnBelowSeven') ?: 0,
                        'btnBelowTen' => $this->input->post('btnBelowTen') ?: 0,
                        'btnBelowNine' => $this->input->post('btnBelowNine') ?: 0,
                        'btnBelowEight' => $this->input->post('btnBelowEight') ?: 0,
                        'btnBelowFour' => $this->input->post('btnBelowFour') ?: 0,
                        'btnDuoTen' => $this->input->post('btnDuoTen') ?: 0,
                        'btnVeto' => $this->input->post('btnVeto') ?: 0,
                        'btnBelowHalfSeven' => $this->input->post('btnBelowHalfSeven') ?: 0,
                        'btnBelowHalfEight' => $this->input->post('btnBelowHalfEight') ?: 0,
                        'btnBelowHalfNine' => $this->input->post('btnBelowHalfNine') ?: 0,
                    );

                    if ($queryData['ListName'] && $queryData['ListCreatedAt'] && $queryData['ListPublic'] != "") {
                        $queryData['ListDesc'] = $this->htmlsanitiser->purify($queryData['ListDesc']);
                        $this->PlaylistModel->updatePlaylist($queryData);
                        $data['resultMessage'] = "Pomyślnie zaktualizowano playlistę!";
                    }
                    else {
                        $data['resultMessage'] = $queryData['ListName'] == "" ? "Nazwa Playlisty jest wymagana!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListCreatedAt'] == "" ? "Data Stworzenia Playlisty jest wymagana!</br>" : '';
                        $data['resultMessage'] .= $queryData['ListPublic'] == "" ? "Status Widoczności Playlisty jest wymagany!</br>" : '';
                    }
                }

                //Fetch the (possibly, by now updated) playlist settings
                $data['playlist'] = $this->PlaylistModel->fetchPlaylistById($listId);
                $this->load->view('templates/main', $data);
            }
            else redirect('errors/403-404');
        }
        else redirect('errors/403-404');
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
        $playlistId = filter_var($this->input->get('playlistId'), FILTER_VALIDATE_INT);
        if ($playlistId) {
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->getListOwnerById($playlistId) == $_SESSION['userId'];
            if ($userAuthorised) {
                $data = array(
                    'body' => 'playlist/hidePlaylist',
                    'title' => 'Uber Rapsy | Ukryj playlistę',
                    'playlist' => $this->PlaylistModel->fetchPlaylistById($playlistId),
                    'redirectSource' => $this->input->get('src')
                );

                //If the user pressed yes, reverse the current ListPublic status (to hide or show the playlist)
                $hidePlaylist = $this->input->get('switch');
                if ($hidePlaylist) {
                    $this->PlaylistModel->setPlaylistPublicStatus(!$data['playlist']->ListPublic, $playlistId);

                    //Fetch the playlist to show it to the user after making changes
                    $data['playlist'] = $this->PlaylistModel->fetchPlaylistById($playlistId);
                }

                $this->load->view('templates/main', $data);
            }
            else redirect('errors/403-404');
        }
        else redirect('errors/403-404');
    }
    
    /**
     * Opens the new playlist form.
     *
     * @return void
     */
	public function newIntegratedPlaylistForm(): void
	{
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if ($userAuthenticated) {
            $data = array(
                'body' => 'playlist/addPlaylist',
                'title' => 'Uber Rapsy | Dodaj nową playlistę!',
                'redirectSource' => $this->input->get('src')
            );
            $this->load->view('templates/main', $data);
        }
        else redirect('errors/403-404');
	}

    /**
     * Processes the Add Playlist form.
     *
     * @return void
     */
    public function addIntegratedPlaylist(): void
    {
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if ($userAuthenticated) {
            //Include google library
            $client = $this->SecurityModel->initialiseLibrary();
            if ($client !== false) {
                //Validate the access token required for an api call
                $tokenExpired = $this->SecurityModel->validateAuthToken($client);
                if ($tokenExpired) {
                    //Refresh token not found
                    $data = array(
                        'body' => 'invalidAction',
                        'title' => 'Błąd autoryzacji tokenu!',
                        'errorMessage' => "Odświeżenie tokenu autoryzującego nie powiodło się.</br> Nie stworzono playlisty."
                    );
                }
                else {
                    $data = array(
                        'body' => 'playlist/addPlaylist',
                        'link' => '',
                        'redirectSource' => $this->input->get('src'),
                        'ListPrivacyStatus' => $this->input->post('playlistVisibilityYT')
                    );

                    $playlistData = array(
                        'ListName' => $this->input->post('playlistName'),
                        'ListDesc' => $this->input->post('playlistDesc'),
                        'ListPublic' => $this->input->post('playlistVisibility'),
                        'ListOwnerId' => $_SESSION['userId'],
                        'ListIntegrated' => 1,
                    );

                    //Validate the form
                    if ($playlistData['ListName'] && in_array($data['ListPrivacyStatus'], ["public", "unlisted", "private"])) {
                        //Sanitise the description and update the newline character
                        $playlistData['ListDesc'] = $this->htmlsanitiser->purify($playlistData['ListDesc']);
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
                        $this->PlaylistModel->insertPlaylist($playlistData);

                        //Fetch the local id of the newly created playlist
                        $listId = $this->PlaylistModel->getListIdByUrl($playlistData['ListUrl']);

                        //Create a log
                        $this->LogModel->createLog('playlist', $listId, "Stworzono zintegrowaną playlistę");
                        $data['resultMessage'] = "Playlista zapisana!";
                    } else {
                        $data['resultMessage'] = "Proszę wypełnić formularz ponownie, wprowadzone dane są niepoprawne.";
                    }
                }
            }
            else {
                //Could not load the library
                $data = array(
                    'body' => 'invalidAction',
                    'title' => 'Wystąpił Błąd!',
                    'errorMessage' => "Nie znaleziono biblioteki google!"
                );
            }

            $this->load->view('templates/main', $data);
        }
        else redirect('errors/403-404');
    }

    /**
     * Shows and validates the form that adds a local playlist.
     *
     * @return void
     */
    public function addLocalPlaylist(): void
    {
        //Make sure the user is logged in to continue
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        $userId = $this->SecurityModel->getCurrentUserId();
        $userAuthorised = $userAuthenticated && $userId !== false;
        if (!$userAuthorised)
            redirect('errors/403-404');

        $data = array(
            'body' => 'playlist/addLocalPlaylist',
            'title' => 'Uber Rapsy | Dodaj lokalną playlistę!',
            'redirectSource' => $this->input->get('src')
        );

        if ($this->input->post()) {
            $queryData = array(
                'ListUrl' => $this->input->post('playlistUrl'),
                'ListName' => $this->input->post('playlistName'),
                'ListDesc' => $this->input->post('playlistDesc'),
                'ListCreatedAt' => $this->input->post('playlistDate'),
                'ListPublic' => $this->input->post('playlistVisibility'),
                'ListOwnerId' => $userId
            );

            //Obtain the unique playlist ID from the url given
            $queryData['ListUrl'] = $this->UtilityModel->extractPlaylistIdFromLink($queryData['ListUrl']);

            if ($queryData['ListName'] && $queryData['ListCreatedAt'] && !empty($queryData['ListPublic'])) {
                //Insert the playlist to the local db
                $queryData['ListDesc'] = $this->htmlsanitiser->purify($queryData['ListDesc']);
                $newListId = $this->PlaylistModel->insertPlaylist($queryData);

                //Create a log
                $data['resultMessage'] = "Pomyślnie dodano playlistę!";
                $this->LogModel->createLog('playlist', $newListId, "Stworzono lokalną playlistę");

                //If a YT URL was provided, fetch the songs and refresh the playlist
                if (!empty($queryData['ListUrl'])) {
                    //Refresh the playlist - if everything went well, the message will be empty
                    $data['displayErrorMessage'] = $this->RefreshPlaylistService->refreshPlaylist($newListId, $userId);
                }
            }
            else {
                $data['resultMessage'] = $queryData['ListName'] == "" ? "Nazwa Playlisty jest wymagana!</br>" : '';
                $data['resultMessage'] .= $queryData['ListCreatedAt'] == "" ? "Data Stworzenia Playlisty jest wymagana!</br>" : '';
                $data['resultMessage'] .= $queryData['ListPublic'] == "" ? "Status Playlisty jest wymagany!</br>" : '';
            }
        }

        $this->load->view('templates/main', $data);
    }

    /**
     * Allows the user to delete a local playlist.
     *
     * @return void
     */
    public function deleteLocalPlaylist(): void
    {
        $playlistId = filter_var($this->input->get('playlistId'), FILTER_VALIDATE_INT);
        if ($playlistId) {
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->getListOwnerById($playlistId) == $_SESSION['userId'];
            if ($userAuthorised) {
                $data = array(
                    'body' => 'playlist/deleteLocal',
                    'title' => 'Uber Rapsy | Usuń playlistę',
                    'playlist' => $this->PlaylistModel->fetchPlaylistById($playlistId),
                    'redirectSource' => $this->input->get('src')
                );

                //Delete the local playlist if confirmed by the user
                $deleteLocal = $this->input->get('del');
                if ($deleteLocal) {
                    $this->PlaylistModel->deleteLocalPlaylist($playlistId);
                    if ($data['redirectSource'] == "pd")
                        redirect('playlistDashboard');
                    else
                        redirect('myPlaylists');
                }

                $this->load->view('templates/main', $data);
            }
            else redirect('errors/403-404');
        }
        else redirect('errors/403-404');
    }

    /**
     * Allows the user to switch the integration status of a playlist
     * An integrated playlist reflects changes made to it between platforms
     *
     * @return void
     */
    public function integratePlaylist(): void
    {
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if ($userAuthenticated) {
            $data = array(
                'body' => 'playlist/integrate',
                'title' => 'Uber Rapsy | Zintegruj playlistę',
                'redirectSource' => $this->input->get('src'),
            );

            //Validate the provided playlist id
            $playlistId = filter_var($this->input->get('playlistId'), FILTER_VALIDATE_INT);
            if ($playlistId) {
                $data['playlist'] = $this->PlaylistModel->fetchPlaylistById($playlistId);
                if ($data['playlist'] !== false) {
                    //Integrate playlists if the form was submitted, otherwise open the form
                    $status = $this->input->get('status');
                    if ($status == "confirm") {
                        //Check if a valid link exists in the db or was entered when integrating the playlist with YT
                        $updatedIntegrationStatus = $data['playlist']->ListIntegrated ? "0" : "1";
                        $updatedLink = $this->input->post('nlink');
                        $linkValid = !$updatedIntegrationStatus || (strlen($data['playlist']->ListUrl) > 10 || strlen($updatedLink) > 10);
                        if ($linkValid) {
                            $data['playlistUpdatedMessage'] = "<h2>Playlista została zaktualizowana!</h2>";
                            $data['playlistUpdatedStatus'] = true;
                            $this->PlaylistModel->updatePlaylistIntegrationStatus($playlistId, $updatedIntegrationStatus, $updatedLink);
                            $this->LogModel->createLog('playlist', $playlistId,
                                $updatedIntegrationStatus ? "Playlista została zintegrowana z YouTube" : "Wyłączono integrację playlisty z YouTube");
                        }
                        else {
                            //This local playlist does not have a link required to integrate it with an existing YT playlist
                            $data['playlistUpdatedMessage'] = "<h2>Zintegrowana plalista musi posiadać swój link na YouTube!</h2>";
                            $data['playlistUpdatedStatus'] = false;
                        }
                    }

                    $this->load->view('templates/main', $data);
                }
                else redirect('errors/403-404');
            }
            else redirect('errors/403-404');
        }
        else redirect('errors/403-404');
    }
}
