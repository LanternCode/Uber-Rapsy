<?=$resultMessage ?? ""?>

<h3>Dodaj nową playlistę</h3>
<form method="post" action="<?=base_url("addPlaylist")?>">
	<label>Nazwa Playlisty:</label>
	<input type="text" name="playlistName" />

	<label>Opis Playlisty:</label>
	<input type="text" name="playlistDesc" />

	<input type="submit" value="Zapisz" />
</form>
