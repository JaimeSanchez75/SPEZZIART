<?php
declare(strict_types=1);
function renderErrorPage(int $statusCode, string $title, string $message, string $detail = ''): void
{
    http_response_code($statusCode);
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (str_starts_with($uri, '/api/') || str_contains($accept, 'application/json')) 
    {
        header('Content-Type: application/json');
        echo json_encode
        ([
            'success' => false,
            'error' => $title,
            'message' => $message,
            'code' => $statusCode
        ]);
        exit;
    }
    require __DIR__ . '/../pages/error/view/ErrorView.php';
    exit;
}