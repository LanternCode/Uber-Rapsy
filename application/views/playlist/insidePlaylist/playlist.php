<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">Powrót do playlist</a>
    <a class="optionsURL" href="#bottom">Dół Listy</a>
    <a class="optionsURL" href="#topoftherap">Góra Listy</a>
    <?php if($isOwner || $isReviewer): ?>
        <?php if(count($songs) > 0): ?>
            <input type="submit" class="optionsURL" value="Zapisz oceny" form="songsForm"/>
        <?php endif; ?>
    <?php endif; ?>
    <select class="optionsURL redirectsBox" onchange="javascript:location.href = this.value;">
        <option value="">Pokaż oceny:</option>
        <option value="<?=base_url("playlist?listId=".$listId)?>">Wszystkie Oceny</option>
        <option value="<?=base_url("tierlist?listId=".$listId."&filter=Adam")?>">Najlepsze: Adam</option>
        <option value="<?=base_url("tierlist?listId=".$listId."&filter=Churchie")?>">Najlepsze: Kościelny</option>
        <option value="<?=base_url("tierlist?listId=".$listId."&filter=Owner")?>">Najlepsze: Właściciel</option>
        <option value="<?=base_url("tierlist?listId=".$listId."&filter=Average")?>">Najlepsze: Średnia</option>
        <option value="<?=base_url("playlist?listId=".$listId."&filter=Unrated")?>">Nieocenione</option>
        <?php foreach($checkboxPropertiesDetails as $propDetails):
            if ($playlist->{$propDetails[1]}): ?>
                <option value="<?=base_url("playlist?ListId=" . $listId . "&filter=Checkbox&prop=".$propDetails[0])?>"><?=$propDetails[2]?></option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
    <form class="optionsURL optionsRight" method="get" action="<?=base_url("playlist")?>">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="hidden" name="ListId" value="<?=$listId?>" />
        <input type="text" placeholder="Rajaner" name="SearchQuery" />
        <input type="submit" value="Szukaj" />
    </form>
</header>
<br id="topoftherap"><br><br>
<div class="averagesBar">
    <div class="averagesBar--left">
        <h2 class="blackBar">Przeglądasz playlistę <?=$playlist->ListName ?? "o nieznanej nazwie"?>!</h2>
        <h3 class="blackBar">Liczba nut: <?=count($songs)?></h3>
        <h3 class="blackBar" title="Średnia ocen jest obliczana tylko gdy obu recenzentów oceniło utwór. Utwory na których zaznaczono przycisk ''10'' nie są brane pod uwagę.">Średnie ocen (?):</h3>
        <h4 class="blackBar">Średnia Ocen Playlisty: <?=number_format($avgOverall, 2)?> (<?=$ratedOverall?>)</h4>
        <h4 title="Średnia ocen na podstawie ocenionych utworów Kościelnego" class="blackBar">Średnia Ocen (Kościelny): <?=number_format($avgChurchie, 2)?> (<?=$ratedChurchie?>)</h4>
        <h4 title="Średnia ocen na podstawie ocenionych utworów Adama" class="blackBar">Średnia Ocen (Adam): <?=number_format($avgAdam, 2)?> (<?=$ratedAdam?>)</h4>
    </div>
    <div class="averagesBar--right">
        <img src="./styles/icons/bigger_cog.png" class="hamburger optionsRight settings_cog menuIcon">
    </div>
</div>
<form id="songsForm" method="post" action="<?=base_url('updateSongRatings')?>">
    <ul class="menu">
        <img src="./styles/icons/bigger_cog.png" class="hamburger closing_cog menuIcon"><br><br><br>
        <li class="optionsURL">Zintegrowana: <?=$playlist->ListIntegrated ? "<a target='_blank' href='https://www.youtube.com/playlist?list=$playlist->ListUrl'>Tak</a>" : "Nie"?></li><br>
        <?php if(isset($_SESSION['userRole']) && ($_SESSION['userRole'] == "user" || $_SESSION['userRole'] == "reviewer")): ?>
            <li class="optionsURL menuURL"><a class="blackBar" href="<?=base_url("downloadSongs?listId=".$listId)?>">Załaduj nowe nuty</a></li><br>
            <li class="optionsURL menuURL"><a class="blackBar" href="<?=base_url('playlist/details?listId='.$listId.'&src=mp')?>">Statystyki i Ustawienia</a></li>
        <?php endif; ?>
    </ul>
    <?php if(isset($refreshSuccess) && $refreshSuccess === false): ?>
        <h2>Podano niepoprawny link do playlisty na YT!</h2>
        <h3>W wolnej chwili przejdź do ustawień i zmień link na poprawny!</h3>
        <h3>Jeśli usunięto playlistę albo chcesz pozbyć się tej wiadomości, usuń link w ustawieniach.</h3>
        <h3>Playlista na YT musi być publiczna lub niepubliczna (ale nie prywatna) aby mogła zostać wczytana.</h3>
        <h3>Poniżej podano znajdujące się już na liście utwory.</h3><br /><br />
    <?php endif; ?>
	<?php if(count($songs) > 0):
        $i = 0; ?>
		<?php foreach($songs as $song): ?>
            <div class="videoContainerBox">
				<img src="<?=$song->SongThumbnailURL?>" alt="thumbnail" class="songThumbnailLeft">
				<div class="dataContainerBox">
                    <input type="hidden" name="songId-<?=$i?>" value="<?=$song->SongId?>"/>
                    <h3 class="songTitle"><a href="https://youtu.be/<?=$song->SongURL?>" target="_blank"><?=$song->SongTitle?></a> (<a target='_blank' href="<?=base_url('song/rev?id='.$song->SongId)?>">+</a>)</h3>
                    <div class="dataContainerBox--split">
                        <div class="dataContainerBox--split__left">
                            <h4 class="dataContainer--gradeContainer">
                                <label>Adam: <?=$song->SongGradeAdam ?? 'Nieoceniona'?> -></label>
                                <input name="nwGradeA-<?=$i+1?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                       value="<?=$song->SongGradeAdam ?? 'Nieoceniona'?>" <?=$isReviewer ? "" : "disabled" ?>/>
                            </h4>
                            <h4 class="dataContainer--gradeContainer">
                                <label>Kościelny: <?=$song->SongGradeChurchie ?? 'Nieoceniona'?> -></label>
                                <input name="nwGradeC-<?=$i+2?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                       value="<?=$song->SongGradeChurchie ?? 'Nieoceniona'?>" <?=$isReviewer ? "" : "disabled" ?>/>
                            </h4>
                            <h4 class="dataContainer--gradeContainer">
                                <label>Moja Ocena: <?=$song->SongGradeOwner ?? 'Nieoceniona'?> -></label>
                                <input name="nwGradeM-<?=$i+27?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                       value="<?=$song->SongGradeOwner ?? 'Nieoceniona'?>" <?=$isOwner ? "" : "disabled" ?>/>
                            </h4>
                            <h5 class="dataContainer--gradeContainer">
                                <label>Średnia:</label>
                                <input class="gradeInput" type="text" value="<?=is_numeric($song->SongGradeAdam) && is_numeric($song->SongGradeChurchie) ? (($song->SongGradeAdam + $song->SongGradeChurchie) / 2) : "Nieoceniona"?>" disabled />
                            </h5>
                            <?php  //only 1 list means there is nowhere to move or copy the song to
                            if(count($allPlaylists) > 1 && $isReviewer): ?>
                                <h5 class="dataContainer--gradeContainer">
                                    <label>Przenieś do:</label>
                                    <select name="<?="nwPlistId-".$i+3?>" class="selectBox">
                                        <option value="0">Nie przenoś</option>
                                        <?php foreach($allPlaylists as $list):
                                            //Do not show the current list in the options
                                            if($list->ListId !== $listId):?>
                                                <option value="<?=$list->ListId?>"><?=$list->ListName?></option>
                                            <?php endif;
                                        endforeach; ?>
                                    </select>
                                </h5>
                                <h5 class="dataContainer--gradeContainer">
                                    <label>Kopiuj do:</label>
                                    <select name="<?="copyPlistId-".$i+20?>" class="selectBox">
                                        <option value="0">Nie kopiuj</option>
                                        <?php foreach($allPlaylists as $list):
                                            //Do not show the current list in the options
                                            if($list->ListId !== $listId):?>
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
                            <textarea placeholder="Komentarz do utworu..." class="commentBox" name="songComment-<?=$i+22?>" <?=$isOwner ? "" : "disabled" ?>><?=$song->SongComment?></textarea>
                        </div>
                    </div>
                    <label <?=$playlist->btnRehearsal ? '' : 'hidden'?>><input type="checkbox" name="<?="songRehearsal-".$i+4?>" class="buttonBox" <?=$song->SongRehearsal ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> Do ponownego odsłuchu</label>
                    <label <?=$playlist->btnBelowFour ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelFour-".$i+17?>" class="buttonBox" <?=$song->SongBelFour ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 4</label>
                    <label <?=$playlist->btnBelowSeven ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelow-".$i+13?>" class="buttonBox" <?=$song->SongBelow ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 7</label>
                    <label <?=$playlist->btnBelowHalfSeven ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfSeven-".$i+23?>" class="buttonBox" <?=$song->SongBelHalfSeven ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 7.5</label>
                    <label <?=$playlist->btnBelowEight ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelEight-".$i+16?>" class="buttonBox" <?=$song->SongBelEight ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 8</label>
                    <label <?=$playlist->btnBelowHalfEight ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfEight-".$i+24?>" class="buttonBox" <?=$song->SongBelHalfEight ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 8.5</label>
                    <label <?=$playlist->btnBelowNine ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelNine-".$i+15?>" class="buttonBox" <?=$song->SongBelNine ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 9</label>
                    <label <?=$playlist->btnBelowHalfNine ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfNine-".$i+25?>" class="buttonBox" <?=$song->SongBelHalfNine ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 9.5</label>
                    <label <?=$playlist->btnBelowTen ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelTen-".$i+14?>" class="buttonBox" <?=$song->SongBelTen ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 10</label>
                    <label <?=$playlist->btnDistinction ? '' : 'hidden'?>><input type="checkbox" name="<?="songDistinction-".$i+5?>" class="buttonBox" <?=$song->SongDistinction ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> Wyróżnienie</label>
                    <label <?=$playlist->btnDuoTen ? '' : 'hidden'?>><input type="checkbox" name="<?="songDuoTen-".$i+18?>" class="buttonBox" <?=$song->SongDuoTen ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> "10"</label>
                    <label <?=$playlist->btnMemorial ? '' : 'hidden'?>><input type="checkbox" name="<?="songMemorial-".$i+6?>" class="buttonBox" <?=$song->SongMemorial ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> 10*</label>
                    <label <?=$playlist->btnUber ? '' : 'hidden'?>><input type="checkbox" name="<?="songUber-".$i+12?>" class="buttonBox" <?=$song->SongUber ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> Uber</label>
                    <label <?=$playlist->btnTop ? '' : 'hidden'?>><input type="checkbox" name="<?="songTop-".$i+10?>" class="buttonBox" <?=$song->SongTop ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> X15</label>
                    <label <?=$playlist->btnXD ? '' : 'hidden'?>><input type="checkbox" name="<?="songXD-".$i+7?>" class="buttonBox" <?=$song->SongXD ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> XD</label>
                    <label <?=$playlist->btnDiscomfort ? '' : 'hidden'?>><input type="checkbox" name="<?="songDiscomfort-".$i+9?>" class="buttonBox" <?=$song->SongDiscomfort ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> Strefa Dyskomfortu</label>
                    <label <?=$playlist->btnDepA ? '' : 'hidden'?>><input type="checkbox" name="<?="songDepA-".$i+26?>" class="buttonBox" <?=$song->SongDepA ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> Dep. Abroad</label>
                    <label <?=$playlist->btnNotRap ? '' : 'hidden'?>><input type="checkbox" name="<?="songNotRap-".$i+8?>" class="buttonBox" <?=$song->SongNotRap ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> To nie rapsik</label>
                    <label <?=$playlist->btnNoGrade ? '' : 'hidden'?>><input type="checkbox" name="<?="songNoGrade-".$i+11?>" class="buttonBox" <?=$song->SongNoGrade ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> Nie oceniam</label>
                    <label <?=$playlist->btnVeto ? '' : 'hidden'?>><input type="checkbox" name="<?="songVeto-".$i+19?>" class="buttonBox" <?=$song->SongVeto ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> VETO</label>
                    <input type="hidden" name="songUpdated-<?=$i+21?>" value="0">
                </div>
			</div>
		<?php
        $i += 28;
        endforeach;?>
	<?php elseif(strlen($searchQuery) > 0 && count($songs) == 0): ?>
        <h3>Nie znaleziono żadnych wyników wyszukiwania!</h3>
	<?php else: ?>
		<h3>Ta playlista jest pusta mordo, nowy sezon już wkrótce!</h3>
	<?php endif; ?>
    <input type="hidden" name="playlistId" value="<?=$listId?>"/>
</form>
<span id="bottom"></span>
<script type="text/javascript" src="<?=base_url( 'scripts/playlist.js' )?>"></script>
<script type="text/javascript" src="<?=base_url( 'scripts/nav.js' )?>"></script>
