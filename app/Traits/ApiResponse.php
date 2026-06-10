<?php

namespace App\Traits;

trait ApiResponse{
    protected function success($data, $message = 'success', $code=200){
        return response()->json([
        'status' => 'success',
        'message' => $message,
        'data' => $data
        ], $code);
    }

    protected function error($message, $code = 400, $data = null) {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }
}


