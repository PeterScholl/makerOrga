<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <a href="/users" class="text-muted small text-decoration-none">
            <i class="bi bi-arrow-left"></i> Zurück zur Liste
        </a>
        <h1 class="h3 mt-1"><?= e($user['name']) ?></h1>
    </div>
    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
    <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-outline-secondary">
        <i class="bi bi-pencil"></i> Bearbeiten
    </a>
    <?php endif ?>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Profil</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Benutzername</dt>
                    <dd class="col-sm-8"><?= e($user['username']) ?></dd>

                    <dt class="col-sm-4">E-Mail</dt>
                    <dd class="col-sm-8"><?= $user['email'] ? e($user['email']) : '<span class="text-muted">—</span>' ?></dd>

                    <dt class="col-sm-4">Rolle</dt>
                    <dd class="col-sm-8"><?= roleBadge($user['role']) ?></dd>

                    <dt class="col-sm-4">Dabei seit</dt>
                    <dd class="col-sm-8"><?= dateFormat($user['created_at']) ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <?php
    $isOwnProfile = (int) ($_SESSION['user_id'] ?? 0) === (int) $user['id'];
    $isAdmin      = ($_SESSION['user_role'] ?? '') === 'admin';
    if ($isOwnProfile || $isAdmin):
    ?>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Passwort ändern</div>
            <div class="card-body">
                <form method="post" action="/users/<?= $user['id'] ?>/password">
                    <?php if ($isOwnProfile): ?>
                    <div class="mb-2">
                        <label class="form-label small">Aktuelles Passwort</label>
                        <input type="password" name="current_password"
                               class="form-control form-control-sm" required autocomplete="current-password">
                    </div>
                    <?php endif ?>
                    <div class="mb-2">
                        <label class="form-label small">Neues Passwort</label>
                        <input type="password" name="new_password"
                               class="form-control form-control-sm" required autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Neues Passwort bestätigen</label>
                        <input type="password" name="confirm_password"
                               class="form-control form-control-sm" required autocomplete="new-password">
                    </div>
                    <button class="btn btn-sm btn-primary">Speichern</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif ?>

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
