const messageElement = document.getElementById("error");
const currentURL = window.location.href;

if (messageElement) {
  // Set a timeout to hide the message after 5000 milliseconds (5 seconds)
  setTimeout(function () {
    // Hide the message element
    messageElement.style.display = "none";

    // Check if the success or error parameter is present in the URL
    if (currentURL.includes("?success=") || currentURL.includes("?error=")) {
      // Remove the success or error parameter from the URL
      const newURL = currentURL.split("?")[0];
      history.replaceState({}, document.title, newURL);
    }
  }, 5000);
}

function toggleUserOptions() {
  var options = document.getElementById("userOptions");
  options.style.display = options.style.display === "flex" ? "none" : "flex";
}

function showBlockedIP() {
  // Hide other sections and show the Blocked IP section
  document.getElementById("blockedIPSection").style.display = "block";
  document.getElementById("accessLogsSection").style.display = "none";
}

function showAccessLogs() {
  // Hide other sections and show the Access Logs section
  document.getElementById("blockedIPSection").style.display = "none";
  document.getElementById("accessLogsSection").style.display = "block";
}

// Function to update the currency symbol based on the selected currency
function updateCurrencySymbol() {
  var currencySelect = document.getElementById("currency");
  var currencySymbol = document.getElementById("currencySymbol");

  // Check if currencySelect and currencySymbol are not null before proceeding
  if (currencySelect && currencySymbol) {
    var selectedCurrency = currencySelect.value;

    // Set the currency symbol based on the selected currency
    if (selectedCurrency === "BDT") {
      currencySymbol.textContent = "৳"; // BDT symbol
    } else if (selectedCurrency === "USD") {
      currencySymbol.textContent = "$"; // USD symbol
    }
    // Add more conditions for other currencies as needed
  }
}

// Attach the updateCurrencySymbol function to the change event of the currency select, if the element exists
document.addEventListener("DOMContentLoaded", function () {
  var currencyElement = document.getElementById("currency");
  if (currencyElement) {
    currencyElement.addEventListener("change", updateCurrencySymbol);
  }
});

// Call the function initially to set the default currency symbol
updateCurrencySymbol();

// Function to get the client's time zone
function getClientTimeZone() {
  return Intl.DateTimeFormat().resolvedOptions().timeZone;
}

document.addEventListener("DOMContentLoaded", function () {
  var timeZoneInput = document.getElementById("timezone");

  if (timeZoneInput) {
    timeZoneInput.value = getClientTimeZone();
  }
});

function handleBlockUnblock(action, id, button) {
  var xhr = new XMLHttpRequest();
  var params =
    "action=" + encodeURIComponent(action) + "&id=" + encodeURIComponent(id);

  xhr.open(
    "POST",
    "../../backend/auth/backend-assets/admin-settings/block_unblock_script.php",
    true
  );
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

  xhr.onload = function () {
    if (xhr.status === 200) {
      var response = xhr.responseText.trim();
      console.log("Response:", response);

      if (response.trim() === "") {
        alert("Empty response received. Please check the server.");
      }

      if (response === "success") {
        // Update the button text and blocked state
        button.textContent = action === "block" ? "Unblock" : "Block";
        button.setAttribute("data-blocked", action === "block" ? 1 : 0);
      } else {
        // Handle error case
        alert("Error occurred. Please try again.");
      }
    } else {
      // Handle other HTTP status codes
      alert("HTTP Error: " + xhr.status);
    }
  };

  xhr.onerror = function () {
    // Handle AJAX error
    alert("Error occurred. Please try again.");
  };

  console.log("Request Payload:", params); // Add this line for debugging
  xhr.send(params);
}

document.addEventListener("click", function (event) {
  var target = event.target;

  // Check if the clicked element has the 'block-unblock-btn' class
  if (target.classList.contains("block-unblock-btn")) {
    event.preventDefault();
    console.log("Button clicked!");

    var id = target.getAttribute("data-id");
    var action = target.textContent.trim().toLowerCase(); // Trim and convert to lowercase

    console.log("Action:", action); // Add this line for debugging

    // Call the function to handle block/unblock action
    handleBlockUnblock(action, id, target);
  }
});
