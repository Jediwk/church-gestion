<?php
namespace Core;

class Security {
    private const MAX_LOGIN_ATTEMPTS = 3;
    private const LOCKOUT_TIME = 900; // 15 minutes
    private const SESSION_LIFETIME = 3600; // 1 heure

    /**
     * Vérifie les tentatives de connexion
     */
    public static function checkLoginAttempts(string $username): bool {
        $attempts = Session::get('login_attempts', []);
        $now = time();

        // Nettoie les anciennes tentatives
        foreach ($attempts as $user => $data) {
            if ($now - $data['timestamp'] > self::LOCKOUT_TIME) {
                unset($attempts[$user]);
            }
        }

        if (isset($attempts[$username])) {
            if ($attempts[$username]['count'] >= self::MAX_LOGIN_ATTEMPTS) {
                if ($now - $attempts[$username]['timestamp'] < self::LOCKOUT_TIME) {
                    return false;
                }
                unset($attempts[$username]);
            }
        }

        Session::set('login_attempts', $attempts);
        return true;
    }

    /**
     * Enregistre une tentative de connexion échouée
     */
    public static function recordFailedAttempt(string $username): void {
        $attempts = Session::get('login_attempts', []);
        if (!isset($attempts[$username])) {
            $attempts[$username] = ['count' => 0, 'timestamp' => time()];
        }
        $attempts[$username]['count']++;
        $attempts[$username]['timestamp'] = time();
        Session::set('login_attempts', $attempts);
    }

    /**
     * Réinitialise les tentatives de connexion
     */
    public static function resetLoginAttempts(string $username): void {
        $attempts = Session::get('login_attempts', []);
        unset($attempts[$username]);
        Session::set('login_attempts', $attempts);
    }

    /**
     * Génère un token CSRF
     */
    public static function generateCsrfToken(): string {
        $token = bin2hex(random_bytes(32));
        Session::set('csrf_token', $token);
        return $token;
    }

    /**
     * Vérifie un token CSRF
     */
    public static function validateCsrfToken(?string $token): bool {
        if (!$token || !Session::has('csrf_token')) {
            return false;
        }
        return hash_equals(Session::get('csrf_token'), $token);
    }

    /**
     * Génère un token de session unique
     */
    public static function generateSessionToken(): string {
        return bin2hex(random_bytes(32));
    }

    /**
     * Vérifie si la session est expirée
     */
    public static function checkSessionExpiration(): bool {
        $lastActivity = Session::get('last_activity', 0);
        $now = time();

        if ($now - $lastActivity > self::SESSION_LIFETIME) {
            Session::destroy();
            return false;
        }

        Session::set('last_activity', $now);
        return true;
    }
}
