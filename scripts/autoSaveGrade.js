document.addEventListener('DOMContentLoaded', function () {
  const slider = document.getElementById('gradeSlider');
  const form = document.getElementById('toplist');
  const confirmation = document.getElementById('confirmation');
  const actionUrl = form.getAttribute('data-url');
  const myRatingElem = document.getElementById('myRating');
  const communityAverageElem = document.getElementById('commAvg');
  let debounceTimeout;

  // Show a message while slider is being changed
  slider.addEventListener('input', function () {
    confirmation.textContent = 'Trwa aktualizacja oceny...';

    clearTimeout(debounceTimeout);
     debounceTimeout = setTimeout(() => {
        const formData = new FormData(form);
        fetch(actionUrl, {
          method: 'POST',
          body: formData
        })
        .then(response => {
          if (!response.ok) {
            return response.json().then(err => {
              throw new Error('Server error');
            });
          }
          return response.json();
        })
        .then(data => {
          confirmation.textContent = 'Ocena zapisana!';
          myRatingElem.textContent = 'Moja Ocena: ' + data.my_rating;
          communityAverageElem.textContent = 'Średnia Społeczności: ' + data.community_average;
        })
        .catch(error => {
          confirmation.textContent = 'Wystąpił błąd!';
        });
     }, 500);
  });
});