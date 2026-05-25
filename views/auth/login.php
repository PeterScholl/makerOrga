<?php /** @var string|null $error */ ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MakerOrga – Anmelden</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh">

<div class="container" style="max-width: 380px">
    <div class="text-center mb-4">
        <h1 class="h4 fw-bold">🔧 MakerOrga</h1>
        <p class="text-muted small">Make & Repair AG</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h2 class="h6 mb-3 text-muted text-uppercase letter-spacing">Anmelden</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
            <?php endif ?>

            <form method="post" action="/login">
                <div class="mb-3">
                    <label class="form-label">Benutzername</label>
                    <input type="text" name="username" class="form-control"
                           autofocus required autocomplete="username">
                </div>
                <div class="mb-4">
                    <label class="form-label">Passwort</label>
                    <input type="password" name="password" class="form-control"
                           required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Anmelden</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
