<?php

declare(strict_types=1);

function trim_string($value): string
{
    return trim((string)($value ?? ''));
}

function is_email_valid(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_e1_form(array $post): array
{
    $errors = [];

    $data = [];
    $data['last_name'] = trim_string($post['last_name'] ?? '');
    $data['first_name'] = trim_string($post['first_name'] ?? '');
    $data['middle_name'] = trim_string($post['middle_name'] ?? '');

    $data['date_of_birth'] = trim_string($post['date_of_birth'] ?? '');
    $data['gender'] = trim_string($post['gender'] ?? '');
    $data['civil_status'] = trim_string($post['civil_status'] ?? '');
    $data['civil_status_other'] = trim_string($post['civil_status_other'] ?? '');

    $data['nationality'] = trim_string($post['nationality'] ?? '');
    $data['place_of_birth'] = trim_string($post['place_of_birth'] ?? '');

    $data['same_as_home_address'] = isset($post['same_as_home_address']) && (string)$post['same_as_home_address'] === '1';

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

    if ($data['last_name'] === '') $errors[] = 'Last Name is required.';
    if ($data['first_name'] === '') $errors[] = 'First Name is required.';
    if ($data['middle_name'] === '') $errors[] = 'Middle Name is required.';

    if ($data['date_of_birth'] === '') $errors[] = 'Date of Birth is required.';
    if ($data['gender'] === '') $errors[] = 'Sex is required.';

    if ($data['civil_status'] === '') {
        $errors[] = 'Civil Status is required.';
    } elseif ($data['civil_status'] === 'others') {
        if ($data['civil_status_other'] === '') {
            $errors[] = 'Civil Status (Others) is required.';
        }
    }

    if ($data['nationality'] === '') $errors[] = 'Nationality is required.';
    if ($data['place_of_birth'] === '') $errors[] = 'Place of Birth is required.';

    if ($data['same_as_home_address']) {
        $data['home_address'] = $data['place_of_birth'];
    } else {
        if ($data['home_address'] === '') $errors[] = 'Home Address is required.';
    }

    if ($data['mobile_number'] === '') $errors[] = 'Mobile/Cellphone Number is required.';

    if ($data['email'] === '') {
        $errors[] = 'E-mail Address is required.';
    } elseif (!is_email_valid($data['email'])) {
        $errors[] = 'E-mail Address must be a valid email (example: name@gmail.com).';
    }

    return [
        'ok' => count($errors) === 0,
        'errors' => $errors,
        'data' => $data,
    ];
}
