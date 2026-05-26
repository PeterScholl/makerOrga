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

    public function edit(string $id): void
    {
        $activity = Activity::findById((int) $id);
        if (!$activity || !Activity::isEditable($activity, $this->currentUserId(), $this->currentRole())) {
            $this->forbidden();
        }
        $canEditAll = in_array($this->currentRole(), ['admin', 'coordinator'], true);
        $users      = $canEditAll ? User::findAllSorted() : [];
        $assigned   = Activity::getUserIds((int) $id);
        $this->render('activities/form', compact('activity', 'users', 'assigned', 'canEditAll'));
    }

    public function update(string $id): void
    {
        $activity = Activity::findById((int) $id);
        if (!$activity || !Activity::isEditable($activity, $this->currentUserId(), $this->currentRole())) {
            $this->forbidden();
        }

        $canEditAll = in_array($this->currentRole(), ['admin', 'coordinator'], true);
        $data       = ['description' => $this->clean($_POST['description'] ?? '')];

        if ($canEditAll) {
            $data['worked_at'] = $_POST['worked_at'] ?? $activity['worked_at'];
            $userIds = array_values(array_unique(array_filter($_POST['user_ids'] ?? [])));
            Activity::updateWithUsers((int) $id, $data, $userIds);
        } else {
            Activity::update((int) $id, $data);
        }

        $orderId = $activity['order_id'] ?? null;
        $this->redirect($orderId ? '/orders/' . $orderId : '/orders');
    }

    public function destroy(string $id): void
    {
        $activity = Activity::findById((int) $id);
        if (!$activity || !Activity::isEditable($activity, $this->currentUserId(), $this->currentRole())) {
            $this->forbidden();
        }
        $orderId = $activity['order_id'] ?? null;
        Activity::delete((int) $id);
        $this->redirect($orderId ? '/orders/' . $orderId : '/orders');
    }
}
