// script.js
document.addEventListener("DOMContentLoaded", function () {
  const wrapperIcon = document.querySelector(".app-sidebar-mb");
  const appWrapperS = document.querySelector(".app-wrapper");
  const deskNav = document.getElementById("des-nav");

  wrapperIcon.addEventListener("click", function () {
    appWrapperS.classList.toggle("show-sidebar");
  });
  deskNav.addEventListener("click", function () {
    appWrapperS.classList.remove("show-sidebar");
  });
});

$("#addEmployeesModal").on("shown.bs.modal", function () {
  $("#projectName").focus();
});
