<?php

class CustomerController extends Controller
{
    public function index(): void
    {
        $customers = Customer::findAll();
        $this->render('customers/index', ['customers' => $customers]);
    }

    public function show(string $id): void
    {
        $customer = Customer::findById((int) $id);
        if (!$customer) {
            http_response_code(404);
            echo '<p>Kunde nicht gefunden.</p>';
            return;
        }
        // Alle Aufträge dieses Kunden mitladen
        $orders = Order::findAll(['customer_id' => (int) $id]);
        $this->render('customers/show', compact('customer', 'orders'));
    }

    public function create(): void
    {
        $this->requireRole('admin', 'coordinator');
        $this->render('customers/form', ['customer' => null]);
    }

    public function store(): void
    {
        $this->requireRole('admin', 'coordinator');
        $data = [
            'name'  => $this->clean($_POST['name'] ?? ''),
            'notes' => $this->clean($_POST['notes'] ?? ''),
        ];
        // E-Mail und Telefon darf nur der Admin beim Anlegen setzen
        if ($this->currentRole() === 'admin') {
            $data['email'] = $this->clean($_POST['email'] ?? '');
            $data['phone'] = $this->clean($_POST['phone'] ?? '');
        }
        Customer::create($data);
        $this->redirect('/customers');
    }

    public function edit(string $id): void
    {
        $this->requireRole('admin', 'coordinator');
        $customer = Customer::findById((int) $id);
        if (!$customer) {
            $this->redirect('/customers');
        }
        $this->render('customers/form', ['customer' => $customer]);
    }

    public function update(string $id): void
    {
        $this->requireRole('admin', 'coordinator');
        $data = [
            'name'  => $this->clean($_POST['name'] ?? ''),
            'notes' => $this->clean($_POST['notes'] ?? ''),
        ];
        // E-Mail und Telefon darf nur der Admin ändern
        if ($this->currentRole() === 'admin') {
            $data['email'] = $this->clean($_POST['email'] ?? '');
            $data['phone'] = $this->clean($_POST['phone'] ?? '');
        }
        Customer::update((int) $id, $data);
        $this->redirect('/customers/' . $id);
    }
}
