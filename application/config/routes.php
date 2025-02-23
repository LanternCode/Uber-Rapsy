<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['playlist'] = 'PlaylistItems/loadPlaylist';
$route['search'] = 'PlaylistItems/globalSearch';
$route['tierlist'] = 'PlaylistItems/tierlist';
$route['updateSongRatings'] = 'PlaylistItems/updateSongRatingsInPlaylist';
$route['downloadSongs'] = 'PlaylistItems/downloadSongs';

$route['adminDashboard'] = 'YoutubeIntegration';
$route['apitestPlaylist'] = 'YoutubeIntegration/result';
$route['newToken'] = 'YoutubeIntegration/generate';
$route['displayReport'] = 'Log/displayReport';
$route['TermsOfService'] = 'Welcome/TOS';

$route['myPlaylists'] = 'Playlist/myPlaylists';
$route['playlistDashboard'] = 'Playlist/dashboard';
$route['playlist/newPlaylist'] = 'Playlist/newPlaylist';
$route['playlist/addPlaylist'] = 'Playlist/addPlaylist';
$route['playlist/addLocal'] = 'Playlist/addLocal';
$route['playlist/details'] = 'Playlist/details';
$route['playlist/edit'] = 'Playlist/edit';
$route['playlist/hidePlaylist'] = 'Playlist/switchPlaylistPublicStatus';
$route['playlist/deleteLocal'] = 'Playlist/deleteLocal';
$route['playlist/delSong'] = 'Playlist/delSong';
$route['playlist/integrate'] = 'Playlist/integrate';
$route['playlist/showLog'] = 'Playlist/showLog';

$route['song/rev'] = 'Song/reviewSong';
$route['song/showLog'] = 'Song/showLog';
$route['song/updateSongVisibility'] = 'Song/updateSongVisibility';

$route['newAccount'] = 'Account/newAccount';
$route['login'] = 'Account';
$route['logout'] = 'Account/logout';
$route['forgottenPassword'] = 'Account/forgottenPassword';
$route['forgottenPassword/reset'] = 'Account/resetPassword';

$route['usersDashboard'] = 'Account/usersDashboard';

$route['testfunc'] = 'Welcome/testfunc';

$route['default_controller'] = 'Welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

if (!in_array($_SERVER['REMOTE_ADDR'], $this->config->item('maintenance_ips')) && $this->config->item('maintenance_mode')) {
    $route['default_controller'] = "Welcome/maintenance";
    $route['(:any)'] = "Welcome/maintenance";
}
