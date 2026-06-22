<?php

require_once __DIR__ . '/envParser.php';
require_once __DIR__ . '/services/ApiService.php';

$apiService = new ApiService($token, $apiUrl);

$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : null;
$dateTo   = isset($_GET['date_to'])   ? trim($_GET['date_to'])   : null;
$page     = isset($_GET['page'])      ? max(0, (int)$_GET['page']) : 0;
$limit    = isset($_GET['limit'])     ? max(10, (int)$_GET['limit']) : 100; // по дефолту 100

$result = $apiService->getStatuses($dateFrom, $dateTo, $page, $limit);

$leads = [];
$apiError = "";

if ($result['success']) {
    $leads = $result['data'];
} else {
    $apiError = $result['message'];
}

function getStatusBadgeClass(string $status): string {
    $status = strtolower(trim($status));
    return match($status) {
        'approved', 'success', 'confirmed' => 'badge--success',
        'pending', 'new'                   => 'badge--warning',
        'rejected', 'cancelled', 'trash'   => 'badge--danger',
        default                            => 'badge--warning',
    };
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Management - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav>
                <ul class="header__wrapper">
                    <li><a href="/" class="header__link">Add lead</a></li>
                    <li><a href="/leads.php" class="header__link">View leads</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="dashboard">
            <div class="dashboard__header">
                <div>
                    <h2>Leads Status Dashboard</h2>
                </div>

                <button type="button" class="btn" id="refreshBtn" onclick="window.location.reload();">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                    Refresh Data
                </button>
            </div>

            <div class="dashboard__filter">
                <form action="leads.php" method="GET" class="dashboard__filter-form">
                    <div class="dashboard__filter-group">
                        <label for="dateFrom">Date From:</label>
                        <input type="date" id="dateFrom" name="date_from" value="<?= htmlspecialchars($dateFrom ?? '') ?>">
                    </div>
                    <div class="dashboard__filter-group">
                        <label for="dateTo">Date To:</label>
                        <input type="date" id="dateTo" name="date_to" value="<?= htmlspecialchars($dateTo ?? '') ?>">
                    </div>
                    <div class="dashboard__filter-group">
                        <label for="limit">Leads per page:</label>
                        <select id="limit" name="limit" style="padding: 6px 10px; border: 1px solid var(--line-color); border-radius: 4px; background: white; font-size: 0.9rem;">
                            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                            <option value="200" <?= $limit == 200 ? 'selected' : '' ?>>200</option>
                            <option value="500" <?= $limit == 500 ? 'selected' : '' ?>>500 (Max)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn dashboard__filter-btn">Apply Filter</button>

                    <?php if (!empty($dateFrom) || !empty($dateTo)): ?>
                        <a href="leads.php" class="btn" style="border-color: #f87171; color: #f87171;">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (!empty($apiError)): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 4px; margin-bottom: 25px; text-align: center; font-weight: 500;">
                    CRM Error: <?= htmlspecialchars($apiError) ?>
                </div>
            <?php endif; ?>

            <div class="dashboard__table-container">
                <table class="dashboard__table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>FTD Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($leads)): ?>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($lead['id'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['email'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge <?= getStatusBadgeClass($lead['status'] ?? '') ?>">
                                        <?php
                                            $statusText = !empty($lead['status']) ? $lead['status'] : 'New';
                                            echo htmlspecialchars(ucfirst($statusText));
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($lead['ftd'] ?? 0) == 1): ?>
                                        <span class="badge badge--ftd-yes">Yes (Deposit)</span>
                                    <?php else: ?>
                                        <span class="badge badge--ftd-no">No</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #64748b; padding: 30px 0;">
                                No leads found for the selected period.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="dashboard__pagination" style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 25px;">

                <?php if ($page > 0): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn">
                        &larr; Previous Page
                    </a>
                <?php else: ?>
                    <button class="btn" $disabled style="opacity: 0.5; cursor: not-allowed;">&larr; Previous Page</button>
                <?php endif; ?>

                <span style="font-weight: 600; font-size: 0.95rem; color: #475569;">Page <?= $page + 1 ?></span>

                <?php if (count($leads) >= $limit): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn">
                        Next Page &rarr;
                    </a>
                <?php else: ?>
                    <button class="btn" $disabled style="opacity: 0.5; cursor: not-allowed;">Next Page &rarr;</button>
                <?php endif; ?>
            </div>
        </section>
    </main>

</body>
</html>