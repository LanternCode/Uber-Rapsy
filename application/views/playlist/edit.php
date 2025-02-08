<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($redirectSource == 'pd'): ?>
    <a href="<?=base_url('playlistDashboard?src='.$redirectSource)?>"><-- Wróć do panelu zarządzania playlistami</a><br><br>
<?php else: ?>
    <a href="<?=base_url('myPlaylists?src='.$redirectSource)?>"><-- Wróć do moich playlist</a><br><br>
<?php endif; ?>
<?=$resultMessage ?? ''?>

<h4>Edytuj Playlistę:</h4>

<form method="post" action="<?=base_url('playlist/edit?listId='.$playlist->ListId)?>">
    <label>Nazwa Playlisty:</label><br />
    <input type="text" name="playlistName" value="<?=$playlist->ListName?>" /><br /><br />

    <label>Opis Playlisty:</label><br />
    <input type="text" name="playlistDesc" value="<?=$playlist->ListDesc?>" /><br /><br />

    <label>Link do playlisty na YT:</label><br />
    <input type="text" name="playlistId" value="<?=$playlist->ListUrl?>" /><br /><br />

    <label>Data stworzenia na YT: (Format: 2021-09-16 16:13:50)</label><br />
    <input id="createdAt" type="text" name="playlistDate" value="<?=$playlist->ListCreatedAt?>" />
    <button onclick="createdAt.value = new Date().toISOString().slice(0, 19).replace('T', ' '); return false;">Teraz</button>
    <br /><br />

    <label>Status Playlisty:</label><br />
    <select name="playlistVisibility">
        <option value="1" <?=$playlist->ListActive === "1" ? "selected" : ''?>>Publiczna - widoczna na stronie głównej</option>
        <option value="0" <?=$playlist->ListActive === "0" ? "selected" : ''?>>Prywatna - widoczna tylko w panelu sterowania</option>
    </select><br /><br />

    <label>Widoczność przycisków:</label><br>
    <label><input type="checkbox" <?=$playlist->btnRehearsal ? "checked" : ""?> name="btnRehearsal"> Do ponownego odsłuchu</label><br>
    <label><input type="checkbox" <?=$playlist->btnBelowFour ? "checked" : ""?> name="btnBelowFour"> < 4</label><br>
    <label><input type="checkbox" <?=$playlist->btnBelowSeven ? "checked" : ""?> name="btnBelowSeven"> < 7</label><br>
    <label><input type="checkbox" <?=$playlist->btnBelowEight ? "checked" : ""?> name="btnBelowEight"> < 8</label><br>
    <label><input type="checkbox" <?=$playlist->btnBelowNine ? "checked" : ""?> name="btnBelowNine"> < 9</label><br>
    <label><input type="checkbox" <?=$playlist->btnBelowTen ? "checked" : ""?> name="btnBelowTen"> < 10</label><br>
    <label><input type="checkbox" <?=$playlist->btnDistinction ? "checked" : ""?> name="btnDistinction"> Wyróżnienie</label><br>
    <label><input type="checkbox" <?=$playlist->btnDuoTen ? "checked" : ""?> name="btnDuoTen"> "10"</label><br>
    <label><input type="checkbox" <?=$playlist->btnMemorial ? "checked" : ""?> name="btnMemorial"> 10*</label><br>
    <label><input type="checkbox" <?=$playlist->btnUber ? "checked" : ""?> name="btnUber"> Uber</label><br>
    <label><input type="checkbox" <?=$playlist->btnTop ? "checked" : ""?> name="btnTop"> X15</label><br>
    <label><input type="checkbox" <?=$playlist->btnXD ? "checked" : ""?> name="btnXD"> XD</label><br>
    <label><input type="checkbox" <?=$playlist->btnDiscomfort ? "checked" : ""?> name="btnDiscomfort"> Strefa Dyskomfortu</label><br>
    <label><input type="checkbox" <?=$playlist->btnNotRap ? "checked" : ""?> name="btnNotRap"> To nie rapsik</label><br>
    <label><input type="checkbox" <?=$playlist->btnNoGrade ? "checked" : ""?> name="btnNoGrade"> Nie oceniam</label><br>
    <label><input type="checkbox" <?=$playlist->btnVeto ? "checked" : ""?> name="btnVeto"> VETO</label><br>
    <label><input type="checkbox" <?=$playlist->btnBelowHalfSeven ? "checked" : ""?> name="btnBelowHalfSeven"> < 7.5</label><br>
    <label><input type="checkbox" <?=$playlist->btnBelowHalfEight ? "checked" : ""?> name="btnBelowHalfEight"> < 8.5</label><br>
    <label><input type="checkbox" <?=$playlist->btnBelowHalfNine ? "checked" : ""?> name="btnBelowHalfNine"> < 9.5</label><br><br>

    <input type="submit" name="playlistFormSubmitted" value="Zapisz" />
</form>