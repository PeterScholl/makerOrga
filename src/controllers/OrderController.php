<?php

class OrderController extends Controller
{
    public function index(): void
    {
        $orders = Order::findAllWithRelations();
        $this->render('orders/index', ['orders' => $orders]);
    }

    public function show(string $id): void
    {
        $order = Order::findById((int) $id);
        if (!$order) {
            http_response_code(404);
            echo '<p>Auftrag nicht gefunden.</p>';
            return;
        }
        $activities = Activity::findByOrder((int) $id);
        $customers  = Customer::findAll();
        $users      = User::findAllSorted();
        $this->render('orders/show', compact('order', 'activities', 'customers', 'users'));
    }

    // Admin und Koordinator dürfen jeden Auftrag bearbeiten.
    // Ein Mitarbeiter darf nur Aufträge bearbeiten, die ihm zugewiesen sind.
    private function canEditOrder(array $order): bool
    {
        if (in_array($this->currentRole(), ['admin', 'coordinator'], true)) {
            return true;
        }
        return $this->currentRole() === 'member'
            && $this->currentUserId() === (int) $order['assigned_user_id'];
    }

    public function create(): void
    {
        $this->requireRole('admin', 'coordinator');
        $customers = Customer::findAll();
        $users     = User::findAllSorted();
        $this->render('orders/form', ['order' => null, 'customers' => $customers, 'users' => $users]);
    }

    public function store(): void
    {
        $this->requireRole('admin', 'coordinator');
        Order::create([
            'type'             => $_POST['type'] ?? 'repair',
            'title'            => $this->clean($_POST['title'] ?? ''),
            'description'      => $this->clean($_POST['description'] ?? ''),
            'device_info'      => $this->clean($_POST['device_info'] ?? ''),
            'priority'         => $_POST['priority'] ?? 'normal',
            'customer_id'      => ($_POST['customer_id'] ?? '') ?: null,
            'assigned_user_id' => ($_POST['assigned_user_id'] ?? '') ?: null,
            'received_at'      => $_POST['received_at'] ?? date('Y-m-d H:i:s'),
        ]);
        $this->redirect('/orders');
    }

    public function edit(string $id): void
    {
        $order = Order::findById((int) $id);
        if (!$order) {
            $this->redirect('/orders');
        }
        if (!$this->canEditOrder($order)) {
            $this->forbidden();
        }
        $customers = Customer::findAll();
        $users     = User::findAllSorted();
        $this->render('orders/form', compact('order', 'customers', 'users'));
    }

    public function update(string $id): void
    {
        $order = Order::findById((int) $id);
        if (!$order) {
            $this->redirect('/orders');
        }
        if (!$this->canEditOrder($order)) {
            $this->forbidden();
        }

        $data = [
            'type'        => $_POST['type'] ?? 'repair',
            'title'       => $this->clean($_POST['title'] ?? ''),
            'description' => $this->clean($_POST['description'] ?? ''),
            'device_info' => $this->clean($_POST['device_info'] ?? ''),
            'status'      => $_POST['status'] ?? 'open',
            'priority'    => $_POST['priority'] ?? 'normal',
            'received_at' => $_POST['received_at'] ?? date('Y-m-d'),
            'completed_at'=> $this->clean($_POST['completed_at'] ?? ''),
            'result'      => $this->clean($_POST['result'] ?? ''),
            'returned'    => isset($_POST['returned']) ? 1 : 0,
        ];

        // Kunde und zugewiesener Mitarbeiter dürfen nur Koordinatoren und Admins ändern
        if (in_array($this->currentRole(), ['admin', 'coordinator'], true)) {
            $data['customer_id']      = ($_POST['customer_id'] ?? '') ?: null;
            $data['assigned_user_id'] = ($_POST['assigned_user_id'] ?? '') ?: null;
        }

        Order::update((int) $id, $data);
        $this->redirect('/orders/' . $id);
    }

    public function destroy(string $id): void
    {
        $this->requireRole('admin');
        Order::delete((int) $id);
        $this->redirect('/orders');
    }
}
