function updateAverage(event)
{
    //find this event's index in the list of dataContainer children
    let childIndex = Array.prototype.indexOf.call(event.parentElement.parentElement.children, event.parentElement);
    //establish the second rating's index
    let indexToSelect = childIndex === 2 ? 3 : 2;
    //save the new rating directly from the event
    let newRating = event.value;
    //obtain the second rating based on the established index
    let secondRating = event.parentElement.parentElement.children[indexToSelect].children[1].value;
    //ensure the entered rating is within the 0-15 range
    if(newRating < 1 || newRating > 15)
    {
        //incorrect value was passed, reset it
        event.value = 0;
        newRating = 0;
    }
    //find the dom element that has the average box
    let avgElem = event.parentElement.parentElement.children[4].children[1];
    //calculate the average
    let average = (parseFloat(newRating) + parseFloat(secondRating)) / 2;
    //find the proposed playlist for this rating
    let playlistName = getPlaylistName(average);
    //update the average
    avgElem.value = average + " (" + playlistName + ")";
}

/**
 * @function toggleUpdate
 * @param event
 */
function toggleUpdate(event)
{
    //Find the hidden input that holds the update boolean
    let elem = event.parentElement.parentElement.lastElementChild;
    //If the bool is false, set it to true
    if(elem.value == 0)
        elem.value = 1;
}

function getPlaylistName(average)
{
    if(average >= 9.5) return "X15";
    else if(average >= 8.25) return "Uber";
    else return "Akademia";
}

const gradeBoxes = document.querySelectorAll(".gradeInput");
const selectBoxes = document.querySelectorAll(".selectBox");
const buttonBoxes = document.querySelectorAll(".buttonBox");

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