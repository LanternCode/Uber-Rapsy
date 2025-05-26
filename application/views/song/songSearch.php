<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1>Wyniki wyszukiwania (znaleziono <?=count($songs)?> utworów!)</h1>
<?php if (count($songs) > 0 && count($songs) < 301): ?>
    <table>
        <thead>
            <tr>
                <th>Nuta</th>
                <th>Moja Ocena</th>
                <th>Średnia Społeczności</th>
                <th>Nagrody</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($songs as $song): ?>
            <tr>
                <td><a href="<?=base_url('songPage?songId='.$song->SongId)?>"><?=$song->SongTitle?></a></td>
                <td><?=$song->myGrade != 0 ? $song->myGrade : '❌' ?></td>
                <td><?=$song->communityAverage != 0 ? $song->communityAverage : '❌'?></td>
                <?php foreach($song->awards as $award): ?>
                    <td><p class="song-awards"><?=$award->award?></p></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif (strlen($searchQuery) < 1): ?>
    <h3>Nie wpisano nic do wyszukiwarki!</h3>
<?php elseif (count($songs) > 300): ?>
    <h3>Znaleziono ponad 300 piosenek! Musisz zawęzić kryteria wyszukiwania!</h3>
<?php else: ?>
    <h3>Nie znaleziono żadnych utworów o podanej nazwie!</h3>
<?php endif; ?>