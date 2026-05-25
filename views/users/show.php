<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <a href="/users" class="text-muted small text-decoration-none">
            <i class="bi bi-arrow-left"></i> Zurück zur Liste
        </a>
        <h1 class="h3 mt-1"><?= e($user['name']) ?></h1>
    </div>
    <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-outline-secondary">
        <i class="bi bi-pencil"></i> Bearbeiten
    </a>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Profil</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">E-Mail</dt>
                    <dd class="col-sm-8"><?= e($user['email']) ?></dd>

                    <dt class="col-sm-4">Rolle</dt>
                    <dd class="col-sm-8"><?= roleBadge($user['role']) ?></dd>

                    <dt class="col-sm-4">Dabei seit</dt>
                    <dd class="col-sm-8"><?= dateFormat($user['created_at']) ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Tätigkeiten</div>
            <div class="card-body">
                <?php if (empty($activities)): ?>
                    <p class="text-muted small">Noch keine Tätigkeiten eingetragen.</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                    <?php foreach ($activities as $a): ?>
                        <li class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <?php if ($a['order_title']): ?>
                                    <a href="/orders/<?= $a['order_id'] ?>" class="text-decoration-none small fw-semibold">
                                        <?= e($a['order_title']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="small text-muted">Ohne Auftrag</span>
                                <?php endif ?>
                                <small class="text-muted"><?= dateFormat($a['worked_at'], true) ?></small>
                            </div>
                            <p class="mb-0 small"><?= nl2br(e($a['description'])) ?></p>
                        </li>
                    <?php endforeach ?>
                    </ul>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>
