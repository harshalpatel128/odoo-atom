<?php

namespace App\Core;

final class Validator
{
    private array $errors = [];

    public function required(array $data, array $fields): self
    {
        foreach ($fields as $field => $label) {
            if (trim((string) ($data[$field] ?? '')) === '') {
                $this->errors[$field] = $label . ' is required.';
            }
        }
        return $this;
    }

    public function email(array $data, string $field, string $label): self
    {
        if (!empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $label . ' must be a valid email address.';
        }
        return $this;
    }

    public function minLength(array $data, string $field, int $length, string $label): self
    {
        if (strlen((string) ($data[$field] ?? '')) < $length) {
            $this->errors[$field] = $label . ' must be at least ' . $length . ' characters.';
        }
        return $this;
    }

    public function dateOrder(array $data, string $startField, string $endField): self
    {
        if (!empty($data[$startField]) && !empty($data[$endField]) && strtotime($data[$startField]) >= strtotime($data[$endField])) {
            $this->errors[$endField] = 'End date/time must be after start date/time.';
        }
        return $this;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }
}
