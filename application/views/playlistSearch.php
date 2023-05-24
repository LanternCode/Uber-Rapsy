<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav class="omniNav">
    <a class="omniNav--Option" href="<?=base_url()?>">UberRapsy</a>
    <a class="optionsURL" href="#bottom">Dół Listy</a>
    <a class="optionsURL" href="#songsForm">Góra Listy</a>
    <?php if(isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']): ?>
        <a class="omniNav--Option" href="<?=base_url("loginYoutube")?>">Panel Sterowania YT</a>
    <?php endif; ?>
    <form class="omniNav--Option optionsRight" method="get" action="<?=base_url("search")?>">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="text" placeholder="Rajaner" name="Search" />
        <input type="submit" value="Szukaj" />
    </form>
</nav>
<h2>Wyniki wyszukiwania!</h2>
	<?php if(count($songs) > 0): ?>
        <h3>Liczba nut: <?=count($songs)?></h3>
		<?php
        $i = 0;
        foreach($songs as $song):?>
			<div class="videoContainer">
				<img src="<?=$song->SongThumbnailURL?>" width="" height="" alt="thumbnail" class="songThumbnailLeft" />
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
                    <label><input type="hidden" name="<?="songRehearsal-".$i+4?>" value="<?=$song->SongRehearsal?>"><input type="checkbox" <?=$song->SongRehearsal ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Do ponownego odsłuchu</label>
                    <label><input type="hidden" name="<?="songBelow-".$i+13?>" value="<?=$song->SongBelow?>"><input type="checkbox" <?=$song->SongBelow ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 7</label>
                    <label><input type="hidden" name="<?="songDistinction-".$i+5?>" value="<?=$song->SongDistinction?>"><input type="checkbox" <?=$song->SongDistinction ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Wyróżnienie</label>
                    <label><input type="hidden" name="<?="songMemorial-".$i+6?>" value="<?=$song->SongMemorial?>"><input type="checkbox" <?=$song->SongMemorial ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> 10*</label>
                    <label><input type="hidden" name="<?="songUber-".$i+12?>" value="<?=$song->SongUber?>"><input type="checkbox" <?=$song->SongUber ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Uber</label>
                    <label><input type="hidden" name="<?="songTop-".$i+10?>" value="<?=$song->SongTop?>"><input type="checkbox" <?=$song->SongTop ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> X15</label>
                    <label><input type="hidden" name="<?="songXD-".$i+7?>" value="<?=$song->SongXD?>"><input type="checkbox" <?=$song->SongXD ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> XD</label>
                    <label><input type="hidden" name="<?="songDiscomfort-".$i+9?>" value="<?=$song->SongDiscomfort?>"><input type="checkbox" <?=$song->SongDiscomfort ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Strefa Dyskomfortu</label>
                    <label><input type="hidden" name="<?="songNotRap-".$i+8?>" value="<?=$song->SongNotRap?>"><input type="checkbox" <?=$song->SongNotRap ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> To nie rapsik</label>
                    <label><input type="hidden" name="<?="songNoGrade-".$i+11?>" value="<?=$song->SongNoGrade?>"><input type="checkbox" <?=$song->SongNoGrade ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Nie oceniam</label>
				</div>
			</div>
		<?php
        $i += 14;
        endforeach;?>
	<?php else: ?>
		<h3>Nie znaleziono nic o podanej nazwie!</h3>
	<?php endif; ?>
<span id="bottom"></span>
<script type="text/javascript" src="<?=base_url( 'scripts/playlist.js' )?>"></script>
