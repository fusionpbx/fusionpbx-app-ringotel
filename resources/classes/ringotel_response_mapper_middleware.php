<?php

class ringotel_response_mapper_middleware implements ringotel_middleware_interface {
    public function handle($response) {
        $mapperService = new ringotel_response_mapper_service();
        return $mapperService->transform($response);
    }
}