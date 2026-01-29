<?php

// This file contains functions to validate form data

// Function to clean up string values (remove spaces at start/end)
function trim_string($value)
{
    // If value is null or empty, return empty string
    if ($value === null || $value === '') {
        return '';
    }
    
    // Convert to string and remove spaces at start and end
    $result = trim((string)$value);
    
    return $result;
}

// Function to check if email is valid
function is_email_valid($email)
{
    // Clean the email
    $email = trim_string($email);
    
    // If empty, it's not valid
    if ($email === '') {
        return false;
    }
    
    // Use PHP's built-in email validator
    $result = filter_var($email, FILTER_VALIDATE_EMAIL);
    
    // If result is not false, email is valid
    if ($result !== false) {
        return true;
    } else {
        return false;
    }
}

// Function to validate the entire form
function validate_e1_form($post)
{
    // Array to store error messages
    $errors = array();
    
    // Array to store cleaned form data
    $data = array();
    
    // Get and clean all form fields
    $data['last_name'] = trim_string($post['last_name'] ?? '');
    $data['first_name'] = trim_string($post['first_name'] ?? '');
    $data['middle_name'] = trim_string($post['middle_name'] ?? '');
    
    $data['date_of_birth'] = trim_string($post['date_of_birth'] ?? '');
    $data['gender'] = trim_string($post['gender'] ?? '');
    $data['civil_status'] = trim_string($post['civil_status'] ?? '');
    $data['civil_status_other'] = trim_string($post['civil_status_other'] ?? '');
    
    $data['nationality'] = trim_string($post['nationality'] ?? '');
    $data['place_of_birth'] = trim_string($post['place_of_birth'] ?? '');
    
    // Check if "same as home address" checkbox is checked
    if (isset($post['same_as_home_address']) && $post['same_as_home_address'] === '1') {
        $data['same_as_home_address'] = true;
    } else {
        $data['same_as_home_address'] = false;
    }
    
    $data['home_address'] = trim_string($post['home_address'] ?? '');
    $data['zip_code'] = trim_string($post['zip_code'] ?? '');
    
    $data['mobile_number'] = trim_string($post['mobile_number'] ?? '');
    $data['email'] = trim_string($post['email'] ?? '');
    
    $data['religion'] = trim_string($post['religion'] ?? '');
    $data['telephone_number'] = trim_string($post['telephone_number'] ?? '');
    
    $data['father_last_name'] = trim_string($post['father_last_name'] ?? '');
    $data['father_first_name'] = trim_string($post['father_first_name'] ?? '');
    $data['father_middle_name'] = trim_string($post['father_middle_name'] ?? '');
    
    $data['mother_last_name'] = trim_string($post['mother_last_name'] ?? '');
    $data['mother_first_name'] = trim_string($post['mother_first_name'] ?? '');
    $data['mother_middle_name'] = trim_string($post['mother_middle_name'] ?? '');
    
    // Validate required fields
    
    // Check last name
    if ($data['last_name'] === '') {
        $errors[] = 'Last Name is required.';
    }
    
    // Check first name
    if ($data['first_name'] === '') {
        $errors[] = 'First Name is required.';
    }
    
    // Check date of birth
    if ($data['date_of_birth'] === '') {
        $errors[] = 'Date of Birth is required.';
    }
    
    // Check gender
    if ($data['gender'] === '') {
        $errors[] = 'Sex is required.';
    }
    
    // Check civil status
    if ($data['civil_status'] === '') {
        $errors[] = 'Civil Status is required.';
    } else if ($data['civil_status'] === 'others') {
        // If civil status is "others", require the other field
        if ($data['civil_status_other'] === '') {
            $errors[] = 'Civil Status (Others) is required.';
        }
    }
    
    // Check nationality
    if ($data['nationality'] === '') {
        $errors[] = 'Nationality is required.';
    }
    
    // Check place of birth
    if ($data['place_of_birth'] === '') {
        $errors[] = 'Place of Birth is required.';
    }
    
    // Check home address (only if checkbox is not checked)
    if ($data['same_as_home_address'] === true) {
        // If checkbox is checked, copy place of birth to home address
        $data['home_address'] = $data['place_of_birth'];
    } else {
        // If checkbox is not checked, home address is required
        if ($data['home_address'] === '') {
            $errors[] = 'Home Address is required.';
        }
    }
    
    // Check mobile number
    if ($data['mobile_number'] === '') {
        $errors[] = 'Mobile/Cellphone Number is required.';
    }
    
    // Check email
    if ($data['email'] === '') {
        $errors[] = 'E-mail Address is required.';
    } else {
        // If email is provided, check if it's valid
        if (!is_email_valid($data['email'])) {
            $errors[] = 'E-mail Address must be a valid email (example: name@gmail.com).';
        }
    }
    
    // Return result
    $result = array();
    $result['ok'] = (count($errors) === 0);
    $result['errors'] = $errors;
    $result['data'] = $data;
    
    return $result;
}

?>
