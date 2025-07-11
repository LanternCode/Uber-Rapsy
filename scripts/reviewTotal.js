function calculateReviewTotal()
{
    const inputs = document.querySelectorAll('.gradeInputReview[name^="review"]:not([name="reviewTotal"])');
    let total = 0;

    inputs.forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val)) {
            total += val;
        }
    });

    const percent = ((total / 90) * 100).toFixed(2);

    //Update total and percentage fields
    document.getElementById('reviewTotal').value = total;
    document.getElementById('reviewPercent').textContent = isNaN(percent) ? 0 : percent;
}

//Run on page load
document.addEventListener('DOMContentLoaded', function()
{
    calculateReviewTotal();

    //Bind live calculation
    const inputs = document.querySelectorAll('.gradeInputReview[name^="review"]:not([name="reviewTotal"])');
    inputs.forEach(input => {
        input.addEventListener('input', calculateReviewTotal);
    });
});
