<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MakerOrga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

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
                <span class="text-white-50 small">
                    <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>
                </span>
                <form method="post" action="/logout" class="m-0">
                    <button class="btn btn-sm btn-outline-light">Abmelden</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<main class="container pb-5">
    <?= $content ?>
</main>

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
