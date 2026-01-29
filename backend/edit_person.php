<?php

// This file displays the edit form with pre-filled data from database

// Include required files
require_once __DIR__ . '/crud_functions.php';

// Get person ID from URL
$personId = $_GET['id'] ?? null;

// Function to safely get data from a person-related table
// This prevents SQL injection by only allowing specific table names
function fetch_person_section($table, $personId)
{
    // List of allowed table names (for security)
    $allowedTables = array(
        'person_self_employment',
        'person_ofw',
        'person_nws',
        'person_certifications',
        'person_sss_processing',
    );
    
    // If table name is not in allowed list, return empty array
    if (!in_array($table, $allowedTables, true)) {
        return array();
    }
    
    // Get database connection
    $pdo = db();
    
    // Prepare SQL query (using table name safely)
    $stmt = $pdo->prepare("SELECT * FROM " . $table . " WHERE person_id = :id LIMIT 1");
    $stmt->execute(array(':id' => $personId));
    
    // Get the row
    $row = $stmt->fetch();
    
    // Return row if found, otherwise return empty array
    if ($row) {
        return $row;
    } else {
        return array();
    }
}

// Function to convert MySQL datetime format to HTML datetime-local format
// MySQL format: YYYY-MM-DD HH:MM:SS
// HTML format: YYYY-MM-DDTHH:MM
function mysql_to_datetime_local($value)
{
    // If value is empty or null, return empty string
    if ($value === null || $value === '') {
        return '';
    }
    
    // Try to parse the MySQL datetime format
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $value);
    
    // If parsing failed, return empty string
    if (!$dt) {
        return '';
    }
    
    // Convert to HTML datetime-local format
    return $dt->format('Y-m-d\TH:i');
}

// Validate person ID
if (!$personId || !is_numeric($personId)) {
    http_response_code(400);
    echo 'Invalid person ID.';
    exit;
}

// Get person data from database
$person = read_person((int)$personId);

// If person not found, show error
if (!$person) {
    http_response_code(404);
    echo 'Person not found.';
    exit;
}

// Pre-fill form data
$data = [
    'last_name' => $person['last_name'],
    'first_name' => $person['first_name'],
    'middle_name' => $person['middle_name'],
    'date_of_birth' => $person['date_of_birth'],
    'gender' => $person['sex'],
    'civil_status' => $person['civil_status_code'],
    'civil_status_other' => $person['civil_status_other'],
    'nationality' => $person['nationality'],
    'place_of_birth' => $person['place_of_birth'],
    'mobile_number' => $person['mobile_number'],
    'email' => $person['email'],
    'religion' => $person['religion'],
    'telephone_number' => $person['telephone_number'],
    'father_last_name' => $person['father_last_name'],
    'father_first_name' => $person['father_first_name'],
    'father_middle_name' => $person['father_middle_name'],
    'mother_last_name' => $person['mother_last_name'],
    'mother_first_name' => $person['mother_first_name'],
    'mother_middle_name' => $person['mother_middle_name'],
    'same_as_home_address' => $person['same_as_home_address'],
    'home_address' => $person['home_address']['address_line'] ?? '',
    'zip_code' => $person['home_address']['zip_code'] ?? '',
];

//For dependents and other sections, you can add similar pre-filling logic here.

$spouse = null;
$children = [];
$others = [];
foreach ($person['dependents'] as $dep) {
    if ($dep['dependent_type'] === 'spouse') {
        $spouse = $dep;
    } elseif ($dep['dependent_type'] === 'child') {
        $children[] = $dep;
    } elseif ($dep['dependent_type'] === 'other') {
        $others[] = $dep;
    }
}

// Part 3/4/5 sections pulled from their own tables
$se  = fetch_person_section('person_self_employment', (int)$personId);
$ofw = fetch_person_section('person_ofw', (int)$personId);
$nws = fetch_person_section('person_nws', (int)$personId);
$cert = fetch_person_section('person_certifications', (int)$personId);
$sss  = fetch_person_section('person_sss_processing', (int)$personId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <title>Edit Person</title>
</head>
<body>
    <nav>E-1</nav>

    <section class="top-section">
        <div class="container-center">
            <p>Republic of the Philippines</p>
            <h3>SOCIAL SECURITY SYSTEM</h3>
            <h2>PERSONAL RECORD</h2>
            <h3>FOR ISSUANCE OF SS NUMBER</h3>
        </div>
    </section>

    <main class="container-center">
        <form class="form" id="e1Form" autocomplete="on" novalidate method="post" action="update_person.php" enctype="multipart/form-data">
            <input type="hidden" name="person_id" value="<?php echo htmlspecialchars((string)$personId); ?>" />

            <section class="part1">
                <div class="section-title" id="personal-data">
                    <p>A. PERSONAL DATA</p>
                </div>

                <div class="form-columns">
                    <div class="left-side">
                        <div class="name">
                            <label class="form-label">Last Name</label>
                            <input class="form-input" type="text" name="last_name" value="<?php echo htmlspecialchars($data['last_name']); ?>" />

                            <label class="form-label">First Name</label>
                            <input class="form-input" type="text" name="first_name" value="<?php echo htmlspecialchars($data['first_name']); ?>" />

                            <label class="form-label">Middle Name</label>
                            <input class="form-input" type="text" name="middle_name" value="<?php echo htmlspecialchars($data['middle_name']); ?>" />
                        </div>

                        <div class="sex">
                            <label class="form-label">Gender</label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" id="gender_male" name="gender" value="male" <?php echo $data['gender'] === 'male' ? 'checked' : ''; ?> />
                                    <label for="gender_male">Male</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="gender_female" name="gender" value="female" <?php echo $data['gender'] === 'female' ? 'checked' : ''; ?> />
                                    <label for="gender_female">Female</label>
                                </div>
                            </div>
                        </div>

                        <div class="nationality">
                            <label class="form-label">Nationality</label>
                            <input class="form-input" type="text" name="nationality" value="<?php echo htmlspecialchars($data['nationality']); ?>" />
                        </div>

                        <div class="place-of-birth">
                            <label class="form-label">Place of Birth</label>
                            <input class="form-input" type="text" name="place_of_birth" value="<?php echo htmlspecialchars($data['place_of_birth']); ?>" />
                            <label class="form-checkbox">
                                <input type="checkbox" id="sameAsHomeAddress" name="same_as_home_address" value="1" <?php echo $data['same_as_home_address'] ? 'checked' : ''; ?> />
                                The same with Home Address
                            </label>
                        </div>

                        <div class="mobile-number">
                            <label class="form-label">Mobile Number</label>
                            <input class="form-input" type="text" name="mobile_number" value="<?php echo htmlspecialchars($data['mobile_number']); ?>" />

                            <label class="form-label">Email Address</label>
                            <input class="form-input" type="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>" />
                        </div>

                        <div class="father-name">
                            <label class="form-label">Father's Name</label>
                            <div class="name-inputs">
                                <input class="form-input" type="text" name="father_last_name" value="<?php echo htmlspecialchars($data['father_last_name'] ?? ''); ?>" />
                                <input class="form-input" type="text" name="father_first_name" value="<?php echo htmlspecialchars($data['father_first_name'] ?? ''); ?>" />
                                <input class="form-input" type="text" name="father_middle_name" value="<?php echo htmlspecialchars($data['father_middle_name'] ?? ''); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="right-side">
                        <div class="date-of-birth">
                            <label class="form-label">Date of Birth</label>
                            <input class="form-input" type="date" name="date_of_birth" value="<?php echo htmlspecialchars($data['date_of_birth']); ?>" />
                        </div>

                        <div class="civil-status">
                            <label class="form-label">Civil Status</label>
                            <select class="form-input" name="civil_status">
                                <option value="">- SELECT CIVIL STATUS -</option>
                                <option value="single" <?php echo $data['civil_status'] === 'single' ? 'selected' : ''; ?>>Single</option>
                                <option value="married" <?php echo $data['civil_status'] === 'married' ? 'selected' : ''; ?>>Married</option>
                                <option value="widowed" <?php echo $data['civil_status'] === 'widowed' ? 'selected' : ''; ?>>Widowed</option>
                                <option value="legally_separated" <?php echo $data['civil_status'] === 'legally_separated' ? 'selected' : ''; ?>>Legally Separated</option>
                                <option value="others" <?php echo $data['civil_status'] === 'others' ? 'selected' : ''; ?>>Others</option>
                            </select>
                            <div class="civil-status-other is-hidden" id="civilStatusOtherBlock">
                                <label class="form-label">Please specify</label>
                                <input class="form-input" type="text" name="civil_status_other" value="<?php echo htmlspecialchars($data['civil_status_other'] ?? ''); ?>" />
                            </div>
                        </div>

                        <div class="religion">
                            <label class="form-label">Religion</label>
                            <input class="form-input" type="text" name="religion" value="<?php echo htmlspecialchars($data['religion'] ?? ''); ?>" />
                        </div>

                        <div class="home-address" id="homeAddressBlock">
                            <div class="home-address-col" id="homeAddressField">
                                <label class="form-label">Home Address</label>
                                <input class="form-input" type="text" name="home_address" value="<?php echo htmlspecialchars($data['home_address']); ?>" />
                            </div>

                            <div class="home-address-col" id="zipCodeField">
                                <label class="form-label">Zip Code</label>
                                <input class="form-input" type="text" name="zip_code" value="<?php echo htmlspecialchars($data['zip_code']); ?>" />
                            </div>
                        </div>

                        <div class="tel-number">
                            <label class="form-label">Telephone Number</label>
                            <input class="form-input" type="text" name="telephone_number" value="<?php echo htmlspecialchars($data['telephone_number'] ?? ''); ?>" />
                        </div>

                        <div class="mother-name">
                            <label class="form-label">Mother's Maiden Name</label>
                            <div class="name-inputs">
                                <input class="form-input" type="text" name="mother_last_name" value="<?php echo htmlspecialchars($data['mother_last_name'] ?? ''); ?>" />
                                <input class="form-input" type="text" name="mother_first_name" value="<?php echo htmlspecialchars($data['mother_first_name'] ?? ''); ?>" />
                                <input class="form-input" type="text" name="mother_middle_name" value="<?php echo htmlspecialchars($data['mother_middle_name'] ?? ''); ?>" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

           <section class="part2">
                <div class="section-title" id="dependent-data">
                    <p>B. DEPENDENT(S)/BENEFICIARY/IES</p>
                </div>

                <div class="dep-card">
                    <div class="dep-block">
                        <div class="dep-block-title">SPOUSE</div>
                        <div class="dep-grid dep-grid-spouse">
                        <div class="dep-head">Last Name</div>
                        <div class="dep-head">First Name</div>
                        <div class="dep-head">Middle Name</div>
                        <div class="dep-head">Suffix</div>
                        <div class="dep-head">Date of Birth</div>

                            <input class="form-input" type="text" name="spouse_last_name" placeholder="E.G. MERCADO" value="<?php echo htmlspecialchars($spouse['last_name'] ?? ''); ?>" />
                            <input class="form-input" type="text" name="spouse_first_name" placeholder="E.G. JOSE" value="<?php echo htmlspecialchars($spouse['first_name'] ?? ''); ?>" />
                            <input class="form-input" type="text" name="spouse_middle_name" placeholder="E.G. ALONSO" value="<?php echo htmlspecialchars($spouse['middle_name'] ?? ''); ?>" />
                            <input class="form-input" type="text" name="spouse_suffix" placeholder="JR." value="<?php echo htmlspecialchars($spouse['suffix'] ?? ''); ?>" />
                            <input class="form-input" type="date" name="spouse_birth" value="<?php echo htmlspecialchars($spouse['date_of_birth'] ?? ''); ?>" />
                        </div>

                    </div>

                    <div class="dep-block">
                        <div class="dep-block-title">CHILD/REN</div>
                        <div class="dep-controls">
                            <label class="form-label" for="childrenCount">Number of Children</label>
                            <input class="form-input dep-count" id="childrenCount" type="number" min="0" max="5" value="<?php echo count($children); ?>" />
                        </div>
                        <div class="dep-grid dep-grid-children" id="childrenGrid">
                            <?php foreach ($children as $index => $child): ?>
                                <div class="dep-head">Last Name</div>
                                <div class="dep-head">First Name</div>
                                <div class="dep-head">Middle Name</div>
                                <div class="dep-head">Suffix</div>
                                <div class="dep-head">Date of Birth</div>

                                <input class="form-input" type="text" name="child_<?php echo $index + 1; ?>_last_name" value="<?php echo htmlspecialchars($child['last_name']); ?>" />
                                <input class="form-input" type="text" name="child_<?php echo $index + 1; ?>_first_name" value="<?php echo htmlspecialchars($child['first_name']); ?>" />
                                <input class="form-input" type="text" name="child_<?php echo $index + 1; ?>_middle_name" value="<?php echo htmlspecialchars($child['middle_name'] ?? ''); ?>" />
                                <input class="form-input" type="text" name="child_<?php echo $index + 1; ?>_suffix" value="<?php echo htmlspecialchars($child['suffix'] ?? ''); ?>" />
                                <input class="form-input" type="date" name="child_<?php echo $index + 1; ?>_birth" value="<?php echo htmlspecialchars($child['date_of_birth'] ?? ''); ?>" />
                            <?php endforeach; ?>
                        </div>


                    <div class="dep-block">
                        <div class="dep-block-title">OTHER BENEFICIARY/IES</div>
                        <div class="dep-block-subtitle">(If without spouse & child and parents are both deceased)</div>
                        <div class="dep-controls">
                            <label class="form-label" for="otherCount">Number of Other Beneficiaries</label>
                            <input class="form-input dep-count" id="otherCount" type="number" min="0" max="2" value="<?php echo count($others); ?>" />
                        </div>
                        <div class="dep-grid dep-grid-other" id="otherGrid">
                            <?php foreach ($others as $index => $other): ?>
                                <div class="dep-head">Last Name</div>
                                <div class="dep-head">First Name</div>
                                <div class="dep-head">Middle Name</div>
                                <div class="dep-head">Suffix</div>
                                <div class="dep-head">Relationship</div>
                                <div class="dep-head">Date of Birth</div>

                                <input class="form-input" type="text" name="other_<?php echo $index + 1; ?>_last_name" value="<?php echo htmlspecialchars($other['last_name']); ?>" />
                                <input class="form-input" type="text" name="other_<?php echo $index + 1; ?>_first_name" value="<?php echo htmlspecialchars($other['first_name']); ?>" />
                                <input class="form-input" type="text" name="other_<?php echo $index + 1; ?>_middle_name" value="<?php echo htmlspecialchars($other['middle_name'] ?? ''); ?>" />
                                <input class="form-input" type="text" name="other_<?php echo $index + 1; ?>_suffix" value="<?php echo htmlspecialchars($other['suffix'] ?? ''); ?>" />
                                <input class="form-input" type="text" name="other_<?php echo $index + 1; ?>_relationship" value="<?php echo htmlspecialchars($other['relationship'] ?? ''); ?>" />
                                <input class="form-input" type="date" name="other_<?php echo $index + 1; ?>_birth" value="<?php echo htmlspecialchars($other['date_of_birth'] ?? ''); ?>" />
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>
            </section>

            <section class="part3">
                <div class="section-title" id="self-employed-data">
                    <p>C. FOR SELF-EMPLOYED/OVERSEAS FILIPINO WORKER/NON-WORKING SPOUSE</p>
                </div>
                <div class="paper-table part3-table">
                    <div class="part3-col">
                        <div class="part3-col-title">SELF-EMPLOYED (SE)</div>

                        <div class="paper-field">
                            <div class="paper-label">Profession/Business</div>
                            <input class="paper-input" type="text" name="se_profession_business" value="<?php echo htmlspecialchars($se['profession_business'] ?? ''); ?>" />
                        </div>

                        <div class="paper-field">
                            <div class="paper-label">Year Prof./Business Started</div>
                            <input class="paper-input" type="text" name="se_year_started" value="<?php echo htmlspecialchars($se['year_started'] ?? ''); ?>" />
                        </div>

                        <div class="paper-field">
                            <div class="paper-label">Monthly Earnings</div>
                            <input class="paper-input" type="text" name="se_monthly_earnings" placeholder="₱" value="<?php echo htmlspecialchars($se['monthly_earnings'] ?? ''); ?>" />
                        </div>
                    </div>

                    <div class="part3-col">
                        <div class="part3-col-title">OVERSEAS FILIPINO WORKER (OFW)</div>

                        <div class="paper-field">
                            <div class="paper-label">Foreign Address</div>
                            <input class="paper-input" type="text" name="ofw_foreign_address" value="<?php echo htmlspecialchars($ofw['foreign_address'] ?? ''); ?>" />
                        </div>

                        <div class="paper-field">
                            <div class="paper-label">Monthly Earnings</div>
                            <input class="paper-input" type="text" name="ofw_monthly_earnings" placeholder="₱" value="<?php echo htmlspecialchars($ofw['monthly_earnings'] ?? ''); ?>" />
                        </div>

                        <div class="paper-field">
                            <div class="paper-label">Are you applying for membership in the Flexi-Fund Program?</div>
                            <div class="paper-yesno">
                                <div class="radio-item">
                                    <input type="radio" id="flexi_fund_yes" name="flexi_fund" value="yes" <?php echo (($ofw['flexi_fund'] ?? '') === 'yes') ? 'checked' : ''; ?> />
                                    <label for="flexi_fund_yes">YES</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="flexi_fund_no" name="flexi_fund" value="no" <?php echo (($ofw['flexi_fund'] ?? '') === 'no') ? 'checked' : ''; ?> />
                                    <label for="flexi_fund_no">NO</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="part3-col">
                        <div class="part3-col-title">NON-WORKING SPOUSE (NWS)</div>

                        <div class="paper-field">
                            <div class="paper-label">SS No./Common Reference No. of Working Spouse</div>
                            <input class="paper-input paper-ssn" type="text" name="nws_working_spouse_ss" maxlength="12" placeholder="12-3456789-0" value="<?php echo htmlspecialchars($nws['working_spouse_ss_no'] ?? ''); ?>" />
                        </div>

                        <div class="paper-field">
                            <div class="paper-label">Monthly Income of Working Spouse (₱)</div>
                            <input class="paper-input" type="text" name="nws_monthly_income" placeholder="₱" value="<?php echo htmlspecialchars($nws['working_spouse_monthly_income'] ?? ''); ?>" />
                        </div>

                        <div class="paper-field">
                            <div class="paper-label">I agree with my spouse's membership with SSS.</div>
                            
                        </div>

                        <div class="paper-field">
                            <div class="paper-label">SIGNATURE OVER PRINTED NAME OF WORKING SPOUSE</div>
                            <input class="paper-input paper-file-input" id="nws_signature_file" type="file" name="nws_signature_file" accept="image/*,.pdf" />
                            <div class="dropzone" id="nws_signature_dropzone" role="button" tabindex="0" aria-describedby="nws_signature_help">
                                <div class="dropzone-inner">
                                    <div class="dropzone-title">Choose a file or drag & drop it here</div>
                                    <div class="dropzone-subtitle" id="nws_signature_help">JPEG, PNG, PDF up to 50MB</div>
                                    <button class="dropzone-btn" type="button" data-dropzone-browse>Browse File</button>
                                    <div class="dropzone-filename" data-dropzone-filename>No file selected</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="part4">
                <div class="section-title" id="certification">
                    <p>D. CERTIFICATION</p>
                </div>

                <div class="paper-table cert-table">
                    <div class="cert-left">
                        <div class="cert-text">
                            <div>I certify that the information provided in this form are true and correct.</div>
                            <div class="cert-note">(If registrant cannot sign, affix fingerprints in the presence of an SSS personnel.)</div>
                        </div>

                        <div class="cert-fields">
                            <div class="cert-field">
                                <input class="paper-input" type="text" name="cert_printed_name" value="<?php echo htmlspecialchars($cert['printed_name'] ?? ''); ?>" />
                                <div class="cert-caption">PRINTED NAME</div>
                            </div>

                            <div class="cert-field cert-field--offset">
                                <input class="paper-input" type="text" name="cert_signature" value="<?php echo htmlspecialchars($cert['signature_text'] ?? ''); ?>" />
                                <div class="cert-caption">SIGNATURE</div>
                                <input class="cert-file" type="file" name="cert_signature_file" accept="image/*,.pdf" />
                            </div>

                            <div class="cert-field">
                                <input class="paper-input" type="date" name="cert_date" value="<?php echo htmlspecialchars($cert['cert_date'] ?? ''); ?>" />
                                <div class="cert-caption">DATE</div>
                            </div>
                        </div>
                    </div>

                    <div class="cert-right">
                        <div class="cert-fp-title">Registrant is required to affix fingerprints.</div>
                        <div class="cert-fp-grid">
                            <div class="finger-box">
                                <div class="finger-blank"></div>
                                <div class="finger-label">RIGHT THUMB</div>
                            </div>
                            <div class="finger-box">
                                <div class="finger-blank"></div>
                                <div class="finger-label">RIGHT INDEX</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="part5">
                <div class="paper-table part5-table">
                    <div class="part5-header">PART II - TO BE FILLED OUT BY SSS</div>

                    <div class="part5-grid">
                        <div class="part5-left">
                            <div class="part5-cell part5-cell--divider">
                                <div class="part5-label">BUSINESS CODE<br /><span class="part5-sub">(FOR SE)</span></div>
                                <input class="part5-input" type="text" name="sss_business_code" value="<?php echo htmlspecialchars($sss['business_code'] ?? ''); ?>" />
                            </div>
                            <div class="part5-cell">
                                <div class="part5-label">WORKING SPOUSE'S MSC <span class="part5-sub">(FOR NWS)</span></div>
                                <input class="part5-input" type="text" name="sss_working_spouse_msc" value="<?php echo htmlspecialchars($sss['working_spouse_msc'] ?? ''); ?>" />
                            </div>

                            <div class="part5-cell part5-cell--divider">
                                <div class="part5-label">MONTHLY SS CONTRIBUTION<br /><span class="part5-sub">(FOR SE/OFW/NWS)</span></div>
                                <input class="part5-input" type="text" name="sss_monthly_contribution" value="<?php echo htmlspecialchars($sss['monthly_contribution'] ?? ''); ?>" />
                            </div>
                            <div class="part5-cell">
                                <div class="part5-label">APPROVED MSC<br /><span class="part5-sub">(FOR SE/OFW/NWS)</span></div>
                                <input class="part5-input" type="text" name="sss_approved_msc" value="<?php echo htmlspecialchars($sss['approved_msc'] ?? ''); ?>" />
                            </div>

                            <div class="part5-cell part5-cell--divider part5-cell--no-bottom">
                                <div class="part5-label">START OF PAYMENT<br /><span class="part5-sub">(FOR SE/NWS)</span></div>
                                <input class="part5-input" type="text" name="sss_start_of_payment" value="<?php echo htmlspecialchars($sss['start_of_payment'] ?? ''); ?>" />
                            </div>
                            <div class="part5-cell part5-cell--no-bottom">
                                <div class="part5-label">FLEXI-FUND APPLICATION<br /><span class="part5-sub">(FOR OFW)</span></div>
                                <div class="part5-checks">
                                    <label class="part5-check"><input type="radio" name="sss_flexi_status" value="approved" <?php echo (($sss['flexi_status'] ?? '') === 'approved') ? 'checked' : ''; ?> /> Approved</label>
                                    <label class="part5-check"><input type="radio" name="sss_flexi_status" value="disapproved" <?php echo (($sss['flexi_status'] ?? '') === 'disapproved') ? 'checked' : ''; ?> /> Disapproved</label>
                                </div>
                            </div>
                        </div>

                        <div class="part5-right">
                            <div class="part5-right-row">
                                <div class="part5-right-title">RECEIVED BY<br /><span class="part5-sub">(REPRESENTATIVE OFFICE/PARTNER AGENT)</span></div>
                                <div class="part5-sign-row">
                                    <div class="part5-sign-col">
                                        <input class="part5-file" type="file" name="sss_received_by_signature" accept="image/*,.pdf" />
                                        <div class="part5-line-caption">SIGNATURE OVER PRINTED NAME</div>
                                    </div>
                                    <div class="part5-sign-col part5-sign-col--date">
                                        <input class="part5-datetime" type="datetime-local" name="sss_received_by_datetime" value="<?php echo htmlspecialchars(mysql_to_datetime_local($sss['received_by_datetime'] ?? '')); ?>" />
                                        <div class="part5-line-caption">DATE &amp; TIME</div>
                                    </div>
                                </div>
                            </div>

                            <div class="part5-right-row">
                                <div class="part5-right-title">RECEIVED &amp; PROCESSED BY<br /><span class="part5-sub">(MSS, BRANCH/SERVICEOFFICE/FOREIGN OFFICE)</span></div>
                                <div class="part5-sign-row">
                                    <div class="part5-sign-col">
                                        <input class="part5-file" type="file" name="sss_processed_by_signature" accept="image/*,.pdf" />
                                        <div class="part5-line-caption">SIGNATURE OVER PRINTED NAME</div>
                                    </div>
                                    <div class="part5-sign-col part5-sign-col--date">
                                        <input class="part5-datetime" type="datetime-local" name="sss_processed_by_datetime" value="<?php echo htmlspecialchars(mysql_to_datetime_local($sss['processed_by_datetime'] ?? '')); ?>" />
                                        <div class="part5-line-caption">DATE &amp; TIME</div>
                                    </div>
                                </div>
                            </div>

                            <div class="part5-right-row part5-right-row--last">
                                <div class="part5-right-title">REVIEWED BY<br /><span class="part5-sub">(MSS, BRANCH/SERVICE OFFICE)</span></div>
                                <div class="part5-sign-row">
                                    <div class="part5-sign-col">
                                        <input class="part5-file" type="file" name="sss_reviewed_by_signature" accept="image/*,.pdf" />
                                        <div class="part5-line-caption">SIGNATURE OVER PRINTED NAME</div>
                                    </div>
                                    <div class="part5-sign-col part5-sign-col--date">
                                        <input class="part5-datetime" type="datetime-local" name="sss_reviewed_by_datetime" value="<?php echo htmlspecialchars(mysql_to_datetime_local($sss['reviewed_by_datetime'] ?? '')); ?>" />
                                        <div class="part5-line-caption">DATE &amp; TIME</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="form-actions">
                <button class="submit-btn" type="submit">Update</button>
                <a href="view_persons.php">Cancel</a>
            </div>
        </form>
    </main>

    <script src="../app.js"></script>
</body>
</html>
