<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MakerOrga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/orders">
            <i class="bi bi-tools"></i> MakerOrga
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/orders">Aufträge</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/customers">Kunden</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/users">Mitarbeiter</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <a class="text-white-50 small text-decoration-none"
                   href="/users/<?= $_SESSION['user_id'] ?? '' ?>">
                    <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>
                </a>
                <form method="post" action="/logout" class="m-0">
                    <button class="btn btn-sm btn-outline-light">Abmelden</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4 flex-grow-1">
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show mt-2" role="alert">
            <?= htmlspecialchars($_SESSION['flash']['text']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif ?>
    <?= $content ?>
</main>

<footer class="bg-dark text-white-50 py-3 mt-auto">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2 small">
        <span>MakerOrga &middot; Make &amp; Repair AG &middot; <?= date('Y') ?></span>
        <div class="d-flex gap-3">
            <a href="https://github.com/PeterScholl/makerOrga"
               class="text-white-50 text-decoration-none" target="_blank">
                <i class="bi bi-github"></i> Projektseite
            </a>
            <a href="https://github.com/PeterScholl/makerOrga/blob/main/docs/konzepte.md"
               class="text-white-50 text-decoration-none" target="_blank">
                <i class="bi bi-file-text"></i> Konzepte
            </a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Bestätigungsdialog für alle Löschen-Buttons
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('submit', e => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });
</script>
</body>
</html>
