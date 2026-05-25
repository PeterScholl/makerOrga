<?php

/**
 * Basisklasse für alle Controller.
 * Stellt render() und redirect() bereit — Methoden die jeder Controller braucht.
 */
abstract class Controller
{
    /**
     * Eine View-Datei laden und als HTML ausgeben.
     *
     * Ablauf:
     *   1. $data-Einträge als Variablen verfügbar machen ($data['orders'] → $orders)
     *   2. View-Datei in einen Puffer rendern (noch kein Output an den Browser)
     *   3. Puffer als $content ins Layout einbetten
     *
     * Beispiel: $this->render('orders/index', ['orders' => $orders])
     *   → lädt views/orders/index.php mit der Variable $orders
     *   → bettet das Ergebnis in views/layout/main.php ein
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);

        ob_start();
        require __DIR__ . '/../../views/' . $view . '.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../views/layout/main.php';
    }

    /**
     * Browser auf eine andere URL weiterleiten.
     * exit verhindert dass danach noch Code ausgeführt wird.
     */
    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    /**
     * Texteingaben bereinigen: Leerzeichen am Rand entfernen, leere Strings zu null.
     */
    protected function clean(string $value): ?string
    {
        $value = trim($value);
        return $value !== '' ? $value : null;
    }

    /**
     * Zugriff verweigern wenn der aktuelle Benutzer keine der erlaubten Rollen hat.
     * Erlaubte Rollen: 'admin', 'coordinator', 'member'
     */
    protected function requireRole(string ...$roles): void
    {
        if (!in_array($this->currentRole(), $roles, true)) {
            $this->forbidden();
        }
    }

    // Gibt HTTP 403 zurück und beendet die Ausführung.
    protected function forbidden(): never
    {
        http_response_code(403);
        $content = '<div class="alert alert-danger mt-3">
            <strong>Kein Zugriff.</strong> Du hast keine Berechtigung für diese Aktion.
        </div>';
        require __DIR__ . '/../../views/layout/main.php';
        exit;
    }

    protected function currentRole(): string
    {
        return $_SESSION['user_role'] ?? '';
    }

    protected function currentUserId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
