<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h2>Witaj w panelu zarządzania playlistami!</h2>
<br/><br/>
<a href="<?=base_url('playlist/addLocal')?>">Dodaj nową, lokalną playlistę</a>
<br/><br/>
<h2>Moje Playlisty</h2>
<br/><br/>
<?php if(count($playlists) == 0): ?>
    <h3>Nie posiadasz żadnych playlist!</h3>
<?php else: ?>
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
                <td><a target="_blank" href="<?=base_url('playlist?ListId='.$playlist->ListId)?>">--></a></td>
                <td><?=$playlist->ListName?></td>
                <td><a href="<?=base_url('playlist/edit?id='.$playlist->ListId)?>">Edytuj</a></td>
                <td><?=$playlist->ListActive === "1" ? "Publiczna" : "Ukryta"?></td>
                <td><a href="<?=base_url('playlist/details?id='.$playlist->ListId)?>">Szczegóły</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
