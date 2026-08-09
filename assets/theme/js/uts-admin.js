/**
 * Small progressive enhancements for the admin control centre.
 * Everything below is optional — every screen works without JavaScript.
 */
(function () {
  "use strict";

  /* Sidebar toggle on small screens. */
  var toggle = document.querySelector(".admin-menu-toggle");
  var sidebar = document.getElementById("admin-sidebar");
  if (toggle && sidebar) {
    toggle.addEventListener("click", function () {
      var open = sidebar.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", String(open));
    });
  }

  /* Live filter for the content editor. */
  var filter = document.querySelector("[data-content-filter]");
  if (filter) {
    var applyFilter = function () {
      var term = filter.value.trim().toLowerCase();
      document.querySelectorAll(".content-section").forEach(function (section) {
        var matches = 0;
        section.querySelectorAll(".content-row").forEach(function (row) {
          var hit = term === "" || row.dataset.search.indexOf(term) !== -1;
          row.hidden = !hit;
          if (hit) matches++;
        });
        section.hidden = matches === 0;
        if (term !== "" && matches > 0) section.open = true;
      });
    };
    filter.addEventListener("input", applyFilter);
    applyFilter();
  }

  /* Suggest a URL slug from the title while the slug is still untouched. */
  var titleField = document.querySelector("[data-slug-source]");
  var slugField = document.querySelector("[data-slug-target]");
  if (titleField && slugField) {
    var slugEdited = slugField.value.trim() !== "";
    slugField.addEventListener("input", function () {
      slugEdited = true;
    });
    titleField.addEventListener("input", function () {
      if (slugEdited) return;
      slugField.value = titleField.value
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");
    });
  }

  /* Live character counters for meta title and description fields. */
  document.querySelectorAll("[data-counter]").forEach(function (field) {
    var ideal = parseInt(field.dataset.counter, 10) || 160;
    var output = document.createElement("span");
    output.className = "hint";
    field.parentNode.appendChild(output);
    var update = function () {
      var length = field.value.length;
      output.textContent = length + " / " + ideal + " characters" + (length > ideal ? " — search engines may truncate this" : "");
    };
    field.addEventListener("input", update);
    update();
  });

  /* Confirm every destructive submit once. */
  document.querySelectorAll("form[data-confirm]").forEach(function (form) {
    form.addEventListener("submit", function (event) {
      if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
  });
})();
