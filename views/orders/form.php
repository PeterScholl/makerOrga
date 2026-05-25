<?php
// $order ist null beim Anlegen, ein Array beim Bearbeiten
$isEdit    = $order !== null;
$action    = $isEdit ? '/orders/' . $order['id'] : '/orders';
$heading   = $isEdit ? 'Auftrag bearbeiten' : 'Neuer Auftrag';
$canAssign = in_array($_SESSION['user_role'] ?? '', ['admin', 'coordinator'], true);

// Hilfsfunktion: gibt gespeicherten Wert oder Fallback zurück
$val = fn(string $key, string $default = '') => e((string)($order[$key] ?? $default));
?>

<div class="mb-4">
    <a href="<?= $isEdit ? '/orders/' . $order['id'] : '/orders' ?>" class="text-muted small text-decoration-none">
        <i class="bi bi-arrow-left"></i> Zurück
    </a>
    <h1 class="h3 mt-1"><?= $heading ?></h1>
</div>

<div class="card shadow-sm" style="max-width: 640px">
    <div class="card-body">
        <form method="post" action="<?= $action ?>">

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label class="form-label">Typ</label>
                    <select name="type" class="form-select">
                        <option value="repair"  <?= ($order['type'] ?? 'repair') === 'repair'  ? 'selected' : '' ?>>Reparatur</option>
                        <option value="project" <?= ($order['type'] ?? '')        === 'project' ? 'selected' : '' ?>>Projekt</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Priorität</label>
                    <select name="priority" class="form-select">
                        <option value="low"    <?= ($order['priority'] ?? '') === 'low'    ? 'selected' : '' ?>>Niedrig</option>
                        <option value="normal" <?= ($order['priority'] ?? 'normal') === 'normal' ? 'selected' : '' ?>>Normal</option>
                        <option value="high"   <?= ($order['priority'] ?? '') === 'high'   ? 'selected' : '' ?>>Hoch</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Titel <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control"
                       value="<?= $val('title') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Gerätebeschreibung</label>
                <input type="text" name="device_info" class="form-control"
                       value="<?= $val('device_info') ?>" placeholder="z.B. Lenovo ThinkPad T14, schwarz">
            </div>

            <div class="mb-3">
                <label class="form-label">Fehler- / Aufgabenbeschreibung</label>
                <textarea name="description" class="form-control" rows="3"><?= $val('description') ?></textarea>
            </div>

            <div class="row g-3 mb-3">
                <?php if ($canAssign): ?>
                <div class="col-sm-6">
                    <label class="form-label">Kunde</label>
                    <select name="customer_id" class="form-select">
                        <option value="">— kein Kunde —</option>
                        <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"
                            <?= ($order['customer_id'] ?? null) == $c['id'] ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Zugewiesener Mitarbeiter</label>
                    <select name="assigned_user_id" class="form-select">
                        <option value="">— niemand —</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>"
                            <?= ($order['assigned_user_id'] ?? null) == $u['id'] ? 'selected' : '' ?>>
                            <?= e($u['name']) ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <?php else: ?>
                <div class="col-sm-6">
                    <label class="form-label">Kunde</label>
                    <p class="form-control-plaintext"><?= e($order['customer_name'] ?? '—') ?></p>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Zugewiesener Mitarbeiter</label>
                    <p class="form-control-plaintext"><?= e($order['assigned_user_name'] ?? '—') ?></p>
                </div>
                <?php endif ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Abgabedatum</label>
                <input type="date" name="received_at" class="form-control"
                       value="<?= $val('received_at', date('Y-m-d')) ?>">
            </div>

            <?php if ($isEdit): ?>
            <hr>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach (['open' => 'Offen', 'in_progress' => 'In Bearbeitung', 'done' => 'Abgeschlossen', 'closed' => 'Archiviert'] as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $order['status'] === $key ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Abschlussdatum</label>
                    <input type="date" name="completed_at" class="form-control"
                           value="<?= $val('completed_at') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Ergebnis / Abschlussbericht</label>
                <textarea name="result" class="form-control" rows="2"><?= $val('result') ?></textarea>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="returned" class="form-check-input" id="returned"
                       <?= $order['returned'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="returned">Gerät / Material zurückgegeben</label>
            </div>
            <?php endif ?>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Speichern</button>
                <a href="<?= $isEdit ? '/orders/' . $order['id'] : '/orders' ?>"
                   class="btn btn-outline-secondary">Abbrechen</a>
            </div>

        </form>
    </div>
</div>
