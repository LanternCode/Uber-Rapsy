<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($redirectSource == 'pd'): ?>
    <a href="<?=base_url('playlistDashboard')?>"><-- Wróć do panelu zarządzania playlistami</a><br><br>
<?php else: ?>
    <a href="<?=base_url('myPlaylists')?>"><-- Wróć do moich playlist</a><br><br>
<?php endif; ?>
<?=$resultMessage ?? ""?>
<?php if (!empty($displayErrorMessage)): ?>
    <h3>Wystąpił błąd w pobieraniu utworów z playlisty na YT :/</h3>
    <h4><?=$displayErrorMessage?></h4>
    <h4>Jeśli po wykonaniu instrukcji, ponowne podjęcie tej samej akcji nie zadziała, niezwłocznie skontaktuj się z administracją.</h4>
<?php endif; ?>

<h3>Dodaj lokalną playlistę</h3>
<h4>Nie zostanie ona dodana na YT a jedynie w bazie danych RAPPAR. Nie jest zintegrowana, ale możesz połączyć ją później.</h4><br />
<form method="post" action="<?=base_url('playlist/addLocal?src='.$redirectSource)?>">
    <label>Nazwa Playlisty:</label><br />
    <input type="text" name="playlistName" size="40" /><br /><br />

    <label>Opis Playlisty:</label><br />
    <textarea name="playlistDesc" rows="10" cols="50"></textarea><br /><br />

    <label>Link do playlisty na YT:</label><br />
    <h4>Jest wymagany aby pobrać utwory z istniejącej playlisty!</h4>
    <input type="text" name="playlistUrl" size="90"/><br /><br />

    <label>Data stworzenia na YT: (Format: 2021-09-16 16:13:50)</label><br />
    <input id="createdAt" type="text" name="playlistDate"/>
    <button onclick="createdAt.value = new Date().toISOString().slice(0, 19).replace('T', ' '); return false;">Teraz</button>
    <br /><br />

    <label>Widoczność Playlisty:</label><br />
    <select name="playlistVisibility">
        <option value="1">Publiczna - osoby z linkiem mogą ją przejrzeć</option>
        <option value="0">Prywatna - widoczna tylko dla właściciela</option>
    </select><br /><br />

    <input type="submit" value="Utwórz lokalną playlistę" />
</form>
