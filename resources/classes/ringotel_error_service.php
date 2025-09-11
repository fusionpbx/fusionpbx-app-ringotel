<?php

// Error Class Service.
// Here you can put your debuging and logging to for error keys ONLY.

class ringotel_error_service {
    public function check($response) {
        if (isset($response['success']) && !$response['success']) {
            echo json_encode(array(
                'success' => false,
                'data' => $response['error']
            ));
            die();
        }
        return $response;
    }
}

// // Example
// $errorService = new ringotel_error_service();
// $errorService->check($dataWithError);

