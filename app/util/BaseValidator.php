<?php


namespace App\util;

use Exception;
use JeffOchoa\ValidatorFactory;

abstract class BaseValidator
{

    protected $validator;

    public function __construct(array $data, $scenario) {
        $rules = $this->rules();
        if (!array_key_exists($scenario, $rules)) {
            throw new Exception('Scenario "' . $scenario . '" not found in ' . get_called_class());
        }

        $factory = new ValidatorFactory();
        $this->validator = $factory->make($data, $rules[$scenario], $this->messages());
    }

    abstract protected function collection();

    abstract protected function rules();

    protected function messages() {
        return [];
    }

    public function getMessageBag() {
        return $this->validator->getMessageBag();
    }

    public function validate() {
        return $this->validator->validate();
    }

    public function validated() {
        return $this->validator->validated();
    }

    public function fails() {
        return $this->validator->fails();
    }

    public function failed() {
        return $this->validator->failed();
    }

    public function errors() {
        return $this->validator->errors();
    }
}