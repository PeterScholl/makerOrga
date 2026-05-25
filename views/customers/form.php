<?php
$isEdit  = $customer !== null;
$action  = $isEdit ? '/customers/' . $customer['id'] : '/customers';
$heading = $isEdit ? 'Kunde bearbeiten' : 'Neuer Kunde';
$val     = fn(string $key) => e((string)($customer[$key] ?? ''));
?>

<div class="mb-4">
    <a href="<?= $isEdit ? '/customers/' . $customer['id'] : '/customers' ?>"
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
                <label class="form-label">Telefon</label>
                <input type="tel" name="phone" class="form-control"
                       value="<?= $val('phone') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Interne Notizen</label>
                <textarea name="notes" class="form-control" rows="3"><?= $val('notes') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Speichern</button>
                <a href="<?= $isEdit ? '/customers/' . $customer['id'] : '/customers' ?>"
                   class="btn btn-outline-secondary">Abbrechen</a>
            </div>
        </form>
    </div>
</div>
