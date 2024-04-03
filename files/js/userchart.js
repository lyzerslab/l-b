document.addEventListener("DOMContentLoaded", function () {
  var userActivityCtx = document
    .getElementById("userActivityChart")
    .getContext("2d");
  var userActivityChart = new Chart(userActivityCtx, {
    type: "line",
    data: {
      labels: [],
      datasets: [
        {
          label: "Active Users",
          data: [],
          fill: false,
          borderColor: "rgb(75, 192, 192)",
          lineTension: 0.1,
        },
        {
          label: "Inactive Users",
          data: [],
          fill: false,
          borderColor: "rgb(255, 99, 132)",
          lineTension: 0.1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: {
          display: true,
          title: {
            display: true,
            text: "Date",
          },
        },
        y: {
          display: true,
          title: {
            display: true,
            text: "User Activity",
          },
        },
      },
    },
  });

  function updateChart(range) {
    // AJAX request to fetch data from PHP script for user activity chart
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
          var data = JSON.parse(xhr.responseText);
          // Update user activity chart data and labels
          userActivityChart.data.labels = data.labels;
          if (data.datasets && data.datasets.length >= 2) {
            userActivityChart.data.datasets[0].data = data.datasets[0].data;
            userActivityChart.data.datasets[1].data = data.datasets[1].data;
          } else {
            console.error("Invalid dataset structure:", data);
          }
          userActivityChart.update();
        } else {
          console.error(
            "Error fetching data for user activity chart:",
            xhr.status,
            xhr.statusText
          );
        }
      }
    };
    xhr.open(
      "GET",
      "./chart-data/fetch_data.php?range=" + encodeURIComponent(range),
      true
    );
    xhr.send();
  }

  document
    .getElementById("timeRangeSelect")
    .addEventListener("change", function () {
      var selectedRange = this.value;
      updateChart(selectedRange);
    });

  // Initial chart update with default selected range
  updateChart(document.getElementById("timeRangeSelect").value);
});
