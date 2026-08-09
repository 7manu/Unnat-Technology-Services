<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}
if (empty($_SESSION['expense_csrf'])) {
    $_SESSION['expense_csrf'] = bin2hex(random_bytes(32));
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
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="assets/js/script.js" defer></script>
    <meta name="expense-csrf" content="<?= htmlspecialchars($_SESSION['expense_csrf'], ENT_QUOTES, 'UTF-8') ?>">
    <link rel="manifest" href="pwa/manifest.json">
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
            <button class="btn expense-launch-button ms-2" data-bs-toggle="modal" data-bs-target="#expenseModal"><span aria-hidden="true">₹</span> Expense Tracker</button>
        
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

    <!-- Expense workspace -->
    <div class="modal fade" id="expenseModal" tabindex="-1" aria-labelledby="expenseModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable expense-dialog">
            <div class="modal-content expense-workspace">
                <div class="expense-header">
                    <div class="expense-title-wrap">
                        <span class="expense-title-icon" aria-hidden="true">₹</span>
                        <div><p class="expense-kicker">Finance workspace</p><h2 id="expenseModalTitle">Expense tracker</h2><p>Record spending, review the month and keep every cost easy to find.</p></div>
                    </div>
                    <button type="button" class="expense-close" data-bs-dismiss="modal" aria-label="Close expense tracker">×</button>
                </div>

                <div class="modal-body expense-body">
                    <section class="expense-summary" aria-label="Monthly expense summary">
                        <article class="expense-summary-card expense-summary-primary"><span>Total spent</span><strong id="expenseTotal">₹0.00</strong><small id="expenseMonthLabel">This month</small></article>
                        <article class="expense-summary-card"><span>Transactions</span><strong id="expenseCount">0</strong><small>Recorded expenses</small></article>
                        <article class="expense-summary-card"><span>Average expense</span><strong id="expenseAverage">₹0.00</strong><small>Per transaction</small></article>
                    </section>

                    <section class="expense-toolbar" aria-label="Expense filters">
                        <label class="expense-field"><span>Reporting month</span><input type="month" id="expenseMonth" value="<?= date('Y-m') ?>" /></label>
                        <label class="expense-field expense-search-field"><span>Search expenses</span><input type="search" id="expenseSearch" maxlength="100" placeholder="Search by description…" autocomplete="off" /></label>
                    </section>

                    <form class="expense-add-form" id="expenseAddForm">
                        <div class="expense-add-heading"><div><span class="expense-form-icon" aria-hidden="true">＋</span></div><div><h3>Add an expense</h3><p>Capture a new cost for the selected period.</p></div></div>
                        <label class="expense-field"><span>Description</span><input type="text" id="expenseDesc" name="description" maxlength="255" placeholder="e.g. Software subscription" required /></label>
                        <label class="expense-field"><span>Amount</span><div class="expense-money-input"><span>₹</span><input type="number" id="expenseAmount" name="amount" min="0.01" max="9999999999.99" step="0.01" placeholder="0.00" required /></div></label>
                        <label class="expense-field"><span>Expense date</span><input type="date" id="expenseDate" name="date" value="<?= date('Y-m-d') ?>" required /></label>
                        <button class="expense-primary-button" id="addExpenseBtn" type="submit"><span aria-hidden="true">＋</span> Add expense</button>
                    </form>

                    <section class="expense-records" aria-labelledby="expenseRecordsTitle">
                        <div class="expense-records-head"><div><p class="expense-kicker">Transactions</p><h3 id="expenseRecordsTitle">Expense history</h3></div><span id="expenseResultText">Loading expenses…</span></div>
                        <div class="expense-table-wrap">
                            <table class="expense-table">
                                <thead><tr><th>Date</th><th>Description</th><th>Amount</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
                                <tbody id="expenseList"><tr><td colspan="4"><div class="expense-state"><span class="expense-loader" aria-hidden="true"></span><strong>Loading expenses</strong><small>Please wait a moment.</small></div></td></tr></tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit expense dialog -->
    <div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content expense-edit-card" id="expenseEditForm">
                <div class="expense-edit-head"><div><p class="expense-kicker">Update transaction</p><h2 id="editExpenseTitle">Edit expense</h2></div><button type="button" class="expense-close" data-bs-dismiss="modal" aria-label="Close edit dialog">×</button></div>
                <div class="expense-edit-body">
                    <input type="hidden" id="editExpenseId" name="id" />
                    <label class="expense-field"><span>Description</span><input type="text" id="editExpenseDesc" name="description" maxlength="255" required /></label>
                    <label class="expense-field"><span>Amount</span><div class="expense-money-input"><span>₹</span><input type="number" id="editExpenseAmount" name="amount" min="0.01" max="9999999999.99" step="0.01" required /></div></label>
                    <label class="expense-field"><span>Expense date</span><input type="date" id="editExpenseDate" name="date" required /></label>
                </div>
                <div class="expense-edit-footer"><button type="button" class="expense-secondary-button" data-bs-dismiss="modal">Cancel</button><button class="expense-primary-button" type="submit">Save changes</button></div>
            </form>
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

        // Expense interactions are isolated in assets/js/script.js.
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
