<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= isset( $title ) ? $title : 'The Remnants Fighters!'; ?></title>
		<link rel="stylesheet" href="<?=base_url( 'styles/grid.css' )?>">
		<link rel="shortcut icon" href="<?=base_url( 'favicon.ico' )?>" type="image/x-icon">
		<link rel="icon" href="<?=base_url( 'favicon.ico' )?>" type="image/x-icon">
	</head>
	<body>
	    <main>
	        <?php isset( $body ) ? $this->load->view( $body ) : redirect( base_url() ); ?>
	    </main>
		<footer>
			<br />
		    <p class="footer">
				iLeanbox 2019-<?=date( 'Y' )?>
				&copy;
				All rights reserved.
				<br />
				Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?>
			</p>
		</footer>
	</body>
</html>
