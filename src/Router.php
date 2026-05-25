<?php

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes[] = ['GET', $path, $handler];
    }

    public function post(string $path, array $handler): void
    {
        $this->routes[] = ['POST', $path, $handler];
    }

    /**
     * Eingehende Anfrage mit den registrierten Routen abgleichen und ausführen.
     */
    public function dispatch(string $method, string $uri): void
    {
        // Query-String abschneiden (/orders?status=open → /orders)
        $path = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $params = $this->match($routePath, $path);

            if ($params !== null) {
                // Handler aufrufen: [OrderController::class, 'index'] → new OrderController()->index(...)
                [$class, $methodName] = $handler;
                (new $class())->$methodName(...$params);
                return;
            }
        }

        $this->notFound();
    }

    /**
     * Prüft ob ein URL-Muster auf den aktuellen Pfad passt.
     * Gibt ein Array der URL-Parameter zurück, oder null bei keiner Übereinstimmung.
     *
     * Beispiel: Muster "/orders/{id}", Pfad "/orders/42" → [42]
     */
    private function match(string $routePath, string $requestPath): ?array
    {
        // {id} → benannte Capture-Group (?P<id>[^/]+)
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return null;
        }

        // Ersten Match (gesamter String) entfernen, nur Parameter zurückgeben
        array_shift($matches);
        return $matches;
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '<h1>404 – Seite nicht gefunden</h1>';
    }
}
