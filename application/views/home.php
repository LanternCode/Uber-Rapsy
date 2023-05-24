<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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
    <form class="omniNav--Option optionsRight" method="get" action="<?=base_url("search")?>" target="_blank">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="text" placeholder="Rajaner" name="Search" />
        <input type="submit" value="Szukaj" />
    </form>
</nav>
<h1>Uber Rapsy!</h1>
<h2>Polski portal do oceniania utworów rapowanych, których można słuchać w domu, w aucie, albo w jakimś innym miejscu.</h2>
<br /><br />
<?php if(isset($lists) && count($lists) > 0): ?>
	<h3>Nasze playlisty:</h3>
	<table>
		<tr>
			<th>Playlista</th>
			<th>Opis</th>
		</tr>
		<?php foreach($lists as $list): ?>
			<tr>
				<td>
					<a href="<?=base_url("playlist?ListId=" . $list->ListId)?>">
						<div>
							<?=$list->ListName;?>
						</div>
					</a><br />
				</td>
				<td>
					<?=$list->ListDesc?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php else: ?>
	<h3>Strona jest obecnie w przebudowie, zapraszamy wkrótce!</h3>
<?php endif; ?>

