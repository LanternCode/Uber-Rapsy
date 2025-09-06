/**
 * Update the average inside the same block as the changed grade input.
 *
 * @param {HTMLInputElement} inputEl - the number input that changed
 * @return {void}
 */
function updateAverage(inputEl)
{
    const row = inputEl.parentElement.parentElement;

    //Rappar-managed playlists display a (disabled) song average box, which must be updated
    const avgElem = row.querySelector('input.gradeInput[type="text"][disabled]');
    if (!avgElem) return;

    //Minimal normalisation, so "01.5" doesn't stick as "01.5" after a reset to 0
    const before = inputEl.value;
    let raw = before.replace(/^0+(?=\d)/, '');
    if (raw === '.') raw = '0.';
    if (raw !== before) inputEl.value = raw;

    //Ensure the newly entered rating is within the 1–15 range and divisible by 0.5
    const newRating = parseFloat(raw);
    const invalid = !Number.isFinite(newRating) || newRating < 1 || newRating > 15 || (newRating * 2) % 1 !== 0;

    //Allow users to be "in the middle of typing"
    const transitional = raw === '' || raw === '0' || raw === '0.' || /\d+\.$/.test(raw);

    if (invalid && !transitional)
        inputEl.value = "0";

    //Collect this row’s numeric grade inputs
    const numbers = Array.from(
        row.querySelectorAll('input.gradeInput[type="number"]')
    ).map(inp => parseFloat(inp.value))
     .filter(v => Number.isFinite(v));

    //Average over all grades
    const count = numbers.length;
    const sum = numbers.reduce((a, b) => a + b, 0);

    //Compute the average and trim trailing zeros
    let average = 0;
    if (count > 0) {
        const result = sum / count;
        average = Number.isInteger(result) ? result : +result.toFixed(2);
    }

    const playlistName = (typeof getPlaylistName === 'function') ? getPlaylistName(average) : '';
    avgElem.value = playlistName ? `${average} (${playlistName})` : String(average);
}

/**
 * This functions updates a flag which is then used to determine which songs were updated
 *
 * @function toggleUpdate
 * @param event the html element calling the function
 * @return {void}
 */
function toggleUpdate(event)
{
    //Find the input of type hidden that holds the update boolean - the last element child of the data container box
    let elem = event.closest(".dataContainerBox").lastElementChild;

    //If the bool is false, set it to true
    if (elem.value == 0)
        elem.value = 1;
}

/**
 * This function returns one of the predefined playlist names based on
 * the song average of grades
 *
 * @function getPlaylistName
 * @param {number} average the song average of grades
 * @returns {string} the name of the playlist where the song could go
 */
function getPlaylistName(average)
{
    if (average >= 10) return "X15";
    else if (average >= 8.25) return "Uber";
    else return "Akademia";
}

const gradeBoxes = document.querySelectorAll('input.gradeInput[type="number"]:not([disabled])');
const selectBoxes = document.querySelectorAll(".selectBox");
const buttonBoxes = document.querySelectorAll(".buttonBox");
const commentBoxes = document.querySelectorAll(".commentBox");

for (let i = 0; i < gradeBoxes.length; ++i) {
  gradeBoxes[i].addEventListener('input', () => { updateAverage(gradeBoxes[i]); });
  gradeBoxes[i].addEventListener('input', () => { toggleUpdate(gradeBoxes[i]); });
}

for (let i = 0; i < selectBoxes.length; ++i) {
    selectBoxes[i].addEventListener('change', () => {toggleUpdate(selectBoxes[i])});
}

for (let i = 0; i < buttonBoxes.length; ++i) {
    buttonBoxes[i].addEventListener('change', () => {toggleUpdate(buttonBoxes[i])});
}

for (let i = 0; i < commentBoxes.length; ++i) {
    commentBoxes[i].addEventListener('input', () => {toggleUpdate(commentBoxes[i])});
}