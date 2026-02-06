<?php

// API Ringotel Response Mapper  Class Service.

class ringotel_response_mapper_service {
    public function transform($response) {
        if ((empty($response['data']['result']))) {
            $response['data']['result'] = array();
        }
        return $response['data']; // in this case it must be like this
    }
}

// // Example
// $errorService = new ringotel_response_mapper_service();
// $errorService->transform($data);

