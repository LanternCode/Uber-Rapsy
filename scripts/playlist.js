let grade = 5;
let jump = 1;

function changeGrade(event) {
    //prevents mouse wheel from scrolling the page
    event.preventDefault();

    const gradeBox = document.getElementById(event.target.name.substr(1));

    let value = gradeBox.value;
    if(value === "Nieoceniona") value = 5;
    else value = parseFloat(value);

    let change = -jump * event.deltaY / 100;
    value += change;

    let id = event.target.name.substr(3);
    if(value >= 1 && value <= 15)
    {
        let user = event.target.name.substr(1, 1);
        gradeBox.value = value;
        document.getElementById("NGB" + user + "-" + id).style.display = "inline";
    }

    calculateAverage(id);
}

const gradeBoxes = document.querySelectorAll(".gradeInput");

for (let i = 0; i < gradeBoxes.length; ++i) {
    gradeBoxes[i].addEventListener('wheel', changeGrade);
}

function calculateAverage(id)
{
    let adamGrade = document.getElementById('A-' + id).value;
    let koscielnyGrade = document.getElementById('K-' + id).value;

    if(isNaN(adamGrade) || isNaN(koscielnyGrade))
    {
        document.getElementById(id).value = 'Nieoceniona';
    }
    else
    {
        let average = (parseInt(adamGrade) + parseInt(koscielnyGrade)) / 2;
        let playlistName = getPlaylistName(average);

        document.getElementById(id).value = average + " (" + playlistName + ")";
        document.getElementById("NGBAv-" + id).style.display = "inline";
    }
}

function getPlaylistName(average)
{
    if(average >= 9.5) return "X15";
    else if(average >= 8.75) return "Uber+";
    else if (average >= 7.5) return "Uber";
    else if (average >= 6.25) return "Wyróżnienie";
    else return "Akademia";
}

function updateJump(value)
{
    jump = value;
}