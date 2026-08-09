(() => {
  "use strict";

  const expenseModal = document.getElementById("expenseModal");
  if (!expenseModal || typeof window.jQuery === "undefined") return;

  const $ = window.jQuery;
  const csrfToken = document.querySelector('meta[name="expense-csrf"]')?.content || "";
  const currency = new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
  const monthFormatter = new Intl.DateTimeFormat("en-IN", { month: "long", year: "numeric" });
  const dateFormatter = new Intl.DateTimeFormat("en-IN", { day: "2-digit", month: "short", year: "numeric" });
  const records = new Map();
  let searchTimer;

  const toast = (message, isError = false) => {
    if (typeof window.showToast === "function") window.showToast(message, isError);
    else window.alert(message);
  };

  const selectedMonth = () => $("#expenseMonth").val();
  const selectedSearch = () => $("#expenseSearch").val().trim();

  const formatMonth = (month) => {
    const parsed = new Date(`${month}-01T00:00:00`);
    return Number.isNaN(parsed.getTime()) ? "Selected month" : monthFormatter.format(parsed);
  };

  const formatDate = (date) => {
    const parsed = new Date(`${date}T00:00:00`);
    return Number.isNaN(parsed.getTime()) ? date : dateFormatter.format(parsed);
  };

  const setLoadingState = () => {
    $("#expenseResultText").text("Loading expenses…");
    $("#expenseList").empty().append(
      $("<tr>").append(
        $("<td>", { colspan: 4 }).append(
          $("<div>", { class: "expense-state" })
            .append($("<span>", { class: "expense-loader", "aria-hidden": "true" }))
            .append($("<strong>").text("Loading expenses"))
            .append($("<small>").text("Please wait a moment."))
        )
      )
    );
  };

  const setEmptyState = (isSearch) => {
    $("#expenseList").empty().append(
      $("<tr>").append(
        $("<td>", { colspan: 4 }).append(
          $("<div>", { class: "expense-state" })
            .append($("<strong>").text(isSearch ? "No matching expenses" : "No expenses this month"))
            .append($("<small>").text(isSearch ? "Try a different search term." : "Add the first transaction using the form above."))
        )
      )
    );
  };

  const setErrorState = (message) => {
    $("#expenseResultText").text("Unable to load");
    $("#expenseList").empty().append(
      $("<tr>").append(
        $("<td>", { colspan: 4 }).append(
          $("<div>", { class: "expense-state" })
            .append($("<strong>").text("Expenses could not be loaded"))
            .append($("<small>").text(message))
        )
      )
    );
  };

  const renderExpenses = (payload) => {
    const items = Array.isArray(payload.items) ? payload.items : [];
    const summary = payload.summary || {};
    records.clear();

    $("#expenseTotal").text(currency.format(Number(summary.total || 0)));
    $("#expenseCount").text(String(Number(summary.count || 0)));
    $("#expenseAverage").text(currency.format(Number(summary.average || 0)));
    $("#expenseMonthLabel").text(formatMonth(selectedMonth()));
    $("#expenseResultText").text(`${items.length} ${items.length === 1 ? "transaction" : "transactions"}`);

    const $list = $("#expenseList").empty();
    if (items.length === 0) {
      setEmptyState(selectedSearch() !== "");
      return;
    }

    items.forEach((item) => {
      const id = Number(item.id);
      records.set(id, item);

      const $edit = $("<button>", {
        type: "button",
        class: "expense-icon-button edit-expense",
        "aria-label": `Edit ${item.description}`,
        title: "Edit expense",
      }).attr("data-id", id).text("Edit");

      const $delete = $("<button>", {
        type: "button",
        class: "expense-icon-button delete delete-expense",
        "aria-label": `Delete ${item.description}`,
        title: "Delete expense",
      }).attr("data-id", id).text("×");

      const $row = $("<tr>")
        .append($("<td>", { class: "expense-date" }).text(formatDate(item.date)))
        .append($("<td>").append($("<span>", { class: "expense-description" }).text(item.description)))
        .append($("<td>", { class: "expense-amount" }).text(currency.format(Number(item.amount))))
        .append($("<td>").append($("<div>", { class: "expense-row-actions" }).append($edit, $delete)));

      $list.append($row);
    });
  };

  const apiError = (xhr, fallback) => xhr.responseJSON?.error || fallback;

  const loadExpenses = () => {
    setLoadingState();
    return $.ajax({
      url: "backend/expense_handler.php",
      method: "GET",
      dataType: "json",
      data: {
        action: "fetch",
        month: selectedMonth(),
        search: selectedSearch(),
        csrf_token: csrfToken,
      },
    })
      .done(renderExpenses)
      .fail((xhr) => {
        const message = apiError(xhr, "Refresh the page and try again.");
        setErrorState(message);
        toast(message, true);
      });
  };

  const submitExpense = (data, button) => {
    const $button = $(button);
    $button.prop("disabled", true);
    return $.ajax({
      url: "backend/expense_handler.php",
      method: "POST",
      dataType: "json",
      data: { ...data, csrf_token: csrfToken },
    })
      .always(() => $button.prop("disabled", false));
  };

  const syncExpenseDateToMonth = () => {
    const month = selectedMonth();
    const today = new Date();
    const localToday = [today.getFullYear(), String(today.getMonth() + 1).padStart(2, "0"), String(today.getDate()).padStart(2, "0")].join("-");
    $("#expenseDate").val(localToday.startsWith(month) ? localToday : `${month}-01`);
  };

  $(expenseModal).on("show.bs.modal", () => {
    syncExpenseDateToMonth();
    loadExpenses();
  });

  $("#expenseMonth").on("change", () => {
    syncExpenseDateToMonth();
    loadExpenses();
  });

  $("#expenseSearch").on("input", () => {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(loadExpenses, 260);
  });

  $("#expenseAddForm").on("submit", function (event) {
    event.preventDefault();
    const button = this.querySelector('button[type="submit"]');
    submitExpense(
      {
        action: "add",
        description: $("#expenseDesc").val().trim(),
        amount: $("#expenseAmount").val(),
        date: $("#expenseDate").val(),
      },
      button
    )
      .done(() => {
        $("#expenseDesc, #expenseAmount").val("");
        loadExpenses();
        toast("Expense added successfully.");
        $("#expenseDesc").trigger("focus");
      })
      .fail((xhr) => toast(apiError(xhr, "Expense could not be added."), true));
  });

  $(document).on("click", ".edit-expense", function () {
    const item = records.get(Number($(this).attr("data-id")));
    if (!item) return;
    $("#editExpenseId").val(item.id);
    $("#editExpenseDesc").val(item.description);
    $("#editExpenseAmount").val(item.amount);
    $("#editExpenseDate").val(item.date);
    bootstrap.Modal.getOrCreateInstance(document.getElementById("editExpenseModal")).show();
  });

  $("#expenseEditForm").on("submit", function (event) {
    event.preventDefault();
    const button = this.querySelector('button[type="submit"]');
    submitExpense(
      {
        action: "edit",
        id: $("#editExpenseId").val(),
        description: $("#editExpenseDesc").val().trim(),
        amount: $("#editExpenseAmount").val(),
        date: $("#editExpenseDate").val(),
      },
      button
    )
      .done(() => {
        bootstrap.Modal.getInstance(document.getElementById("editExpenseModal"))?.hide();
        loadExpenses();
        toast("Expense updated successfully.");
      })
      .fail((xhr) => toast(apiError(xhr, "Expense could not be updated."), true));
  });

  $(document).on("click", ".delete-expense", function () {
    const id = Number($(this).attr("data-id"));
    const item = records.get(id);
    if (!item || !window.confirm(`Delete “${item.description}”?`)) return;

    submitExpense({ action: "delete", id }, this)
      .done(() => {
        loadExpenses();
        toast("Expense deleted successfully.");
      })
      .fail((xhr) => toast(apiError(xhr, "Expense could not be deleted."), true));
  });
})();
