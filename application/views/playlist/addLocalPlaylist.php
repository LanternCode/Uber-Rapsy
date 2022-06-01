<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<a href="<?=base_url('playlistDashboard')?>"><-- Wróć do panelu zarządzania playlistami</a><br><br>
<?=$resultMessage ?? ""?>

<h3>Dodaj lokalnie playlistę</h3>
<h4>Nie zostanie ona dodana na YT a jedynie w bazie danych Uber-Rapsów. Nie jest zintegrowana.</h4><br />
<form method="post" action="<?=base_url('playlist/addLocal')?>">
    <label>Nazwa Playlisty:</label><br />
    <input type="text" name="playlistName" size="40" /><br /><br />

    <label>Opis Playlisty:</label><br />
    <textarea name="playlistDesc" rows="10" cols="50"></textarea><br /><br />

    <label>Link do playlisty na YT:</label><br />
    <h4>Jest wymagany aby pobrać utwory z istniejącej playlisty!</h4>
    <input type="text" name="playlistId" size="90"/><br /><br />

    <label>Data stworzenia na YT: (Format: 2021-09-16 16:13:50)</label><br />
    <input id="createdAt" type="text" name="playlistDate"/>
    <button onclick="createdAt.value = new Date().toISOString().slice(0, 19).replace('T', ' '); return false;">Teraz</button>
    <br /><br />

    <label>Status Playlisty:</label><br />
    <select name="playlistVisibility">
        <option value="1">Publiczna</option>
        <option value="0">Prywatna</option>
    </select><br /><br />

    <input type="submit" name="playlistFormSubmitted" value="Utwórz lokalną playlistę" />
</form>
