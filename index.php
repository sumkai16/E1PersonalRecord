<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>E1 Personal Record</title>
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
        <form class="form" id="e1Form" autocomplete="on" novalidate method="post" action="submit.php" enctype="multipart/form-data">
            <div class="form-errors is-hidden" id="formErrors"></div>

            <section class="part1">
                <div class="section-title" id="personal-data">
                    <p>A. PERSONAL DATA</p>
                </div>

                <div class="form-columns">
                    <div class="left-side">
                        <div class="name">
                            <label class="form-label">Last Name</label>
                            <input class="form-input" type="text" name="last_name" placeholder="E.G. MERCADO" />

                            <label class="form-label">First Name</label>
                            <input class="form-input" type="text" name="first_name" placeholder="E.G. JOSE" />

                            <label class="form-label">Middle Name</label>
                            <input class="form-input" type="text" name="middle_name" placeholder="E.G. ALONSO" />
                        </div>

                        <div class="sex">
                            <label class="form-label">Gender</label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" id="gender_male" name="gender" value="male" />
                                    <label for="gender_male">Male</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="gender_female" name="gender" value="female" />
                                    <label for="gender_female">Female</label>
                                </div>
                            </div>
                        </div>

                        <div class="nationality">
                            <label class="form-label">Nationality</label>
                            <input class="form-input" type="text" name="nationality" placeholder="FILIPINO" />
                        </div>

                        <div class="place-of-birth">
                            <label class="form-label">Place of Birth</label>
                            <input class="form-input" type="text" name="place_of_birth" placeholder="City/Municipality, Province" />
                            <label class="form-checkbox">
                                <input type="checkbox" id="sameAsHomeAddress" name="same_as_home_address" value="1" />
                                The same with Home Address
                            </label>
                        </div>

                        <div class="mobile-number">
                            <label class="form-label">Mobile Number</label>
                            <input class="form-input" type="text" name="mobile_number" placeholder="+63" />

                            <label class="form-label">Email Address</label>
                            <input class="form-input" type="email" name="email" placeholder="email@domain.com" />
                        </div>

                        <div class="father-name">
                            <label class="form-label">Father's Name</label>
                            <div class="name-inputs">
                                <input class="form-input" type="text" name="father_last_name" placeholder="Last Name" />
                                <input class="form-input" type="text" name="father_first_name" placeholder="First Name" />
                                <input class="form-input" type="text" name="father_middle_name" placeholder="Middle Name" />
                            </div>
                        </div>
                    </div>

                    <div class="right-side">
                        <div class="date-of-birth">
                            <label class="form-label">Date of Birth</label>
                            <input class="form-input" type="date" name="date_of_birth" />
                        </div>

                        <div class="civil-status">
                            <label class="form-label">Civil Status</label>
                            <select class="form-input" name="civil_status">
                                <option value="">- SELECT CIVIL STATUS -</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="widowed">Widowed</option>
                                <option value="legally_separated">Legally Separated</option>
                                <option value="others">Others</option>
                            </select>
                            <div class="civil-status-other is-hidden" id="civilStatusOtherBlock">
                                <label class="form-label">Please specify</label>
                                <input class="form-input" type="text" name="civil_status_other" placeholder="Please specify" />
                            </div>
                        </div>

                        <div class="religion">
                            <label class="form-label">Religion</label>
                            <input class="form-input" type="text" name="religion" placeholder="Religion" />
                        </div>

                        <div class="home-address" id="homeAddressBlock">
                            <div class="home-address-col" id="homeAddressField">
                                <label class="form-label">Home Address</label>
                                <input class="form-input" type="text" name="home_address" placeholder="House/Lot No., Street, Barangay" />
                            </div>

                            <div class="home-address-col" id="zipCodeField">
                                <label class="form-label">Zip Code</label>
                                <input class="form-input" type="text" name="zip_code" placeholder="Zip Code" />
                            </div>
                        </div>

                        <div class="tel-number">
                            <label class="form-label">Telephone Number</label>
                            <input class="form-input" type="text" name="telephone_number" placeholder="Telephone Number" />
                        </div>

                        <div class="mother-name">
                            <label class="form-label">Mother's Maiden Name</label>
                            <div class="name-inputs">
                                <input class="form-input" type="text" name="mother_last_name" placeholder="Last Name" />
                                <input class="form-input" type="text" name="mother_first_name" placeholder="First Name" />
                                <input class="form-input" type="text" name="mother_middle_name" placeholder="Middle Name" />
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

                            <input class="form-input" type="text" name="spouse_last_name" placeholder="E.G. MERCADO" />
                            <input class="form-input" type="text" name="spouse_first_name" placeholder="E.G. JOSE" />
                            <input class="form-input" type="text" name="spouse_middle_name" placeholder="E.G. ALONSO" />
                            <input class="form-input" type="text" name="spouse_suffix" placeholder="JR." />
                            <input class="form-input" type="date" name="spouse_birth" />
                        </div>
                    </div>

                    <div class="dep-block">
                        <div class="dep-block-title">CHILD/REN</div>
                        <div class="dep-controls">
                            <label class="form-label" for="childrenCount">Number of Children</label>
                            <input class="form-input dep-count" id="childrenCount" type="number" min="0" max="5" value="0" />
                        </div>
                        <div class="dep-grid dep-grid-children" id="childrenGrid"></div>
                    </div>

                    <div class="dep-block">
                        <div class="dep-block-title">OTHER BENEFICIARY/IES</div>
                        <div class="dep-block-subtitle">(If without spouse & child and parents are both deceased)</div>
                        <div class="dep-controls">
                            <label class="form-label" for="otherCount">Number of Other Beneficiaries</label>
                            <input class="form-input dep-count" id="otherCount" type="number" min="0" max="2" value="0" />
                        </div>
                        <div class="dep-grid dep-grid-other" id="otherGrid"></div>
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
                            <input class="paper-input" type="text" name="se_profession_business" />
                        </div>

                        <div class="paper-field">
                            <div class="paper-label">Year Prof./Business Started</div>
                            <input class="paper-input" type="text" name="se_year_started" />
                        </div>

                        <div class="paper-field">
                            <div class="paper-label">Monthly Earnings</div>
                            <input class="paper-input" type="text" name="se_monthly_earnings" placeholder="₱" />
                        </div>
                    </div>

                    <div class="part3-col">
                        <div class="part3-col-title">OVERSEAS FILIPINO WORKER (OFW)</div>

                        <div class="paper-field">
                            <div class="paper-label">Foreign Address</div>
                            <input class="paper-input" type="text" name="ofw_foreign_address" />
                        </div>

                        <div class="paper-field">
                            <div class="paper-label">Monthly Earnings</div>
                            <input class="paper-input" type="text" name="ofw_monthly_earnings" placeholder="₱" />
                        </div>

                        <div class="paper-field">
                            <div class="paper-label">Are you applying for membership in the Flexi-Fund Program?</div>
                            <div class="paper-yesno">
                                <div class="radio-item">
                                    <input type="radio" id="flexi_fund_yes" name="flexi_fund" value="yes" />
                                    <label for="flexi_fund_yes">YES</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="flexi_fund_no" name="flexi_fund" value="no" />
                                    <label for="flexi_fund_no">NO</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="part3-col">
                        <div class="part3-col-title">NON-WORKING SPOUSE (NWS)</div>

                        <div class="paper-field">
                            <div class="paper-label">SS No./Common Reference No. of Working Spouse</div>
                            <input class="paper-input paper-ssn" type="text" name="nws_working_spouse_ss" maxlength="12" placeholder="12-3456789-0" />
                        </div>

                        <div class="paper-field">
                            <div class="paper-label">Monthly Income of Working Spouse (₱)</div>
                            <input class="paper-input" type="text" name="nws_monthly_income" placeholder="₱" />
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
                                <input class="paper-input" type="text" name="cert_printed_name" />
                                <div class="cert-caption">PRINTED NAME</div>
                            </div>

                            <div class="cert-field cert-field--offset">
                                <input class="paper-input" type="text" name="cert_signature" />
                                <div class="cert-caption">SIGNATURE</div>
                                <input class="cert-file" type="file" name="cert_signature_file" accept="image/*,.pdf" />
                            </div>

                            <div class="cert-field">
                                <input class="paper-input" type="date" name="cert_date" />
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
                                <input class="part5-input" type="text" name="sss_business_code" />
                            </div>
                            <div class="part5-cell">
                                <div class="part5-label">WORKING SPOUSE'S MSC <span class="part5-sub">(FOR NWS)</span></div>
                                <input class="part5-input" type="text" name="sss_working_spouse_msc" />
                            </div>

                            <div class="part5-cell part5-cell--divider">
                                <div class="part5-label">MONTHLY SS CONTRIBUTION<br /><span class="part5-sub">(FOR SE/OFW/NWS)</span></div>
                                <input class="part5-input" type="text" name="sss_monthly_contribution" />
                            </div>
                            <div class="part5-cell">
                                <div class="part5-label">APPROVED MSC<br /><span class="part5-sub">(FOR SE/OFW/NWS)</span></div>
                                <input class="part5-input" type="text" name="sss_approved_msc" />
                            </div>

                            <div class="part5-cell part5-cell--divider part5-cell--no-bottom">
                                <div class="part5-label">START OF PAYMENT<br /><span class="part5-sub">(FOR SE/NWS)</span></div>
                                <input class="part5-input" type="text" name="sss_start_of_payment" />
                            </div>
                            <div class="part5-cell part5-cell--no-bottom">
                                <div class="part5-label">FLEXI-FUND APPLICATION<br /><span class="part5-sub">(FOR OFW)</span></div>
                                <div class="part5-checks">
                                    <label class="part5-check"><input type="radio" name="sss_flexi_status" value="approved" /> Approved</label>
                                    <label class="part5-check"><input type="radio" name="sss_flexi_status" value="disapproved" /> Disapproved</label>
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
                                        <input class="part5-datetime" type="datetime-local" name="sss_received_by_datetime" />
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
                                        <input class="part5-datetime" type="datetime-local" name="sss_processed_by_datetime" />
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
                                        <input class="part5-datetime" type="datetime-local" name="sss_reviewed_by_datetime" />
                                        <div class="part5-line-caption">DATE &amp; TIME</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="form-actions">
                <button class="submit-btn" type="submit">Submit</button>
            </div>
        </form>
    </main>

    <script src="app.js"></script>
</body>
</html>