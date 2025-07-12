<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?=isset($errorMessage) ? "<h4 class='reviewError'>".$errorMessage."</h4>" : "" ?>
<label class="reviewBox">Tytuł recenzowanego utworu: <?=$song->SongTitle?></label>
<label class="reviewBox">Autorzy recenzowanego utworu: <?=$song->SongChannelName?></label>
<label class="reviewBox">Data wydania recenzowanego utworu: <?=$song->SongReleaseYear?></label><br>
<?php if ($song->SongURL !== null): ?>
    <label class="reviewBox">Posłuchaj utworu w YouTube: <a href="<?=base_url('youtu.be/'.$song->SongURL)?>" target="_blank"><?=$song->SongTitle?></a></label><br>
<?php endif; ?>
<form method="POST" action="<?=base_url('song/showReview?reviewId='.$review->reviewId)?>">
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
        <label>Popularność:</label>
        <input class="gradeInputReview" type="text" name="reviewImpact" value="<?=$input['reviewImpact'] ?? $review->reviewImpact?>" <?=!$isReviewOwner ? 'readonly' : ''?>>/5<br>
    </article>
    <article class="reviewBox">
        <label>Słuchalność:</label>
        <input class="gradeInputReview" type="text" name="reviewRh" value="<?=$input['reviewRh'] ?? $review->reviewRh?>" <?=!$isReviewOwner ? 'readonly' : ''?>>/5<br>
    </article>
    <article class="reviewBox">
        <label>Kompozycja:</label>
        <input class="gradeInputReview" type="text" name="reviewComp" value="<?=$input['reviewComp'] ?? $review->reviewComp?>" <?=!$isReviewOwner ? 'readonly' : ''?>>/10<br>
    </article>
    <article class="reviewBox">
        <label>Refleksyjność:</label>
        <input class="gradeInputReview" type="text" name="reviewReflection" value="<?=$input['reviewReflection'] ?? $review->reviewReflection?>" <?=!$isReviewOwner ? 'readonly' : ''?>>/10<br>
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
        <label>Razem:</label>
        <input class="gradeInputReview" type="text" name="reviewTotal" id="reviewTotal" disabled>/90 (<span id="reviewPercent">0</span>%)<br>
    </article>
    <article class="reviewBox">
        <label>Recenzja:</label><br>
        <textarea name="reviewTextContent" id="txt"><?=$input['reviewTextContent'] ?? $review->reviewTextContent?></textarea>
    </article>
    <?php if ($isReviewOwner): ?>
        <input type="submit" class="btnSaveReview big-button" value="Zapisz Recenzję!">><br>
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
        readonly: isReadonly
    });
</script>