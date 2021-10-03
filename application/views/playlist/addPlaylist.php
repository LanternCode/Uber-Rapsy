<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<a href="<?=base_url('playlistDashboard')?>"><-- Wróć do panelu zarządzania playlistami</a><br><br>
<?=$resultMessage ?? ""?>

<h3>Dodaj nową playlistę</h3>
<form method="post" action="<?=base_url('playlist/addPlaylist')?>">
	<label>Nazwa Playlisty:</label>
	<input type="text" name="playlistName" />

	<label>Opis Playlisty:</label>
	<input type="text" name="playlistDesc" />

	<input type="submit" value="Zapisz" />
</form>
