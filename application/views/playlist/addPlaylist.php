<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($redirectSource == 'pd'): ?>
    <a href="<?=base_url('playlistDashboard')?>"><-- Wróć do panelu zarządzania playlistami</a><br><br>
<?php else: ?>
    <a href="<?=base_url('myPlaylists')?>"><-- Wróć do moich playlist</a><br><br>
<?php endif; ?>
<?=$resultMessage ?? ""?>

<h3>Dodaj nową playlistę</h3>
<h4>Zostanie ona dodana na YT oraz w bazie danych Uber-Rapsów, będzie automatycznie zintegrowana.</h4><br />
<form method="post" action="<?=base_url('playlist/addPlaylist?src='.$redirectSource)?>">
	<label>Nazwa Playlisty:</label>
	<input type="text" name="playlistName" size="40" />

	<br><br><label>Opis Playlisty:</label><br>
    <textarea name="playlistDesc" rows="10" cols="50"></textarea>

    <br><br><label>Widoczność Playlisty na YT:</label><br />
    <select name="playlistVisibilityYT">
        <option value="public">Publiczna</option>
        <option value="unlisted">Niepubliczna</option>
        <option value="private">Prywatna</option>
    </select><br /><br />

    <label>Widoczność Playlisty na Uber:</label><br />
    <select name="playlistVisibility">
        <option value="1">Publiczna - widoczna dla ogółu</option>
        <option value="0">Prywatna - widoczna tylko dla właściciela</option>
    </select><br /><br />

	<br><br><input type="submit" value="Dodaj playlistę na YT" />
</form>
