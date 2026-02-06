<?php

// Here You can put whatever u want
// Logging/Debuging and etc for output

class ringotel_response_output_converter {
    public static function json_to_associative_array(string $json_string): array {
        $json = trim($json_string);
        if ($json === '') {
            return [];
        }

        $associative_array = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Logger::log("Invalid JSON string. Error: " . json_last_error_msg()); // YOUR Logger class here
            return [];
        }
        return $associative_array;
    }

    public static function associative_array_to_json($input): string {
        if (is_string($input)) {
            return $input;
        } elseif (is_array($input)) {
            $json = json_encode($input);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Logger::log("Failed to convert array to JSON. Error: " . json_last_error_msg()); // YOUR Logger class here
                return '';
            }
            return $json;
        } else {
            // Logger::log("Input must be a string or an array."); // YOUR Logger class here
            return '';
        }
    }


}