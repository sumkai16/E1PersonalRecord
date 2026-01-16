function getById(id) {
    return document.getElementById(id);
}

function clearElement(el) {
    if (!el) return;
    el.innerHTML = '';
}

function createDiv(className, text) {
    const el = document.createElement('div');
    el.className = className;
    el.textContent = text;
    return el;
}

function createInput(className, type, name, placeholder) {
    const el = document.createElement('input');
    el.className = className;
    el.type = type;
    el.name = name;
    if (placeholder) el.placeholder = placeholder;
    return el;
}

function appendHeaderCells(grid, headers) {
    for (let i = 0; i < headers.length; i++) {
        grid.appendChild(createDiv('dep-head', headers[i]));
    }
}

function appendChildRow(grid, index) {
    grid.appendChild(createDiv('dep-index', String(index) + '.'));
    grid.appendChild(createInput('form-input', 'text', `child_${index}_last_name`, 'Last Name'));
    grid.appendChild(createInput('form-input', 'text', `child_${index}_first_name`, 'First Name'));
    grid.appendChild(createInput('form-input', 'text', `child_${index}_middle_name`, 'Middle Name'));
    grid.appendChild(createInput('form-input', 'text', `child_${index}_suffix`, 'Suffix'));
    grid.appendChild(createInput('form-input', 'date', `child_${index}_birth`, ''));
}

function appendOtherRow(grid, index) {
    grid.appendChild(createDiv('dep-index', String(index) + '.'));
    grid.appendChild(createInput('form-input', 'text', `other_${index}_last_name`, 'Last Name'));
    grid.appendChild(createInput('form-input', 'text', `other_${index}_first_name`, 'First Name'));
    grid.appendChild(createInput('form-input', 'text', `other_${index}_middle_name`, 'Middle Name'));
    grid.appendChild(createInput('form-input', 'text', `other_${index}_suffix`, 'Suffix'));
    grid.appendChild(createInput('form-input', 'text', `other_${index}_relationship`, 'Relationship'));
    grid.appendChild(createInput('form-input', 'date', `other_${index}_birth`, ''));
}

function buildChildrenRows(count) {
    const grid = getById('childrenGrid');
    if (!grid) return;

    clearElement(grid);
    if (!count) return;

    appendHeaderCells(grid, ['#', 'Last Name', 'First Name', 'Middle Name', 'Suffix', 'Date of Birth']);
    for (let i = 1; i <= count; i++) {
        appendChildRow(grid, i);
    }
}

function buildOtherRows(count) {
    const grid = getById('otherGrid');
    if (!grid) return;

    clearElement(grid);
    if (!count) return;

    appendHeaderCells(grid, ['#', 'Last Name', 'First Name', 'Middle Name', 'Suffix', 'Relationship', 'Date of Birth']);
    for (let i = 1; i <= count; i++) {
        appendOtherRow(grid, i);
    }
}

function clampNumberInput(el) {
    const min = Number(el.min || 0);
    const max = Number(el.max || 0);
    let value = Number(el.value || 0);

    if (Number.isNaN(value)) value = 0;
    if (value < min) value = min;
    if (max && value > max) value = max;

    el.value = String(value);
    return value;
}

function setDropzoneFilename(fileInput, filenameEl) {
    if (!filenameEl) return;
    const file = fileInput.files && fileInput.files[0];
    filenameEl.textContent = file ? file.name : 'No file selected';
}

function setupDropzone(fileInput, dropzoneEl) {
    const browseBtn = dropzoneEl.querySelector('[data-dropzone-browse]');
    const filenameEl = dropzoneEl.querySelector('[data-dropzone-filename]');

    function openPicker() {
        fileInput.click();
    }

    function onBrowseClick() {
        openPicker();
    }

    function onDropzoneClick(e) {
        if (e.target === browseBtn) return;
        openPicker();
    }

    function onDropzoneKeydown(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openPicker();
        }
    }

    function onInputChange() {
        setDropzoneFilename(fileInput, filenameEl);
    }

    function onDragOver(e) {
        e.preventDefault();
        dropzoneEl.classList.add('is-dragover');
    }

    function onDragLeave() {
        dropzoneEl.classList.remove('is-dragover');
    }

    function onDrop(e) {
        e.preventDefault();
        dropzoneEl.classList.remove('is-dragover');

        const files = e.dataTransfer && e.dataTransfer.files;
        if (files && files.length) {
            fileInput.files = files;
            setDropzoneFilename(fileInput, filenameEl);
        }
    }

    if (browseBtn) browseBtn.addEventListener('click', onBrowseClick);
    dropzoneEl.addEventListener('click', onDropzoneClick);
    dropzoneEl.addEventListener('keydown', onDropzoneKeydown);
    fileInput.addEventListener('change', onInputChange);
    dropzoneEl.addEventListener('dragover', onDragOver);
    dropzoneEl.addEventListener('dragleave', onDragLeave);
    dropzoneEl.addEventListener('drop', onDrop);

    setDropzoneFilename(fileInput, filenameEl);
}

function initDependentGrids() {
    const childrenCount = getById('childrenCount');
    const otherCount = getById('otherCount');

    if (childrenCount) {
        childrenCount.addEventListener('input', function () {
            buildChildrenRows(clampNumberInput(childrenCount));
        });
        buildChildrenRows(clampNumberInput(childrenCount));
    }

    if (otherCount) {
        otherCount.addEventListener('input', function () {
            buildOtherRows(clampNumberInput(otherCount));
        });
        buildOtherRows(clampNumberInput(otherCount));
    }
}

function initNwsSignatureDropzone() {
    const signatureInput = getById('nws_signature_file');
    const signatureDropzone = getById('nws_signature_dropzone');
    if (!signatureInput || !signatureDropzone) return;

    setupDropzone(signatureInput, signatureDropzone);
}

function getInputByName(form, name) {
    if (!form) return null;
    return form.querySelector('[name="' + name + '"]');
}

function getCheckedRadioValue(form, name) {
    if (!form) return '';
    const checked = form.querySelector('input[type="radio"][name="' + name + '"]:checked');
    return checked ? checked.value : '';
}

function trimValue(value) {
    return String(value || '').trim();
}

function clearValidationUI(form) {
    if (!form) return;

    const errorBox = getById('formErrors');
    if (errorBox) {
        errorBox.classList.add('is-hidden');
        errorBox.innerHTML = '';
    }

    const errorInputs = form.querySelectorAll('.input-error');
    for (let i = 0; i < errorInputs.length; i++) {
        errorInputs[i].classList.remove('input-error');
    }
}

function showErrors(errors) {
    const errorBox = getById('formErrors');
    if (!errorBox) return;

    errorBox.innerHTML = '';
    for (let i = 0; i < errors.length; i++) {
        errorBox.appendChild(createDiv('', errors[i]));
    }

    if (errors.length) {
        errorBox.classList.remove('is-hidden');
    } else {
        errorBox.classList.add('is-hidden');
    }
}

function markInputError(inputEl) {
    if (!inputEl) return;
    inputEl.classList.add('input-error');
}

function isEmailValid(email) {
    const value = trimValue(email);
    if (!value) return false;
    const at = value.indexOf('@');
    const dot = value.lastIndexOf('.');
    return at > 0 && dot > at + 1 && dot < value.length - 1;
}

function validateForm(form) {
    const errors = [];

    const lastName = getInputByName(form, 'last_name');
    const firstName = getInputByName(form, 'first_name');
    const middleName = getInputByName(form, 'middle_name');
    const dob = getInputByName(form, 'date_of_birth');
    const nationality = getInputByName(form, 'nationality');
    const placeOfBirth = getInputByName(form, 'place_of_birth');
    const mobile = getInputByName(form, 'mobile_number');
    const email = getInputByName(form, 'email');
    const civilStatus = getInputByName(form, 'civil_status');
    const civilStatusOther = getInputByName(form, 'civil_status_other');
    const homeAddress = getInputByName(form, 'home_address');

    const sameAsHome = getById('sameAsHomeAddress');
    const isSameAsHomeChecked = !!(sameAsHome && sameAsHome.checked);

    if (!trimValue(lastName && lastName.value)) {
        errors.push('Last Name is required.');
        markInputError(lastName);
    }
    if (!trimValue(firstName && firstName.value)) {
        errors.push('First Name is required.');
        markInputError(firstName);
    }
    if (!trimValue(middleName && middleName.value)) {
        errors.push('Middle Name is required.');
        markInputError(middleName);
    }

    if (!trimValue(dob && dob.value)) {
        errors.push('Date of Birth is required.');
        markInputError(dob);
    }

    if (!getCheckedRadioValue(form, 'gender')) {
        errors.push('Sex is required.');
        const genderMale = getById('gender_male');
        const genderFemale = getById('gender_female');
        markInputError(genderMale);
        markInputError(genderFemale);
    }

    if (!trimValue(civilStatus && civilStatus.value)) {
        errors.push('Civil Status is required.');
        markInputError(civilStatus);
    } else if (civilStatus.value === 'others') {
        if (!trimValue(civilStatusOther && civilStatusOther.value)) {
            errors.push('Civil Status (Others) is required.');
            markInputError(civilStatusOther);
        }
    }

    if (!trimValue(nationality && nationality.value)) {
        errors.push('Nationality is required.');
        markInputError(nationality);
    }

    if (!trimValue(placeOfBirth && placeOfBirth.value)) {
        errors.push('Place of Birth is required.');
        markInputError(placeOfBirth);
    }

    if (!isSameAsHomeChecked) {
        if (!trimValue(homeAddress && homeAddress.value)) {
            errors.push('Home Address is required.');
            markInputError(homeAddress);
        }
    }

    if (!trimValue(mobile && mobile.value)) {
        errors.push('Mobile/Cellphone Number is required.');
        markInputError(mobile);
    }

    if (!trimValue(email && email.value)) {
        errors.push('E-mail Address is required.');
        markInputError(email);
    } else if (!isEmailValid(email.value)) {
        errors.push('E-mail Address must be a valid email (example: name@gmail.com).');
        markInputError(email);
    }

    return errors;
}

function setCivilStatusOtherVisibility() {
    const form = getById('e1Form');
    if (!form) return;

    const civilStatus = getInputByName(form, 'civil_status');
    const otherBlock = getById('civilStatusOtherBlock');
    const otherInput = getInputByName(form, 'civil_status_other');
    if (!civilStatus || !otherBlock || !otherInput) return;

    if (civilStatus.value === 'others') {
        otherBlock.classList.remove('is-hidden');
        otherInput.disabled = false;
    } else {
        otherBlock.classList.add('is-hidden');
        otherInput.disabled = true;
        otherInput.value = '';
        otherInput.classList.remove('input-error');
    }
}

function initCivilStatusOther() {
    const form = getById('e1Form');
    if (!form) return;

    const civilStatus = getInputByName(form, 'civil_status');
    if (!civilStatus) return;

    civilStatus.addEventListener('change', function () {
        setCivilStatusOtherVisibility();
    });

    setCivilStatusOtherVisibility();
}

function setHomeAddressVisibility() {
    const sameAsHome = getById('sameAsHomeAddress');
    const homeBlock = getById('homeAddressBlock');
    if (!sameAsHome || !homeBlock) return;

    const homeField = getById('homeAddressField');

    if (sameAsHome.checked) {
        if (homeField) {
            homeField.classList.add('is-hidden');
            const homeInputs = homeField.querySelectorAll('input, select, textarea');
            for (let i = 0; i < homeInputs.length; i++) {
                homeInputs[i].disabled = true;
                homeInputs[i].classList.remove('input-error');
            }
        }
    } else {
        if (homeField) {
            homeField.classList.remove('is-hidden');
            const homeInputs = homeField.querySelectorAll('input, select, textarea');
            for (let i = 0; i < homeInputs.length; i++) {
                homeInputs[i].disabled = false;
            }
        }
    }
}

function syncHomeAddressIfSame(form) {
    const sameAsHome = getById('sameAsHomeAddress');
    if (!sameAsHome || !sameAsHome.checked) return;

    const placeOfBirth = getInputByName(form, 'place_of_birth');
    const homeAddress = getInputByName(form, 'home_address');

    if (homeAddress && placeOfBirth) {
        homeAddress.disabled = false;
        homeAddress.value = placeOfBirth.value;
    }
}

function initSameAsHomeAddress() {
    const sameAsHome = getById('sameAsHomeAddress');
    if (!sameAsHome) return;

    sameAsHome.addEventListener('change', function () {
        setHomeAddressVisibility();
    });

    setHomeAddressVisibility();
}

function initFormValidation() {
    const form = getById('e1Form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        clearValidationUI(form);
        setHomeAddressVisibility();

        const errors = validateForm(form);
        if (errors.length) {
            showErrors(errors);
            const firstErrorInput = form.querySelector('.input-error');
            if (firstErrorInput && firstErrorInput.focus) firstErrorInput.focus();
            return;
        }

        syncHomeAddressIfSame(form);

        showErrors([]);
        form.submit();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initDependentGrids();
    initNwsSignatureDropzone();
    initSameAsHomeAddress();
    initCivilStatusOther();
    initFormValidation();
});
