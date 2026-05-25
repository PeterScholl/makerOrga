<?php

// Autoloader einbinden — macht alle Klassen aus src/ verfügbar
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helpers.php';

$router = new Router();

// ── Aufträge ──────────────────────────────────────────────────────────────────
$router->get('/',                  [OrderController::class, 'index']);
$router->get('/orders',            [OrderController::class, 'index']);
$router->get('/orders/new',        [OrderController::class, 'create']);
$router->post('/orders',           [OrderController::class, 'store']);
$router->get('/orders/{id}',       [OrderController::class, 'show']);
$router->get('/orders/{id}/edit',  [OrderController::class, 'edit']);
$router->post('/orders/{id}',      [OrderController::class, 'update']);
$router->post('/orders/{id}/delete', [OrderController::class, 'destroy']);

// ── Kunden ────────────────────────────────────────────────────────────────────
$router->get('/customers',           [CustomerController::class, 'index']);
$router->get('/customers/new',       [CustomerController::class, 'create']);
$router->post('/customers',          [CustomerController::class, 'store']);
$router->get('/customers/{id}',      [CustomerController::class, 'show']);
$router->get('/customers/{id}/edit', [CustomerController::class, 'edit']);
$router->post('/customers/{id}',     [CustomerController::class, 'update']);

// ── Mitarbeiter ───────────────────────────────────────────────────────────────
$router->get('/users',           [UserController::class, 'index']);
$router->get('/users/new',       [UserController::class, 'create']);
$router->post('/users',          [UserController::class, 'store']);
$router->get('/users/{id}',      [UserController::class, 'show']);
$router->get('/users/{id}/edit', [UserController::class, 'edit']);
$router->post('/users/{id}',     [UserController::class, 'update']);

// ── Tätigkeiten ───────────────────────────────────────────────────────────────
$router->post('/activities',          [ActivityController::class, 'store']);
$router->post('/activities/{id}',     [ActivityController::class, 'update']);
$router->post('/activities/{id}/delete', [ActivityController::class, 'destroy']);

// Anfrage auflösen
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
