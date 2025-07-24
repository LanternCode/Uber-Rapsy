<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">UberRapsy</a>
    <a class="optionsURL" href="#bottom">Dół Listy</a>
    <a class="optionsURL" href="#songsForm">Góra Listy</a>
    <?php if (count($songs) > 0 && count($userOwnedPlaylistIDs) > 0): ?>
        <input type="submit" class="optionsURL" value="Zapisz oceny" form="songsForm"/>
    <?php endif; ?>
    <form class="optionsURL optionsRight" method="get" action="<?=base_url("search")?>">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="text" placeholder="Rajaner" name="SearchQuery" />
        <input type="hidden" value="true" name="GlobalSearch" />
        <input type="submit" value="Szukaj" />
    </form>
</nav>
<br><br><br>
<h2>Wyniki wyszukiwania!</h2>
	<?php if(count($songs) > 0 && count($songs) < 301): ?>
        <h3>Liczba nut: <?=count($songs)?></h3>
        <form id="songsForm" method="post" action="<?=base_url('updateGradesFromSearch')?>">
            <?php
            $i = 0;
            foreach($songs as $key => $song):
                $isPlaylistOwner = in_array($song->listId, $userOwnedPlaylistIDs);
                $rapparManagedPlaylist = (int) $playlist[$key]->ListOwnerId === 1; ?>
                <div class="videoContainerBox">
                    <img src="<?=$song->SongThumbnailURL?>" alt="thumbnail" class="songThumbnailLeft" />
                    <div class="dataContainerBox">
                        <input type="hidden" name="playlistSongId-<?=$i?>" value="<?=$song->id?>"/>
                        <h4>Z playlisty: <?=$playlist[$key]->ListName?></h4>
                        <h3 class="songTitle"><a href="https://youtu.be/<?=$song->SongURL?>" target="_blank"><?=$song->SongTitle?></a> (<a target='_blank' href="<?=base_url('song/rev?id='.$song->SongId)?>">+</a>)</h3>
                        <div class="dataContainerBox--split">
                            <div class="dataContainerBox--split__left">
                                <?php if ($rapparManagedPlaylist): ?>
                                    <h4 class="dataContainer--gradeContainer">
                                        <label>Adam: <?=$song->SongGradeAdam ?? 'Nieoceniona'?> -></label>
                                        <input name="newGradeAdam-<?=$i+1?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                               value="<?=$song->SongGradeAdam ?? 'Nieoceniona'?>" <?=$isReviewer ? "" : "disabled" ?>/>
                                    </h4>
                                    <h4 class="dataContainer--gradeContainer">
                                        <label>Kościelny: <?=$song->SongGradeChurchie ?? 'Nieoceniona'?> -></label>
                                        <input name="newGradeChurchie-<?=$i+2?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                               value="<?=$song->SongGradeChurchie ?? 'Nieoceniona'?>" <?=$isReviewer ? "" : "disabled" ?>/>
                                    </h4>
                                    <h5 class="dataContainer--gradeContainer">
                                        <label>Średnia:</label>
                                        <input class="gradeInput" type="text" value="<?=$song->Average ?? "Nieoceniona"?>" disabled />
                                    </h5>
                                <?php else: ?>
                                    <h4 class="dataContainer--gradeContainer">
                                        <label>Ocena Właściciela Playlisty: <?=$song->SongGradeOwner ?? 'Nieoceniona'?> -></label>
                                        <input name="myNewGrade-<?=$i+27?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                               value="<?=$song->SongGradeOwner ?? 'Nieoceniona'?>" <?=$isPlaylistOwner ? "" : "disabled" ?>/>
                                    </h4>
                                <?php endif;
                                //only 1 list means there is nowhere to move or copy the song to
                                if (count($userOwnedPlaylists) > 1 && $isPlaylistOwner): ?>
                                    <h5 class="dataContainer--gradeContainer">
                                        <label>Przenieś do:</label>
                                        <select name="<?="newPlaylistId-".$i+3?>" class="selectBox">
                                            <option value="0">Nie przenoś</option>
                                            <?php foreach ($userOwnedPlaylists as $list):
                                                if ($list->ListId !== $playlist[$key]->ListId):?>
                                                    <option value="<?=$list->ListId?>"><?=$list->ListName?></option>
                                                <?php endif;
                                            endforeach; ?>
                                        </select>
                                    </h5>
                                    <h5 class="dataContainer--gradeContainer">
                                        <label>Kopiuj do:</label>
                                        <select name="<?="copyPlaylistId-".$i+20?>" class="selectBox">
                                            <option value="0">Nie kopiuj</option>
                                            <?php foreach ($userOwnedPlaylists as $list):
                                                if ($list->ListId !== $playlist[$key]->ListId):?>
                                                    <option value="<?=$list->ListId?>"><?=$list->ListName?></option>
                                                <?php endif;
                                            endforeach; ?>
                                        </select>
                                    </h5>
                                <?php else: ?>
                                    <select style="display:none;" name="<?="newPlaylistId-".$i+3?>">
                                        <option value="0">Nie przenoś</option>
                                    </select>
                                    <select style="display:none;" name="<?="copyPlaylistId-".$i+20?>">
                                        <option value="0">Nie kopiuj</option>
                                    </select>
                                <?php endif;?>
                            </div>
                            <div class="dataContainerBox--split__right">
                                <textarea rows="8" cols="40" class="commentBox" name="songComment-<?=$i+22?>" <?=$isPlaylistOwner ? "" : "disabled" ?>><?=$song->SongComment?></textarea>
                            </div>
                        </div>
                        <label <?=$playlist[$key]->btnRehearsal ? '' : 'hidden'?>><input type="checkbox" name="<?="songRehearsal-".$i+4?>" class="buttonBox" <?=$song->SongRehearsal ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> Do ponownego odsłuchu</label>
                        <label <?=$playlist[$key]->btnBelowFour ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelFour-".$i+17?>" class="buttonBox" <?=$song->SongBelFour ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 4</label>
                        <label <?=$playlist[$key]->btnBelowSeven ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelow-".$i+13?>" class="buttonBox" <?=$song->SongBelow ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 7</label>
                        <label <?=$playlist[$key]->btnBelowHalfSeven ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfSeven-".$i+23?>" class="buttonBox" <?=$song->SongBelHalfSeven ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 7.5</label>
                        <label <?=$playlist[$key]->btnBelowEight ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelEight-".$i+16?>" class="buttonBox" <?=$song->SongBelEight ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 8</label>
                        <label <?=$playlist[$key]->btnBelowHalfEight ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfEight-".$i+24?>" class="buttonBox" <?=$song->SongBelHalfEight ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 8.5</label>
                        <label <?=$playlist[$key]->btnBelowNine ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelNine-".$i+15?>" class="buttonBox" <?=$song->SongBelNine ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 9</label>
                        <label <?=$playlist[$key]->btnBelowHalfNine ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfNine-".$i+25?>" class="buttonBox" <?=$song->SongBelHalfNine ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 9.5</label>
                        <label <?=$playlist[$key]->btnBelowTen ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelTen-".$i+14?>" class="buttonBox" <?=$song->SongBelTen ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 10</label>
                        <label <?=$playlist[$key]->btnDistinction ? '' : 'hidden'?>><input type="checkbox" name="<?="songDistinction-".$i+5?>" class="buttonBox" <?=$song->SongDistinction ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> Wyróżnienie</label>
                        <label <?=$playlist[$key]->btnDuoTen ? '' : 'hidden'?>><input type="checkbox" name="<?="songDuoTen-".$i+18?>" class="buttonBox" <?=$song->SongDuoTen ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> "10"</label>
                        <label <?=$playlist[$key]->btnMemorial ? '' : 'hidden'?>><input type="checkbox" name="<?="songMemorial-".$i+6?>" class="buttonBox" <?=$song->SongMemorial ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> 10*</label>
                        <label <?=$playlist[$key]->btnUber ? '' : 'hidden'?>><input type="checkbox" name="<?="songUber-".$i+12?>" class="buttonBox" <?=$song->SongUber ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> Uber</label>
                        <label <?=$playlist[$key]->btnTop ? '' : 'hidden'?>><input type="checkbox" name="<?="songTop-".$i+10?>" class="buttonBox" <?=$song->SongTop ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> X15</label>
                        <label <?=$playlist[$key]->btnXD ? '' : 'hidden'?>><input type="checkbox" name="<?="songXD-".$i+7?>" class="buttonBox" <?=$song->SongXD ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> XD</label>
                        <label <?=$playlist[$key]->btnDiscomfort ? '' : 'hidden'?>><input type="checkbox" name="<?="songDiscomfort-".$i+9?>" class="buttonBox" <?=$song->SongDiscomfort ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> Strefa Dyskomfortu</label>
                        <label <?=$playlist[$key]->btnDepA ? '' : 'hidden'?>><input type="checkbox" name="<?="songDepA-".$i+26?>" class="buttonBox" <?=$song->SongDepA ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> Dep. Abroad</label>
                        <label <?=$playlist[$key]->btnNotRap ? '' : 'hidden'?>><input type="checkbox" name="<?="songNotRap-".$i+8?>" class="buttonBox" <?=$song->SongNotRap ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> To nie rapsik</label>
                        <label <?=$playlist[$key]->btnNoGrade ? '' : 'hidden'?>><input type="checkbox" name="<?="songNoGrade-".$i+11?>" class="buttonBox" <?=$song->SongNoGrade ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> Nie oceniam</label>
                        <label <?=$playlist[$key]->btnVeto ? '' : 'hidden'?>><input type="checkbox" name="<?="songVeto-".$i+19?>" class="buttonBox" <?=$song->SongVeto ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> VETO</label>
                        <input type="hidden" name="songUpdated-<?=$i+21?>" value="0">
                    </div>
                </div>
            <?php
            $i += 28;
            endforeach;?>
            <input type="hidden" name="playlistId" value="search"/>
            <input type="hidden" name="searchQuery" value="<?=$searchQuery?>"/>
        </form>
    <?php elseif (strlen($searchQuery) < 1): ?>
        <h3>Nie wpisano nic do wyszukiwarki!</h3>
    <?php elseif (count($songs) > 300): ?>
        <h3>Znaleziono ponad 300 piosenek! Musisz zawęzić kryteria wyszukiwania!</h3>
	<?php else: ?>
		<h3>Nie znaleziono żadnych utworów o podanej nazwie!</h3>
	<?php endif; ?>
<span id="bottom"></span>
<script type="text/javascript" src="<?=base_url('scripts/playlist.js')?>"></script>