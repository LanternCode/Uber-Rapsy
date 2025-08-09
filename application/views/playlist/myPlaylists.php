<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h2>Moje Konto</h2>
<br>
Nazwa Użytkownika: <?=$profile->username?><br><br>
Ilość punktów RAPPAR: <?=$profile->userScore?><br><br>
Miejsce w rankingu punktowym:
<?=
    empty($scores->user_rank)
        ? 'Nie posiadasz jeszcze żadnych punktów. Gdy już jakieś zdobędziesz, tutaj zobaczysz swoje miejsce w rankingu!'
        : $scores->user_rank.' '.
            ($scores->user_rank == 1
                ? '(Zajmujesz pierwsze miejsce w rankingu!!! Różnica punktów między pierwszym a drugim miejscem to '.$scores->to_next.'.)'
                : '(Różnica punktów między tobą a wyższym miejscem to '.-$scores->to_next.'!)')
?><br><br>
Ranking najbardziej aktywnych użytkowników RAPPAR: <a href="<?=base_url('contributorsRanking')?>">Kliknij Tutaj</a>!
<br><br>
<h2>Moje Playlisty</h2><br>
<a class="big-button" href="<?=base_url('playlist/addLocal?src=mp')?>">Dodaj nową, lokalną playlistę</a>
<br/><br/>
<?php if (count($playlists) == 0): ?>
    <h3>Nie posiadasz żadnych playlist!</h3>
<?php else: ?>
    <table>
        <tr>
            <th>Przejdź</th>
            <th>Nazwa Playlisty</th>
            <th>Edycja</th>
            <th>Widoczność</th>
            <th>Szczegóły</th>
            <th>Status</th>
        </tr>
        <?php foreach ($playlists as $playlist): ?>
            <tr>
                <td><a target="_blank" href="<?=base_url('playlist?playlistId='.$playlist->ListId)?>">--></a></td>
                <td><?=$playlist->ListName?></td>
                <td><a href="<?=base_url('playlist/edit?playlistId='.$playlist->ListId.'&src=mp')?>">Edytuj</a></td>
                <td><?=$playlist->ListPublic === "1" ? "Publiczna" : "Prywatna"?></td>
                <td><a href="<?=base_url('playlist/details?playlistId='.$playlist->ListId.'&src=mp')?>">Szczegóły</a></td>
                <td><?=$playlist->ListActive === "1" ? "Aktywna" : "Archiwalna"?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>