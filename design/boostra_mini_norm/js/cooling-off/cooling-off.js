(function () {
  var countdownTimer = document.getElementById("countdown-timer");
  var coolingOffEnd = parseInt(countdownTimer.dataset.endTimestamp);
  var confirmTimestamp = parseInt(countdownTimer.dataset.confirmTimestamp);
  var countdownDisplay = document.getElementById("countdown-display");

  function updateCountdown() {
    var currentTime = Math.floor(Date.now() / 1000);
    var remainingSeconds = coolingOffEnd - currentTime;

    if (remainingSeconds > 0) {
      var days = Math.floor(remainingSeconds / 86400);
      var hours = Math.floor((remainingSeconds % 86400) / 3600);
      var minutes = Math.floor((remainingSeconds % 3600) / 60);
      var seconds = Math.floor(remainingSeconds % 60);

      var timeString;
      if (days > 0) {
        timeString =
          String(days).padStart(2, "0") +
          " дн " +
          String(hours).padStart(2, "0") +
          " : " +
          String(minutes).padStart(2, "0") +
          " : " +
          String(seconds).padStart(2, "0");
      } else {
        timeString =
          String(hours).padStart(2, "0") +
          " : " +
          String(minutes).padStart(2, "0") +
          " : " +
          String(seconds).padStart(2, "0");
      }

      countdownDisplay.textContent = timeString;
    } else {
      countdownDisplay.textContent = "00 : 00 : 00";
      if (countdownInterval) {
        clearInterval(countdownInterval);
      }
    }
  }

  // Update immediately
  updateCountdown();

  // Update every second
  countdownInterval = setInterval(updateCountdown, 1000);
})();
