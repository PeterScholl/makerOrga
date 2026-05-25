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
        $users      = User::findAll();
        $this->render('orders/show', compact('order', 'activities', 'customers', 'users'));
    }

    public function create(): void
    {
        $customers = Customer::findAll();
        $users     = User::findAll();
        $this->render('orders/form', ['order' => null, 'customers' => $customers, 'users' => $users]);
    }

    public function store(): void
    {
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
        $customers = Customer::findAll();
        $users     = User::findAll();
        $this->render('orders/form', compact('order', 'customers', 'users'));
    }

    public function update(string $id): void
    {
        $data = [
            'type'             => $_POST['type'] ?? 'repair',
            'title'            => $this->clean($_POST['title'] ?? ''),
            'description'      => $this->clean($_POST['description'] ?? ''),
            'device_info'      => $this->clean($_POST['device_info'] ?? ''),
            'status'           => $_POST['status'] ?? 'open',
            'priority'         => $_POST['priority'] ?? 'normal',
            'customer_id'      => ($_POST['customer_id'] ?? '') ?: null,
            'assigned_user_id' => ($_POST['assigned_user_id'] ?? '') ?: null,
            'received_at'      => $_POST['received_at'] ?? date('Y-m-d'),
            'completed_at'     => $this->clean($_POST['completed_at'] ?? ''),
            'result'           => $this->clean($_POST['result'] ?? ''),
            'returned'         => isset($_POST['returned']) ? 1 : 0,
        ];
        Order::update((int) $id, $data);
        $this->redirect('/orders/' . $id);
    }

    public function destroy(string $id): void
    {
        Order::delete((int) $id);
        $this->redirect('/orders');
    }
}
