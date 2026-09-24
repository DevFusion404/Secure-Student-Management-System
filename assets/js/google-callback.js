(function () {
  var greeting = document.getElementById('welcome-greeting');

  if (greeting) {
    var hour = new Date().getHours();
    var salutation = hour < 12 ? ' this morning' : (hour < 18 ? ' this afternoon' : ' this evening');
    greeting.textContent = salutation;
  }
})();
