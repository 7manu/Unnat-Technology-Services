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

/* ---------------------------------------------------------------------
   Page section builder

   Blocks are held as JSON in a hidden textarea. The UI is rebuilt from the
   definitions the server sends, so the field list for every block type is
   defined in exactly one place (backend/cms_blocks.php).
   ------------------------------------------------------------------ */
(function () {
  "use strict";

  var builder = document.querySelector("[data-block-builder]");
  var jsonField = document.querySelector("[data-block-json]");
  var definitionTag = document.getElementById("block-definitions");
  if (!builder || !jsonField || !definitionTag) return;

  var definitions = JSON.parse(definitionTag.textContent);
  var types = definitions.types;
  var commonFields = definitions.common;
  var mediaTag = document.getElementById("block-media");
  var media = mediaTag ? JSON.parse(mediaTag.textContent) : [];

  var list = builder.querySelector("[data-block-list]");
  var addButtons = builder.querySelector("[data-block-add-buttons]");

  var blocks = [];
  try {
    blocks = JSON.parse(jsonField.value) || [];
  } catch (error) {
    blocks = [];
  }
  if (!Array.isArray(blocks)) blocks = [];

  /* The raw JSON stays available but collapsed once the builder is running. */
  jsonField.closest(".admin-card").classList.add("has-builder");

  var el = function (tag, className, text) {
    var node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  };

  var defaultValue = function (field) {
    if (field.type === "select") return Object.keys(field.options)[0];
    return "";
  };

  var newBlock = function (type) {
    var block = { type: type };
    var fields = types[type].fields || {};
    Object.keys(fields).forEach(function (name) { block[name] = defaultValue(fields[name]); });
    Object.keys(commonFields).forEach(function (name) { block[name] = defaultValue(commonFields[name]); });
    if (types[type].items) block.items = [newItem(type)];
    return block;
  };

  var newItem = function (type) {
    var item = {};
    var fields = types[type].items.fields;
    Object.keys(fields).forEach(function (name) { item[name] = defaultValue(fields[name]); });
    return item;
  };

  var sync = function () {
    jsonField.value = JSON.stringify(blocks, null, 2);
  };

  /* ---- individual field controls ---- */
  var buildField = function (name, field, value, onChange) {
    var wrap = el("div", "admin-field" + (field.type === "html" || field.type === "textarea" ? " full" : ""));
    var id = "f" + Math.random().toString(36).slice(2, 9);
    var label = el("label", null, field.label);
    label.setAttribute("for", id);
    wrap.appendChild(label);

    var input;
    if (field.type === "select") {
      input = el("select");
      Object.keys(field.options).forEach(function (key) {
        var option = el("option", null, field.options[key]);
        option.value = key;
        if (key === value) option.selected = true;
        input.appendChild(option);
      });
    } else if (field.type === "html" || field.type === "textarea") {
      input = el("textarea");
      input.rows = field.rows || 3;
      input.value = value || "";
    } else {
      input = el("input");
      input.type = field.type === "number" ? "number" : "text";
      input.value = value || "";
      if (field.type === "url") input.placeholder = "products.php, #contact or https://…";
    }
    input.id = id;
    input.addEventListener("input", function () { onChange(input.value); });
    input.addEventListener("change", function () { onChange(input.value); });
    wrap.appendChild(input);

    /* Image fields get a picker and a live thumbnail. */
    if (field.type === "image") {
      var tools = el("div", "block-image-tools");
      var pick = el("button", "admin-button ghost small", "Choose image");
      pick.type = "button";
      pick.addEventListener("click", function () {
        openPicker(function (path) {
          input.value = path;
          onChange(path);
          preview.src = path;
          preview.hidden = !path;
        });
      });
      var clear = el("button", "admin-button ghost small", "Clear");
      clear.type = "button";
      clear.addEventListener("click", function () {
        input.value = "";
        onChange("");
        preview.hidden = true;
      });
      tools.appendChild(pick);
      tools.appendChild(clear);
      wrap.appendChild(tools);

      var preview = el("img", "content-image-preview");
      preview.loading = "lazy";
      preview.alt = "";
      preview.hidden = !value;
      if (value) preview.src = value;
      input.addEventListener("input", function () {
        preview.src = input.value;
        preview.hidden = !input.value;
      });
      wrap.appendChild(preview);
    }

    return wrap;
  };

  /* ---- one block ---- */
  var buildBlock = function (block, index) {
    var definition = types[block.type];
    if (!definition) return el("div");

    var card = el("div", "block-card");

    var head = el("div", "block-card-head");
    head.appendChild(el("span", "block-badge", definition.label));
    var summary = el("span", "block-summary", block.heading || block.title || definition.hint || "");
    head.appendChild(summary);

    var controls = el("div", "block-controls");
    [["↑", "Move up", -1], ["↓", "Move down", 1]].forEach(function (spec) {
      var button = el("button", "admin-button ghost small", spec[0]);
      button.type = "button";
      button.title = spec[1];
      button.addEventListener("click", function () {
        var target = index + spec[2];
        if (target < 0 || target >= blocks.length) return;
        var moved = blocks.splice(index, 1)[0];
        blocks.splice(target, 0, moved);
        render();
      });
      controls.appendChild(button);
    });

    var remove = el("button", "admin-button danger small", "Remove");
    remove.type = "button";
    remove.addEventListener("click", function () {
      if (!window.confirm("Remove this " + definition.label.toLowerCase() + " section?")) return;
      blocks.splice(index, 1);
      render();
    });
    controls.appendChild(remove);
    head.appendChild(controls);
    card.appendChild(head);

    var body = el("div", "block-card-body");
    var grid = el("div", "admin-form-grid");

    Object.keys(definition.fields || {}).forEach(function (name) {
      grid.appendChild(buildField(name, definition.fields[name], block[name], function (value) {
        block[name] = value;
        if (name === "heading") summary.textContent = value;
        sync();
      }));
    });
    Object.keys(commonFields).forEach(function (name) {
      grid.appendChild(buildField(name, commonFields[name], block[name], function (value) {
        block[name] = value;
        sync();
      }));
    });
    body.appendChild(grid);

    /* Repeating rows: cards, gallery images, questions, figures, logos. */
    if (definition.items) {
      if (!Array.isArray(block.items)) block.items = [];
      var itemsWrap = el("div", "block-items");
      itemsWrap.appendChild(el("h4", "block-items-title", definition.items.label));

      block.items.forEach(function (item, itemIndex) {
        var row = el("div", "block-item");
        var rowHead = el("div", "block-item-head");
        rowHead.appendChild(el("span", "block-item-index", definition.items.label + " " + (itemIndex + 1)));

        var rowControls = el("div", "block-controls");
        [["↑", -1], ["↓", 1]].forEach(function (spec) {
          var button = el("button", "admin-button ghost small", spec[0]);
          button.type = "button";
          button.addEventListener("click", function () {
            var target = itemIndex + spec[1];
            if (target < 0 || target >= block.items.length) return;
            var moved = block.items.splice(itemIndex, 1)[0];
            block.items.splice(target, 0, moved);
            render();
          });
          rowControls.appendChild(button);
        });
        var removeItem = el("button", "admin-button danger small", "Remove");
        removeItem.type = "button";
        removeItem.addEventListener("click", function () {
          block.items.splice(itemIndex, 1);
          render();
        });
        rowControls.appendChild(removeItem);
        rowHead.appendChild(rowControls);
        row.appendChild(rowHead);

        var itemGrid = el("div", "admin-form-grid");
        Object.keys(definition.items.fields).forEach(function (name) {
          itemGrid.appendChild(buildField(name, definition.items.fields[name], item[name], function (value) {
            item[name] = value;
            sync();
          }));
        });
        row.appendChild(itemGrid);
        itemsWrap.appendChild(row);
      });

      var addItem = el("button", "admin-button ghost small", "+ " + definition.items.add_label);
      addItem.type = "button";
      addItem.addEventListener("click", function () {
        block.items.push(newItem(block.type));
        render();
      });
      itemsWrap.appendChild(addItem);
      body.appendChild(itemsWrap);
    }

    card.appendChild(body);
    return card;
  };

  var render = function () {
    list.innerHTML = "";
    if (!blocks.length) {
      var empty = el("div", "block-empty");
      empty.appendChild(el("strong", null, "No sections yet."));
      empty.appendChild(el("p", "hint", "Pick a block below to start building the page."));
      list.appendChild(empty);
    }
    blocks.forEach(function (block, index) { list.appendChild(buildBlock(block, index)); });
    sync();
  };

  Object.keys(types).forEach(function (type) {
    var button = el("button", "admin-button ghost small", types[type].label);
    button.type = "button";
    button.title = types[type].hint || "";
    button.addEventListener("click", function () {
      blocks.push(newBlock(type));
      render();
      var cards = list.querySelectorAll(".block-card");
      if (cards.length) cards[cards.length - 1].scrollIntoView({ behavior: "smooth", block: "center" });
    });
    addButtons.appendChild(button);
  });

  /* ---- media picker overlay ---- */
  var pickerCallback = null;
  var picker = el("div", "media-picker");
  picker.hidden = true;
  var pickerPanel = el("div", "media-picker-panel");
  var pickerHead = el("div", "media-picker-head");
  pickerHead.appendChild(el("h3", null, "Choose an image"));
  var pickerClose = el("button", "admin-button ghost small", "Close");
  pickerClose.type = "button";
  pickerClose.addEventListener("click", function () { picker.hidden = true; });
  pickerHead.appendChild(pickerClose);
  pickerPanel.appendChild(pickerHead);

  var pickerGrid = el("div", "media-picker-grid");
  if (!media.length) {
    var none = el("p", "hint", "No images uploaded yet. Add some in the media library, then come back.");
    pickerPanel.appendChild(none);
  }
  media.forEach(function (file) {
    var choice = el("button", "media-picker-item");
    choice.type = "button";
    var thumb = el("img");
    thumb.src = file.path;
    thumb.alt = file.alt || "";
    thumb.loading = "lazy";
    choice.appendChild(thumb);
    choice.appendChild(el("code", null, file.path));
    choice.addEventListener("click", function () {
      if (pickerCallback) pickerCallback(file.path);
      picker.hidden = true;
    });
    pickerGrid.appendChild(choice);
  });
  pickerPanel.appendChild(pickerGrid);

  var pickerFoot = el("div", "media-picker-foot");
  var libraryLink = el("a", "admin-button ghost small", "Open media library");
  libraryLink.href = "admin.php?view=media";
  libraryLink.target = "_blank";
  libraryLink.rel = "noopener";
  pickerFoot.appendChild(libraryLink);
  pickerPanel.appendChild(pickerFoot);

  picker.appendChild(pickerPanel);
  picker.addEventListener("click", function (event) {
    if (event.target === picker) picker.hidden = true;
  });
  document.body.appendChild(picker);

  var openPicker = function (callback) {
    pickerCallback = callback;
    picker.hidden = false;
  };

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") picker.hidden = true;
  });

  render();
})();
