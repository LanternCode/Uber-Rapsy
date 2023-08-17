<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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

