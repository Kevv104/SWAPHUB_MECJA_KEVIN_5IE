<?php declare(strict_types=1);

function normalizzaPermesso(string $permesso): ?string
{
    $mappa = [
        'upload_product' => 'manage_products',
        'accept_friend_request' => 'manage_friend_request',
        'view_chat' => 'manage_chat',
        'send_message' => null,
        'reject_friend_request' => null,
    ];

    if (array_key_exists($permesso, $mappa)) {
        return $mappa[$permesso];
    }

    return $permesso;
}