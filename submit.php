<?php

declare(strict_types=1);

require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/db.php';

function post_string_submit(array $post, string $key): string
{
    return trim_string($post[$key] ?? '');
}

function empty_string_to_null(string $value): ?string
{
    $v = trim_string($value);
    return $v === '' ? null : $v;
}

function datetime_local_to_mysql(string $value): ?string
{
    $v = trim_string($value);
    return $v === '' ? null : str_replace('T', ' ', $v);
}

function render_html_error_page(int $statusCode, string $title, string $message, ?string $debugMessage = null): void
{
    http_response_code($statusCode);

    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . htmlspecialchars($title) . '</title>';
    echo '<style>';
    echo 'body{font-family:Arial,sans-serif;background:#f5f6f8;color:#1f2937;padding:24px;}';
    echo '.card{max-width:900px;margin:0 auto;background:#fff;border:1px solid #dcdfe4;border-radius:6px;padding:16px;}';
    echo '.title{font-weight:700;margin-bottom:10px;}';
    echo '.btn{display:inline-block;margin-top:12px;background:#2563eb;color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none;font-weight:700;}';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    echo '<div class="card">';
    echo '<div class="title">' . htmlspecialchars($title) . '</div>';
    echo '<div>' . htmlspecialchars($message) . '</div>';
    if ($debugMessage !== null && $debugMessage !== '') {
        echo '<div style="margin-top:10px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;border-radius:6px;padding:10px 12px;">';
        echo '<div style="font-weight:700;margin-bottom:6px;">Debug (local only)</div>';
        echo '<div>' . htmlspecialchars($debugMessage) . '</div>';
        echo '</div>';
    }
    echo '<a class="btn" href="index.php">Back to form</a>';
    echo '</div>';
    echo '</body>';
    echo '</html>';
    exit;
}

function has_any_value(array $values): bool
{
    for ($i = 0; $i < count($values); $i++) {
        if (trim_string($values[$i]) !== '') return true;
    }
    return false;
}

function save_uploaded_file(string $fieldName): ?string
{
    if (!isset($_FILES[$fieldName])) return null;
    if (!is_array($_FILES[$fieldName])) return null;

    $file = $_FILES[$fieldName];
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    if (!isset($file['tmp_name']) || !is_string($file['tmp_name'])) return null;
    if (!isset($file['name']) || !is_string($file['name'])) return null;

    // Basic upload hardening: limit file size and restrict to common safe types.
    if (isset($file['size']) && is_numeric($file['size'])) {
        $maxBytes = 5 * 1024 * 1024; // 5MB
        if ((int)$file['size'] > $maxBytes) return null;
    }

    $uploadsDir = __DIR__ . '/uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
    }

    $originalName = $file['name'];
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $ext = $ext ? ('.' . strtolower($ext)) : '';

    // Block dangerous extensions even if someone tries to upload them.
    $blockedExts = [
        '.php', '.phtml', '.php3', '.php4', '.php5', '.php7',
        '.phar', '.cgi', '.pl', '.asp', '.aspx', '.jsp',
        '.sh', '.bat', '.cmd', '.exe',
    ];
    if ($ext !== '' && in_array($ext, $blockedExts, true)) return null;

    // Allowlist of extensions we accept for signature uploads.
    $allowedExts = ['.png', '.jpg', '.jpeg', '.webp', '.pdf'];
    if ($ext === '' || !in_array($ext, $allowedExts, true)) return null;

    // MIME check (best effort). This helps block renamed files.
    $allowedMimes = ['image/png', 'image/jpeg', 'image/webp', 'application/pdf'];
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!is_string($mime) || !in_array($mime, $allowedMimes, true)) return null;
        }
    }

    $safeName = 'upload_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . $ext;

    $targetPath = $uploadsDir . '/' . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) return null;

    return 'uploads/' . $safeName;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    render_html_error_page(405, 'Method Not Allowed', 'Method Not Allowed');
}

$result = validate_e1_form($_POST);

if (!$result['ok']) {
    http_response_code(400);

    $errors = $result['errors'];
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Validation Error</title>';
    echo '<style>';
    echo 'body{font-family:Arial,sans-serif;background:#f5f6f8;color:#1f2937;padding:24px;}';
    echo '.card{max-width:900px;margin:0 auto;background:#fff;border:1px solid #dcdfe4;border-radius:6px;padding:16px;}';
    echo '.title{font-weight:700;margin-bottom:10px;}';
    echo '.err{background:#fff5f5;border:1px solid #fecaca;color:#991b1b;border-radius:6px;padding:10px 12px;margin:10px 0;}';
    echo '.err li{margin:4px 0;}';
    echo '.btn{display:inline-block;margin-top:12px;background:#2563eb;color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none;font-weight:700;}';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    echo '<div class="card">';
    echo '<div class="title">Please fix the following errors:</div>';
    echo '<div class="err"><ul>';
    for ($i = 0; $i < count($errors); $i++) {
        echo '<li>' . htmlspecialchars($errors[$i]) . '</li>';
    }
    echo '</ul></div>';
    echo '<a class="btn" href="index.php">Back to form</a>';
    echo '</div>';
    echo '</body>';
    echo '</html>';
    exit;
}

$data = $result['data'];

try {
    $pdo = db();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id FROM civil_statuses WHERE code = :code LIMIT 1');
    $stmt->execute([':code' => $data['civil_status']]);
    $civilStatusRow = $stmt->fetch();
    if (!$civilStatusRow || !isset($civilStatusRow['id'])) {
        throw new Exception('Invalid civil status.');
    }
    $civilStatusId = (int)$civilStatusRow['id'];

    $personStmt = $pdo->prepare(
        'INSERT INTO persons (
            last_name, first_name, middle_name,
            date_of_birth, sex,
            civil_status_id, civil_status_other,
            nationality, place_of_birth,
            mobile_number, email,
            religion, telephone_number,
            father_last_name, father_first_name, father_middle_name,
            mother_last_name, mother_first_name, mother_middle_name,
            same_as_home_address
        ) VALUES (
            :last_name, :first_name, :middle_name,
            :date_of_birth, :sex,
            :civil_status_id, :civil_status_other,
            :nationality, :place_of_birth,
            :mobile_number, :email,
            :religion, :telephone_number,
            :father_last_name, :father_first_name, :father_middle_name,
            :mother_last_name, :mother_first_name, :mother_middle_name,
            :same_as_home_address
        )'
    );

    $personStmt->execute([
        ':last_name' => $data['last_name'],
        ':first_name' => $data['first_name'],
        ':middle_name' => $data['middle_name'],
        ':date_of_birth' => $data['date_of_birth'],
        ':sex' => $data['gender'],
        ':civil_status_id' => $civilStatusId,
        ':civil_status_other' => $data['civil_status'] === 'others' ? $data['civil_status_other'] : null,
        ':nationality' => $data['nationality'],
        ':place_of_birth' => $data['place_of_birth'],
        ':mobile_number' => $data['mobile_number'],
        ':email' => $data['email'],
        ':religion' => empty_string_to_null($data['religion']),
        ':telephone_number' => empty_string_to_null($data['telephone_number']),
        ':father_last_name' => empty_string_to_null($data['father_last_name']),
        ':father_first_name' => empty_string_to_null($data['father_first_name']),
        ':father_middle_name' => empty_string_to_null($data['father_middle_name']),
        ':mother_last_name' => empty_string_to_null($data['mother_last_name']),
        ':mother_first_name' => empty_string_to_null($data['mother_first_name']),
        ':mother_middle_name' => empty_string_to_null($data['mother_middle_name']),
        ':same_as_home_address' => $data['same_as_home_address'] ? 1 : 0,
    ]);

    $personId = (int)$pdo->lastInsertId();

    $homeStmt = $pdo->prepare(
        'INSERT INTO person_home_addresses (person_id, address_line, zip_code)
         VALUES (:person_id, :address_line, :zip_code)'
    );
    $homeStmt->execute([
        ':person_id' => $personId,
        ':address_line' => $data['home_address'],
        ':zip_code' => empty_string_to_null($data['zip_code']),
    ]);

    // Part 2 - spouse
    $spouseLast = post_string_submit($_POST, 'spouse_last_name');
    $spouseFirst = post_string_submit($_POST, 'spouse_first_name');
    $spouseMiddle = post_string_submit($_POST, 'spouse_middle_name');
    $spouseSuffix = post_string_submit($_POST, 'spouse_suffix');
    $spouseBirth = post_string_submit($_POST, 'spouse_birth');

    if (has_any_value([$spouseLast, $spouseFirst, $spouseMiddle, $spouseSuffix, $spouseBirth])) {
        $depStmt = $pdo->prepare(
            'INSERT INTO person_dependents (person_id, dependent_type, last_name, first_name, middle_name, suffix, date_of_birth, relationship)
             VALUES (:person_id, :dependent_type, :last_name, :first_name, :middle_name, :suffix, :date_of_birth, :relationship)'
        );
        $depStmt->execute([
            ':person_id' => $personId,
            ':dependent_type' => 'spouse',
            ':last_name' => $spouseLast !== '' ? $spouseLast : '-',
            ':first_name' => $spouseFirst !== '' ? $spouseFirst : '-',
            ':middle_name' => empty_string_to_null($spouseMiddle),
            ':suffix' => empty_string_to_null($spouseSuffix),
            ':date_of_birth' => empty_string_to_null($spouseBirth),
            ':relationship' => null,
        ]);
    }

    // Part 2 - children (dynamic)
    $childIndices = [];
    foreach ($_POST as $key => $value) {
        if (preg_match('/^child_(\d+)_last_name$/', (string)$key, $m)) {
            $childIndices[] = (int)$m[1];
        }
    }
    $childIndices = array_values(array_unique($childIndices));
    sort($childIndices);

    if (count($childIndices) > 0) {
        $depStmt = $pdo->prepare(
            'INSERT INTO person_dependents (person_id, dependent_type, last_name, first_name, middle_name, suffix, date_of_birth, relationship)
             VALUES (:person_id, :dependent_type, :last_name, :first_name, :middle_name, :suffix, :date_of_birth, :relationship)'
        );
        foreach ($childIndices as $i) {
            $last = post_string_submit($_POST, 'child_' . $i . '_last_name');
            $first = post_string_submit($_POST, 'child_' . $i . '_first_name');
            $middle = post_string_submit($_POST, 'child_' . $i . '_middle_name');
            $suffix = post_string_submit($_POST, 'child_' . $i . '_suffix');
            $birth = post_string_submit($_POST, 'child_' . $i . '_birth');

            if (!has_any_value([$last, $first, $middle, $suffix, $birth])) continue;

            $depStmt->execute([
                ':person_id' => $personId,
                ':dependent_type' => 'child',
                ':last_name' => $last !== '' ? $last : '-',
                ':first_name' => $first !== '' ? $first : '-',
                ':middle_name' => empty_string_to_null($middle),
                ':suffix' => empty_string_to_null($suffix),
                ':date_of_birth' => empty_string_to_null($birth),
                ':relationship' => null,
            ]);
        }
    }

    // Part 2 - other beneficiaries (dynamic)
    $otherIndices = [];
    foreach ($_POST as $key => $value) {
        if (preg_match('/^other_(\d+)_last_name$/', (string)$key, $m)) {
            $otherIndices[] = (int)$m[1];
        }
    }
    $otherIndices = array_values(array_unique($otherIndices));
    sort($otherIndices);

    if (count($otherIndices) > 0) {
        $depStmt = $pdo->prepare(
            'INSERT INTO person_dependents (person_id, dependent_type, last_name, first_name, middle_name, suffix, date_of_birth, relationship)
             VALUES (:person_id, :dependent_type, :last_name, :first_name, :middle_name, :suffix, :date_of_birth, :relationship)'
        );
        foreach ($otherIndices as $i) {
            $last = post_string_submit($_POST, 'other_' . $i . '_last_name');
            $first = post_string_submit($_POST, 'other_' . $i . '_first_name');
            $middle = post_string_submit($_POST, 'other_' . $i . '_middle_name');
            $suffix = post_string_submit($_POST, 'other_' . $i . '_suffix');
            $relationship = post_string_submit($_POST, 'other_' . $i . '_relationship');
            $birth = post_string_submit($_POST, 'other_' . $i . '_birth');

            if (!has_any_value([$last, $first, $middle, $suffix, $relationship, $birth])) continue;

            $depStmt->execute([
                ':person_id' => $personId,
                ':dependent_type' => 'other',
                ':last_name' => $last !== '' ? $last : '-',
                ':first_name' => $first !== '' ? $first : '-',
                ':middle_name' => empty_string_to_null($middle),
                ':suffix' => empty_string_to_null($suffix),
                ':date_of_birth' => empty_string_to_null($birth),
                ':relationship' => empty_string_to_null($relationship),
            ]);
        }
    }

    // Part 3 - SE
    $seProfession = post_string_submit($_POST, 'se_profession_business');
    $seYearStarted = post_string_submit($_POST, 'se_year_started');
    $seMonthly = post_string_submit($_POST, 'se_monthly_earnings');
    if (has_any_value([$seProfession, $seYearStarted, $seMonthly])) {
        $stmt = $pdo->prepare(
            'INSERT INTO person_self_employment (person_id, profession_business, year_started, monthly_earnings)
             VALUES (:person_id, :profession_business, :year_started, :monthly_earnings)'
        );
        $stmt->execute([
            ':person_id' => $personId,
            ':profession_business' => empty_string_to_null($seProfession),
            ':year_started' => empty_string_to_null($seYearStarted),
            ':monthly_earnings' => empty_string_to_null($seMonthly),
        ]);
    }

    // Part 3 - OFW
    $ofwAddress = post_string_submit($_POST, 'ofw_foreign_address');
    $ofwMonthly = post_string_submit($_POST, 'ofw_monthly_earnings');
    $flexiFund = post_string_submit($_POST, 'flexi_fund');
    if (has_any_value([$ofwAddress, $ofwMonthly, $flexiFund])) {
        $stmt = $pdo->prepare(
            'INSERT INTO person_ofw (person_id, foreign_address, monthly_earnings, flexi_fund)
             VALUES (:person_id, :foreign_address, :monthly_earnings, :flexi_fund)'
        );
        $stmt->execute([
            ':person_id' => $personId,
            ':foreign_address' => empty_string_to_null($ofwAddress),
            ':monthly_earnings' => empty_string_to_null($ofwMonthly),
            ':flexi_fund' => ($flexiFund === 'yes' || $flexiFund === 'no') ? $flexiFund : null,
        ]);
    }

    // Part 3 - NWS
    $nwsSS = post_string_submit($_POST, 'nws_working_spouse_ss');
    $nwsIncome = post_string_submit($_POST, 'nws_monthly_income');
    $nwsSigPath = save_uploaded_file('nws_signature_file');
    if (has_any_value([$nwsSS, $nwsIncome, $nwsSigPath])) {
        $stmt = $pdo->prepare(
            'INSERT INTO person_nws (person_id, working_spouse_ss_no, working_spouse_monthly_income, working_spouse_signature_file_path)
             VALUES (:person_id, :working_spouse_ss_no, :working_spouse_monthly_income, :working_spouse_signature_file_path)'
        );
        $stmt->execute([
            ':person_id' => $personId,
            ':working_spouse_ss_no' => empty_string_to_null($nwsSS),
            ':working_spouse_monthly_income' => empty_string_to_null($nwsIncome),
            ':working_spouse_signature_file_path' => $nwsSigPath,
        ]);
    }

    // Part 4 - Certification
    $certPrinted = post_string_submit($_POST, 'cert_printed_name');
    $certSignatureText = post_string_submit($_POST, 'cert_signature');
    $certDate = post_string_submit($_POST, 'cert_date');
    $certSigPath = save_uploaded_file('cert_signature_file');
    if (has_any_value([$certPrinted, $certSignatureText, $certDate, $certSigPath])) {
        $stmt = $pdo->prepare(
            'INSERT INTO person_certifications (person_id, printed_name, signature_text, signature_file_path, cert_date)
             VALUES (:person_id, :printed_name, :signature_text, :signature_file_path, :cert_date)'
        );
        $stmt->execute([
            ':person_id' => $personId,
            ':printed_name' => empty_string_to_null($certPrinted),
            ':signature_text' => empty_string_to_null($certSignatureText),
            ':signature_file_path' => $certSigPath,
            ':cert_date' => empty_string_to_null($certDate),
        ]);
    }

    // Part 5 - SSS Processing
    $sssBusinessCode = post_string_submit($_POST, 'sss_business_code');
    $sssWorkingSpouseMsc = post_string_submit($_POST, 'sss_working_spouse_msc');
    $sssMonthlyContribution = post_string_submit($_POST, 'sss_monthly_contribution');
    $sssApprovedMsc = post_string_submit($_POST, 'sss_approved_msc');
    $sssStartPayment = post_string_submit($_POST, 'sss_start_of_payment');
    $sssFlexiStatus = post_string_submit($_POST, 'sss_flexi_status');

    $receivedSigPath = save_uploaded_file('sss_received_by_signature');
    $receivedDateTime = post_string_submit($_POST, 'sss_received_by_datetime');
    $processedSigPath = save_uploaded_file('sss_processed_by_signature');
    $processedDateTime = post_string_submit($_POST, 'sss_processed_by_datetime');
    $reviewedSigPath = save_uploaded_file('sss_reviewed_by_signature');
    $reviewedDateTime = post_string_submit($_POST, 'sss_reviewed_by_datetime');

    if (has_any_value([
        $sssBusinessCode,
        $sssWorkingSpouseMsc,
        $sssMonthlyContribution,
        $sssApprovedMsc,
        $sssStartPayment,
        $sssFlexiStatus,
        $receivedSigPath,
        $receivedDateTime,
        $processedSigPath,
        $processedDateTime,
        $reviewedSigPath,
        $reviewedDateTime,
    ])) {
        $stmt = $pdo->prepare(
            'INSERT INTO person_sss_processing (
                person_id,
                business_code, working_spouse_msc, monthly_contribution, approved_msc, start_of_payment, flexi_status,
                received_by_signature_path, received_by_datetime,
                processed_by_signature_path, processed_by_datetime,
                reviewed_by_signature_path, reviewed_by_datetime
            ) VALUES (
                :person_id,
                :business_code, :working_spouse_msc, :monthly_contribution, :approved_msc, :start_of_payment, :flexi_status,
                :received_by_signature_path, :received_by_datetime,
                :processed_by_signature_path, :processed_by_datetime,
                :reviewed_by_signature_path, :reviewed_by_datetime
            )'
        );
        $stmt->execute([
            ':person_id' => $personId,
            ':business_code' => empty_string_to_null($sssBusinessCode),
            ':working_spouse_msc' => empty_string_to_null($sssWorkingSpouseMsc),
            ':monthly_contribution' => empty_string_to_null($sssMonthlyContribution),
            ':approved_msc' => empty_string_to_null($sssApprovedMsc),
            ':start_of_payment' => empty_string_to_null($sssStartPayment),
            ':flexi_status' => ($sssFlexiStatus === 'approved' || $sssFlexiStatus === 'disapproved') ? $sssFlexiStatus : null,
            ':received_by_signature_path' => $receivedSigPath,
            ':received_by_datetime' => datetime_local_to_mysql($receivedDateTime),
            ':processed_by_signature_path' => $processedSigPath,
            ':processed_by_datetime' => datetime_local_to_mysql($processedDateTime),
            ':reviewed_by_signature_path' => $reviewedSigPath,
            ':reviewed_by_datetime' => datetime_local_to_mysql($reviewedDateTime),
        ]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $isLocal = isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');
    error_log('E1PersonalRecord submit.php error: ' . $e->getMessage());
    error_log($e->getTraceAsString());

    render_html_error_page(
        500,
        'Server Error',
        'Unable to save your form. Please try again.',
        $isLocal ? $e->getMessage() : null
    );
}

echo '<!DOCTYPE html>';
echo '<html lang="en">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<title>Submitted</title>';
echo '<style>';
echo 'body{font-family:Arial,sans-serif;background:#f5f6f8;color:#1f2937;padding:24px;}';
echo '.card{max-width:900px;margin:0 auto;background:#fff;border:1px solid #dcdfe4;border-radius:6px;padding:16px;}';
echo '.row{margin:6px 0;}';
echo '.label{font-weight:700;}';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<div class="card">';
echo '<div class="row"><span class="label">Status:</span> Saved to database</div>';
echo '<div class="row"><span class="label">Person ID:</span> ' . htmlspecialchars((string)$personId) . '</div>';
echo '<div class="row"><span class="label">Place of Birth:</span> ' . htmlspecialchars($data['place_of_birth']) . '</div>';
echo '<div class="row"><span class="label">Home Address:</span> ' . htmlspecialchars($data['home_address']) . '</div>';
echo '<div class="row"><span class="label">Email:</span> ' . htmlspecialchars($data['email']) . '</div>';
echo '<div class="row" style="margin-top:12px;">(Files are saved under uploads/ and stored as file paths.)</div>';
echo '</div>';
echo '</body>';
echo '</html>';
