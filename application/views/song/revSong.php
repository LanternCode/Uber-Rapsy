<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?=isset($successMessage) && $successMessage == 1 ? "<h3 class='reviewSuccess'>Recenzja została zapisana!</h3><br><br>" : ""?>
<?=$errorMessage ? "<h4 class='reviewError'>".$errorMessage."</h4>" : "" ?>
<?php if($existingReview != false){
    $rD = $existingReview['reviewDate'];
    $rT = is_numeric($existingReview['reviewText']) ? $existingReview['reviewText'] : 0;
    $rM = is_numeric($existingReview['reviewMusic']) ? $existingReview['reviewMusic'] : 0;
    $rI = is_numeric($existingReview['reviewImpact']) ? $existingReview['reviewImpact'] : 0;
    $rH = is_numeric($existingReview['reviewRh']) ? $existingReview['reviewRh'] : 0;
    $rC = is_numeric($existingReview['reviewComp']) ? $existingReview['reviewComp'] : 0;
    $rR = is_numeric($existingReview['reviewReflection']) ? $existingReview['reviewReflection'] : 0;
    $rU = is_numeric($existingReview['reviewUber']) ? $existingReview['reviewUber'] : 0;
    $rP = is_numeric($existingReview['reviewPartner']) ? $existingReview['reviewPartner'] : 0;
    $rV = stripcslashes($existingReview['reviewRev']);

    $total = $rT + $rM + $rI + $rH + $rC + $rR + $rU + $rP;
    $percentage = floor($total / 90 * 100);
}
?>
<label class="reviewBox">Tytuł: <?=$song->SongTitle?></label>
<label class="reviewBox">Autor: <?='?'//$song->SongAuthor?></label><br>
<form method="POST" action="<?=base_url('song/rev?id='.$song->SongId)?>">
    <article class="reviewBoxDate">
        <label>Data Oceny:</label>
        <input id="createdAt" type="text" name="reviewDate" value="<?=$rD ?? ''?>">
        <button onclick="createdAt.value = new Date().toISOString().slice(0, 10); return false;">Dzisiaj</button>
    </article>
    <article class="reviewBox">
        <label>Tekst:</label>
        <input class="gradeInputReview" type="text" name="reviewText" value="<?=$rT ?? ''?>">/20<br>
    </article>
    <article class="reviewBox">
        <label>Muzyka:</label>
        <input class="gradeInputReview" type="text" name="reviewMusic" value="<?=$rM ?? ''?>">/20<br>
    </article>
    <article class="reviewBox">
        <label>Popularność:</label>
        <input class="gradeInputReview" type="text" name="reviewImpact" value="<?=$rI ?? ''?>">/5<br>
    </article>
    <article class="reviewBox">
        <label>Słuchalność:</label>
        <input class="gradeInputReview" type="text" name="reviewRh" value="<?=$rH ?? ''?>">/5<br>
    </article>
    <article class="reviewBox">
        <label>Kompozycja:</label>
        <input class="gradeInputReview" type="text" name="reviewComp" value="<?=$rC ?? ''?>">/10<br>
    </article>
    <article class="reviewBox">
        <label>Refleksyjność:</label>
        <input class="gradeInputReview" type="text" name="reviewReflection" value="<?=$rR ?? ''?>">/10<br>
    </article>
    <article class="reviewBox">
        <label>Ocena Uber:</label>
        <input class="gradeInputReview" type="text" name="reviewUber" value="<?=$rU ?? ''?>">/10<br>
    </article>
    <article class="reviewBox">
        <label>Ocena Partnera:</label>
        <input class="gradeInputReview" type="text" name="reviewPartner" value="<?=$rP ?? ''?>">/10<br>
    </article>
    <article class="reviewBox">
        <label>Razem:</label>
        <input class="gradeInputReview" type="text" name="reviewTotal" value="<?=$total ?? ''?>" disabled>/90 (<?=$percentage ?? ''?>%)<br>
    </article>
    <article class="reviewBox">
        <label>Recenzja:</label><br>
        <textarea name="reviewRev" id="txt" class=""><?=$rV ?? ''?></textarea>
    </article>
    <input type="submit" class="btnSaveReview" value="Zapisz Recenzję!"><br>
</form>
<script src="https://cdn.tiny.cloud/1/622hecsg6zxldlharfjthzkv1fck34b6l7eufosk6rwayu6r/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#txt',
        height: '420',
        width: '840'
    });
</script>