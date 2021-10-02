<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<a href="<?=base_url('playlistDashboard')?>"><-- Wróć do panelu zarządzania playlistami</a><br><br>

<p>ID w lokalnej bazie danych: <?=$playlist->ListId?></p><br>
<p>ID playlisty na YT: <?=$playlist->ListUrl?></p><br>
<p>Link do playlisty: <a target="_blank" href="https://www.youtube.com/playlist?list=<?=$playlist->ListUrl?>"><?=$playlist->ListName?></a></p></br>
<p>Nazwa playlisty: <?=$playlist->ListName?></p><br>
<p>Opis playlisty: <?=$playlist->ListDesc?></p><br>
<p>Data dodania playlisty: <?=$playlist->ListCreatedAt?></p><br>
<p>Playlista publiczna: <?=$playlist->ListActive === "1" ? "Tak" : "Nie"?></p><br><br>

<a href="<?=base_url('playlist/quickEdit?id='.$playlist->ListId)?>">Edytuj Playlistę</a><br>
<a href="<?=base_url('playlist/hidePlaylist?id='.$playlist->ListId)?>"><?=$playlist->ListActive === "1" ? "Ukryj" : "Upublicznij"?> Playlistę</a><br>
<a href="<?=base_url('playlist/deleteLocal?id='.$playlist->ListId)?>">Usuń Playlistę</a><br><br>

<h3>Tracklista</h3>
<table>
    <tr>
        <th>Track</th>
        <th>Status na YT</th>
        <th>Ocena Adama</th>
        <th>Ocena Kościelnego</th>
        <th>Usuń z listy</th>
    </tr>
    <?php foreach($songs as $song): ?>
        <tr>
            <td><?=$song->SongTitle?></td>
            <td>-</td>
            <td><?=$song->SongGradeAdam?></td>
            <td><?=$song->SongGradeChurchie?></td>
            <td><a href="<?=base_url('playlist/delSong?id='.$song->SongId)?>">Usuń</a></td>
        </tr>
    <?php endforeach; ?>
</table>