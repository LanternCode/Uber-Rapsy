<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">Powrót do playlist</a>
    <a class="optionsURL" href="#bottom">Dół Listy</a>
    <a class="optionsURL" href="#songsForm">Góra Listy</a>
    <p class="optionsURL">Zintegrowana: <?=$ListIntegrated ? "<a target='_blank' href='https://www.youtube.com/playlist?list=$ListUrl'>Tak</a>" : "Nie"?></p>
    <?php if(isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer"): ?>
        <?php if(count($songs) > 0): ?>
            <input type="submit" class="optionsURL" value="Zapisz oceny" form="songsForm"/>
        <?php endif; ?>
        <a class="optionsURL" href="<?=base_url("downloadSongs?ListId=" . $ListId)?>">Załaduj nowe nuty</a>
        <a class="optionsURL" href="<?=base_url('playlist/details?id='.$ListId)?>">Statystyki i Ustawienia</a>
    <?php endif; ?>
    <select id="selectbox" class="optionsURL" onchange="javascript:location.href = this.value;">
        <option value="">Pokaż oceny:</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId)?>">Wszystkie oceny</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId . "&Reviewer=Adam")?>">Najlepsze: Adam</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId . "&Reviewer=Churchie")?>">Najlepsze: Kościelny</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId . "&Reviewer=Average")?>">Najlepsze: Średnia</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId . "&Reviewer=Repeat")?>">Ponowny Odsłuch</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId . "&Reviewer=Unrated")?>">Nieoceniona</option>
    </select>
    <form class="optionsURL optionsRight" method="get" action="<?=base_url("playlist")?>">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="hidden" name="ListId" value="<?=$ListId?>" />
        <input type="text" placeholder="Rajaner" name="Search" />
        <input type="submit" value="Szukaj" />
    </form>
</header>
<h2>Przeglądasz playlistę <?=$ListName ?? "o nieznanej nazwie"?>!</h2>
<form id="songsForm" method="post" action="<?=base_url('updateGrades')?>">
	<?php if(count($songs) > 0):
        $i = 0; ?>
        <h3>Liczba nut: <?=count($songs)?></h3>
        <h4>Średnia Ocen Playlisty: <?=number_format($avgOverall, 2)?> (<?=$ratedCount?>)</h4>
        <h4>Średnia Ocen (Adam): <?=number_format($avgAdam, 2)?></h4>
        <h4>Średnia Ocen (Kościelny): <?=number_format($avgChurchie, 2)?></h4>
		<?php foreach($songs as $song): ?>
            <div class="videoContainer">
				<img src="<?=$song->SongThumbnailURL?>" alt="thumbnail" class="songThumbnailLeft" />
				<div class="dataContainer">
                    <input type="hidden" name="songId-<?=$i?>" value="<?=$song->SongId?>"/>
                    <h3 class="songTitle"><a href="https://youtu.be/<?=$song->SongURL?>" target="_blank"><?=$song->SongTitle?></a> (<a target='_blank' href="<?=base_url('song/rev?id='.$song->SongId)?>">+</a>)</h3>
                    <h4 class="dataContainer--gradeContainer">
                        <label>Adam:</label>
                        <?=$song->SongGradeAdam ?? 'Nieoceniona'?> ->
                        <input name="nwGradeA-<?=$i+1?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                               value="<?=$song->SongGradeAdam ?? 'Nieoceniona'?>" <?=$reviewer ? "" : "disabled" ?>/>
                    </h4>
                    <h4 class="dataContainer--gradeContainer">
                        <label>Kościelny:</label>
                        <?=$song->SongGradeChurchie ?? 'Nieoceniona'?> ->
                        <input name="nwGradeC-<?=$i+2?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                               value="<?=$song->SongGradeChurchie ?? 'Nieoceniona'?>" <?=$reviewer ? "" : "disabled" ?>/>
                    </h4>
                    <h5 class="dataContainer--gradeContainer">
                        <label>Średnia:</label>
                        <input type="text" value="<?=is_numeric($song->SongGradeAdam) && is_numeric($song->SongGradeChurchie) ? (($song->SongGradeAdam + $song->SongGradeChurchie) / 2) : "Nieoceniona"?>" disabled />
                    </h5>
						<?php  //only 1 list means there is nowhere to move or copy the song to
						if(count($lists) > 1 && $reviewer): ?>
							<h5 class="dataContainer--gradeContainer" hidden>
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
                            <h5 class="dataContainer--gradeContainer">
                                <label>Kopiuj do:</label>
                                <select name="<?="copyPlistId-".$i+20?>">
                                    <option value="0">Nie kopiuj</option>
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
                            <select style="display:none;" name="<?="copyPlistId-".$i+20?>">
                                <option value="0">Nie kopiuj</option>
                            </select>
                        <?php endif;?>
                    <label <?=$playlist->btnRehearsal ? '' : 'hidden'?>><input type="hidden" name="<?="songRehearsal-".$i+4?>" value="<?=$song->SongRehearsal?>"><input type="checkbox" <?=$song->SongRehearsal ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Do ponownego odsłuchu</label>
                    <label <?=$playlist->btnBelowFour ? '' : 'hidden'?>><input type="hidden" name="<?="songBelFour-".$i+17?>" value="<?=$song->SongBelFour?>"><input type="checkbox" <?=$song->SongBelFour ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 4</label>
                    <label <?=$playlist->btnBelowSeven ? '' : 'hidden'?>><input type="hidden" name="<?="songBelow-".$i+13?>" value="<?=$song->SongBelow?>"><input type="checkbox" <?=$song->SongBelow ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 7</label>
                    <label <?=$playlist->btnBelowEight ? '' : 'hidden'?>><input type="hidden" name="<?="songBelEight-".$i+16?>" value="<?=$song->SongBelEight?>"><input type="checkbox" <?=$song->SongBelEight ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 8</label>
                    <label <?=$playlist->btnBelowNine ? '' : 'hidden'?>><input type="hidden" name="<?="songBelNine-".$i+15?>" value="<?=$song->SongBelNine?>"><input type="checkbox" <?=$song->SongBelNine ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 9</label>
                    <label <?=$playlist->btnBelowTen ? '' : 'hidden'?>><input type="hidden" name="<?="songBelTen-".$i+14?>" value="<?=$song->SongBelTen?>"><input type="checkbox" <?=$song->SongBelTen ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 10</label>
                    <label <?=$playlist->btnDistinction ? '' : 'hidden'?>><input type="hidden" name="<?="songDistinction-".$i+5?>" value="<?=$song->SongDistinction?>"><input type="checkbox" <?=$song->SongDistinction ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Wyróżnienie</label>
                    <label <?=$playlist->btnDuoTen ? '' : 'hidden'?>><input type="hidden" name="<?="songDuoTen-".$i+18?>" value="<?=$song->SongDuoTen?>"><input type="checkbox" <?=$song->SongDuoTen ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> "10"</label>
                    <label <?=$playlist->btnMemorial ? '' : 'hidden'?>><input type="hidden" name="<?="songMemorial-".$i+6?>" value="<?=$song->SongMemorial?>"><input type="checkbox" <?=$song->SongMemorial ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> 10*</label>
                    <label <?=$playlist->btnUber ? '' : 'hidden'?>><input type="hidden" name="<?="songUber-".$i+12?>" value="<?=$song->SongUber?>"><input type="checkbox" <?=$song->SongUber ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Uber</label>
                    <label <?=$playlist->btnTop ? '' : 'hidden'?>><input type="hidden" name="<?="songTop-".$i+10?>" value="<?=$song->SongTop?>"><input type="checkbox" <?=$song->SongTop ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> X15</label>
                    <label <?=$playlist->btnXD ? '' : 'hidden'?>><input type="hidden" name="<?="songXD-".$i+7?>" value="<?=$song->SongXD?>"><input type="checkbox" <?=$song->SongXD ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> XD</label>
                    <label <?=$playlist->btnDiscomfort ? '' : 'hidden'?>><input type="hidden" name="<?="songDiscomfort-".$i+9?>" value="<?=$song->SongDiscomfort?>"><input type="checkbox" <?=$song->SongDiscomfort ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Strefa Dyskomfortu</label>
                    <label <?=$playlist->btnNotRap ? '' : 'hidden'?>><input type="hidden" name="<?="songNotRap-".$i+8?>" value="<?=$song->SongNotRap?>"><input type="checkbox" <?=$song->SongNotRap ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> To nie rapsik</label>
                    <label <?=$playlist->btnNoGrade ? '' : 'hidden'?>><input type="hidden" name="<?="songNoGrade-".$i+11?>" value="<?=$song->SongNoGrade?>"><input type="checkbox" <?=$song->SongNoGrade ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Nie oceniam</label>
                    <label <?=$playlist->btnVeto ? '' : 'hidden'?>><input type="hidden" name="<?="songVeto-".$i+19?>" value="<?=$song->SongVeto?>"><input type="checkbox" <?=$song->SongVeto ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> VETO</label>
				</div>
			</div>
		<?php
        $i += 21;
        endforeach;?>
	<?php else: ?>
		<h3>Ta playlista jest pusta mordo, nowy sezon już wkrótce!</h3>
	<?php endif; ?>
    <input type="hidden" name="playlistId" value="<?=$ListId?>"/>
</form>
<span id="bottom"></span>
<script type="text/javascript" src="<?=base_url( 'scripts/playlist.js' )?>"></script>
