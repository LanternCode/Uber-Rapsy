<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AuthModel extends CI_Model
{
	function __construct(){
		parent::__construct();
	}

	function refreshToken()
	{

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => "https://YOUR_DOMAIN/oauth/token",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "grant_type=refresh_token&client_id=%24%7Baccount.clientId%7D&client_secret=YOUR_CLIENT_SECRET&refresh_token=YOUR_REFRESH_TOKEN",
			CURLOPT_HTTPHEADER => [
				"content-type: application/x-www-form-urlencoded"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			echo $response;
		}
	}

}
