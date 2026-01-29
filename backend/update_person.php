<?php

// This file handles updating person records
// It validates the form, updates data in database, and handles file uploads

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

// Get person ID from form
$personId = $_POST['person_id'] ?? null;
if (!$personId || !is_numeric($personId)) {
    http_response_code(400);
    echo 'Invalid person ID.';
    exit;
}

// Validate the form data
$result = validate_e1_form($_POST);

// If validation failed, show errors
if (!$result['ok']) {
    http_response_code(400);
    echo '<!DOCTYPE html><html><body>';
    echo '<h1>Validation Errors</h1><ul>';
    foreach ($result['errors'] as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul><a href="edit_person.php?id=' . htmlspecialchars((string)$personId) . '">Back</a>';
    echo '</body></html>';
    exit;
}

// Get cleaned form data
$data = $result['data'];

// Try to update data in database
try {
    // Get database connection
    $pdo = db();
    // Start transaction (all or nothing)
    $pdo->beginTransaction();
    
    // Update person record (this also updates home address)
    update_person((int)$personId, $data);
    
    // ===== PART 2: DEPENDENTS =====
    // Delete old dependents first (they will be re-inserted)
    $deleteDepsStmt = $pdo->prepare('DELETE FROM person_dependents WHERE person_id = :id');
    $deleteDepsStmt->execute(array(':id' => $personId));
    
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
    // Delete old records first (they will be re-inserted if needed)
    
    // Delete Self-Employed
    $deleteSeStmt = $pdo->prepare('DELETE FROM person_self_employment WHERE person_id = :id');
    $deleteSeStmt->execute(array(':id' => $personId));
    
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
    
    // Delete OFW
    $deleteOfwStmt = $pdo->prepare('DELETE FROM person_ofw WHERE person_id = :id');
    $deleteOfwStmt->execute(array(':id' => $personId));
    
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
    
    // Delete NWS
    $deleteNwsStmt = $pdo->prepare('DELETE FROM person_nws WHERE person_id = :id');
    $deleteNwsStmt->execute(array(':id' => $personId));
    
    // Save Non-Working Spouse (NWS) information
    $nwsSS = trim_string($_POST['nws_working_spouse_ss'] ?? '');
    $nwsIncome = trim_string($_POST['nws_monthly_income'] ?? '');
    $nwsSigPath = save_uploaded_file('nws_signature_file');
    
    // If file was uploaded, use new path; otherwise keep existing path
    if ($nwsSigPath === null) {
        // Check if there's existing file path in database
        $checkNwsStmt = $pdo->prepare('SELECT working_spouse_signature_file_path FROM person_nws WHERE person_id = :id LIMIT 1');
        $checkNwsStmt->execute(array(':id' => $personId));
        $existingNws = $checkNwsStmt->fetch();
        if ($existingNws && $existingNws['working_spouse_signature_file_path']) {
            $nwsSigPath = $existingNws['working_spouse_signature_file_path'];
        }
    }
    
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
    // Delete old certification
    $deleteCertStmt = $pdo->prepare('DELETE FROM person_certifications WHERE person_id = :id');
    $deleteCertStmt->execute(array(':id' => $personId));
    
    $certPrinted = trim_string($_POST['cert_printed_name'] ?? '');
    $certSignatureText = trim_string($_POST['cert_signature'] ?? '');
    $certDate = trim_string($_POST['cert_date'] ?? '');
    $certSigPath = save_uploaded_file('cert_signature_file');
    
    // If file was uploaded, use new path; otherwise keep existing path
    if ($certSigPath === null) {
        // Check if there's existing file path in database
        $checkCertStmt = $pdo->prepare('SELECT signature_file_path FROM person_certifications WHERE person_id = :id LIMIT 1');
        $checkCertStmt->execute(array(':id' => $personId));
        $existingCert = $checkCertStmt->fetch();
        if ($existingCert && $existingCert['signature_file_path']) {
            $certSigPath = $existingCert['signature_file_path'];
        }
    }
    
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
    // Delete old SSS processing
    $deleteSssStmt = $pdo->prepare('DELETE FROM person_sss_processing WHERE person_id = :id');
    $deleteSssStmt->execute(array(':id' => $personId));
    
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
    
    // If files were not uploaded, keep existing paths
    if ($receivedSigPath === null) {
        $checkSssStmt = $pdo->prepare('SELECT received_by_signature_path FROM person_sss_processing WHERE person_id = :id LIMIT 1');
        $checkSssStmt->execute(array(':id' => $personId));
        $existingSss = $checkSssStmt->fetch();
        if ($existingSss && $existingSss['received_by_signature_path']) {
            $receivedSigPath = $existingSss['received_by_signature_path'];
        }
    }
    
    if ($processedSigPath === null) {
        $checkSssStmt = $pdo->prepare('SELECT processed_by_signature_path FROM person_sss_processing WHERE person_id = :id LIMIT 1');
        $checkSssStmt->execute(array(':id' => $personId));
        $existingSss = $checkSssStmt->fetch();
        if ($existingSss && $existingSss['processed_by_signature_path']) {
            $processedSigPath = $existingSss['processed_by_signature_path'];
        }
    }
    
    if ($reviewedSigPath === null) {
        $checkSssStmt = $pdo->prepare('SELECT reviewed_by_signature_path FROM person_sss_processing WHERE person_id = :id LIMIT 1');
        $checkSssStmt->execute(array(':id' => $personId));
        $existingSss = $checkSssStmt->fetch();
        if ($existingSss && $existingSss['reviewed_by_signature_path']) {
            $reviewedSigPath = $existingSss['reviewed_by_signature_path'];
        }
    }
    
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
    
    // Redirect to view persons page
    header('Location: view_persons.php?updated=1');
    exit;
    
} catch (Exception $e) {
    // If something went wrong, undo all changes
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Show error message
    http_response_code(500);
    echo 'Error updating person: ' . htmlspecialchars($e->getMessage());
    exit;
}

?>
