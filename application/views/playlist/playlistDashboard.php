<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h4>Witaj w panelu zarządzania playlistami!</h4>
<br/><br/>
<a href="<?=base_url('playlist/newPlaylist?src=pd')?>">Dodaj nową playlistę</a>
<br/><br/>
<a href="<?=base_url('playlist/addLocal?src=pd')?>">Dodaj lokalną playlistę</a>
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
            <td><a target="_blank" href="<?=base_url('playlist?listId='.$playlist->ListId)?>">--></a></td>
            <td><?=$playlist->ListName?></td>
            <td><a href="<?=base_url('playlist/edit?listId='.$playlist->ListId.'&src=pd')?>">Edytuj</a></td>
            <td><?=$playlist->ListPublic === "1" ? "Publiczna" : "Ukryta"?></td>
            <td><a href="<?=base_url('playlist/details?listId='.$playlist->ListId.'&src=pd')?>">Szczegóły</a></td>
        </tr>
    <?php endforeach; ?>
</table>