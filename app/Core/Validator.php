<?php

namespace App\Core;

class Validator
{
    private static $errors = [];

    public static function validate($data, $rules)
    {
        self::$errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $ruleArray = explode('|', $fieldRules);

            foreach ($ruleArray as $rule) {
                self::applyRule($field, $value, $rule);
            }
        }

        return empty(self::$errors);
    }

    private static function applyRule($field, $value, $rule)
    {
        $parts = explode(':', $rule);
        $ruleName = trim($parts[0]);
        $parameter = isset($parts[1]) ? trim($parts[1]) : null;

        match ($ruleName) {
            'required' => self::required($field, $value),
            'email' => self::email($field, $value),
            'min' => self::min($field, $value, $parameter),
            'max' => self::max($field, $value, $parameter),
            'numeric' => self::numeric($field, $value),
            'string' => self::string($field, $value),
            'array' => self::arrayType($field, $value),
            'unique' => self::unique($field, $value, $parameter),
            'in' => self::in($field, $value, $parameter),
            'confirmed' => self::confirmed($field, $value),
            default => null,
        };
    }

    private static function required($field, $value)
    {
        if (empty($value) && $value !== 0 && $value !== false) {
            self::addError($field, ucfirst($field) . " is required");
        }
    }

    private static function email($field, $value)
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            self::addError($field, ucfirst($field) . " must be a valid email");
        }
    }

    private static function min($field, $value, $length)
    {
        if ($value && strlen($value) < $length) {
            self::addError($field, ucfirst($field) . " must be at least $length characters");
        }
    }

    private static function max($field, $value, $length)
    {
        if ($value && strlen($value) > $length) {
            self::addError($field, ucfirst($field) . " must not exceed $length characters");
        }
    }

    private static function numeric($field, $value)
    {
        if ($value && !is_numeric($value)) {
            self::addError($field, ucfirst($field) . " must be numeric");
        }
    }

    private static function string($field, $value)
    {
        if ($value && !is_string($value)) {
            self::addError($field, ucfirst($field) . " must be a string");
        }
    }

    private static function arrayType($field, $value)
    {
        if ($value && !is_array($value)) {
            self::addError($field, ucfirst($field) . " must be an array");
        }
    }

    private static function in($field, $value, $options)
    {
        if ($value) {
            $allowedValues = explode(',', str_replace(' ', '', $options));
            if (!in_array($value, $allowedValues)) {
                self::addError($field, ucfirst($field) . " is invalid");
            }
        }
    }

    private static function unique($field, $value, $table)
    {
        if (!$value) return;

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
            $stmt->execute([$value]);
            if ($stmt->fetchColumn() > 0) {
                self::addError($field, ucfirst($field) . " already exists");
            }
        } catch (\Exception $e) {
            // Handle database error
        }
    }

    private static function confirmed($field, $value)
    {
        // Implementar según necesidades
    }

    private static function addError($field, $message)
    {
        if (!isset(self::$errors[$field])) {
            self::$errors[$field] = [];
        }
        self::$errors[$field][] = $message;
    }

    public static function errors()
    {
        return self::$errors;
    }

    public static function sanitize($data)
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}
