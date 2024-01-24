(function ($, Drupal, drupalSettings) {

  'use strict';

  const time_limit = drupalSettings.time_limit;
  const started_time = drupalSettings.started_time;

  // Update the countdown every second
  const timerInterval = setInterval(updateTimer, 1000);

  function updateTimer() {
    // Get the current date and time
    const currentTime = new Date().getTime();
    // Calculate end time.
    const endTime = started_time + time_limit;
    // Calculate the remaining time in milliseconds
    const remainingTime = endTime - currentTime;

    // Check if the countdown has reached zero
    if (remainingTime <= 0) {
      clearInterval(timerInterval); // Stop the timer
      document.getElementById('quiz-timer-value').innerHTML = 'Time expired!';
      document.getElementById('quiz-finish').click();
    } else {
      // Calculate the remaining hours, minutes, and seconds
      const hours = Math.floor((remainingTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((remainingTime % (1000 * 60)) / 1000);

      const formattedHours = hours.toString().padStart(2, '0');
      const formattedMinutes = minutes.toString().padStart(2, '0');
      const formattedSeconds = seconds.toString().padStart(2, '0');

      // Display the remaining time in the 'timer' element
      document.getElementById('quiz-timer-value').innerHTML = `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
      // Add class 'warning' if left less then 10 seconds.
      if (remainingTime <= 10000) {
        document.getElementById('quiz-timer').classList.add('warning');
      }
    }
  }

})(jQuery, Drupal, drupalSettings);