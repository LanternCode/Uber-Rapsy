<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<a href="<?=base_url('playlistDashboard')?>"><-- Wróć do panelu zarządzania playlistami</a><br><br>
<?=$resultMessage ?? ''?>

<h4>Edytuj Playlistę:</h4>

<form method="post" action="<?=base_url('playlist/quickEdit?id='.$playlist->ListId)?>">
    <label>Nazwa Playlisty:</label><br />
    <input type="text" name="playlistName" value="<?=$playlist->ListName?>" /><br /><br />

    <label>Opis Playlisty:</label><br />
    <input type="text" name="playlistDesc" value="<?=$playlist->ListDesc?>" /><br /><br />

    <label>ID playlisty na YT: (Np: PLkIbfiOcITXoeEjCiGOlOYUUp7-FwbqPy)</label><br />
    <input type="text" name="playlistId" value="<?=$playlist->ListUrl?>" /><br /><br />

    <label>Data stworzenia na YT: (Format: 2021-09-16 16:13:50)</label><br />
    <input id="createdAt" type="text" name="playlistDate" value="<?=$playlist->ListCreatedAt?>" />
    <button onclick="createdAt.value = new Date().toISOString().slice(0, 19).replace('T', ' '); return false;">Teraz</button>
    <br /><br />

    <label>Status Playlisty:</label><br />
    <select name="playlistVisibility">
        <option value="1" <?=$playlist->ListActive === "1" ? "selected" : ''?>>Publiczna</option>
        <option value="0" <?=$playlist->ListActive === "0" ? "selected" : ''?>>Prywatna</option>
    </select><br /><br />

    <input type="submit" name="playlistFormSubmitted" value="Zapisz" />
</form>