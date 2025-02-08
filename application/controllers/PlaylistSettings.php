<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION)){
	session_start();
}

/**
 * Controller responsible for handling playlist settings.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class PlaylistSettings extends CI_Controller {
    public function __construct() {
        parent::__construct();
        //$this->load->model('PlaylistModel');
    }
}