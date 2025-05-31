<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">UberRapsy</a>
    <?php if (isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']): ?>
        <a class="optionsURL" href="<?=base_url("myPlaylists")?>">Moje Playlisty</a>
        <a class="optionsURL" href="<?=base_url("importSongs")?>">Dodaj Nowe Nuty</a>
        <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'reviewer'): ?>
            <a class="optionsURL" href="<?=base_url("")?>">Dodaj Toplistę</a>
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
        <h3>Nie znaleziono żadnych utworów do utworzenia toplisty!</h3>
    <?php endif; ?>
</main>