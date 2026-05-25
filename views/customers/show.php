<?php
/** @var array $customer */
/** @var array[] $orders */
?>
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <a href="/customers" class="text-muted small text-decoration-none">
            <i class="bi bi-arrow-left"></i> Zurück zur Liste
        </a>
        <h1 class="h3 mt-1"><?= e($customer['name']) ?></h1>
    </div>
    <a href="/customers/<?= $customer['id'] ?>/edit" class="btn btn-outline-secondary">
        <i class="bi bi-pencil"></i> Bearbeiten
    </a>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Kontakt</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">E-Mail</dt>
                    <dd class="col-sm-8"><?= e($customer['email'] ?? '—') ?></dd>

                    <dt class="col-sm-4">Telefon</dt>
                    <dd class="col-sm-8"><?= e($customer['phone'] ?? '—') ?></dd>
                </dl>
                <?php if ($customer['notes']): ?>
                <hr>
                <p class="text-muted small mb-1">Notizen</p>
                <p class="mb-0"><?= nl2br(e($customer['notes'])) ?></p>
                <?php endif ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Aufträge</div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <p class="text-muted small">Keine Aufträge vorhanden.</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                    <?php foreach ($orders as $o): ?>
                        <li class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <a href="/orders/<?= $o['id'] ?>" class="text-decoration-none">
                                <?= e($o['title']) ?>
                            </a>
                            <span><?= statusBadge($o['status']) ?></span>
                        </li>
                    <?php endforeach ?>
                    </ul>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>
