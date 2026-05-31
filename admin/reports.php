<?php
require 'includes/admin_auth.php';

$page_title = 'Reports';

$stats = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN `status` = 'Pending'     THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN `status` = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN `status` = 'Completed'   THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN `status` = 'Rejected'    THEN 1 ELSE 0 END) AS rejected,
        SUM(CASE WHEN `priority` = 'High'      THEN 1 ELSE 0 END) AS high_p,
        SUM(CASE WHEN `priority` = 'Medium'    THEN 1 ELSE 0 END) AS medium_p,
        SUM(CASE WHEN `priority` = 'Low'       THEN 1 ELSE 0 END) AS low_p
    FROM requests
")->fetch_assoc();

$total = max((int)$stats['total'], 1);

$cat_result = $conn->query("
    SELECT category, COUNT(*) AS cnt
    FROM   requests
    GROUP  BY category
    ORDER  BY cnt DESC
");

$bld_result = $conn->query("
    SELECT building_name, COUNT(*) AS cnt
    FROM   requests
    GROUP  BY building_name
    ORDER  BY cnt DESC
    LIMIT  8
");

require 'includes/admin_header.php';
?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Total',       $stats['total'],       'bi-clipboard-data'],
        ['Pending',     $stats['pending'],      'bi-hourglass-split'],
        ['In Progress', $stats['in_progress'],  'bi-arrow-repeat'],
        ['Completed',   $stats['completed'],    'bi-check-circle'],
        ['Rejected',    $stats['rejected'],     'bi-x-circle'],
    ];
    foreach ($cards as [$label, $val, $icon]): ?>
        <div class="col-6 col-xl" style="flex:1 1 140px;">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-number"><?= (int)$val ?></div>
                        <div class="stat-label"><?= $label ?></div>
                    </div>
                    <i class="bi <?= $icon ?> stat-icon"></i>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">

    <!-- Category Breakdown -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                <i class="bi bi-tag text-muted"></i>
                <span class="fw-semibold">Requests by Category</span>
            </div>
            <div class="card-body">
                <?php if ($cat_result->num_rows === 0): ?>
                    <p class="text-muted text-center py-4">No data yet.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php while ($c = $cat_result->fetch_assoc()):
                            $pct = round(($c['cnt'] / $total) * 100);
                        ?>
                            <div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-medium"><?= htmlspecialchars($c['category']) ?></span>
                                    <span class="small text-muted"><?= $c['cnt'] ?> (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width:<?= $pct ?>%"></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Priority + Status -->
    <div class="col-lg-6 d-flex flex-column gap-4">

        <div class="card">
            <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle text-muted"></i>
                <span class="fw-semibold">Requests by Priority</span>
            </div>
            <div class="card-body d-flex flex-column gap-3">
                <?php
                $pdata = [
                    'High'   => [(int)$stats['high_p'],   'bg-danger'],
                    'Medium' => [(int)$stats['medium_p'],  'bg-warning'],
                    'Low'    => [(int)$stats['low_p'],     'bg-success'],
                ];
                foreach ($pdata as $lbl => [$cnt, $bar]):
                    $pct = round(($cnt / $total) * 100);
                ?>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-medium"><?= $lbl ?> Priority</span>
                            <span class="small text-muted"><?= $cnt ?> (<?= $pct ?>%)</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar <?= $bar ?>" role="progressbar" style="width:<?= $pct ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                <i class="bi bi-pie-chart text-muted"></i>
                <span class="fw-semibold">Requests by Status</span>
            </div>
            <div class="card-body d-flex flex-column gap-3">
                <?php
                $sdata = [
                    'Pending'     => [(int)$stats['pending'],     'bg-warning'],
                    'In Progress' => [(int)$stats['in_progress'], 'bg-info'],
                    'Completed'   => [(int)$stats['completed'],   'bg-success'],
                    'Rejected'    => [(int)$stats['rejected'],    'bg-danger'],
                ];
                foreach ($sdata as $lbl => [$cnt, $bar]):
                    $pct = round(($cnt / $total) * 100);
                ?>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-medium"><?= $lbl ?></span>
                            <span class="small text-muted"><?= $cnt ?> (<?= $pct ?>%)</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar <?= $bar ?>" role="progressbar" style="width:<?= $pct ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- Buildings -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                <i class="bi bi-buildings text-muted"></i>
                <span class="fw-semibold">Requests by Building</span>
            </div>
            <div class="card-body">
                <?php if ($bld_result->num_rows === 0): ?>
                    <p class="text-muted text-center py-4">No data yet.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php while ($b = $bld_result->fetch_assoc()):
                            $pct = round(($b['cnt'] / $total) * 100);
                        ?>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-medium text-truncate" style="max-width:200px;">
                                        <?= htmlspecialchars($b['building_name']) ?>
                                    </span>
                                    <span class="small text-muted ms-2"><?= $b['cnt'] ?> (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width:<?= $pct ?>%"></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php require 'includes/admin_footer.php'; ?>
