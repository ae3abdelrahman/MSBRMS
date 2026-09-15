<?php
require_once __DIR__ . '/../config/db.php';

class User
{
    /**
     * Register a new customer account.
     * Returns [success(bool), message(string)]
     */
    public static function register($fullName, $email, $phone, $password)
    {
        $db = getDB();

        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return [false, 'This email is already registered.'];
        }

        if (strlen($password) < 6) {
            return [false, 'Password must be at least 6 characters.'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare(
            "INSERT INTO users (full_name, email, phone, password_hash, role) VALUES (?,?,?,?,'customer')"
        );
        $stmt->execute([$fullName, $email, $phone, $hash]);

        return [true, 'Registration successful. You can now log in.'];
    }

    /**
     * Attempt login. Returns user array on success, false on failure.
     */
    public static function attemptLogin($email, $password)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    public static function find($userId)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public static function all($role = null)
    {
        $db = getDB();
        if ($role) {
            $stmt = $db->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
            $stmt->execute([$role]);
        } else {
            $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
        }
        return $stmt->fetchAll();
    }

    public static function setStatus($userId, $status)
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        return $stmt->execute([$status, $userId]);
    }
}
