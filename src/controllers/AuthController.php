<?php

class AuthController extends Controller
{
    public function showLogin(): void
    {
        // Bereits eingeloggt → direkt zur Startseite
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/orders');
        }
        $this->renderLogin();
    }

    public function login(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user     = User::findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            // Session regenerieren verhindert Session-Fixation-Angriffe
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $this->redirect('/orders');
        }

        $this->renderLogin('E-Mail oder Passwort ist falsch.');
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }

    // Login-Seite hat ein eigenes schlankes Layout (kein Navbar nötig)
    private function renderLogin(?string $error = null): void
    {
        require __DIR__ . '/../../views/auth/login.php';
    }
}
