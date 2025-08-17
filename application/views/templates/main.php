<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?=!empty($title) ? ($title.' - RAPPAR') : 'Portal do oceniania utworów rapowanych - RAPPAR'?></title>
		<link rel="stylesheet" href="<?=base_url('styles/grid.css')?>">
        <link rel="icon" type="image/png" href="<?=base_url('styles/icons/favicon-96x96.png')?>" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="<?=base_url('styles/icons/favicon.svg')?>" />
        <link rel="shortcut icon" href="<?=base_url('styles/icons/favicon.ico')?>" />
        <link rel="apple-touch-icon" sizes="180x180" href="<?=base_url('styles/icons/apple-touch-icon.png')?>" />
        <link rel="manifest" href="<?=base_url('site.webmanifest')?>" />
	</head>
	<body>
        <header class="optionsHeader">
            <a class="optionsURL" href="<?=base_url()?>">RAPPAR</a>
            <?php if ($userLoggedIn ?? false): ?>
                <a class="optionsURL" href="<?=base_url("myPlaylists")?>">Moje Konto i Playlisty</a>
            <?php endif; ?>
            <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
            <?php if ($isReviewer ?? false): ?>
                <a class="optionsURL" href="<?=base_url("adminDashboard")?>">Panel Sterowania</a>
            <?php endif; ?>
            <?php if ($userLoggedIn ?? false): ?>
                <a class="optionsURL" href="<?=base_url("logout")?>">Wyloguj się</a>
            <?php else: ?>
                <a class="optionsURL" href="<?=base_url("login")?>">Zaloguj się</a>
            <?php endif; ?>
            <form class="optionsURL optionsRight" method="get" action="<?=base_url("search")?>" target="_blank">
                <label class="optionsSearchLabel">Szukaj nuty</label>
                <input type="text" placeholder="Rajaner" name="SearchQuery" />
                <input type="hidden" value="true" name="GlobalSearch" />
                <input type="submit" value="Szukaj" />
            </form>
        </header>
	    <main>
            <br><br><br>
	        <?php isset($body) ? $this->load->view($body) : redirect(base_url()); ?>
	    </main>
		<footer>
			<br><p class="footer">LanternCode 2019-<?=date('Y')?> &copy; All rights reserved.</p>
		</footer>
	</body>
</html>