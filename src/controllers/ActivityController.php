<?php

class ActivityController extends Controller
{
    public function store(): void
    {
        $orderId = ($_POST['order_id'] ?? '') ?: null;
        $userIds = array_values(array_unique(array_filter($_POST['user_ids'] ?? [])));

        Activity::create([
            'order_id'    => $orderId,
            'description' => $this->clean($_POST['description'] ?? ''),
            'worked_at'   => $_POST['worked_at'] ?? date('Y-m-d H:i:s'),
            'user_ids'    => $userIds,
        ]);

        $redirect = $orderId ? '/orders/' . $orderId : '/orders';
        $this->redirect($redirect);
    }

    public function update(string $id): void
    {
        $activity = Activity::findById((int) $id);
        $userIds  = $_POST['user_ids'] ?? [];

        Activity::updateWithUsers((int) $id, [
            'description' => $this->clean($_POST['description'] ?? ''),
            'worked_at'   => $_POST['worked_at'] ?? date('Y-m-d H:i:s'),
        ], $userIds);

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
