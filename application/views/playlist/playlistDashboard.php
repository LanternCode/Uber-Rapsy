<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h4>Witaj w panelu zarządzania playlistami!</h4>
<br/><br/>
<a href="<?=base_url('playlist/newPlaylist')?>">Dodaj nową playlistę</a>
<br/><br/>
<a href="<?=base_url('playlist/addLocal')?>">Dodaj lokalną playlistę</a>
<br/><br/>
<h4>Aktywne Playlisty</h4>
<br/><br/>
<table>
    <tr>
        <th>Przejdź</th>
        <th>Nazwa Playlisty</th>
        <th>Edycja</th>
        <th>Widoczność</th>
        <th>Szczegóły</th>
    </tr>
    <?php foreach($playlists as $playlist): ?>
        <tr>
            <td><a href="<?=base_url('playlist?ListId='.$playlist->ListId)?>">--></a></td>
            <td><?=$playlist->ListName?></td>
            <td><a href="<?=base_url('playlist/edit?id='.$playlist->ListId)?>">Edytuj</a></td>
            <td><?=$playlist->ListActive === "1" ? "Publiczna" : "Ukryta"?></td>
            <td><a href="<?=base_url('playlist/details?id='.$playlist->ListId)?>">Szczegóły</a></td>
        </tr>
    <?php endforeach; ?>
</table>