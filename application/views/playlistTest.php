<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">Powrót do playlist</a>
    <a class="optionsURL" href="#bottom">Dół Listy</a>
    <a class="optionsURL" href="#songsForm">Góra Listy</a>
    <select id="selectbox" class="optionsURL" onchange="javascript:location.href = this.value;">
        <option value="">Pokaż oceny:</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId)?>">Wszystkie oceny</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId . "&Reviewer=Adam")?>">Najlepsze: Adam</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId . "&Reviewer=Churchie")?>">Najlepsze: Kościelny</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId . "&Reviewer=Average")?>">Najlepsze: Średnia</option>
    </select>
    <form class="optionsURL" method="get" action="<?=base_url("playlist")?>">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="hidden" name="ListId" value="<?=$ListId?>" />
        <input type="text" placeholder="Rajaner" name="Search" />
        <input type="submit" value="Szukaj" />
    </form>
    <?php if(isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer"): ?>
        <?php if(count($songs) > 0): ?>
            <input type="submit" class="optionsURL" value="Zapisz oceny" form="songsForm"/>
        <?php endif; ?>
        <a class="optionsURL" href="<?=base_url("downloadSongs?ListId=" . $ListId)?>">Załaduj nowe nuty</a>
        <a class="optionsURL" href="<?=base_url('playlist/details?id='.$ListId)?>">Statystyki i Ustawienia</a>
    <?php endif; ?>
</header>
<h2>Przeglądasz playlistę <?=$ListName ?? "o nieznanej nazwie"?>!</h2>
<form id="songsForm" method="post" action="<?=base_url('updateGradesTest')?>">
	<?php if(count($songs) > 0): ?>
		<?php
        $i = 0;
        foreach($songs as $song):?>
			<div class="videoContainer">
				<img src="<?=$song->SongThumbnailURL?>" width="250" height="140" alt="thumbnail" class="songThumbnailLeft" />
				<div class="dataContainer">
                    <input type="hidden" name="songId-<?=$i?>" value="<?=$song->SongId?>"/>
					<h3 class="songTitle"><a href="https://youtu.be/<?=$song->SongURL?>" target="_blank"><?=$song->SongTitle?></a></h3>
						<h4 class="dataContainer--gradeContainer">
							<label>Adam:</label>
                            <input name="nwGradeA-<?=$i+1?>" class="gradeInput" type="number" value="<?=$song->SongGradeAdam ?? 'Nieoceniona'?>" <?=$reviewer ? "" : "disabled" ?> min="0" max="15"/>
						</h4>
						<h4 class="dataContainer--gradeContainer">
							<label>Kościelny:</label>
                            <input name="nwGradeC-<?=$i+2?>" class="gradeInput" type="number" value="<?=$song->SongGradeChurchie ?? 'Nieoceniona'?>" <?=$reviewer ? "" : "disabled" ?> min="0" max="15"/>
						</h4>
						<h5 class="dataContainer--gradeContainer">
                            <label>Średnia:</label>
							<input type="text" value="<?=is_numeric($song->SongGradeAdam) && is_numeric($song->SongGradeChurchie) ? (($song->SongGradeAdam + $song->SongGradeChurchie) / 2) : "Nieoceniona"?>" disabled />
						</h5>
						<?php  //only 1 list means there is nowhere to move the song to
						if(count($lists) > 1 && $reviewer): ?>
							<h5 class="dataContainer--gradeContainer">
                                <label>Przenieś do:</label>
								<select name="<?="nwPlistId-".$i+3?>">
									<option value="0">Nie przenoś</option>
									<?php foreach($lists as $list):
										//Do not show the current list in the options
										if($list->ListId !== $ListId):?>
											<option value="<?=$list->ListId?>"><?=$list->ListName?></option>
										<?php endif;
									endforeach; ?>
								</select>
							</h5>
						<?php else: ?>
                            <select style="display:none;" name="<?="playlistId-".$song->SongId?>">
                                <option value="0">Nie przenoś</option>
                            </select>
                        <?php endif;?>
				</div>
				<img src="<?=$song->SongThumbnailURL?>" width="250" height="140" alt="thumbnail" class="songThumbnailRight" />
			</div>
		<?php
        $i += 4;
        endforeach;?>
	<?php else: ?>
		<h3>Ta playlista jest pusta mordo, nowy sezon już wkrótce!</h3>
	<?php endif; ?>
    <input type="hidden" name="playlistId" value="<?=$ListId?>"/>
</form>
<span id="bottom"></span>
<script type="text/javascript" src="<?=base_url( 'scripts/playlist.js' )?>"></script>
