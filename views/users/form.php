<?php
$isEdit  = $user !== null;
$action  = $isEdit ? '/users/' . $user['id'] : '/users';
$heading = $isEdit ? 'Mitarbeiter bearbeiten' : 'Neuer Mitarbeiter';
$val     = fn(string $key) => e((string)($user[$key] ?? ''));
?>

<div class="mb-4">
    <a href="<?= $isEdit ? '/users/' . $user['id'] : '/users' ?>"
       class="text-muted small text-decoration-none">
        <i class="bi bi-arrow-left"></i> Zurück
    </a>
    <h1 class="h3 mt-1"><?= $heading ?></h1>
</div>

<div class="card shadow-sm" style="max-width: 480px">
    <div class="card-body">
        <form method="post" action="<?= $action ?>">

            <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control"
                       value="<?= $val('name') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">E-Mail</label>
                <input type="email" name="email" class="form-control"
                       value="<?= $val('email') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">
                    Passwort <?= $isEdit ? '<span class="text-muted small">(leer lassen = unverändert)</span>' : '<span class="text-danger">*</span>' ?>
                </label>
                <input type="password" name="password" class="form-control"
                       <?= $isEdit ? '' : 'required' ?> autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label class="form-label">Rolle</label>
                <select name="role" class="form-select">
                    <option value="member"      <?= ($user['role'] ?? 'member') === 'member'      ? 'selected' : '' ?>>Mitarbeiter</option>
                    <option value="coordinator" <?= ($user['role'] ?? '')        === 'coordinator' ? 'selected' : '' ?>>Koordinator</option>
                    <option value="admin"       <?= ($user['role'] ?? '')        === 'admin'       ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Speichern</button>
                <a href="<?= $isEdit ? '/users/' . $user['id'] : '/users' ?>"
                   class="btn btn-outline-secondary">Abbrechen</a>
            </div>
        </form>
    </div>
</div>
