<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= $title ?? 'Uber Rapsy! | Portal do oceniania utworów rapowanych'; ?></title>
		<link rel="stylesheet" href="<?=base_url( 'styles/grid.css' )?>">
		<link rel="shortcut icon" href="<?=base_url( 'styles/icons/favicon.ico' )?>" type="image/x-icon">
		<link rel="icon" href="<?=base_url( 'styles/icons/favicon.ico' )?>" type="image/x-icon">
	</head>
	<body>
        <header class="optionsHeader">
            <a class="optionsURL" href="<?=base_url()?>">UberRapsy</a>
            <?php if(isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']): ?>
                <a class="optionsURL" href="<?=base_url("myPlaylists")?>">Moje Playlisty</a>
                <?php if($_SESSION['userRole'] === 'reviewer'): ?>
                    <a class="optionsURL" href="<?=base_url("songsToplist")?>">Toplisty RAPPAR</a>
                    <a class="optionsURL" href="<?=base_url("adminDashboard")?>">Panel Sterowania</a>
                <?php endif; ?>
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
			<br><p class="footer">UberRapsy 2019-<?=date('Y')?> &copy; All rights reserved.</p>
		</footer>
	</body>
</html>
