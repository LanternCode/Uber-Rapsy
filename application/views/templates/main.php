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
		<nav class="omniNav">
			<a class="omniNav--Option" href="<?=base_url()?>">UberRapsy</a>
            <p class="omniNav--Option">Status: <?=(isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']
                    && isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer") ? "Recenzent" : "Gość"?></p>
            <?php if(isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']): ?>
                <a class="omniNav--Option" href="<?=base_url("loginYoutube")?>">Panel Sterowania YT</a>
                <a class="omniNav--Option" href="<?=base_url("logout")?>">Wyloguj się</a>
            <?php else: ?>
                <a class="omniNav--Option" href="<?=base_url("login")?>">Zaloguj się</a>
            <?php endif; ?>
		</nav>
	    <main>
	        <?php isset($body) ? $this->load->view($body) : redirect(base_url()); ?>
	    </main>
		<footer>
			<br><p class="footer">UberRapsy 2019-<?=date('Y')?> &copy; All rights reserved.</p>
		</footer>
	</body>
</html>
