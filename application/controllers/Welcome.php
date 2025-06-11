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
 * @link https://lanterncode.com/Uber-Rapsy/
 *
 * @property PlaylistModel $PlaylistModel
 * @property AccountModel $AccountModel
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
            'title' => "Uber Rapsy | Portal do oceniania utworów rapowanych"
        );

        //Automatic sign-in
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;
        if (isset($_COOKIE["login"]) && !$userLoggedIn) {
            $this->AccountModel->automaticSignIn();
        }

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
            'title' => "Uber Rapsy | Zasady Użytkowania serwisu Uber Rapsy"
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
            'body' => 'test',
            'title' => "Uber Rapsy | Test!"
        );

        //$newReportId = $this->LogModel->submitReport("Hello");
        //print_r($newReportId);
        //die();

        $this->load->view( 'templates/main', $data );
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
}
