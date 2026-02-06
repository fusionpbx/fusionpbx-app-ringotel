<?php
class ringotel_logging_middleware implements ringotel_middleware_interface {
    public function handle($response) {
        // Log the response or perform any logging logic
        return $response;
    }
}