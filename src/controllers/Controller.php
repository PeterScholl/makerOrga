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
}
