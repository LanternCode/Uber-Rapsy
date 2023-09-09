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
    //Find this event's index in the list of dataContainer children
    let childIndex = Array.prototype.indexOf.call(event.parentElement.parentElement.children, event.parentElement);
    //Establish the second rating's index
    let indexToSelect = childIndex === 1 ? 0 : 1;
    //Save the new rating directly from the event
    let newRating = event.value;
    //Obtain the second rating based on the established index
    let secondRating = event.parentElement.parentElement.children[indexToSelect].children[1].value;
    //Ensure the entered rating is within the 0-15 range
    if(newRating < 1 || newRating > 15)
    {
        //If not, use 0 as the average placeholder
        newRating = 0;
    }
    //Find the dom element that has the average box
    let avgElem = event.parentElement.parentElement.children[2].children[1];
    //Calculate the average
    let average = (parseFloat(newRating) + parseFloat(secondRating)) / 2;
    //Find the proposed playlist for this rating
    let playlistName = getPlaylistName(average);
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
    //Find the hidden input that holds the update boolean - the last element child of the data container box
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
    if(average >= 9.5) return "X15";
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