<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Validator;

class ValidationException extends Exception
{
    protected $validator;

    protected $code = 422;

    public function __construct($validator) {
        $this->validator = $validator;
    }

    public function render() {
        // return a json with desired format
        return response()->json([
            "status" => "error",
            "message" => $this->validator->errors()->first()
        ], $this->code);
    }
}
