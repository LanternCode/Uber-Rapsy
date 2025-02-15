/**
 * When the grade of the song is changed, this function calculates
 * the new average of two grades and updates the box to show the new average
 *
 * @function updateAverage
 * @param event the html element where the grade is changed
 * @return {void}
 */
function updateAverage(event)
{
    //Ensure the newly entered rating is within the 1-15 range and can be divided by 0.5
    let newRating = event.value;
    if (newRating < 1 || newRating > 15 || newRating % 0.5 !== 0) {
        //If not, use 0 as the average placeholder
        event.value = 0;
    }
    //Obtain all ratings based on the index
    let firstRating = parseFloat(event.parentElement.parentElement.children[0].children[1].value);
    let secondRating = parseFloat(event.parentElement.parentElement.children[1].children[1].value);
    let thirdRating = parseFloat(event.parentElement.parentElement.children[2].children[1].value);
    //Calculate the new average
    let average = ((firstRating, secondRating, thirdRating) => {
        if (firstRating > 0 || secondRating > 0 || thirdRating > 0) {
            let localAvg = firstRating > 0 ? firstRating : 0;
            localAvg += secondRating > 0 ? secondRating : 0;
            localAvg += thirdRating > 0 ? thirdRating : 0;
            let result = localAvg / ((firstRating > 0) + (secondRating > 0) + (thirdRating > 0));
            return result % 1 === 0 ? result : result.toFixed(2);
        }
        else return 0;
    })(firstRating, secondRating, thirdRating);
    //Find the proposed playlist for this rating
    let playlistName = getPlaylistName(average);
    //Find the dom element that has the average box
    let avgElem = event.parentElement.parentElement.children[3].children[1];
    //Update the average
    avgElem.value = average + " (" + playlistName + ")";
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
    if(elem.value == 0)
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
    if(average >= 10) return "X15";
    else if(average >= 8.25) return "Uber";
    else return "Akademia";
}

const gradeBoxes = document.querySelectorAll(".gradeInput");
const selectBoxes = document.querySelectorAll(".selectBox");
const buttonBoxes = document.querySelectorAll(".buttonBox");
const commentBoxes = document.querySelectorAll(".commentBox");

for (let i = 0; i < gradeBoxes.length; ++i) {
    gradeBoxes[i].addEventListener('input', () => {updateAverage(gradeBoxes[i])});
    gradeBoxes[i].addEventListener('input', () => {toggleUpdate(gradeBoxes[i])});
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