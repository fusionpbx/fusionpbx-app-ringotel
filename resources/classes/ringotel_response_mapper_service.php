<?php

// API Ringotel Response Mapper  Class Service.

class ringotel_response_mapper_service {
    public function transform($response) {
        return $response['data']; // in this case it must be like this
    }
}

// // Example
// $errorService = new ringotel_response_mapper_service();
// $errorService->transform($data);

