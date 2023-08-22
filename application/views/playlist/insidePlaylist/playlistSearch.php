<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">UberRapsy</a>
    <a class="optionsURL" href="#bottom">Dół Listy</a>
    <a class="optionsURL" href="#songsForm">Góra Listy</a>
    <?php if(isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer" && count($songs) > 0): ?>
        <input type="submit" class="optionsURL" value="Zapisz oceny" form="songsForm"/>
    <?php endif; ?>
    <form class="optionsURL optionsRight" method="get" action="<?=base_url("playlist")?>">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="text" placeholder="Rajaner" name="SearchQuery" />
        <input type="hidden" value="true" name="GlobalSearch" />
        <input type="submit" value="Szukaj" />
    </form>
</nav>
<br><br><br>
<h2>Wyniki wyszukiwania!</h2>
	<?php if(count($songs) > 0): ?>
        <h3>Liczba nut: <?=count($songs)?></h3>
        <form id="songsForm" method="post" action="<?=base_url('updateSelection')?>">
		<?php
        $i = 0;
        foreach($songs as $key => $song):?>
			<div class="videoContainerBox">
				<img src="<?=$song->SongThumbnailURL?>" width="" height="" alt="thumbnail" class="songThumbnailLeft" />
				<div class="dataContainerBox">
                    <input type="hidden" name="songId-<?=$i?>" value="<?=$song->SongId?>"/>
                    <h4>Z playlisty: <?=$playlist[$key]->ListName?></h4>
                    <h3 class="songTitle"><a href="https://youtu.be/<?=$song->SongURL?>" target="_blank"><?=$song->SongTitle?></a> (<a target='_blank' href="<?=base_url('song/rev?id='.$song->SongId)?>">+</a>)</h3>
                    <div class="dataContainerBox--split">
                        <div class="dataContainerBox--split__left">
                            <h4 class="dataContainer--gradeContainer">
                                <label>Adam: <?=$song->SongGradeAdam ?? 'Nieoceniona'?> -></label>
                                <input name="nwGradeA-<?=$i+1?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                       value="<?=$song->SongGradeAdam ?? 'Nieoceniona'?>" <?=$Reviewer ? "" : "disabled" ?>/>
                            </h4>
                            <h4 class="dataContainer--gradeContainer">
                                <label>Kościelny: <?=$song->SongGradeChurchie ?? 'Nieoceniona'?> -></label>
                                <input name="nwGradeC-<?=$i+2?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                       value="<?=$song->SongGradeChurchie ?? 'Nieoceniona'?>" <?=$Reviewer ? "" : "disabled" ?>/>
                            </h4>
                            <h5 class="dataContainer--gradeContainer">
                                <label>Średnia:</label>
                                <input class="gradeInput" type="text" value="<?=is_numeric($song->SongGradeAdam) && is_numeric($song->SongGradeChurchie) ? (($song->SongGradeAdam + $song->SongGradeChurchie) / 2) : "Nieoceniona"?>" disabled />
                            </h5>
                            <?php  //only 1 list means there is nowhere to move or copy the song to
                            if(count($lists) > 1 && $Reviewer): ?>
                                <h5 class="dataContainer--gradeContainer">
                                    <label>Przenieś do:</label>
                                    <select name="<?="nwPlistId-".$i+3?>" class="selectBox">
                                        <option value="0">Nie przenoś</option>
                                        <?php foreach($lists as $list):
                                            if($list->ListId !== $playlist[$key]->ListId):?>
                                                <option value="<?=$list->ListId?>"><?=$list->ListName?></option>
                                            <?php endif;
                                        endforeach; ?>
                                    </select>
                                </h5>
                                <h5 class="dataContainer--gradeContainer">
                                    <label>Kopiuj do:</label>
                                    <select name="<?="copyPlistId-".$i+20?>" class="selectBox">
                                        <option value="0">Nie kopiuj</option>
                                        <?php foreach($lists as $list):
                                            if($list->ListId !== $playlist[$key]->ListId):?>
                                                <option value="<?=$list->ListId?>"><?=$list->ListName?></option>
                                            <?php endif;
                                        endforeach; ?>
                                    </select>
                                </h5>
                            <?php else: ?>
                                <select style="display:none;" name="<?="nwPlistId-".$i+3?>">
                                    <option value="0">Nie przenoś</option>
                                </select>
                                <select style="display:none;" name="<?="copyPlistId-".$i+20?>">
                                    <option value="0">Nie kopiuj</option>
                                </select>
                            <?php endif;?>
                        </div>
                        <div class="dataContainerBox--split__right">
                            <textarea rows="8" cols="40" class="commentBox" name="songComment-<?=$i+22?>"><?=$song->SongComment?></textarea>
                        </div>
                    </div>
                    <label <?=$playlist[$key]->btnRehearsal ? '' : 'hidden'?>><input type="hidden" name="<?="songRehearsal-".$i+4?>" value="<?=$song->SongRehearsal?>"><input type="checkbox" class="buttonBox" <?=$song->SongRehearsal ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Do ponownego odsłuchu</label>
                    <label <?=$playlist[$key]->btnBelowFour ? '' : 'hidden'?>><input type="hidden" name="<?="songBelFour-".$i+17?>" value="<?=$song->SongBelFour?>"><input type="checkbox" class="buttonBox" <?=$song->SongBelFour ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 4</label>
                    <label <?=$playlist[$key]->btnBelowSeven ? '' : 'hidden'?>><input type="hidden" name="<?="songBelow-".$i+13?>" value="<?=$song->SongBelow?>"><input type="checkbox" class="buttonBox" <?=$song->SongBelow ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 7</label>
                    <label <?=$playlist[$key]->btnBelowHalfSeven ? '' : 'hidden'?>><input type="hidden" name="<?="SongBelHalfSeven-".$i+23?>" value="<?=$song->SongBelHalfSeven?>"><input type="checkbox" class="buttonBox" <?=$song->SongBelHalfSeven ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 7.5</label>
                    <label <?=$playlist[$key]->btnBelowEight ? '' : 'hidden'?>><input type="hidden" name="<?="songBelEight-".$i+16?>" value="<?=$song->SongBelEight?>"><input type="checkbox" class="buttonBox" <?=$song->SongBelEight ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 8</label>
                    <label <?=$playlist[$key]->btnBelowHalfEight ? '' : 'hidden'?>><input type="hidden" name="<?="SongBelHalfEight-".$i+24?>" value="<?=$song->SongBelHalfEight?>"><input type="checkbox" class="buttonBox" <?=$song->SongBelHalfEight ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 8.5</label>
                    <label <?=$playlist[$key]->btnBelowNine ? '' : 'hidden'?>><input type="hidden" name="<?="songBelNine-".$i+15?>" value="<?=$song->SongBelNine?>"><input type="checkbox" class="buttonBox" <?=$song->SongBelNine ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 9</label>
                    <label <?=$playlist[$key]->btnBelowHalfNine ? '' : 'hidden'?>><input type="hidden" name="<?="SongBelHalfNine-".$i+25?>" value="<?=$song->SongBelHalfNine?>"><input type="checkbox" class="buttonBox" <?=$song->SongBelHalfNine ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 9.5</label>
                    <label <?=$playlist[$key]->btnBelowTen ? '' : 'hidden'?>><input type="hidden" name="<?="songBelTen-".$i+14?>" value="<?=$song->SongBelTen?>"><input type="checkbox" class="buttonBox" <?=$song->SongBelTen ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 10</label>
                    <label <?=$playlist[$key]->btnDistinction ? '' : 'hidden'?>><input type="hidden" name="<?="songDistinction-".$i+5?>" value="<?=$song->SongDistinction?>"><input type="checkbox" class="buttonBox" <?=$song->SongDistinction ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Wyróżnienie</label>
                    <label <?=$playlist[$key]->btnDuoTen ? '' : 'hidden'?>><input type="hidden" name="<?="songDuoTen-".$i+18?>" value="<?=$song->SongDuoTen?>"><input type="checkbox" class="buttonBox" <?=$song->SongDuoTen ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> "10"</label>
                    <label <?=$playlist[$key]->btnMemorial ? '' : 'hidden'?>><input type="hidden" name="<?="songMemorial-".$i+6?>" value="<?=$song->SongMemorial?>"><input type="checkbox" class="buttonBox" <?=$song->SongMemorial ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> 10*</label>
                    <label <?=$playlist[$key]->btnUber ? '' : 'hidden'?>><input type="hidden" name="<?="songUber-".$i+12?>" value="<?=$song->SongUber?>"><input type="checkbox" class="buttonBox" <?=$song->SongUber ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Uber</label>
                    <label <?=$playlist[$key]->btnTop ? '' : 'hidden'?>><input type="hidden" name="<?="songTop-".$i+10?>" value="<?=$song->SongTop?>"><input type="checkbox" class="buttonBox" <?=$song->SongTop ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> X15</label>
                    <label <?=$playlist[$key]->btnXD ? '' : 'hidden'?>><input type="hidden" name="<?="songXD-".$i+7?>" value="<?=$song->SongXD?>"><input type="checkbox" class="buttonBox" <?=$song->SongXD ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> XD</label>
                    <label <?=$playlist[$key]->btnDiscomfort ? '' : 'hidden'?>><input type="hidden" name="<?="songDiscomfort-".$i+9?>" value="<?=$song->SongDiscomfort?>"><input type="checkbox" class="buttonBox" <?=$song->SongDiscomfort ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Strefa Dyskomfortu</label>
                    <label <?=$playlist[$key]->btnNotRap ? '' : 'hidden'?>><input type="hidden" name="<?="songNotRap-".$i+8?>" value="<?=$song->SongNotRap?>"><input type="checkbox" class="buttonBox" <?=$song->SongNotRap ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> To nie rapsik</label>
                    <label <?=$playlist[$key]->btnNoGrade ? '' : 'hidden'?>><input type="hidden" name="<?="songNoGrade-".$i+11?>" value="<?=$song->SongNoGrade?>"><input type="checkbox" class="buttonBox" <?=$song->SongNoGrade ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Nie oceniam</label>
                    <label <?=$playlist[$key]->btnVeto ? '' : 'hidden'?>><input type="hidden" name="<?="songVeto-".$i+19?>" value="<?=$song->SongVeto?>"><input type="checkbox" class="buttonBox" <?=$song->SongVeto ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> VETO</label>
                    <input type="hidden" name="songUpdated-<?=$i+21?>" value="0">
                </div>
			</div>
		<?php
        $i += 26;
        endforeach;?>
            <input type="hidden" name="playlistId" value="search"/>
        </form>
	<?php else: ?>
		<h3>Nie znaleziono nic o podanej nazwie!</h3>
	<?php endif; ?>
<span id="bottom"></span>
<script type="text/javascript" src="<?=base_url( 'scripts/playlist.js' )?>"></script>