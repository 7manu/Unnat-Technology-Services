<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Asset Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/Images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/Images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/Images/favicon-16x16.png">
    <link rel="shortcut icon" href="assets/Images/favicon.ico">
    <meta name="theme-color" content="#0d6efd">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="manifest" href="pwa/manifest.json">
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/Assets/pwa/sw.js')  // ✅ Full correct path
        .then(() => console.log("✅ Service Worker Registered"))
        .catch(err => console.error("SW error:", err));
    }
    </script>
    <style>
        body.dark-mode {
            background-color: #121212;
            color: #f1f1f1;
        }

        .dark-mode .card,
        .dark-mode .modal-content,
        .dark-mode .accordion-button,
        .dark-mode .form-control,
        .dark-mode .btn {
            background-color: #1f1f1f !important;
            color: #f1f1f1 !important;
            border-color: #444 !important;
        }

        .dark-mode .accordion-button:not(.collapsed) {
            background-color: #2c2c2c !important;
        }

        .dark-mode .list-group-item {
            background-color: #1f1f1f;
            border-color: #444;
            color: #f1f1f1;
        }
        .dark-mode input,
        .dark-mode select,
        .dark-mode textarea {
            color: #f1f1f1 !important;
            background-color: #1f1f1f !important;
            border-color: #444 !important;
        }

        .dark-mode input::placeholder,
        .dark-mode textarea::placeholder {
            color: #aaa !important;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']['username']); ?> 👋</h2>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <button id="darkModeToggle" class="btn btn-outline-dark btn-sm me-2 mb-2">
                    🌙 Dark Mode
                </button>
                <button class="btn btn-outline-info btn-sm me-2 mb-2" data-bs-toggle="modal" data-bs-target="#analyticsModal">📊 Total Analytics</button>
                <a href="change_password.php" class="btn btn-outline-warning btn-sm me-2 mb-2">Change Password</a>
                <a href="backend/logout.php" class="btn btn-outline-danger btn-sm mb-2">Logout</a>
            </div>
        </div>

        <div class="mb-4">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAssetModal">➕ Add Asset</button>
            <button class="btn btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#expenseModal">💸 Expense Tracker</button>
        
            <input type="text" id="searchInput" class="form-control mt-3" placeholder="Search asset by name...">
            <!-- Filter Accordion -->
            <div class="accordion my-3" id="filterAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="filterHeading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    🔍 Filter & Sort Assets
                </button>
                </h2>
                <div id="filterCollapse" class="accordion-collapse collapse" aria-labelledby="filterHeading" data-bs-parent="#filterAccordion">
                <div class="accordion-body">
                    <div class="row">
                    <div class="col-md-6 col-lg-3 mb-2">
                        <label>Min Monthly Value</label>
                        <input type="number" id="minValue" class="form-control" placeholder="₹ Min">
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <label>Max Monthly Value</label>
                        <input type="number" id="maxValue" class="form-control" placeholder="₹ Max">
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <label>Start Date From</label>
                        <input type="date" id="dateFrom" class="form-control">
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <label>Start Date To</label>
                        <input type="date" id="dateTo" class="form-control">
                    </div>
                    <div class="col-md-6 col-lg-3 mt-3">
                        <label>Sort By:</label>
                        <select id="sortSelect" class="form-select">
                        <option value="latest" selected>Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="low-high">Price: Low to High</option>
                        <option value="high-low">Price: High to Low</option>
                        <option value="az">Name: A–Z</option>
                        <option value="za">Name: Z–A</option>
                        </select>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>

        </div>

        <div id="assetsTable" class="table-responsive">
            <!-- Asset list will be loaded here -->
        </div>
    </div>

    <!-- Add Asset Modal -->
    <div class="modal fade" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addAssetForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Asset Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Approx Monthly Value</label>
                        <input type="number" step="0.01" name="value_per_month" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Details</label>
                        <textarea name="details" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Asset</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Asset Modal -->
    <div class="modal fade" id="editAssetModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editAssetForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-2">
                        <label>Asset Name</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Approx Monthly Value</label>
                        <input type="number" step="0.01" name="value_per_month" id="editValue" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Start Date</label>
                        <input type="date" name="start_date" id="editDate" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Details</label>
                        <textarea name="details" id="editDetails" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Update Asset</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Analytics Modal -->
    <div class="modal fade" id="analyticsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">📊 Asset Summary</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <p><strong>Total Monthly Earning:</strong> ₹<span id="totalMonth"></span></p>
            <p><strong>Estimated Per Day Earning:</strong> ₹<span id="perDay"></span></p>
            <hr>
            <p><strong>Most Profitable Asset:</strong><br><span id="mostAsset"></span></p>
            <p><strong>Least Profitable Asset:</strong><br><span id="leastAsset"></span></p>
        </div>
        </div>
    </div>
    </div>

    <!-- Expense Tracker Modal -->
    <div class="modal fade" id="expenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">💸 Expense Tracker</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="row mb-3">
            <div class="col-md-6">
                <label>Month</label>
                <input type="month" id="expenseMonth" class="form-control" value="<?= date('Y-m') ?>">
                <input type="text" id="expenseSearch" class="form-control mt-2" placeholder="Search expense description...">
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <h5>Total: ₹<span id="expenseTotal">0</span></h5>
            </div>
            </div>

            <div class="input-group mb-3">
            <input type="text" id="expenseDesc" class="form-control" placeholder="Description">
            <input type="number" id="expenseAmount" class="form-control" placeholder="₹ Amount">
            <button class="btn btn-primary" id="addExpenseBtn">➕</button>
            </div>

            <ul class="list-group" id="expenseList"></ul>
        </div>
        </div>
    </div>
    </div>
    <!-- Toast Container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
    <div id="toastBox" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
        <div class="toast-body" id="toastMsg">Action success</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    </div>

    <script>
        function showToast(message, isError = false) {
            const toastBox = new bootstrap.Toast(document.getElementById('toastBox'));
            $("#toastMsg").text(message);
            const box = $("#toastBox");

            box.removeClass("bg-success bg-danger").addClass(isError ? "bg-danger" : "bg-success");
            toastBox.show();
        }

        function loadAssets(query = "") {
            const sort = $("#sortSelect").val();
            const min = $("#minValue").val();
            const max = $("#maxValue").val();
            const dateFrom = $("#dateFrom").val();
            const dateTo = $("#dateTo").val();

            $.get("backend/get_assets.php", {
                q: query,
                sort: sort,
                min: min,
                max: max,
                dateFrom: dateFrom,
                dateTo: dateTo
            }, function(data) {
                $("#assetsTable").html(data);
            });
        }

        $("#searchInput, #sortSelect, #minValue, #maxValue, #dateFrom, #dateTo").on("input change", function() {
            loadAssets($("#searchInput").val());
        });

        $("#addAssetForm").on("submit", async function (e) {
            e.preventDefault();
            const formData = Object.fromEntries(new FormData(this).entries());

            if (navigator.onLine) {
                // Online: submit immediately
                $.post("backend/add_asset.php", formData, function (res) {
                    alert(res);
                    $("#addAssetModal").modal("hide");
                    loadAssets();
                    $("#addAssetForm")[0].reset();
                });
            } else {
                // Offline: store in IndexedDB
                const db = await openAssetDB();
                const tx = db.transaction('pendingAssets', 'readwrite');
                tx.objectStore('pendingAssets').add(formData);
                showToast("No internet! Asset saved offline. Will sync when online.", true);  // red error toast

                $("#addAssetModal").modal("hide");
                $("#addAssetForm")[0].reset();

                // Register background sync
                if ('serviceWorker' in navigator && 'SyncManager' in window) {
                    const reg = await navigator.serviceWorker.ready;
                    reg.sync.register('sync-assets');
                }
            }
        });

        function openAssetDB() {
            return new Promise((resolve, reject) => {
                const req = indexedDB.open('AssetDB', 1);
                req.onerror = () => reject("DB failed");
                req.onsuccess = () => resolve(req.result);
                req.onupgradeneeded = e => {
                    const db = e.target.result;
                    db.createObjectStore('pendingAssets', { keyPath: 'id', autoIncrement: true });
                };
            });
        }

        $("#searchInput").on("keyup", function() {
            let query = $(this).val();
            loadAssets(query);
        });

        // Load initially
        loadAssets();

        // Register service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('pwa/sw.js');
        }
        // Handle Delete button click
        $(document).on("click", ".deleteBtn", function () {
            const id = $(this).data("id");
            if (confirm("Are you sure you want to delete this asset?")) {
                $.post("backend/delete_asset.php", { id }, function (res) {
                    alert(res);
                    loadAssets();
                });
            }
        });
        // Handle Edit button click
        $(document).on("click", ".editBtn", function () {
            const row = $(this).closest("tr");
            $("#editId").val($(this).data("id"));
            $("#editName").val(row.find("td:eq(1)").text());
            $("#editValue").val(row.find("td:eq(2)").text().replace("₹", "").trim());
            $("#editDate").val(row.find("td:eq(3)").text());
            $("#editDetails").val(row.find("td:eq(4)").text().replace(/\n/g, ""));
            $("#editAssetModal").modal("show");
        });

        // Handle form submit
        $("#editAssetForm").submit(function (e) {
            e.preventDefault();
            $.post("backend/edit_asset.php", $(this).serialize(), function (res) {
                alert(res);
                $("#editAssetModal").modal("hide");
                loadAssets();
            });
        });
        $('#analyticsModal').on('show.bs.modal', function () {
            $.get("backend/get_analytics.php", function (data) {
                let analytics = JSON.parse(data);
                $("#totalMonth").text(analytics.totalMonthly);
                $("#perDay").text(analytics.perDay);
                $("#mostAsset").text(analytics.mostProfitable.name + " (₹" + analytics.mostProfitable.value + ")");
                $("#leastAsset").text(analytics.leastProfitable.name + " (₹" + analytics.leastProfitable.value + ")");
            });
        });

        function loadExpenses(month, search = '') {
            $.get("backend/expense_handler.php", { action: "fetch", month, search })
                .done(function (res) {
                    $("#expenseTotal").text(res.total ?? 0);
                    const $list = $("#expenseList").empty();

                    if (res.items && res.items.length > 0) {
                        res.items.forEach(exp => {
                            $list.append(`
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>${exp.date} - ${exp.description}</span>
                                    <div>
                                        <strong class="me-2">₹${exp.amount}</strong>
                                        <button class="btn btn-sm btn-warning edit-expense" 
                                                data-id="${exp.id}" 
                                                data-desc="${exp.description}" 
                                                data-amt="${exp.amount}">✏️</button>
                                        <button class="btn btn-sm btn-danger delete-expense" 
                                                data-id="${exp.id}">🗑️</button>
                                    </div>
                                </li>
                            `);
                        });
                    } else {
                        $list.html(`<li class="list-group-item text-center text-muted">No expenses found for this month.</li>`);
                    }
                })
                .fail(function (xhr) {
                    console.error("Failed to load expenses:", xhr.responseText);
                    showToast("❌ Failed to load expenses", true);
                });
        }

        // Expense search input handler
        $("#expenseSearch").on("input", function () {
            loadExpenses($("#expenseMonth").val(), $(this).val());
        });

        // Month change handler
        $("#expenseMonth").on("change", function () {
            loadExpenses(this.value, $("#expenseSearch").val());
        });

        // Modal open: load current month's expenses with search
        $('#expenseModal').on('show.bs.modal', function () {
            loadExpenses($("#expenseMonth").val(), $("#expenseSearch").val());
        });

        $(document).ready(function () {
            $("#addExpenseBtn").on("click", function () {
                const desc = $("#expenseDesc").val();
                const amt = $("#expenseAmount").val();
                if (desc && amt > 0) {
                    $.post("backend/expense_handler.php?action=add", {
                        description: desc,
                        amount: amt
                    }, function (res) {
                        $("#expenseDesc").val("");
                        $("#expenseAmount").val("");
                        loadExpenses($("#expenseMonth").val());
                        showToast("Expense added successfully");
                    });
                } else {
                    showToast("Enter valid description and amount", true);
                }
            });
        });
        
        // DELETE Expense
        $(document).on("click", ".delete-expense", function () {
            const id = $(this).data("id");
            if (confirm("Delete this expense?")) {
                $.post("backend/expense_handler.php", {
                    action: "delete",
                    id: id
                }, function (res) {
                    if (res.success) {
                        loadExpenses($("#expenseMonth").val());
                        showToast("Expense deleted successfully");
                    } else {
                        showToast("Failed to delete", true);
                    }
                });
            }
        });

        // EDIT Expense
        $(document).on("click", ".edit-expense", function () {
            const id = $(this).data("id");
            const currentDesc = $(this).data("desc");
            const currentAmt = $(this).data("amt");

            const newDesc = prompt("Edit description:", currentDesc);
            const newAmt = prompt("Edit amount:", currentAmt);

            if (newDesc !== null && newAmt !== null && parseFloat(newAmt) > 0) {
                $.post("backend/expense_handler.php", {
                    action: "edit",
                    id: id,
                    description: newDesc,
                    amount: newAmt
                }, function (res) {
                    if (res.success) {
                        loadExpenses($("#expenseMonth").val());
                        showToast("Expense updated successfully");
                    } else {
                        showToast("Failed to update", true);
                    }
                });
            }
        });
    </script>
    <script>
        // On page load, apply saved theme
        document.addEventListener("DOMContentLoaded", () => {
            if (localStorage.getItem("theme") === "dark") {
                document.body.classList.add("dark-mode");
                document.getElementById("darkModeToggle").innerText = "☀️ Light Mode";
            }
        });

        // Toggle dark/light mode
        document.getElementById("darkModeToggle").addEventListener("click", () => {
            document.body.classList.toggle("dark-mode");
            const isDark = document.body.classList.contains("dark-mode");
            localStorage.setItem("theme", isDark ? "dark" : "light");
            document.getElementById("darkModeToggle").innerText = isDark ? "☀️ Light Mode" : "🌙 Dark Mode";
        });
    </script>

</body>
</html>
