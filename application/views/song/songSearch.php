<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">UberRapsy</a>
    <?php if (isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']): ?>
        <a class="optionsURL" href="<?=base_url("myPlaylists")?>">Moje Playlisty</a>
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
</main>