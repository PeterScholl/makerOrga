<?php

require_once __DIR__ . '/Model.php';

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Passwort niemals im Klartext speichern — password_hash() erzeugt einen
     * sicheren bcrypt-Hash der nicht rückrechenbar ist.
     */
    public static function create(array $data): int
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        return parent::create($data);
    }
}
