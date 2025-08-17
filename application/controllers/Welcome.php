<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!isset($_SESSION))
    session_start();

/**
 * Controller responsible for the homepage, server maintenance, and other
 *  standalone pages.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/RAPPAR/
 *
 * @property PlaylistModel $PlaylistModel
 * @property AccountModel $AccountModel
 * @property SecurityModel $SecurityModel
 * @property CI_Output $output
 */
class Welcome extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
        $this->load->model('PlaylistModel');
        $this->load->model('AccountModel');
        $this->load->helper('cookie');
    }

    /**
     * Display the homepage.
     *
     * @return void
     */
	public function index(): void
    {
		$data = array(
            'lists' => $this->PlaylistModel->fetchHomepagePlaylists(),
            'body' => 'home',
            'title' => "Witaj w RAPPAR, portalu do oceniania utworów rapowanych! | Dołącz do naszej społeczności już dziś i oceniaj, recenzuj i dodawaj utwory!
             W RAPPAR możesz tworzyć playlisty, oceniać dodane na nie utwory i dzielić się nimi z innymi!",
            'userLoggedIn' => $this->SecurityModel->authenticateUser(),
            'isReviewer' => $this->SecurityModel->authenticateReviewer()
        );

        //Automatic sign-in
        if (isset($_COOKIE["login"]) && !$data['userLoggedIn'])
            $this->AccountModel->automaticSignIn();

		$this->load->view('templates/main', $data);
	}

    /**
     * Display the Terms of Service page.
     *
     * @return void
     */
    public function TOS(): void
    {
        $data = array(
            'body' => 'termsOfService',
            'title' => "Warunki korzystania z serwisu RAPPAR | Rejestrując się akceptujesz poniższy regulamin",
            'userLoggedIn' => $this->SecurityModel->authenticateUser(),
            'isReviewer' => $this->SecurityModel->authenticateReviewer()
        );

        $this->load->view('templates/main', $data);
    }

    /**
     * Test-specific route.
     *
     * @return void
     */
    public function testfunc(): void
    {
        $data = array(
            'body' => 'errors/403-404',
            'title' => "Test!",
            'userLoggedIn' => $this->SecurityModel->authenticateUser(),
            'isReviewer' => $this->SecurityModel->authenticateReviewer()
        );

        //$newReportId = $this->LogModel->submitReport("Hello");
        //print_r($newReportId);
        //die();

        $this->load->view('templates/main', $data);
    }

    /**
     * Maintenance-specific route.
     *
     * @return void
     */
    public function maintenance(): void
    {
        $this->output->set_status_header('503');
        $this->load->view('maintenance');
    }

    /**
     * Show an error if the user tries to access 403 or 404 content.
     *
     * @return void
     */
    public function error(): void
    {
        $data = array(
            'body' => 'errors/403-404',
            'title' => "Wystąpił błąd | Podana treść nie istnieje lub nie masz do niej dostępu",
            'userLoggedIn' => $this->SecurityModel->authenticateUser(),
            'isReviewer' => $this->SecurityModel->authenticateReviewer()
        );

        $this->load->view('templates/main', $data);
    }
}
