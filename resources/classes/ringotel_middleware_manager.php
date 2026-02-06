<?php

class ringotel_middleware_manager {
    private $middlewares = [];

    public function addMiddleware(ringotel_middleware_interface $middleware) {
        $this->middlewares[] = $middleware;
    }

    public function handle($response) {
        foreach ($this->middlewares as $middleware) {
            $response = $middleware->handle($response);
            // add exit case if u want
        }
        return $response;
    }
}