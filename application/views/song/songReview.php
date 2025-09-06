<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">RAPPAR</a>
    <?php if ($userLoggedIn): ?>
        <a class="optionsURL" href="<?=base_url("myPlaylists")?>">Moje Konto i Playlisty</a>
    <?php endif; ?>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <a class="optionsURL" href="<?=base_url("songPage?songId=".$song->SongId)?>">Wróc do utworu</a>
    <?php if ($userLoggedIn): ?>
        <?php if ($isReviewer): ?>
            <a class="optionsURL" href="<?=base_url("adminDashboard")?>">Panel Sterowania</a>
        <?php endif; ?>
        <a class="optionsURL" href="<?=base_url("logout")?>">Wyloguj się</a>
    <?php endif; ?>
</header>
<main>
    <br><br><br>
    <h1>Recenzja utworu</h1>
    <?=isset($errorMessage) ? "<h4 class='reviewError'>".$errorMessage."</h4>" : "" ?>
    <label class="reviewBox">Tytuł recenzowanego utworu: <?=$song->SongTitle?></label>
    <label class="reviewBox">Autorzy recenzowanego utworu: <?=$song->SongChannelName?></label>
    <label class="reviewBox">Data wydania recenzowanego utworu: <?=$song->SongReleaseYear?></label><br>
    <?php if ($song->SongURL !== null): ?>
        <label class="reviewBox">Posłuchaj utworu w YouTube: <a href="<?='https://youtu.be/'.$song->SongURL?>" target="_blank"><?=$song->SongTitle?></a></label><br>
    <?php endif; ?>
    <form method="POST" action="<?=base_url('song/showReview?reviewId='.$review->reviewId)?>">
        <?php if ($isReviewOwner || $isReviewer): ?>
            <article class="reviewBox">
                <h2>Status recenzji</h2>
                <label>
                    <input type="checkbox" name="deleteReview" id="delReview">Recenzja jest aktywna. Jeśli chcesz ją usunąć (ta decyzja jest nieodwracalna!), zaznacz ptaszek.
                </label>
                <label id="delReviewConf" style="display: none;">
                    <input type="checkbox" name="delReviewConf">Ostatecznie potwierdzam usunięcie recenzji i akceptuję, że ta decyzja jest nieodwracalna. Aby kontynuować, zaznacz ptaszek i zapisz zmiany.
                </label>
            </article><br>
            <article class="reviewBox">
                <h2>Widoczność recenzji</h2>
                <label>
                    <input type="checkbox" name="reviewActive" <?=isset($input['reviewActive']) ? ($input['reviewActive'] ? 'checked' : '') : ($review->reviewActive ? 'checked' : '')?>>Recenzje w RAPPAR są domyślnie publiczne. Jeśli nie chcesz żeby inni widzieli Twoją recenzję, odznacz ptaszek obok i zapisz recenzję przyciskiem na dole strony.<br>
                    Jeśli zmienisz zdanie, możesz w dowolnym momencie wrócić tutaj i zaznaczyć ptaszek aby upublicznić recenzję.
                </label>
            </article><br>
        <?php endif; ?>
        <article class="reviewBoxTitle">
            <label>Tytuł Recenzji:</label>
            <input class="titleInputReview" type="text" name="reviewTitle" value="<?=$input['reviewTitle'] ?? $review->reviewTitle?>" <?=!$isReviewOwner ? 'readonly' : ''?> required><br>
        </article><br>
        <article class="reviewBoxDate">
            <label>Data Recenzji:</label>
            <input id="createdAt" type="text" name="reviewDate" value="<?=$input['reviewDate'] ?? $review->reviewDate?>" <?=!$isReviewOwner ? 'readonly' : ''?>>
            <?php if ($isReviewOwner): ?>
                <button onclick="createdAt.value = new Date().toISOString().slice(0, 10); return false;">Dzisiaj</button>
            <?php endif; ?>
        </article>
        <article class="reviewBox">
            <label>Tekst:</label>
            <input class="gradeInputReview" type="text" name="reviewText" value="<?=$input['reviewText'] ?? $review->reviewText?>" <?=!$isReviewOwner ? 'readonly' : ''?>>/20<br>
        </article>
        <article class="reviewBox">
            <label>Muzyka:</label>
            <input class="gradeInputReview" type="text" name="reviewMusic" value="<?=$input['reviewMusic'] ?? $review->reviewMusic?>" <?=!$isReviewOwner ? 'readonly' : ''?>>/20<br>
        </article>
        <article class="reviewBox">
            <label>Kompozycja (mix/master) i ułożenie:</label>
            <input class="gradeInputReview" type="text" name="reviewComp" value="<?=$input['reviewComp'] ?? $review->reviewComp?>" <?=!$isReviewOwner ? 'readonly' : ''?>>/10<br>
        </article>
        <article class="reviewBox">
            <label>Ocena Uber:</label>
            <input class="gradeInputReview" type="text" name="reviewUber" value="<?=$input['reviewUber'] ?? $review->reviewUber?>" <?=!$isReviewOwner ? 'readonly' : ''?>>/10<br>
        </article>
        <article class="reviewBox">
            <label>Ocena Partnera:</label>
            <input class="gradeInputReview" type="text" name="reviewPartner" value="<?=$input['reviewPartner'] ?? $review->reviewPartner?>" <?=!$isReviewOwner ? 'readonly' : ''?>>/10<br>
        </article>
        <article class="reviewBox">
            <label>Unikalność:</label>
            <input class="gradeInputReview" type="text" name="reviewUnique" value="<?=$input['reviewUnique'] ?? $review->reviewUnique?>" <?=!$isReviewOwner ? 'readonly' : ''?>>/5<br>
        </article>
        <article class="reviewBox">
            <label>Styl:</label>
            <input class="gradeInputReview" type="text" name="reviewStyle" value="<?=$input['reviewStyle'] ?? $review->reviewStyle?>" <?=!$isReviewOwner ? 'readonly' : ''?>>/5<br>
        </article>
        <article class="reviewBox">
            <label>Refleksyjność:</label>
            <input class="gradeInputReview" type="text" name="reviewReflective" value="<?=$input['reviewReflective'] ?? $review->reviewReflective?>" <?=!$isReviewOwner ? 'readonly' : ''?>>/5<br>
        </article>
        <article class="reviewBox">
            <label>Motyw:</label>
            <input class="gradeInputReview" type="text" name="reviewMotive" value="<?=$input['reviewMotive'] ?? $review->reviewMotive?>" <?=!$isReviewOwner ? 'readonly' : ''?>>/5<br>
        </article>
        <article class="reviewBox">
            <label>Razem:</label>
            <input class="gradeInputReview" type="text" name="reviewTotal" id="reviewTotal" disabled>/90 (<span id="reviewPercent">0</span>%)<br>
        </article>
        <article class="reviewBox">
            <label>Recenzja:</label><br>
            <textarea name="reviewTextContent" id="txt"><?=$input['reviewTextContent'] ?? $review->reviewTextContent?></textarea>
        </article>
        <?php if ($isReviewOwner || $isReviewer): ?>
            <input type="submit" class="btnSaveReview big-button" value="Zapisz Recenzję!"><br>
        <?php endif; ?>
    </form>
    <script type="text/javascript" src="<?=base_url('scripts/reviewTotal.js')?>"></script>
    <script src="https://cdn.tiny.cloud/1/622hecsg6zxldlharfjthzkv1fck34b6l7eufosk6rwayu6r/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        let isReadonly = <?=$isReviewOwner ? 0 : 1?>;
        tinymce.init({
            selector: '#txt',
            height: '420',
            width: '840',
            invalid_elements: 'script,style,iframe,object,embed',
            verify_html: true,
            readonly: isReadonly
        });
        document.getElementById('delReview').addEventListener('change', function() {
        const section = document.getElementById('delReviewConf');
        section.style.display = this.checked ? 'block' : 'none';
        });
    </script>
</main>