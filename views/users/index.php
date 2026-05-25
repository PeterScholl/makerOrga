<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Mitarbeiter</h1>
    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
    <a href="/users/new" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Neuer Mitarbeiter
    </a>
    <?php endif ?>
</div>

<?php if (empty($users)): ?>
    <p class="text-muted">Noch keine Mitarbeiter eingetragen.</p>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm rounded">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>E-Mail</th>
                <th>Rolle</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td>
                    <a href="/users/<?= $user['id'] ?>" class="text-decoration-none fw-semibold">
                        <?= e($user['name']) ?>
                    </a>
                </td>
                <td><?= $user['email'] ? e($user['email']) : '<span class="text-muted">—</span>' ?></td>
                <td><?= roleBadge($user['role']) ?></td>
                <td>
                    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                    <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
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
