<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['playlist'] = 'Playlist/playlist';
$route['updateGrades'] = 'Playlist/update';
$route['downloadSongs'] = 'Playlist/downloadSongs';

$route['loginYoutube'] = 'YoutubeIntegration';
$route['apitestPlaylist'] = 'YoutubeIntegration/result';
$route['newToken'] = 'YoutubeIntegration/generate';

$route['playlistDashboard'] = 'Playlist/dashboard';
$route['playlist/newPlaylist'] = 'Playlist/newPlaylist';
$route['playlist/addPlaylist'] = 'Playlist/addPlaylist';
$route['playlist/addLocal'] = 'Playlist/addLocal';
$route['playlist/details'] = 'Playlist/details';
$route['playlist/quickEdit'] = 'Playlist/quickEdit';
$route['playlist/hidePlaylist'] = 'Playlist/hidePlaylist';
$route['playlist/deleteLocal'] = 'Playlist/deleteLocal';
$route['playlist/delSong'] = 'Playlist/delSong';
$route['playlist/integrate'] = 'Playlist/integrate';
$route['playlist/showLog'] = 'Playlist/showLog';
$route['updateSelection'] = 'Playlist/updateSelection';

$route['song/rev'] = 'Song/reviewSong';
$route['song/showLog'] = 'Song/showLog';
$route['search'] = 'Song/search';

$route['login'] = 'Account';
$route['logout'] = 'Account/logout';

$route['testfunc'] = 'Welcome/testfunc';

$route['default_controller'] = 'Welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
