<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?=!empty($title) ? ($title.' - RAPPAR') : 'Portal do oceniania utworów rapowanych - RAPPAR'?></title>
        <link rel="stylesheet" href="<?=base_url('styles/grid.css')?>">
        <link rel="shortcut icon" href="<?=base_url('styles/icons/favicon.ico')?>" type="image/x-icon">
        <link rel="icon" href="<?=base_url('styles/icons/favicon.ico')?>" type="image/x-icon">
    </head>
    <body>
        <?php isset($body) ? $this->load->view($body) : redirect(base_url()); ?>
        <footer>
            <br><p class="footer">LanternCode 2019-<?=date('Y')?> &copy; All rights reserved.</p>
        </footer>
    </body>
</html>