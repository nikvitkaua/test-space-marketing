<?php

class Validator
{
    /**
     * Leads form inputs validation
     * * @param array $data Data from $_POST
     * @return array Errors array (empty if ok)
     */
    public function validateLeadForm(array $data): array
    {
        $errors = [];

        $firstName = isset($data['firstName']) ? trim($data['firstName']) : '';
        $lastName  = isset($data['lastName']) ? trim($data['lastName']) : '';
        $phone     = isset($data['phone']) ? trim($data['phone']) : '';
        $email     = isset($data['email']) ? trim($data['email']) : '';

        if (empty($firstName)) {
            $errors['firstName'] = "First name is required.";
        } elseif (mb_strlen($firstName) < 2) {
            $errors['firstName'] = "First name must be at least 2 characters.";
        }

        if (empty($lastName)) {
            $errors['lastName'] = "Last name is required.";
        } elseif (mb_strlen($lastName) < 2) {
            $errors['lastName'] = "Last name must be at least 2 characters.";
        }

        if (empty($email)) {
            $errors['email'] = "Email address is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Please enter a valid email address.";
        }

        if (empty($phone)) {
            $errors['phone'] = "Phone number is required.";
        } elseif (!preg_match('/^[0-9\-\+\s\(\)]+$/', $phone)) {
            $errors['phone'] = "Phone number contains invalid characters.";
        } elseif (strlen($phone) < 7) {
            $errors['phone'] = "Phone number is too short.";
        }

        return $errors;
    }
}