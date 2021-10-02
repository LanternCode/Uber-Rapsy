<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?=$resultMessage ?? ""?>

<h3>Dodaj lokalnie playlistę</h3>
<h4>Nie zostanie ona dodana na YT a jedynie w bazie danych Uber-Rapsów</h4><br />
<form method="post" action="<?=base_url('playlist/addLocal')?>">
    <label>Nazwa Playlisty:</label><br />
    <input type="text" name="playlistName" /><br /><br />

    <label>Opis Playlisty:</label><br />
    <input type="text" name="playlistDesc" /><br /><br />

    <label>ID playlisty na YT: (Np: PLkIbfiOcITXoeEjCiGOlOYUUp7-FwbqPy)</label><br />
    <input type="text" name="playlistId" /><br /><br />

    <label>Data stworzenia na YT: (Format: 2021-09-16 16:13:50)</label><br />
    <input id="createdAt" type="text" name="playlistDate" />
    <button onclick="createdAt.value = new Date().toISOString().slice(0, 19).replace('T', ' '); return false;">Teraz</button>
    <br /><br />

    <label>Status Playlisty:</label><br />
    <select name="playlistVisibility">
        <option value="1">Publiczna</option>
        <option value="0">Prywatna</option>
    </select><br /><br />

    <input type="submit" name="playlistFormSubmitted" value="Zapisz" />
</form>
