<?=isset($resultMessage) ? $resultMessage : ""?>

<h3>Dodaj nową playlistę</h3>
<form method="post" action="<?=base_url("addPlaylist")?>">
	<label>Nazwa Playlisty:</label>
	<input type="text" name="playlistName" />

	<label>Opis Playlisty:</label>
	<input type="text" name="playlistDesc" />

	<label>Link do playlisty na YT:</label>
	<input type="url" name="playlistUrl" />

	<input type="submit" value="Zapisz" />
</form>
