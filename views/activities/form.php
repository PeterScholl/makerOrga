<?php
// $canEditAll: true für Admin/Koordinator, false für Mitarbeiter
$daysLeft = null;
if (!$canEditAll && ACTIVITY_EDIT_DAYS > 0) {
    $ageSeconds = time() - strtotime($activity['created_at']);
    $daysLeft   = max(0, ACTIVITY_EDIT_DAYS - (int) floor($ageSeconds / 86400));
}
?>

<div class="mb-4">
    <a href="<?= $activity['order_id'] ? '/orders/' . $activity['order_id'] : '/orders' ?>"
       class="text-muted small text-decoration-none">
        <i class="bi bi-arrow-left"></i> Zurück zum Auftrag
    </a>
    <h1 class="h3 mt-1">Tätigkeit bearbeiten</h1>
</div>

<?php if ($daysLeft !== null): ?>
<div class="alert alert-info py-2 small">
    <?php if ($daysLeft > 0): ?>
        Noch <strong><?= $daysLeft ?> Tag<?= $daysLeft !== 1 ? 'e' : '' ?></strong>
        bearbeitbar — danach ist diese Tätigkeit gesperrt.
    <?php else: ?>
        Das Bearbeitungsfenster ist abgelaufen. Bitte einen Koordinator um Korrektur.
    <?php endif ?>
</div>
<?php endif ?>

<div class="card shadow-sm" style="max-width: 540px">
    <div class="card-body">
        <form method="post" action="/activities/<?= $activity['id'] ?>">

            <?php if ($canEditAll): ?>
            <div class="mb-3">
                <label class="form-label">Mitarbeiter</label>
                <?php foreach ($users as $user): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="user_ids[]" value="<?= $user['id'] ?>"
                           id="u_<?= $user['id'] ?>"
                           <?= in_array($user['id'], $assigned, false) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="u_<?= $user['id'] ?>">
                        <?= e($user['name']) ?>
                    </label>
                </div>
                <?php endforeach ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Datum / Uhrzeit</label>
                <input type="datetime-local" name="worked_at" class="form-control"
                       value="<?= date('Y-m-d\TH:i', strtotime($activity['worked_at'])) ?>" required>
            </div>
            <?php endif ?>

            <div class="mb-3">
                <label class="form-label">Beschreibung <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" rows="4" required><?= e($activity['description']) ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Speichern</button>
                <a href="<?= $activity['order_id'] ? '/orders/' . $activity['order_id'] : '/orders' ?>"
                   class="btn btn-outline-secondary">Abbrechen</a>
            </div>

        </form>
    </div>
</div>
