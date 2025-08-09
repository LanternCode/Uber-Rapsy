<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['playlist'] = 'PlaylistItems/loadPlaylist';
$route['search'] = 'PlaylistItems/globalSearch';
$route['tierlist'] = 'PlaylistItems/tierlist';
$route['updateSongRatings'] = 'PlaylistItems/updateSongRatingsInPlaylist';
$route['updateGradesFromSearch'] = 'PlaylistItems/updateGradesFromSearch';
$route['downloadSongs'] = 'PlaylistItems/downloadSongs';
$route['playlist/delSong'] = 'PlaylistItems/deletePlaylistSong';

$route['adminDashboard'] = 'YoutubeIntegration';
$route['apitestPlaylist'] = 'YoutubeIntegration/result';
$route['newToken'] = 'YoutubeIntegration/generate';
$route['displayReport'] = 'Log/displayReport';
$route['TermsOfService'] = 'Welcome/TOS';

$route['myPlaylists'] = 'Playlist/myPlaylists';
$route['playlistDashboard'] = 'Playlist/playlistDashboard';
$route['playlist/newPlaylist'] = 'Playlist/newIntegratedPlaylistForm';
$route['playlist/addPlaylist'] = 'Playlist/addIntegratedPlaylist';
$route['playlist/addLocal'] = 'Playlist/addLocalPlaylist';
$route['playlist/details'] = 'Playlist/playlistDetails';
$route['playlist/edit'] = 'Playlist/editPlaylist';
$route['playlist/hidePlaylist'] = 'Playlist/switchPlaylistPublicStatus';
$route['playlist/deleteLocal'] = 'Playlist/deleteLocalPlaylist';
$route['playlist/integrate'] = 'Playlist/integratePlaylist';
$route['playlist/showLog'] = 'Log/showPlaylistLog';

$route['playlistItems/showLog'] = 'Log/showPlaylistSongLog';
$route['playlistItems/updatePlaylistSongVisibility'] = 'PlaylistItems/updatePlaylistSongVisibility';

$route['frontpage'] = 'Song/frontpage';
$route['songSearch'] = 'Song/songSearch';
$route['songPage'] = 'Song/songPage';
$route['importSongs'] = 'Song/importSongs';
$route['confirmImporting'] = 'Song/confirmSongImport';
$route['manualImport'] = 'Song/manualImport';
$route['song/edit'] = 'Song/editSong';
$route['song/edit'] = 'Song/editSong';
$route['song/showLog'] = 'Log/showSongLog';
$route['song/awards'] = 'Song/manageSongAwards';
$route['song/updateVisibility'] = 'Song/updateSongVisibility';
$route['song/deleteSong'] = 'Song/deleteSong';
$route['song/autoSave'] = 'Song/rateSongAuto';
$route['song/reviewSong'] = 'Song/newSongReview';
$route['song/showReview'] = 'Song/songReview';
$route['song/addToPlaylist'] = 'Song/addSongToPlaylist';

$route['newAccount'] = 'Account/newAccount';
$route['login'] = 'Account/login';
$route['logout'] = 'Account/logout';
$route['forgottenPassword'] = 'Account/forgottenPassword';
$route['forgottenPassword/reset'] = 'Account/resetPassword';
$route['contributorsRanking'] = 'Account/contributorsRanking';
$route['user/details'] = 'Account/userProfile';

$route['usersDashboard'] = 'Account/usersDashboard';

$route['testfunc'] = 'Welcome/testfunc';

$route['default_controller'] = 'Welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

if (!in_array($_SERVER['REMOTE_ADDR'], $this->config->item('maintenance_ips')) && $this->config->item('maintenance_mode')) {
    $route['default_controller'] = "Welcome/maintenance";
    $route['(:any)'] = "Welcome/maintenance";
}