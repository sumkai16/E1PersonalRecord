// This file contains JavaScript code for the form
// It handles form validation, dynamic fields, and file uploads

// Helper function to get element by ID (shorter way to write document.getElementById)
function getById(id) {
    return document.getElementById(id);
}

// Function to clear all content inside an element
function clearElement(el) {
    // If element doesn't exist, do nothing
    if (!el) {
        return;
    }
    // Clear the inner HTML
    el.innerHTML = '';
}

// Function to create a div element with a class and text
function createDiv(className, text) {
    // Create a new div element
    var el = document.createElement('div');
    // Set the class name
    el.className = className;
    // Set the text content
    el.textContent = text;
    // Return the element
    return el;
}

// Function to create an input element
function createInput(className, type, name, placeholder) {
    // Create a new input element
    var el = document.createElement('input');
    // Set the class name
    el.className = className;
    // Set the input type (text, date, etc.)
    el.type = type;
    // Set the name attribute (used when form is submitted)
    el.name = name;
    // Set placeholder if provided
    if (placeholder) {
        el.placeholder = placeholder;
    }
    // Return the element
    return el;
}

// Function to add header cells to a grid
function appendHeaderCells(grid, headers) {
    // Loop through each header
    for (var i = 0; i < headers.length; i++) {
        // Create a div for each header and add it to the grid
        grid.appendChild(createDiv('dep-head', headers[i]));
    }
}

// Function to add a child row to the grid
function appendChildRow(grid, index) {
    // Add index number
    grid.appendChild(createDiv('dep-index', String(index) + '.'));
    // Add input fields for child information
    grid.appendChild(createInput('form-input', 'text', 'child_' + index + '_last_name', 'Last Name'));
    grid.appendChild(createInput('form-input', 'text', 'child_' + index + '_first_name', 'First Name'));
    grid.appendChild(createInput('form-input', 'text', 'child_' + index + '_middle_name', 'Middle Name'));
    grid.appendChild(createInput('form-input', 'text', 'child_' + index + '_suffix', 'Suffix'));
    grid.appendChild(createInput('form-input', 'date', 'child_' + index + '_birth', ''));
}

// Function to add an "other beneficiary" row to the grid
function appendOtherRow(grid, index) {
    // Add index number
    grid.appendChild(createDiv('dep-index', String(index) + '.'));
    // Add input fields for other beneficiary information
    grid.appendChild(createInput('form-input', 'text', 'other_' + index + '_last_name', 'Last Name'));
    grid.appendChild(createInput('form-input', 'text', 'other_' + index + '_first_name', 'First Name'));
    grid.appendChild(createInput('form-input', 'text', 'other_' + index + '_middle_name', 'Middle Name'));
    grid.appendChild(createInput('form-input', 'text', 'other_' + index + '_suffix', 'Suffix'));
    grid.appendChild(createInput('form-input', 'text', 'other_' + index + '_relationship', 'Relationship'));
    grid.appendChild(createInput('form-input', 'date', 'other_' + index + '_birth', ''));
}

// Function to build children rows based on count
function buildChildrenRows(count) {
    // Get the grid element
    var grid = getById('childrenGrid');
    // If grid doesn't exist, do nothing
    if (!grid) {
        return;
    }
    
    // Clear the grid first
    clearElement(grid);
    
    // If count is 0 or empty, do nothing
    if (!count) {
        return;
    }
    
    // Add header row
    appendHeaderCells(grid, ['#', 'Last Name', 'First Name', 'Middle Name', 'Suffix', 'Date of Birth']);
    
    // Add rows for each child
    for (var i = 1; i <= count; i++) {
        appendChildRow(grid, i);
    }
}

// Function to build other beneficiary rows based on count
function buildOtherRows(count) {
    // Get the grid element
    var grid = getById('otherGrid');
    // If grid doesn't exist, do nothing
    if (!grid) {
        return;
    }
    
    // Clear the grid first
    clearElement(grid);
    
    // If count is 0 or empty, do nothing
    if (!count) {
        return;
    }
    
    // Add header row
    appendHeaderCells(grid, ['#', 'Last Name', 'First Name', 'Middle Name', 'Suffix', 'Relationship', 'Date of Birth']);
    
    // Add rows for each other beneficiary
    for (var i = 1; i <= count; i++) {
        appendOtherRow(grid, i);
    }
}

// Function to make sure number input stays within min and max
function clampNumberInput(el) {
    // Get min value (default to 0)
    var min = Number(el.min || 0);
    // Get max value (default to 0, which means no max)
    var max = Number(el.max || 0);
    // Get current value (default to 0)
    var value = Number(el.value || 0);
    
    // If value is not a number, set to 0
    if (Number.isNaN(value)) {
        value = 0;
    }
    
    // If value is less than min, set to min
    if (value < min) {
        value = min;
    }
    
    // If max is set and value is greater than max, set to max
    if (max && value > max) {
        value = max;
    }
    
    // Update the input value
    el.value = String(value);
    
    // Return the clamped value
    return value;
}

// Function to update the filename display in dropzone
function setDropzoneFilename(fileInput, filenameEl) {
    // If filename element doesn't exist, do nothing
    if (!filenameEl) {
        return;
    }
    
    // Get the selected file
    var file = null;
    if (fileInput.files && fileInput.files[0]) {
        file = fileInput.files[0];
    }
    
    // Update the display text
    if (file) {
        filenameEl.textContent = file.name;
    } else {
        filenameEl.textContent = 'No file selected';
    }
}

// Function to set up file dropzone functionality
function setupDropzone(fileInput, dropzoneEl) {
    // Get the browse button and filename display elements
    var browseBtn = dropzoneEl.querySelector('[data-dropzone-browse]');
    var filenameEl = dropzoneEl.querySelector('[data-dropzone-filename]');
    
    // Function to open file picker
    function openPicker() {
        fileInput.click();
    }
    
    // Function to handle browse button click
    function onBrowseClick() {
        openPicker();
    }
    
    // Function to handle dropzone click
    function onDropzoneClick(e) {
        // Don't open picker if clicking the browse button (it has its own handler)
        if (e.target === browseBtn) {
            return;
        }
        openPicker();
    }
    
    // Function to handle keyboard (Enter or Space key)
    function onDropzoneKeydown(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openPicker();
        }
    }
    
    // Function to handle file input change
    function onInputChange() {
        setDropzoneFilename(fileInput, filenameEl);
    }
    
    // Function to handle drag over (file being dragged over dropzone)
    function onDragOver(e) {
        e.preventDefault();
        dropzoneEl.classList.add('is-dragover');
    }
    
    // Function to handle drag leave (file being dragged away)
    function onDragLeave() {
        dropzoneEl.classList.remove('is-dragover');
    }
    
    // Function to handle drop (file dropped on dropzone)
    function onDrop(e) {
        e.preventDefault();
        dropzoneEl.classList.remove('is-dragover');
        
        // Get the dropped files
        var files = null;
        if (e.dataTransfer && e.dataTransfer.files) {
            files = e.dataTransfer.files;
        }
        
        // If files were dropped, set them to the input
        if (files && files.length) {
            fileInput.files = files;
            setDropzoneFilename(fileInput, filenameEl);
        }
    }
    
    // Add event listeners
    if (browseBtn) {
        browseBtn.addEventListener('click', onBrowseClick);
    }
    dropzoneEl.addEventListener('click', onDropzoneClick);
    dropzoneEl.addEventListener('keydown', onDropzoneKeydown);
    fileInput.addEventListener('change', onInputChange);
    dropzoneEl.addEventListener('dragover', onDragOver);
    dropzoneEl.addEventListener('dragleave', onDragLeave);
    dropzoneEl.addEventListener('drop', onDrop);
    
    // Set initial filename display
    setDropzoneFilename(fileInput, filenameEl);
}

// Function to initialize dependent grids (children and others)
function initDependentGrids() {
    // Get the count input elements
    var childrenCount = getById('childrenCount');
    var otherCount = getById('otherCount');
    
    // Set up children count input
    if (childrenCount) {
        // When user types in the input, rebuild the grid
        childrenCount.addEventListener('input', function () {
            buildChildrenRows(clampNumberInput(childrenCount));
        });
        // Build initial grid
        buildChildrenRows(clampNumberInput(childrenCount));
    }
    
    // Set up other count input
    if (otherCount) {
        // When user types in the input, rebuild the grid
        otherCount.addEventListener('input', function () {
            buildOtherRows(clampNumberInput(otherCount));
        });
        // Build initial grid
        buildOtherRows(clampNumberInput(otherCount));
    }
}

// Function to initialize signature dropzone
function initNwsSignatureDropzone() {
    // Get the file input and dropzone elements
    var signatureInput = getById('nws_signature_file');
    var signatureDropzone = getById('nws_signature_dropzone');
    
    // If elements don't exist, do nothing
    if (!signatureInput || !signatureDropzone) {
        return;
    }
    
    // Set up the dropzone
    setupDropzone(signatureInput, signatureDropzone);
}

// Function to get input element by name attribute
function getInputByName(form, name) {
    // If form doesn't exist, return null
    if (!form) {
        return null;
    }
    // Find input with matching name attribute
    return form.querySelector('[name="' + name + '"]');
}

// Function to get value of checked radio button
function getCheckedRadioValue(form, name) {
    // If form doesn't exist, return empty string
    if (!form) {
        return '';
    }
    // Find checked radio button with matching name
    var checked = form.querySelector('input[type="radio"][name="' + name + '"]:checked');
    // Return value if found, otherwise return empty string
    if (checked) {
        return checked.value;
    } else {
        return '';
    }
}

// Function to trim whitespace from value
function trimValue(value) {
    // Convert to string, handle null/undefined, then trim
    var str = String(value || '');
    return str.trim();
}

// Function to clear all validation errors from the form
function clearValidationUI(form) {
    // If form doesn't exist, do nothing
    if (!form) {
        return;
    }
    
    // Hide error box
    var errorBox = getById('formErrors');
    if (errorBox) {
        errorBox.classList.add('is-hidden');
        errorBox.innerHTML = '';
    }
    
    // Remove error class from all inputs
    var errorInputs = form.querySelectorAll('.input-error');
    for (var i = 0; i < errorInputs.length; i++) {
        errorInputs[i].classList.remove('input-error');
    }
}

// Function to display error messages
function showErrors(errors) {
    // Get error box element
    var errorBox = getById('formErrors');
    // If doesn't exist, do nothing
    if (!errorBox) {
        return;
    }
    
    // Clear previous errors
    errorBox.innerHTML = '';
    
    // Add each error message
    for (var i = 0; i < errors.length; i++) {
        errorBox.appendChild(createDiv('', errors[i]));
    }
    
    // Show or hide error box based on whether there are errors
    if (errors.length > 0) {
        errorBox.classList.remove('is-hidden');
    } else {
        errorBox.classList.add('is-hidden');
    }
}

// Function to mark an input as having an error
function markInputError(inputEl) {
    // If input doesn't exist, do nothing
    if (!inputEl) {
        return;
    }
    // Add error class
    inputEl.classList.add('input-error');
}

// Function to check if email is valid
function isEmailValid(email) {
    // Trim the email
    var value = trimValue(email);
    // If empty, not valid
    if (!value) {
        return false;
    }
    
    // Check if email has @ symbol and dot in correct positions
    var atIndex = value.indexOf('@');
    var dotIndex = value.lastIndexOf('.');
    
    // @ must be after first character
    // . must be after @
    // . must be before last character
    if (atIndex > 0 && dotIndex > atIndex + 1 && dotIndex < value.length - 1) {
        return true;
    } else {
        return false;
    }
}

// Function to validate the entire form
function validateForm(form) {
    // Array to store error messages
    var errors = [];
    
    // Get all form inputs
    var lastName = getInputByName(form, 'last_name');
    var firstName = getInputByName(form, 'first_name');
    var middleName = getInputByName(form, 'middle_name');
    var dob = getInputByName(form, 'date_of_birth');
    var nationality = getInputByName(form, 'nationality');
    var placeOfBirth = getInputByName(form, 'place_of_birth');
    var mobile = getInputByName(form, 'mobile_number');
    var email = getInputByName(form, 'email');
    var civilStatus = getInputByName(form, 'civil_status');
    var civilStatusOther = getInputByName(form, 'civil_status_other');
    var homeAddress = getInputByName(form, 'home_address');
    
    // Check if "same as home address" checkbox is checked
    var sameAsHome = getById('sameAsHomeAddress');
    var isSameAsHomeChecked = false;
    if (sameAsHome && sameAsHome.checked) {
        isSameAsHomeChecked = true;
    }
    
    // Validate last name
    if (!trimValue(lastName && lastName.value)) {
        errors.push('Last Name is required.');
        markInputError(lastName);
    }
    
    // Validate first name
    if (!trimValue(firstName && firstName.value)) {
        errors.push('First Name is required.');
        markInputError(firstName);
    }
    
    // Validate date of birth
    if (!trimValue(dob && dob.value)) {
        errors.push('Date of Birth is required.');
        markInputError(dob);
    }
    
    // Validate gender
    if (!getCheckedRadioValue(form, 'gender')) {
        errors.push('Sex is required.');
        var genderMale = getById('gender_male');
        var genderFemale = getById('gender_female');
        markInputError(genderMale);
        markInputError(genderFemale);
    }
    
    // Validate civil status
    if (!trimValue(civilStatus && civilStatus.value)) {
        errors.push('Civil Status is required.');
        markInputError(civilStatus);
    } else if (civilStatus.value === 'others') {
        // If "others" is selected, require the other field
        if (!trimValue(civilStatusOther && civilStatusOther.value)) {
            errors.push('Civil Status (Others) is required.');
            markInputError(civilStatusOther);
        }
    }
    
    // Validate nationality
    if (!trimValue(nationality && nationality.value)) {
        errors.push('Nationality is required.');
        markInputError(nationality);
    }
    
    // Validate place of birth
    if (!trimValue(placeOfBirth && placeOfBirth.value)) {
        errors.push('Place of Birth is required.');
        markInputError(placeOfBirth);
    }
    
    // Validate home address (only if checkbox is not checked)
    if (!isSameAsHomeChecked) {
        if (!trimValue(homeAddress && homeAddress.value)) {
            errors.push('Home Address is required.');
            markInputError(homeAddress);
        }
    }
    
    // Validate mobile number
    if (!trimValue(mobile && mobile.value)) {
        errors.push('Mobile/Cellphone Number is required.');
        markInputError(mobile);
    }
    
    // Validate email
    if (!trimValue(email && email.value)) {
        errors.push('E-mail Address is required.');
        markInputError(email);
    } else if (!isEmailValid(email.value)) {
        errors.push('E-mail Address must be a valid email (example: name@gmail.com).');
        markInputError(email);
    }
    
    // Return array of errors
    return errors;
}

// Function to show/hide civil status "other" field
function setCivilStatusOtherVisibility() {
    // Get form element
    var form = getById('e1Form');
    if (!form) {
        return;
    }
    
    // Get civil status select and other field elements
    var civilStatus = getInputByName(form, 'civil_status');
    var otherBlock = getById('civilStatusOtherBlock');
    var otherInput = getInputByName(form, 'civil_status_other');
    
    // If elements don't exist, do nothing
    if (!civilStatus || !otherBlock || !otherInput) {
        return;
    }
    
    // If "others" is selected, show the field
    if (civilStatus.value === 'others') {
        otherBlock.classList.remove('is-hidden');
        otherInput.disabled = false;
    } else {
        // Otherwise, hide it and clear the value
        otherBlock.classList.add('is-hidden');
        otherInput.disabled = true;
        otherInput.value = '';
        otherInput.classList.remove('input-error');
    }
}

// Function to initialize civil status "other" field
function initCivilStatusOther() {
    // Get form element
    var form = getById('e1Form');
    if (!form) {
        return;
    }
    
    // Get civil status select
    var civilStatus = getInputByName(form, 'civil_status');
    if (!civilStatus) {
        return;
    }
    
    // When civil status changes, update visibility
    civilStatus.addEventListener('change', function () {
        setCivilStatusOtherVisibility();
    });
    
    // Set initial visibility
    setCivilStatusOtherVisibility();
}

// Function to show/hide home address field
function setHomeAddressVisibility() {
    // Get checkbox and home address block
    var sameAsHome = getById('sameAsHomeAddress');
    var homeBlock = getById('homeAddressBlock');
    if (!sameAsHome || !homeBlock) {
        return;
    }
    
    // Get home address field
    var homeField = getById('homeAddressField');
    
    // If checkbox is checked, hide the field
    if (sameAsHome.checked) {
        if (homeField) {
            homeField.classList.add('is-hidden');
            // Disable all inputs in the field
            var homeInputs = homeField.querySelectorAll('input, select, textarea');
            for (var i = 0; i < homeInputs.length; i++) {
                homeInputs[i].disabled = true;
                homeInputs[i].classList.remove('input-error');
            }
        }
    } else {
        // If checkbox is not checked, show the field
        if (homeField) {
            homeField.classList.remove('is-hidden');
            // Enable all inputs in the field
            var homeInputs = homeField.querySelectorAll('input, select, textarea');
            for (var i = 0; i < homeInputs.length; i++) {
                homeInputs[i].disabled = false;
            }
        }
    }
}

// Function to copy place of birth to home address if checkbox is checked
function syncHomeAddressIfSame(form) {
    // Get checkbox
    var sameAsHome = getById('sameAsHomeAddress');
    // If checkbox is not checked, do nothing
    if (!sameAsHome || !sameAsHome.checked) {
        return;
    }
    
    // Get place of birth and home address inputs
    var placeOfBirth = getInputByName(form, 'place_of_birth');
    var homeAddress = getInputByName(form, 'home_address');
    
    // Copy value from place of birth to home address
    if (homeAddress && placeOfBirth) {
        homeAddress.disabled = false;
        homeAddress.value = placeOfBirth.value;
    }
}

// Function to initialize "same as home address" checkbox
function initSameAsHomeAddress() {
    // Get checkbox element
    var sameAsHome = getById('sameAsHomeAddress');
    if (!sameAsHome) {
        return;
    }
    
    // When checkbox changes, update visibility
    sameAsHome.addEventListener('change', function () {
        setHomeAddressVisibility();
    });
    
    // Set initial visibility
    setHomeAddressVisibility();
}

// Function to initialize form validation
function initFormValidation() {
    // Get form element
    var form = getById('e1Form');
    if (!form) {
        return;
    }
    
    // When form is submitted, validate it first
    form.addEventListener('submit', function (e) {
        // Prevent form from submitting automatically
        e.preventDefault();
        
        // Clear previous validation errors
        clearValidationUI(form);
        // Update home address visibility
        setHomeAddressVisibility();
        
        // Validate the form
        var errors = validateForm(form);
        
        // If there are errors, show them and stop
        if (errors.length > 0) {
            showErrors(errors);
            // Focus on first error input
            var firstErrorInput = form.querySelector('.input-error');
            if (firstErrorInput && firstErrorInput.focus) {
                firstErrorInput.focus();
            }
            return;
        }
        
        // If no errors, sync home address if needed
        syncHomeAddressIfSame(form);
        
        // Clear error display
        showErrors([]);
        
        // Submit the form
        form.submit();
    });
}

// When page loads, initialize everything
document.addEventListener('DOMContentLoaded', function () {
    initDependentGrids();
    initNwsSignatureDropzone();
    initSameAsHomeAddress();
    initCivilStatusOther();
    initFormValidation();
});
