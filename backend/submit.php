<?php

// This file handles form submission
// It validates the form, saves data to database, and handles file uploads

// Include required files
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/crud_functions.php';

// Function to check if any value in array is not empty
function has_any_value($values)
{
    // Loop through all values
    for ($i = 0; $i < count($values); $i++) {
        // If any value is not empty, return true
        if (trim_string($values[$i]) !== '') {
            return true;
        }
    }
    // If all values are empty, return false
    return false;
}

// Function to save uploaded file
function save_uploaded_file($fieldName)
{
    // Check if file was uploaded
    if (!isset($_FILES[$fieldName])) {
        return null;
    }
    
    // Check if it's an array (should be)
    if (!is_array($_FILES[$fieldName])) {
        return null;
    }
    
    // Get file information
    $file = $_FILES[$fieldName];
    
    // Check if upload was successful
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Check if temp file exists
    if (!isset($file['tmp_name']) || !is_string($file['tmp_name'])) {
        return null;
    }
    
    // Check if filename exists
    if (!isset($file['name']) || !is_string($file['name'])) {
        return null;
    }
    
    // Create uploads directory if it doesn't exist
    $uploadsDir = __DIR__ . '/uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
    }
    
    // Get original filename and extension
    $originalName = $file['name'];
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    // Add dot before extension if extension exists
    if ($ext) {
        $ext = '.' . strtolower($ext);
    } else {
        $ext = '';
    }
    
    // Create a safe filename (to prevent conflicts and security issues)
    $safeName = 'upload_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . $ext;
    
    // Full path where file will be saved
    $targetPath = $uploadsDir . '/' . $safeName;
    
    // Move file from temp location to uploads folder
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return null;
    }
    
    // Return relative path (for storing in database)
    return 'uploads/' . $safeName;
}

// Check if form was submitted via POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

// Validate the form data
$result = validate_e1_form($_POST);

// If validation failed, show errors
if (!$result['ok']) {
    http_response_code(400);
    
    $errors = $result['errors'];
    
    // Display error page
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
    // Display each error
    for ($i = 0; $i < count($errors); $i++) {
        echo '<li>' . htmlspecialchars($errors[$i]) . '</li>';
    }
    echo '</ul></div>';
    echo '<a class="btn" href="../index.php">Back to form</a>';
    echo '</div>';
    echo '</body>';
    echo '</html>';
    exit;
}

// Get cleaned form data
$data = $result['data'];

// Try to save data to database
try {
    // Get database connection
    $pdo = db();
    // Start transaction (all or nothing)
    $pdo->beginTransaction();
    
    // Create person record and get person ID
    $personId = create_person($data);
    
    // ===== PART 2: DEPENDENTS =====
    
    // Save spouse information
    $spouseLast = trim_string($_POST['spouse_last_name'] ?? '');
    $spouseFirst = trim_string($_POST['spouse_first_name'] ?? '');
    $spouseMiddle = trim_string($_POST['spouse_middle_name'] ?? '');
    $spouseSuffix = trim_string($_POST['spouse_suffix'] ?? '');
    $spouseBirth = trim_string($_POST['spouse_birth'] ?? '');
    
    // If any spouse field has value, save spouse
    if (has_any_value(array($spouseLast, $spouseFirst, $spouseMiddle, $spouseSuffix, $spouseBirth))) {
        $depStmt = $pdo->prepare(
            'INSERT INTO person_dependents (person_id, dependent_type, last_name, first_name, middle_name, suffix, date_of_birth, relationship)
             VALUES (:person_id, :dependent_type, :last_name, :first_name, :middle_name, :suffix, :date_of_birth, :relationship)'
        );
        $depStmt->execute(array(
            ':person_id' => $personId,
            ':dependent_type' => 'spouse',
            ':last_name' => ($spouseLast !== '') ? $spouseLast : '-',
            ':first_name' => ($spouseFirst !== '') ? $spouseFirst : '-',
            ':middle_name' => ($spouseMiddle !== '') ? $spouseMiddle : null,
            ':suffix' => ($spouseSuffix !== '') ? $spouseSuffix : null,
            ':date_of_birth' => ($spouseBirth !== '') ? $spouseBirth : null,
            ':relationship' => null,
        ));
    }
    
    // Save children (dynamic - can have multiple)
    // First, find all child indices from POST data
    $childIndices = array();
    foreach ($_POST as $key => $value) {
        // Look for keys like "child_1_last_name", "child_2_last_name", etc.
        if (preg_match('/^child_(\d+)_last_name$/', (string)$key, $matches)) {
            $childIndices[] = (int)$matches[1];
        }
    }
    // Remove duplicates and sort
    $childIndices = array_values(array_unique($childIndices));
    sort($childIndices);
    
    // If there are children, save them
    if (count($childIndices) > 0) {
        $depStmt = $pdo->prepare(
            'INSERT INTO person_dependents (person_id, dependent_type, last_name, first_name, middle_name, suffix, date_of_birth, relationship)
             VALUES (:person_id, :dependent_type, :last_name, :first_name, :middle_name, :suffix, :date_of_birth, :relationship)'
        );
        
        // Loop through each child
        foreach ($childIndices as $i) {
            $last = trim_string($_POST['child_' . $i . '_last_name'] ?? '');
            $first = trim_string($_POST['child_' . $i . '_first_name'] ?? '');
            $middle = trim_string($_POST['child_' . $i . '_middle_name'] ?? '');
            $suffix = trim_string($_POST['child_' . $i . '_suffix'] ?? '');
            $birth = trim_string($_POST['child_' . $i . '_birth'] ?? '');
            
            // If any field has value, save this child
            if (has_any_value(array($last, $first, $middle, $suffix, $birth))) {
                $depStmt->execute(array(
                    ':person_id' => $personId,
                    ':dependent_type' => 'child',
                    ':last_name' => ($last !== '') ? $last : '-',
                    ':first_name' => ($first !== '') ? $first : '-',
                    ':middle_name' => ($middle !== '') ? $middle : null,
                    ':suffix' => ($suffix !== '') ? $suffix : null,
                    ':date_of_birth' => ($birth !== '') ? $birth : null,
                    ':relationship' => null,
                ));
            }
        }
    }
    
    // Save other beneficiaries (dynamic - can have multiple)
    // Find all other indices from POST data
    $otherIndices = array();
    foreach ($_POST as $key => $value) {
        // Look for keys like "other_1_last_name", "other_2_last_name", etc.
        if (preg_match('/^other_(\d+)_last_name$/', (string)$key, $matches)) {
            $otherIndices[] = (int)$matches[1];
        }
    }
    // Remove duplicates and sort
    $otherIndices = array_values(array_unique($otherIndices));
    sort($otherIndices);
    
    // If there are other beneficiaries, save them
    if (count($otherIndices) > 0) {
        $depStmt = $pdo->prepare(
            'INSERT INTO person_dependents (person_id, dependent_type, last_name, first_name, middle_name, suffix, date_of_birth, relationship)
             VALUES (:person_id, :dependent_type, :last_name, :first_name, :middle_name, :suffix, :date_of_birth, :relationship)'
        );
        
        // Loop through each other beneficiary
        foreach ($otherIndices as $i) {
            $last = trim_string($_POST['other_' . $i . '_last_name'] ?? '');
            $first = trim_string($_POST['other_' . $i . '_first_name'] ?? '');
            $middle = trim_string($_POST['other_' . $i . '_middle_name'] ?? '');
            $suffix = trim_string($_POST['other_' . $i . '_suffix'] ?? '');
            $relationship = trim_string($_POST['other_' . $i . '_relationship'] ?? '');
            $birth = trim_string($_POST['other_' . $i . '_birth'] ?? '');
            
            // If any field has value, save this other beneficiary
            if (has_any_value(array($last, $first, $middle, $suffix, $relationship, $birth))) {
                $depStmt->execute(array(
                    ':person_id' => $personId,
                    ':dependent_type' => 'other',
                    ':last_name' => ($last !== '') ? $last : '-',
                    ':first_name' => ($first !== '') ? $first : '-',
                    ':middle_name' => ($middle !== '') ? $middle : null,
                    ':suffix' => ($suffix !== '') ? $suffix : null,
                    ':date_of_birth' => ($birth !== '') ? $birth : null,
                    ':relationship' => ($relationship !== '') ? $relationship : null,
                ));
            }
        }
    }
    
    // ===== PART 3: SELF-EMPLOYED / OFW / NWS =====
    
    // Save Self-Employed (SE) information
    $seProfession = trim_string($_POST['se_profession_business'] ?? '');
    $seYearStarted = trim_string($_POST['se_year_started'] ?? '');
    $seMonthly = trim_string($_POST['se_monthly_earnings'] ?? '');
    
    if (has_any_value(array($seProfession, $seYearStarted, $seMonthly))) {
        $stmt = $pdo->prepare(
            'INSERT INTO person_self_employment (person_id, profession_business, year_started, monthly_earnings)
             VALUES (:person_id, :profession_business, :year_started, :monthly_earnings)'
        );
        $stmt->execute(array(
            ':person_id' => $personId,
            ':profession_business' => ($seProfession !== '') ? $seProfession : null,
            ':year_started' => ($seYearStarted !== '') ? $seYearStarted : null,
            ':monthly_earnings' => ($seMonthly !== '') ? $seMonthly : null,
        ));
    }
    
    // Save Overseas Filipino Worker (OFW) information
    $ofwAddress = trim_string($_POST['ofw_foreign_address'] ?? '');
    $ofwMonthly = trim_string($_POST['ofw_monthly_earnings'] ?? '');
    $flexiFund = trim_string($_POST['flexi_fund'] ?? '');
    
    if (has_any_value(array($ofwAddress, $ofwMonthly, $flexiFund))) {
        $stmt = $pdo->prepare(
            'INSERT INTO person_ofw (person_id, foreign_address, monthly_earnings, flexi_fund)
             VALUES (:person_id, :foreign_address, :monthly_earnings, :flexi_fund)'
        );
        
        // Only allow 'yes' or 'no' for flexi_fund
        $flexiFundValue = null;
        if ($flexiFund === 'yes' || $flexiFund === 'no') {
            $flexiFundValue = $flexiFund;
        }
        
        $stmt->execute(array(
            ':person_id' => $personId,
            ':foreign_address' => ($ofwAddress !== '') ? $ofwAddress : null,
            ':monthly_earnings' => ($ofwMonthly !== '') ? $ofwMonthly : null,
            ':flexi_fund' => $flexiFundValue,
        ));
    }
    
    // Save Non-Working Spouse (NWS) information
    $nwsSS = trim_string($_POST['nws_working_spouse_ss'] ?? '');
    $nwsIncome = trim_string($_POST['nws_monthly_income'] ?? '');
    $nwsSigPath = save_uploaded_file('nws_signature_file');
    
    if (has_any_value(array($nwsSS, $nwsIncome, $nwsSigPath))) {
        $stmt = $pdo->prepare(
            'INSERT INTO person_nws (person_id, working_spouse_ss_no, working_spouse_monthly_income, working_spouse_signature_file_path)
             VALUES (:person_id, :working_spouse_ss_no, :working_spouse_monthly_income, :working_spouse_signature_file_path)'
        );
        $stmt->execute(array(
            ':person_id' => $personId,
            ':working_spouse_ss_no' => ($nwsSS !== '') ? $nwsSS : null,
            ':working_spouse_monthly_income' => ($nwsIncome !== '') ? $nwsIncome : null,
            ':working_spouse_signature_file_path' => $nwsSigPath,
        ));
    }
    
    // ===== PART 4: CERTIFICATION =====
    
    $certPrinted = trim_string($_POST['cert_printed_name'] ?? '');
    $certSignatureText = trim_string($_POST['cert_signature'] ?? '');
    $certDate = trim_string($_POST['cert_date'] ?? '');
    $certSigPath = save_uploaded_file('cert_signature_file');
    
    if (has_any_value(array($certPrinted, $certSignatureText, $certDate, $certSigPath))) {
        $stmt = $pdo->prepare(
            'INSERT INTO person_certifications (person_id, printed_name, signature_text, signature_file_path, cert_date)
             VALUES (:person_id, :printed_name, :signature_text, :signature_file_path, :cert_date)'
        );
        $stmt->execute(array(
            ':person_id' => $personId,
            ':printed_name' => ($certPrinted !== '') ? $certPrinted : null,
            ':signature_text' => ($certSignatureText !== '') ? $certSignatureText : null,
            ':signature_file_path' => $certSigPath,
            ':cert_date' => ($certDate !== '') ? $certDate : null,
        ));
    }
    
    // ===== PART 5: SSS PROCESSING =====
    
    $sssBusinessCode = trim_string($_POST['sss_business_code'] ?? '');
    $sssWorkingSpouseMsc = trim_string($_POST['sss_working_spouse_msc'] ?? '');
    $sssMonthlyContribution = trim_string($_POST['sss_monthly_contribution'] ?? '');
    $sssApprovedMsc = trim_string($_POST['sss_approved_msc'] ?? '');
    $sssStartPayment = trim_string($_POST['sss_start_of_payment'] ?? '');
    $sssFlexiStatus = trim_string($_POST['sss_flexi_status'] ?? '');
    
    // Save signature files
    $receivedSigPath = save_uploaded_file('sss_received_by_signature');
    $receivedDateTime = trim_string($_POST['sss_received_by_datetime'] ?? '');
    $processedSigPath = save_uploaded_file('sss_processed_by_signature');
    $processedDateTime = trim_string($_POST['sss_processed_by_datetime'] ?? '');
    $reviewedSigPath = save_uploaded_file('sss_reviewed_by_signature');
    $reviewedDateTime = trim_string($_POST['sss_reviewed_by_datetime'] ?? '');
    
    // If any SSS field has value, save SSS processing data
    if (has_any_value(array(
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
    ))) {
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
        
        // Convert datetime-local format (YYYY-MM-DDTHH:MM) to MySQL format (YYYY-MM-DD HH:MM:SS)
        $receivedDateTimeFormatted = null;
        if ($receivedDateTime !== '') {
            $receivedDateTimeFormatted = str_replace('T', ' ', $receivedDateTime) . ':00';
        }
        
        $processedDateTimeFormatted = null;
        if ($processedDateTime !== '') {
            $processedDateTimeFormatted = str_replace('T', ' ', $processedDateTime) . ':00';
        }
        
        $reviewedDateTimeFormatted = null;
        if ($reviewedDateTime !== '') {
            $reviewedDateTimeFormatted = str_replace('T', ' ', $reviewedDateTime) . ':00';
        }
        
        // Only allow 'approved' or 'disapproved' for flexi_status
        $flexiStatusValue = null;
        if ($sssFlexiStatus === 'approved' || $sssFlexiStatus === 'disapproved') {
            $flexiStatusValue = $sssFlexiStatus;
        }
        
        $stmt->execute(array(
            ':person_id' => $personId,
            ':business_code' => ($sssBusinessCode !== '') ? $sssBusinessCode : null,
            ':working_spouse_msc' => ($sssWorkingSpouseMsc !== '') ? $sssWorkingSpouseMsc : null,
            ':monthly_contribution' => ($sssMonthlyContribution !== '') ? $sssMonthlyContribution : null,
            ':approved_msc' => ($sssApprovedMsc !== '') ? $sssApprovedMsc : null,
            ':start_of_payment' => ($sssStartPayment !== '') ? $sssStartPayment : null,
            ':flexi_status' => $flexiStatusValue,
            ':received_by_signature_path' => $receivedSigPath,
            ':received_by_datetime' => $receivedDateTimeFormatted,
            ':processed_by_signature_path' => $processedSigPath,
            ':processed_by_datetime' => $processedDateTimeFormatted,
            ':reviewed_by_signature_path' => $reviewedSigPath,
            ':reviewed_by_datetime' => $reviewedDateTimeFormatted,
        ));
    }
    
    // If everything worked, commit the transaction
    if ($pdo->inTransaction()) {
        $pdo->commit();
    }
    
} catch (Exception $e) {
    // If something went wrong, undo all changes
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Check if we're on localhost (for debugging)
    $isLocal = false;
    if (isset($_SERVER['SERVER_NAME'])) {
        if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
            $isLocal = true;
        }
    }
    
    // Log the error
    error_log('E1PersonalRecord submit.php error: ' . $e->getMessage());
    error_log($e->getTraceAsString());
    
    // Show error page
    http_response_code(500);
    
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Server Error</title>';
    echo '<style>';
    echo 'body{font-family:Arial,sans-serif;background:#f5f6f8;color:#1f2937;padding:24px;}';
    echo '.card{max-width:900px;margin:0 auto;background:#fff;border:1px solid #dcdfe4;border-radius:6px;padding:16px;}';
    echo '.title{font-weight:700;margin-bottom:10px;}';
    echo '.btn{display:inline-block;margin-top:12px;background:#2563eb;color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none;font-weight:700;}';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    echo '<div class="card">';
    echo '<div class="title">Server Error</div>';
    echo '<div>Unable to save your form. Please try again.</div>';
    // Show debug info only on localhost
    if ($isLocal) {
        echo '<div style="margin-top:10px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;border-radius:6px;padding:10px 12px;">';
        echo '<div style="font-weight:700;margin-bottom:6px;">Debug (local only)</div>';
        echo '<div>' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '</div>';
    }
    echo '<a class="btn" href="../index.php">Back to form</a>';
    echo '</div>';
    echo '</body>';
    echo '</html>';
    exit;
}

// If we get here, everything was successful
// Show success page
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

?>
