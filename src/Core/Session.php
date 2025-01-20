<?php
namespace App\Core;

class Session {
    private static bool $initialized = false;

    /**
     * Initialise la session de manière sécurisée
     */
    public static function init(): void {
        if (self::$initialized) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            // Configuration des cookies de session
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();
            
            // Régénération de l'ID de session pour prévenir la fixation de session
            if (!isset($_SESSION['_created'])) {
                $_SESSION['_created'] = time();
                session_regenerate_id(true);
            } else {
                // Régénère l'ID toutes les 30 minutes
                if (time() - $_SESSION['_created'] > 1800) {
                    session_regenerate_id(true);
                    $_SESSION['_created'] = time();
                }
            }
        }

        self::$initialized = true;
    }

    /**
     * Définit une valeur dans la session
     * @param string $key Clé
     * @param mixed $value Valeur
     */
    public static function set(string $key, mixed $value): void {
        self::init();
        $_SESSION[$key] = $value;
    }

    /**
     * Récupère une valeur de la session
     * @param string $key Clé
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed {
        self::init();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Vérifie si une clé existe dans la session
     * @param string $key Clé
     * @return bool
     */
    public static function has(string $key): bool {
        self::init();
        return isset($_SESSION[$key]);
    }

    /**
     * Supprime une valeur de la session
     * @param string $key Clé
     */
    public static function remove(string $key): void {
        self::init();
        unset($_SESSION[$key]);
    }

    /**
     * Définit un message flash
     * @param string $type Type de message (success, error, warning, info)
     * @param string $message Message
     */
    public static function setFlash(string $type, string $message): void {
        self::init();
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Récupère et supprime les messages flash
     * @return array Messages flash
     */
    public static function getFlash(): array {
        self::init();
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    /**
     * Détruit la session
     */
    public static function destroy(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            $_SESSION = [];
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
        }
        self::$initialized = false;
    }

    /**
     * Définit un timeout de session
     * @param int $minutes Minutes avant timeout
     */
    public static function setTimeout(int $minutes): void {
        self::set('timeout', time() + ($minutes * 60));
    }

    /**
     * Vérifie si la session a expiré
     * @return bool
     */
    public static function isExpired(): bool {
        $timeout = self::get('timeout');
        if (!$timeout) {
            return false;
        }
        return time() > $timeout;
    }
}
