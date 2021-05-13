function lowerGrade(user, id)
{
    let value = document.getElementById(user + '-' + id).value;
    if(isNaN(value)) value = 5;
    value--;
    if(value >= 1) document.getElementById(user + '-' + id).value = value;
    else document.getElementById(user + '-' + id).value = 1;

    document.getElementById("NGB" + user + "-" + id).style.display = "inline";

    calculateAverage(id);
}

function raiseGrade(user, id)
{
    let value = document.getElementById(user + '-' + id).value;
    if(isNaN(value)) value = 5;
    value++;
    if(value <= 15) document.getElementById(user + '-' + id).value = value;
    else document.getElementById(user + '-' + id).value = 15;

    document.getElementById("NGB" + user + "-" + id).style.display = "inline";

    calculateAverage(id);
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

// var scrollCount = 1;
// window.addEventListener('mousewheel', function(e){
//
//   if(e.wheelDelta<0 && scrollCount<15){
//     scrollCount++;
//   }
//
//   else if(e.wheelDelta>0 && scrollCount>1){
//     scrollCount--;
//   }
//   document.querySelector('.number').innerHTML = scrollCount;
// });
