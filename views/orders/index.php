<?php /** @var array[] $orders */ ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Aufträge</h1>
    <?php if (in_array($_SESSION['user_role'] ?? '', ['admin', 'coordinator'], true)): ?>
    <a href="/orders/new" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Neuer Auftrag
    </a>
    <?php endif ?>
</div>

<?php if (empty($orders)): ?>
    <p class="text-muted">Noch keine Aufträge vorhanden.</p>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm rounded">
        <thead class="table-dark">
            <tr>
                <th class="text-muted fw-normal" style="width:3rem">Nr.</th>
                <th>Titel</th>
                <th>Typ</th>
                <th>Status</th>
                <th>Priorität</th>
                <th>Kunde</th>
                <th>Mitarbeiter</th>
                <th>Abgabe</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td class="text-muted small"><?= $order['id'] ?></td>
                <td>
                    <a href="/orders/<?= $order['id'] ?>" class="text-decoration-none fw-semibold">
                        <?= htmlspecialchars($order['title']) ?>
                    </a>
                </td>
                <td><?= $order['type'] === 'repair' ? 'Reparatur' : 'Projekt' ?></td>
                <td><?= statusBadge($order['status']) ?></td>
                <td><?= priorityBadge($order['priority']) ?></td>
                <td><?= htmlspecialchars($order['customer_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($order['assigned_user_name'] ?? '—') ?></td>
                <td><?= dateFormat($order['received_at']) ?></td>
                <td>
                    <?php
                    $role    = $_SESSION['user_role'] ?? '';
                    $uid     = (int)($_SESSION['user_id'] ?? 0);
                    $canEdit = in_array($role, ['admin', 'coordinator'], true)
                            || ($role === 'member' && $uid === (int)$order['assigned_user_id']);
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
<?php endif ?>
