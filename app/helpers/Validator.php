<?php
/**
 * Validator Helper Class
 * 
 * Provides methods to validate form input against various rules.
 */
class Validator {
    private $errors = [];
    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Validate a field against a rule.
     * 
     * @param string $field The field name in the data array.
     * @param string $rule The rule to apply (required, email, numeric, min:x, max:x, phone).
     * @param string|null $customMessage Optional custom error message.
     */
    public function validate($field, $rule, $customMessage = null) {
        $value = $this->data[$field] ?? null;

        // Parse rule parameters (e.g., min:5)
        $params = [];
        if (strpos($rule, ':') !== false) {
            list($ruleName, $paramStr) = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
            $rule = $ruleName;
        }

        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, $customMessage ?? "$field is required.");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, $customMessage ?? "$field must be a valid email address.");
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, $customMessage ?? "$field must be a number.");
                }
                break;

            case 'min':
                if (!empty($value)) {
                    $limit = $params[0] ?? 0;
                    if (is_numeric($value)) {
                        if ($value < $limit) {
                            $this->addError($field, $customMessage ?? "$field must be at least $limit.");
                        }
                    } elseif (is_string($value)) {
                        if (strlen($value) < $limit) {
                            $this->addError($field, $customMessage ?? "$field must be at least $limit characters.");
                        }
                    }
                }
                break;

            case 'max':
                if (!empty($value)) {
                    $limit = $params[0] ?? 0;
                    if (is_numeric($value)) {
                        if ($value > $limit) {
                            $this->addError($field, $customMessage ?? "$field must not exceed $limit.");
                        }
                    } elseif (is_string($value)) {
                        if (strlen($value) > $limit) {
                            $this->addError($field, $customMessage ?? "$field must not exceed $limit characters.");
                        }
                    }
                }
                break;

            case 'phone':
                // Basic phone validation (allows +, -, space, brackets, and digits)
                if (!empty($value) && !preg_match("/^[\d\+\-\(\)\s]{7,20}$/", $value)) {
                    $this->addError($field, $customMessage ?? "$field must be a valid phone number.");
                }
                break;
        }
    }

    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function isValid() {
        return empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getFirstError() {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0];
        }
        return null;
    }
}
?>
