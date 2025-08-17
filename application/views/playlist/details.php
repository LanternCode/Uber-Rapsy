<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($redirectSource == 'pd'): ?>
    <a href="<?=base_url('playlistDashboard')?>"><-- Wróć do panelu zarządzania playlistami</a><br><br>
<?php elseif ($redirectSource == 'mp'): ?>
    <a href="<?=base_url('myPlaylists')?>"><-- Wróć do moich playlist</a><br><br>
<?php else: ?>
    <a href='<?=base_url('playlist?playlistId='.$playlist->ListId)?>'><-- Wróć do playlisty</a>
<?php endif; ?>

<h3>Informacje o playliście</h3><br>
<p>Nazwa playlisty: <?=$playlist->ListName?></p><br>
<p>Opis playlisty: <?=$playlist->ListDesc?></p><br>
<p>Data dodania playlisty: <?=$playlist->ListCreatedAt?></p><br>
<p>Ilość utworów na playliście: <?=count($songs)?></p><br>
<?php if ($isReviewer): ?>
    <p>Playlista zintegrowana z YT: <?=$playlist->ListIntegrated ? "Tak" : "Nie"?> <a href="<?=base_url('playlist/integrate?playlistId='.$playlist->ListId.'&src='.$redirectSource)?>">(Zmień status integracji)</a></p><br>
<?php endif; ?>
<p>Playlista publiczna: <?=$playlist->ListPublic === "1" ? "Tak" : "Nie"?></p><br>
<p>Playlista archiwalna: <?=$playlist->ListActive === "1" ? "Nie" : "Tak"?></p><br>
<p>Link do playlisty na YT: <?=!empty($playlist->ListUrl) ? "<a target='_blank' href='https://www.youtube.com/playlist?list=$playlist->ListUrl'>https://www.youtube.com/playlist?list=$playlist->ListUrl</a>" : "Brak"?></p></br>
<p>Link w RAPPAR: <a target='_blank' href='<?=base_url('playlist?playlistId='.$playlist->ListId)?>'><?=base_url('playlist?playlistId='.$playlist->ListId)?></a></p></br>
<p>ID w lokalnej bazie danych: <?=$playlist->ListId?></p><br>
<p>ID playlisty na YT: <?=$playlist->ListUrl?></p><br>
<p>ID właściciela playlisty: <?=$playlist->ListOwnerId?></p><br>
<p>Nazwa użytkownika właściciela playlisty: <?=$playlistOwnerUsername?></p><br><br>

<h3>Zarządzaj playlistą</h3><br>
<a href="<?=base_url('downloadSongs?playlistId='.$playlist->ListId.'&src='.$redirectSource)?>">Załaduj nowe nuty</a><br><br>
<a href="<?=base_url('playlist/edit?playlistId='.$playlist->ListId.'&src='.$redirectSource)?>">Edytuj Playlistę</a><br><br>
<a href="<?=base_url('playlist/hidePlaylist?playlistId='.$playlist->ListId.'&src='.$redirectSource)?>"><?=$playlist->ListPublic === "1" ? "Ukryj" : "Upublicznij"?> Playlistę</a><br><br>
<a href="<?=base_url('playlist/deleteLocal?playlistId='.$playlist->ListId.'&src='.$redirectSource)?>">Usuń Playlistę</a><br><br>
<a href="<?=base_url('playlist/showLog?playlistId='.$playlist->ListId.'&src='.$redirectSource)?>">Pokaż ostatnie zmiany</a><br><br>

<?php if (count($songs) > 0): ?>
    <h3>Tracklista</h3>
    <table>
        <tr>
            <th>Track w RAPPAR</th>
            <th>Track na YT</th>
            <?php if ($isRapparManaged): ?>
                <th>Ocena Adama</th>
                <th>Ocena Kościelnego</th>
            <?php else: ?>
                <th>Ocena Właściciela</th>
            <?php endif; ?>
            <th>Ukryj Utwór</th>
            <th>Usuń z listy</th>
            <th>Sprawdź Historię</th>
        </tr>
        <?php foreach ($songs as $song): ?>
            <tr>
                <?php if ($song->SongDeleted): ?>
                    <td><i><b><?=$song->SongTitle?></b></i></td>
                    <td>-</td>
                    <?php if ($isRapparManaged): ?>
                        <td>-</td>
                        <td>-</td>
                    <?php else: ?>
                        <td>-</td>
                    <?php endif; ?>
                    <td>-</td>
                    <td>-</td>
                <?php else: ?>
                    <td><a href="<?=base_url('songPage?songId='.$song->SongId)?>"><?=$song->SongTitle?></a></td>
                    <td><?=$song->SongURL != '' ? '<a href="https://youtu.be/'.$song->SongURL.'">Link</a>' : '-'?></td>
                    <?php if ($isRapparManaged): ?>
                        <td><?=$song->SongGradeAdam ?: "Nieoceniona"?></td>
                        <td><?=$song->SongGradeChurchie ?: "Nieoceniona"?></td>
                    <?php else: ?>
                        <td><?=$song->SongGradeOwner ?: "Nieoceniona"?></td>
                    <?php endif; ?>
                    <td><a href="<?=base_url('playlistItems/updatePlaylistSongVisibility?songId='.$song->id.'&src='.$redirectSource)?>"><?=$song->SongVisible ? "Ukryj" : "Upublicznij"?></a></td>
                    <td><a href="<?=base_url('playlist/delSong?songId='.$song->id.'&src='.$redirectSource)?>">Usuń</a></td>
                <?php endif; ?>
                <td><a href="<?=base_url('playlistItems/showLog?songId='.$song->id.'&src='.$redirectSource)?>">Historia</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <h3>Ta playlista nie posiada żadnych załadowanych tracków.</h3>
<?php endif; ?>