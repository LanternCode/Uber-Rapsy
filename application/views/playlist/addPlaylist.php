<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?=$resultMessage ?? ""?>

<h3>Dodaj nową playlistę</h3>
<form method="post" action="<?=base_url('playlist/addPlaylist')?>">
	<label>Nazwa Playlisty:</label>
	<input type="text" name="playlistName" />

	<label>Opis Playlisty:</label>
	<input type="text" name="playlistDesc" />

	<input type="submit" value="Zapisz" />
</form>
