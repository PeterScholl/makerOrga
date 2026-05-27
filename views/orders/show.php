<?php
/** @var array $order */
/** @var array[] $activities */
/** @var array[] $users */
/** @var array[] $customers */
?>
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <a href="/orders" class="text-muted small text-decoration-none">
            <i class="bi bi-arrow-left"></i> Zurück zur Liste
        </a>
        <h1 class="h3 mt-1">
            <?= e($order['title']) ?>
            <span class="text-muted fw-normal fs-5 ms-1">#<?= $order['id'] ?></span>
        </h1>
    </div>
    <div class="d-flex gap-2">
        <a href="/orders/<?= $order['id'] ?>/edit" class="btn btn-outline-secondary">
            <i class="bi bi-pencil"></i> Bearbeiten
        </a>
        <form method="post" action="/orders/<?= $order['id'] ?>/delete"
              data-confirm="Auftrag wirklich löschen?">
            <button class="btn btn-outline-danger">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    </div>
</div>

<div class="row g-4">

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Auftragsdaten</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Typ</dt>
                    <dd class="col-sm-8"><?= $order['type'] === 'repair' ? 'Reparatur' : 'Projekt' ?></dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8"><?= statusBadge($order['status']) ?></dd>

                    <dt class="col-sm-4">Priorität</dt>
                    <dd class="col-sm-8"><?= priorityBadge($order['priority']) ?></dd>

                    <dt class="col-sm-4">Abgabe</dt>
                    <dd class="col-sm-8"><?= dateFormat($order['received_at']) ?></dd>

                    <?php if ($order['completed_at']): ?>
                    <dt class="col-sm-4">Abschluss</dt>
                    <dd class="col-sm-8"><?= dateFormat($order['completed_at']) ?></dd>
                    <?php endif ?>

                    <dt class="col-sm-4">Rückgabe</dt>
                    <dd class="col-sm-8">
                        <?= $order['returned'] ? '<span class="text-success"><i class="bi bi-check-circle"></i> Zurückgegeben</span>'
                                               : '<span class="text-muted">Noch nicht zurückgegeben</span>' ?>
                    </dd>

                    <?php if ($order['device_info']): ?>
                    <dt class="col-sm-4">Gerät</dt>
                    <dd class="col-sm-8"><?= e($order['device_info']) ?></dd>
                    <?php endif ?>

                    <?php if ($order['customer_name']): ?>
                    <dt class="col-sm-4">Kunde</dt>
                    <dd class="col-sm-8"><?= e($order['customer_name']) ?></dd>
                    <?php endif ?>

                    <?php if ($order['assigned_user_name']): ?>
                    <dt class="col-sm-4">Mitarbeiter</dt>
                    <dd class="col-sm-8"><?= e($order['assigned_user_name']) ?></dd>
                    <?php endif ?>
                </dl>

                <?php if ($order['description']): ?>
                <hr>
                <p class="mb-0 text-muted small">Beschreibung</p>
                <p class="mb-0"><?= nl2br(e($order['description'])) ?></p>
                <?php endif ?>

                <?php if ($order['result']): ?>
                <hr>
                <p class="mb-0 text-muted small">Ergebnis</p>
                <p class="mb-0"><?= nl2br(e($order['result'])) ?></p>
                <?php endif ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Tätigkeiten</div>
            <div class="card-body">

                <?php if (empty($activities)): ?>
                    <p class="text-muted small">Noch keine Tätigkeiten eingetragen.</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-4">
                    <?php foreach ($activities as $activity): ?>
                        <?php $canEdit = in_array($activity['id'], $editableActivityIds, false); ?>
                        <li class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?= e($activity['user_names']) ?></strong>
                                    <small class="text-muted ms-2"><?= dateFormat($activity['worked_at'], true) ?></small>
                                </div>
                                <?php if ($canEdit): ?>
                                <div class="d-flex gap-1">
                                    <a href="/activities/<?= $activity['id'] ?>/edit"
                                       class="btn btn-sm btn-outline-secondary py-0 px-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="post" action="/activities/<?= $activity['id'] ?>/delete"
                                          data-confirm="Tätigkeit wirklich löschen?">
                                        <button class="btn btn-sm btn-outline-danger py-0 px-1">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                <?php endif ?>
                            </div>
                            <p class="mb-0 small mt-1"><?= nl2br(e($activity['description'])) ?></p>
                        </li>
                    <?php endforeach ?>
                    </ul>
                <?php endif ?>

                <p class="fw-semibold small mb-2">Tätigkeit eintragen</p>
                <form method="post" action="/activities">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                    <div class="mb-2">
                        <select name="user_ids[]" id="primary-worker"
                                class="form-select form-select-sm" required>
                            <option value="">— Mitarbeiter wählen —</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>"><?= e($user['name']) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <?php if (count($users) > 1): ?>
                    <div class="mb-2">
                        <button type="button" id="toggle-extra-workers"
                                class="btn btn-sm btn-link ps-0 text-decoration-none text-secondary">
                            <i class="bi bi-person-plus"></i> Weitere Mitarbeiter hinzufügen
                        </button>
                        <div id="extra-workers-panel" class="d-none mt-1 border rounded p-2">
                            <p class="small text-muted mb-1">Weitere Beteiligte (optional):</p>
                            <?php foreach ($users as $user): ?>
                            <div class="form-check">
                                <input class="form-check-input extra-worker" type="checkbox"
                                       name="user_ids[]" value="<?= $user['id'] ?>"
                                       id="extra_<?= $user['id'] ?>">
                                <label class="form-check-label small" for="extra_<?= $user['id'] ?>">
                                    <?= e($user['name']) ?>
                                </label>
                            </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                    <?php endif ?>

                    <div class="mb-2">
                        <input type="datetime-local" name="worked_at"
                               class="form-control form-control-sm"
                               value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                    <div class="mb-2">
                        <textarea name="description" class="form-control form-control-sm"
                                  rows="2" placeholder="Was wurde gemacht?" required></textarea>
                    </div>
                    <button class="btn btn-sm btn-primary">Eintragen</button>
                </form>

                <script>
                (function () {
                    const primary = document.getElementById('primary-worker');
                    const toggle  = document.getElementById('toggle-extra-workers');
                    const panel   = document.getElementById('extra-workers-panel');
                    const extras  = document.querySelectorAll('.extra-worker');

                    function syncDisabled() {
                        extras.forEach(cb => {
                            cb.disabled = cb.value === primary.value;
                            if (cb.disabled) cb.checked = false;
                        });
                    }

                    toggle?.addEventListener('click', () => {
                        panel.classList.toggle('d-none');
                        const open = !panel.classList.contains('d-none');
                        toggle.innerHTML = open
                            ? '<i class="bi bi-dash-circle"></i> Weitere ausblenden'
                            : '<i class="bi bi-person-plus"></i> Weitere Mitarbeiter hinzufügen';
                        if (open) syncDisabled();
                    });

                    primary?.addEventListener('change', syncDisabled);
                })();
                </script>

            </div>
        </div>
    </div>

</div>
