<?php
/** @var array[] $orders */
/** @var array   $filters */
/** @var string  $sort */
/** @var string  $dir */

// Anzahl aktiver Filter für Badge am Button
$activeFilterCount = count(array_filter([
    !empty($filters['status']),
    !empty($filters['priority']),
    !empty($filters['type']),
]));

// Hilfsfunktion: URL für Spalten-Sortierung (erhält aktive Filter)
$sortUrl = function (string $col) use ($sort, $dir, $filters): string {
    $nextDir = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
    $params  = array_filter([
        'sort'     => $col,
        'dir'      => $nextDir,
        'status'   => $filters['status']   ?: null,
        'priority' => $filters['priority'] ?: null,
        'type'     => $filters['type']     ?: null,
    ]);
    return '/orders?' . http_build_query($params);
};

// Hilfsfunktion: Sortier-Icon für Spaltenköpfe
$sortIcon = function (string $col) use ($sort, $dir): string {
    if ($sort !== $col) return '<i class="bi bi-arrow-down-up text-secondary opacity-50 ms-1"></i>';
    return $dir === 'asc'
        ? '<i class="bi bi-arrow-up ms-1"></i>'
        : '<i class="bi bi-arrow-down ms-1"></i>';
};
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Aufträge</h1>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#filter-panel">
            <i class="bi bi-funnel"></i> Filter
            <?php if ($activeFilterCount > 0): ?>
                <span class="badge bg-primary ms-1"><?= $activeFilterCount ?></span>
            <?php endif ?>
        </button>
        <?php if (in_array($_SESSION['user_role'] ?? '', ['admin', 'coordinator'], true)): ?>
        <a href="/orders/new" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Neuer Auftrag
        </a>
        <?php endif ?>
    </div>
</div>

<div class="collapse <?= $activeFilterCount > 0 ? 'show' : '' ?>" id="filter-panel">
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="get" action="/orders" class="row g-3">
                <?php if ($sort !== 'received_at' || $dir !== 'desc'): ?>
                    <input type="hidden" name="sort" value="<?= e($sort) ?>">
                    <input type="hidden" name="dir"  value="<?= e($dir) ?>">
                <?php endif ?>

                <div class="col-sm-4">
                    <p class="fw-semibold small mb-2">Status</p>
                    <?php foreach (['open' => 'Offen', 'in_progress' => 'In Bearbeitung', 'done' => 'Abgeschlossen', 'closed' => 'Archiviert'] as $val => $label): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="status[]" value="<?= $val ?>" id="s_<?= $val ?>"
                               <?= in_array($val, $filters['status'], true) ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="s_<?= $val ?>"><?= $label ?></label>
                    </div>
                    <?php endforeach ?>
                </div>

                <div class="col-sm-4">
                    <p class="fw-semibold small mb-2">Priorität</p>
                    <?php foreach (['low' => 'Niedrig', 'normal' => 'Normal', 'high' => 'Hoch'] as $val => $label): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="priority[]" value="<?= $val ?>" id="p_<?= $val ?>"
                               <?= in_array($val, $filters['priority'], true) ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="p_<?= $val ?>"><?= $label ?></label>
                    </div>
                    <?php endforeach ?>
                </div>

                <div class="col-sm-4">
                    <p class="fw-semibold small mb-2">Typ</p>
                    <?php foreach (['repair' => 'Reparatur', 'project' => 'Projekt'] as $val => $label): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="type[]" value="<?= $val ?>" id="t_<?= $val ?>"
                               <?= in_array($val, $filters['type'], true) ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="t_<?= $val ?>"><?= $label ?></label>
                    </div>
                    <?php endforeach ?>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-sm btn-primary">Anwenden</button>
                    <a href="/orders" class="btn btn-sm btn-outline-secondary">Zurücksetzen</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (empty($orders)): ?>
    <p class="text-muted">Keine Aufträge gefunden.</p>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm rounded">
        <thead class="table-dark">
            <tr>
                <th class="fw-normal" style="width:3.5rem">
                    <a href="<?= $sortUrl('id') ?>" class="text-white text-decoration-none">
                        Nr.<?= $sortIcon('id') ?>
                    </a>
                </th>
                <th>
                    <a href="<?= $sortUrl('title') ?>" class="text-white text-decoration-none">
                        Titel<?= $sortIcon('title') ?>
                    </a>
                </th>
                <th>Typ</th>
                <th>
                    <a href="<?= $sortUrl('status') ?>" class="text-white text-decoration-none">
                        Status<?= $sortIcon('status') ?>
                    </a>
                </th>
                <th>
                    <a href="<?= $sortUrl('priority') ?>" class="text-white text-decoration-none">
                        Priorität<?= $sortIcon('priority') ?>
                    </a>
                </th>
                <th>Kunde</th>
                <th>Mitarbeiter</th>
                <th>
                    <a href="<?= $sortUrl('received_at') ?>" class="text-white text-decoration-none">
                        Abgabe<?= $sortIcon('received_at') ?>
                    </a>
                </th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td class="text-muted small"><?= $order['id'] ?></td>
                <td>
                    <a href="/orders/<?= $order['id'] ?>" class="text-decoration-none fw-semibold">
                        <?= e($order['title']) ?>
                    </a>
                </td>
                <td class="small"><?= $order['type'] === 'repair' ? 'Reparatur' : 'Projekt' ?></td>
                <td><?= statusBadge($order['status']) ?></td>
                <td><?= priorityBadge($order['priority']) ?></td>
                <td class="small"><?= e($order['customer_name'] ?? '—') ?></td>
                <td class="small"><?= e($order['assigned_user_name'] ?? '—') ?></td>
                <td class="small"><?= dateFormat($order['received_at']) ?></td>
                <td>
                    <?php
                    $role    = $_SESSION['user_role'] ?? '';
                    $uid     = (int) ($_SESSION['user_id'] ?? 0);
                    $canEdit = in_array($role, ['admin', 'coordinator'], true)
                            || ($role === 'member' && $uid === (int) $order['assigned_user_id']);
                    ?>
                    <?php if ($canEdit): ?>
                    <a href="/orders/<?= $order['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <?php endif ?>
                </td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
<p class="text-muted small mt-1"><?= count($orders) ?> Aufträge</p>
<?php endif ?>
