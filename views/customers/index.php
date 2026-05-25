<?php /** @var array[] $customers */ ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Kunden</h1>
    <?php if (in_array($_SESSION['user_role'] ?? '', ['admin', 'coordinator'], true)): ?>
    <a href="/customers/new" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Neuer Kunde
    </a>
    <?php endif ?>
</div>

<?php if (empty($customers)): ?>
    <p class="text-muted">Noch keine Kunden eingetragen.</p>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm rounded">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>E-Mail</th>
                <th>Telefon</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
            <tr>
                <td>
                    <a href="/customers/<?= $customer['id'] ?>" class="text-decoration-none fw-semibold">
                        <?= e($customer['name']) ?>
                    </a>
                </td>
                <td><?= e($customer['email'] ?? '—') ?></td>
                <td><?= e($customer['phone'] ?? '—') ?></td>
                <td>
                    <?php if (in_array($_SESSION['user_role'] ?? '', ['admin', 'coordinator'], true)): ?>
                    <a href="/customers/<?= $customer['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
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
