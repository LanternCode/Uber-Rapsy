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
        <?php isset($body) ? $this->load->view($body) : redirect(base_url()); ?>
        <footer>
            <br><p class="footer">LanternCode 2019-<?=date('Y')?> &copy; All rights reserved.</p>
        </footer>
    </body>
</html>