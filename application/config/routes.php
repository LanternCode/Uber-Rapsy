<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['playlist'] = 'Playlist/playlist';
$route['updateGrades'] = 'Playlist/update';
$route['downloadSongs'] = 'Playlist/downloadSongs';

$route['loginYoutube'] = 'YoutubeIntegration';
$route['apitestPlaylist'] = 'YoutubeIntegration/result';
$route['newToken'] = 'YoutubeIntegration/generate';

$route['newPlaylist'] = 'Playlist/newPlaylist';
$route['addPlaylist'] = 'Playlist/addPlaylist';

$route['login'] = 'Account';
$route['logout'] = 'Account/logout';

$route['testfunc'] = 'Welcome/testfunc';

$route['default_controller'] = 'Welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
