<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= isset( $title ) ? $title : 'Uber Rapsy!'; ?></title>
		<link rel="stylesheet" href="<?=base_url( 'styles/grid.css' )?>">
		<link rel="shortcut icon" href="<?=base_url( 'favicon.ico' )?>" type="image/x-icon">
		<link rel="icon" href="<?=base_url( 'favicon.ico' )?>" type="image/x-icon">
	</head>
	<body>
		<nav>
			<a href="<?=base_url()?>">UberRapsy</a><br />
			<a href="<?=base_url("loginYoutube")?>">Panel Sterowania YT</a>
		</nav>
	    <main>
	        <?php isset( $body ) ? $this->load->view( $body ) : redirect( base_url() ); ?>
	    </main>
		<footer>
			<br />
		    <p class="footer">
				UberRapsy 2019-<?=date( 'Y' )?> &copy; All rights reserved.
				<br />
				Czas ładowania strony: <strong>{elapsed_time}</strong> sekund.
				<br />
				Stworzone przez LanternCode.
			</p>
		</footer>
	</body>
</html>
