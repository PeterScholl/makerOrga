<?php

class UserController extends Controller
{
    public function index(): void
    {
        $users = User::findAll();
        $this->render('users/index', ['users' => $users]);
    }

    public function show(string $id): void
    {
        $user = User::findById((int) $id);
        if (!$user) {
            http_response_code(404);
            echo '<p>Mitarbeiter nicht gefunden.</p>';
            return;
        }
        $activities = Activity::findByUser((int) $id);
        $this->render('users/show', compact('user', 'activities'));
    }

    public function create(): void
    {
        $this->requireRole('admin');
        $this->render('users/form', ['user' => null]);
    }

    public function store(): void
    {
        $this->requireRole('admin');
        User::create([
            'name'     => $this->clean($_POST['name'] ?? ''),
            'username' => $this->clean($_POST['username'] ?? ''),
            'email'    => $this->clean($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role'     => $_POST['role'] ?? 'member',
        ]);
        $this->redirect('/users');
    }

    public function edit(string $id): void
    {
        $this->requireRole('admin');
        $user = User::findById((int) $id);
        if (!$user) {
            $this->redirect('/users');
        }
        $this->render('users/form', ['user' => $user]);
    }

    public function update(string $id): void
    {
        $this->requireRole('admin');
        $data = [
            'name'     => $this->clean($_POST['name'] ?? ''),
            'username' => $this->clean($_POST['username'] ?? ''),
            'email'    => $this->clean($_POST['email'] ?? ''),
            'role'     => $_POST['role'] ?? 'member',
        ];
        // Passwort nur ändern wenn ein neues eingegeben wurde
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }
        User::update((int) $id, $data);
        $this->redirect('/users/' . $id);
    }

    public function changePassword(string $id): void
    {
        // Nur eigenes Profil oder Admin
        if ($this->currentUserId() !== (int) $id && $this->currentRole() !== 'admin') {
            $this->forbidden();
        }

        $user = User::findById((int) $id);
        if (!$user) {
            $this->redirect('/users');
        }

        // Eigenes Passwort: aktuelles Passwort muss stimmen
        if ($this->currentUserId() === (int) $id) {
            $current = $_POST['current_password'] ?? '';
            if (!password_verify($current, $user['password'])) {
                $_SESSION['flash'] = ['type' => 'danger', 'text' => 'Das aktuelle Passwort ist falsch.'];
                $this->redirect('/users/' . $id);
            }
        }

        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($new) || $new !== $confirm) {
            $_SESSION['flash'] = ['type' => 'danger', 'text' => 'Die Passwörter stimmen nicht überein oder sind leer.'];
            $this->redirect('/users/' . $id);
        }

        User::update((int) $id, ['password' => password_hash($new, PASSWORD_BCRYPT)]);
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Passwort erfolgreich geändert.'];
        $this->redirect('/users/' . $id);
    }
}
