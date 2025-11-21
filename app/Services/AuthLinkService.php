<?php

namespace App\Services;

class AuthLinkService
{
    /**
     * Decodifica os dados base64 do link e retorna array do usuário
     */
    public static function decode(?string $data)
    {
        if (!$data) {
            return null;
        }

        $decoded = base64_decode($data, true);
        $userData = json_decode($decoded, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($userData['email'])) {
            return null;
        }

        return $userData;
    }
}
