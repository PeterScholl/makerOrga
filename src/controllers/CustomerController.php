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
        $this->render('customers/form', ['customer' => null]);
    }

    public function store(): void
    {
        Customer::create([
            'name'  => $this->clean($_POST['name'] ?? ''),
            'email' => $this->clean($_POST['email'] ?? ''),
            'phone' => $this->clean($_POST['phone'] ?? ''),
            'notes' => $this->clean($_POST['notes'] ?? ''),
        ]);
        $this->redirect('/customers');
    }

    public function edit(string $id): void
    {
        $customer = Customer::findById((int) $id);
        if (!$customer) {
            $this->redirect('/customers');
        }
        $this->render('customers/form', ['customer' => $customer]);
    }

    public function update(string $id): void
    {
        Customer::update((int) $id, [
            'name'  => $this->clean($_POST['name'] ?? ''),
            'email' => $this->clean($_POST['email'] ?? ''),
            'phone' => $this->clean($_POST['phone'] ?? ''),
            'notes' => $this->clean($_POST['notes'] ?? ''),
        ]);
        $this->redirect('/customers/' . $id);
    }
}
