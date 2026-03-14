<?php
class Auth
{
    private static ?array $user = null;

    public static function setUser(array $user): void
    {
        self::$user = $user;
    }

    public static function user(): ?array
    {
        return self::$user;
    }

    public static function check(): bool
    {
        return self::$user !== null;
    }

    public static function id(): ?int
    {
        return self::$user['id'] ?? null;
    }
    
}
?>