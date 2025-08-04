<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">UberRapsy</a>
    <?php if (isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']): ?>
        <a class="optionsURL" href="<?=base_url("myPlaylists")?>">Moje Konto i Playlisty</a>
    <?php endif; ?>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <?php if (isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']): ?>
        <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'reviewer'): ?>
            <a class="optionsURL" href="<?=base_url("adminDashboard")?>">Panel Sterowania</a>
        <?php endif; ?>
        <a class="optionsURL" href="<?=base_url("logout")?>">Wyloguj się</a>
    <?php else: ?>
        <a class="optionsURL" href="<?=base_url("login")?>">Zaloguj się</a>
    <?php endif; ?>
    <form class="optionsURL optionsRight" method="get" action="<?=base_url("songSearch")?>">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="text" placeholder="Strumień" name="searchQuery" required/>
        <input type="submit" value="Szukaj" />
    </form>
</header>
<main>
    <br><br><br>
    <h1>Wyniki wyszukiwania<?= count($songs) > 0 ? " (znaleziono ".
    ($c = count($songs)).' '.(
        ($c % 10 === 1 && $c % 100 !== 11)
            ? ' utwór'
            : (
                ($c % 10 >= 2 && $c % 10 <= 4 && ($c % 100 < 12 || $c % 100 > 14))
                    ? ' utwory'
                    : ' utworów'
            )
    ).'!)' : "";
    ?></h1>
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
            <?php foreach ($songs as $song): ?>
                <tr>
                    <td>
                        <?php if ($song->SongDeleted): ?>
                            <?=$song->SongTitle?> (usunięta)
                            <a target="_blank" href="<?=base_url('song/showLog?songId='.$song->SongId)?>" title="Wyświetl logi utworu">📄️</a>
                            <a href="<?=base_url('user/details?uid='.$song->SongAddedBy)?>" title="Pokaż profil autora utworu">👤</a>
                        <?php else: ?>
                            <a href="<?=base_url('songPage?songId='.$song->SongId.'&query='.$searchQuery)?>"><?=$song->SongTitle?></a> <?=$song->SongVisible ? '' : '(ukryta)'?>
                            <a href="<?=base_url('song/reviewSong?songId='.$song->SongId)?>" title="Recenzuj utwór">📝</a>
                            <a href="<?=base_url('song/addToPlaylist?songId='.$song->SongId.'&query='.$searchQuery)?>" title="Dodaj utwór do playlisty">➕</a>
                            <?php if ($isReviewer): ?>
                                <a href="<?=base_url('song/edit?songId='.$song->SongId)?>" title="Edytuj utwór">🔧</a>
                                <a href="<?=base_url('song/updateVisibility?songId='.$song->SongId.'&src=search&query='.$searchQuery)?>" title="Pokaż lub ukryj utwór">👁️</a>
                                <a target="_blank" href="<?=base_url('song/showLog?songId='.$song->SongId)?>" title="Wyświetl logi utworu">📄️</a>
                                <a href="<?=base_url('user/details?uid='.$song->SongAddedBy)?>" title="Pokaż profil autora utworu">👤</a>
                                <a target="_blank" href="<?=base_url('song/awards?songId='.$song->SongId)?>" title="Zarządzaj nagrodami utworu">🏆</a>
                                <a href="<?=base_url('song/deleteSong?songId='.$song->SongId.'&src=search&query='.$searchQuery)?>" title="Usuń utwór">❌</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td><?=$song->myGrade != 0 ? $song->myGrade : '❌' ?></td>
                    <td><?=$song->communityAverage != 0 ? $song->communityAverage : '❌'?></td>
                    <?php foreach($song->awards as $award): ?>
                        <td><p class="song-awards centered"><?=$award->award?></p></td>
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
        <?php if (!empty($_SESSION['userLoggedIn'])): ?>
            <h4>Upewnij się, że wpisana nazwa utworu jest poprawna. Jeśli tak, możesz <a href="<?=base_url('importSongs')?>">kliknąć tutaj</a> i dodać utwór do RAPPAR!</h4>
        <?php else: ?>
            <h4>Upewnij się, że wpisana nazwa utworu jest poprawna. Jeśli tak, <a href="<?=base_url('login?src=songSearch')?>">zaloguj się</a> i dodaj utwór do RAPPAR!</h4>
        <?php endif; ?>
    <?php endif; ?>
</main>