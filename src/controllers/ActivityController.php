<?php

class ActivityController extends Controller
{
    public function store(): void
    {
        $orderId = ($_POST['order_id'] ?? '') ?: null;

        Activity::create([
            'order_id'    => $orderId,
            'user_id'     => (int) ($_POST['user_id'] ?? 0),
            'description' => $this->clean($_POST['description'] ?? ''),
            // worked_at kann in der Vergangenheit liegen — Mitarbeiter trägt selbst ein
            'worked_at'   => $_POST['worked_at'] ?? date('Y-m-d H:i:s'),
        ]);

        // Nach dem Speichern zurück zum Auftrag, falls vorhanden
        $redirect = $orderId ? '/orders/' . $orderId : '/orders';
        $this->redirect($redirect);
    }

    public function update(string $id): void
    {
        $activity = Activity::findById((int) $id);
        Activity::update((int) $id, [
            'description' => $this->clean($_POST['description'] ?? ''),
            'worked_at'   => $_POST['worked_at'] ?? date('Y-m-d H:i:s'),
        ]);

        $orderId = $activity['order_id'] ?? null;
        $this->redirect($orderId ? '/orders/' . $orderId : '/orders');
    }

    public function destroy(string $id): void
    {
        $activity = Activity::findById((int) $id);
        $orderId  = $activity['order_id'] ?? null;
        Activity::delete((int) $id);
        $this->redirect($orderId ? '/orders/' . $orderId : '/orders');
    }
}
