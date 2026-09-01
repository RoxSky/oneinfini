<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Kosongkan agar tidak memicu view error handler bawaan framework
    }

    public function render($request, Throwable $e)
    {
        // Gunakan PHP Native murni, jangan gunakan fungsi Laravel sama sekali
        http_response_code(500);
        header('Content-Type: application/json');
        
        echo json_encode([
            'TANGKAP_ERROR_ASLI' => $e->getMessage(),
            'DI_FILE' => $e->getFile(),
            'PADA_BARIS' => $e->getLine()
        ]);
        exit; // Matikan eksekusi script paksa di sini
    }
}
