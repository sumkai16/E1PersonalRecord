<?php

declare(strict_types=1);

function trim_string($value): string
{
    return trim((string)($value ?? ''));
}

function post_string(array $post, string $key): string
{
    return trim_string($post[$key] ?? '');
}

function post_checkbox_is_one(array $post, string $key): bool
{
    return isset($post[$key]) && (string)$post[$key] === '1';
}

function is_email_valid(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_e1_form(array $post): array
{
    $errors = [];

    $data = [];
    $data['last_name'] = post_string($post, 'last_name');
    $data['first_name'] = post_string($post, 'first_name');
    $data['middle_name'] = post_string($post, 'middle_name');

    $data['date_of_birth'] = post_string($post, 'date_of_birth');
    $data['gender'] = post_string($post, 'gender');
    $data['civil_status'] = post_string($post, 'civil_status');
    $data['civil_status_other'] = post_string($post, 'civil_status_other');

    $data['nationality'] = post_string($post, 'nationality');
    $data['place_of_birth'] = post_string($post, 'place_of_birth');

    $data['same_as_home_address'] = post_checkbox_is_one($post, 'same_as_home_address');

    $data['home_address'] = post_string($post, 'home_address');
    $data['zip_code'] = post_string($post, 'zip_code');

    $data['mobile_number'] = post_string($post, 'mobile_number');
    $data['email'] = post_string($post, 'email');

    $data['religion'] = post_string($post, 'religion');
    $data['telephone_number'] = post_string($post, 'telephone_number');

    $data['father_last_name'] = post_string($post, 'father_last_name');
    $data['father_first_name'] = post_string($post, 'father_first_name');
    $data['father_middle_name'] = post_string($post, 'father_middle_name');

    $data['mother_last_name'] = post_string($post, 'mother_last_name');
    $data['mother_first_name'] = post_string($post, 'mother_first_name');
    $data['mother_middle_name'] = post_string($post, 'mother_middle_name');

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
