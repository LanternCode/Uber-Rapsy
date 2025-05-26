<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1>Toplisty RAPPAR - Znajdź coś dla siebie!</h1><br><br>
<h2>Top100 RAPPAR</h2>
<?php if (count($songs) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Nuta</th>
                <th>Średnia Społeczności</th>
                <th>Moja Ocena</th>
                <th>Nagrody</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($songs as $song): ?>
            <tr>
                <td><a href="<?=base_url('songPage?songId='.$song->SongId)?>"><?=$song->SongTitle?></a></td>
                <td><?=$song->communityAverage != 0 ? $song->communityAverage : '❌'?></td>
                <td><?=$song->myRating != 0 ? $song->myRating : '❌' ?></td>
                <?php foreach($song->awards as $award): ?>
                    <td><p class="song-awards centered"><?=$award->award?></p></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <h3>Nie znaleziono żadnych utworów o podanej nazwie!</h3>
<?php endif; ?>