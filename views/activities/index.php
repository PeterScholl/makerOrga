<?php
/** @var array[] $activities */
/** @var array[] $users */
/** @var array $filters */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Tätigkeiten</h1>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="/activities" class="row g-2 align-items-end">
            <div class="col-sm-auto">
                <label class="form-label small mb-1">Von</label>
                <input type="date" name="from" class="form-control form-control-sm"
                       value="<?= e($filters['from']) ?>">
            </div>
            <div class="col-sm-auto">
                <label class="form-label small mb-1">Bis</label>
                <input type="date" name="to" class="form-control form-control-sm"
                       value="<?= e($filters['to']) ?>">
            </div>
            <div class="col-sm-auto">
                <label class="form-label small mb-1">Mitarbeiter</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Alle</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"
                            <?= (string) ($filters['user_id'] ?? '') === (string) $user['id'] ? 'selected' : '' ?>>
                            <?= e($user['name']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-sm-auto">
                <button class="btn btn-sm btn-primary">Filtern</button>
                <a href="/activities" class="btn btn-sm btn-outline-secondary ms-1">Zurücksetzen</a>
            </div>
        </form>
    </div>
</div>

<?php if (empty($activities)): ?>
    <p class="text-muted">Keine Tätigkeiten im gewählten Zeitraum.</p>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Datum</th>
                        <th>Mitarbeiter</th>
                        <th>Tätigkeit</th>
                        <th>Auftrag</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($activities as $a): ?>
                    <tr>
                        <td class="text-nowrap small"><?= dateFormat($a['worked_at'], true) ?></td>
                        <td class="small"><?= e($a['user_names']) ?></td>
                        <td class="small"><?= nl2br(e($a['description'])) ?></td>
                        <td class="small">
                            <?php if ($a['order_title']): ?>
                                <a href="/orders/<?= $a['order_id'] ?>" class="text-decoration-none">
                                    <?= e($a['order_title']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
    <p class="text-muted small mt-2"><?= count($activities) ?> Einträge</p>
<?php endif ?>
