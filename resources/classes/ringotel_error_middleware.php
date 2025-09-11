<?php

class ringotel_error_middleware implements ringotel_middleware_interface {
    public function handle($response) {
        $errorService = new ringotel_error_service();
        return $errorService->check($response);
    }
}