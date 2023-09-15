<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['playlist'] = 'Playlist/playlist';
$route['updateGrades'] = 'Playlist/updateSongsInPlaylist';
$route['downloadSongs'] = 'Playlist/downloadSongs';
$route['updateSelection'] = 'Playlist/updateSongsInPlaylist';

$route['loginYoutube'] = 'YoutubeIntegration';
$route['apitestPlaylist'] = 'YoutubeIntegration/result';
$route['newToken'] = 'YoutubeIntegration/generate';
$route['displayReport'] = 'Log/displayReport';

$route['playlistDashboard'] = 'Playlist/dashboard';
$route['playlist/newPlaylist'] = 'Playlist/newPlaylist';
$route['playlist/addPlaylist'] = 'Playlist/addPlaylist';
$route['playlist/addLocal'] = 'Playlist/addLocal';
$route['playlist/details'] = 'Playlist/details';
$route['playlist/edit'] = 'Playlist/edit';
$route['playlist/hidePlaylist'] = 'Playlist/hidePlaylist';
$route['playlist/deleteLocal'] = 'Playlist/deleteLocal';
$route['playlist/delSong'] = 'Playlist/delSong';
$route['playlist/updateSongVisibility'] = 'Playlist/updateSongVisibility';
$route['playlist/integrate'] = 'Playlist/integrate';
$route['playlist/showLog'] = 'Playlist/showLog';

$route['song/rev'] = 'Song/reviewSong';
$route['song/showLog'] = 'Song/showLog';

$route['login'] = 'Account';
$route['logout'] = 'Account/logout';

$route['testfunc'] = 'Welcome/testfunc';

$route['default_controller'] = 'Welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
