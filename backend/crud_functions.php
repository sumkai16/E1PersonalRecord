<?php

// This file contains functions to Create, Read, Update, and Delete person records
// CRUD = Create, Read, Update, Delete

// Include database connection file
require_once __DIR__ . '/../db.php';
// Include validation functions
require_once __DIR__ . '/validation.php';

// Function to create a new person record
function create_person($data)
{
    // Get database connection
    $pdo = db();
    
    // First, we need to get the civil status ID from the database
    // because we store the ID, not the text code
    $stmt = $pdo->prepare('SELECT id FROM civil_statuses WHERE code = :code LIMIT 1');
    $stmt->execute(array(':code' => $data['civil_status']));
    $civilStatusRow = $stmt->fetch();
    $civilStatusId = (int)$civilStatusRow['id'];
    
    // Prepare SQL statement to insert person data
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
    
    // Set civil_status_other to null if not "others"
    $civilStatusOther = null;
    if ($data['civil_status'] === 'others') {
        $civilStatusOther = $data['civil_status_other'];
    }
    
    // Set optional fields to null if empty
    $religion = ($data['religion'] !== '') ? $data['religion'] : null;
    $telephoneNumber = ($data['telephone_number'] !== '') ? $data['telephone_number'] : null;
    $fatherLastName = ($data['father_last_name'] !== '') ? $data['father_last_name'] : null;
    $fatherFirstName = ($data['father_first_name'] !== '') ? $data['father_first_name'] : null;
    $fatherMiddleName = ($data['father_middle_name'] !== '') ? $data['father_middle_name'] : null;
    $motherLastName = ($data['mother_last_name'] !== '') ? $data['mother_last_name'] : null;
    $motherFirstName = ($data['mother_first_name'] !== '') ? $data['mother_first_name'] : null;
    $motherMiddleName = ($data['mother_middle_name'] !== '') ? $data['mother_middle_name'] : null;
    
    // Convert boolean to 1 or 0 for database
    $sameAsHomeAddress = ($data['same_as_home_address'] === true) ? 1 : 0;
    
    // Execute the insert statement with all the values
    $personStmt->execute(array(
        ':last_name' => $data['last_name'],
        ':first_name' => $data['first_name'],
        ':middle_name' => $data['middle_name'],
        ':date_of_birth' => $data['date_of_birth'],
        ':sex' => $data['gender'],
        ':civil_status_id' => $civilStatusId,
        ':civil_status_other' => $civilStatusOther,
        ':nationality' => $data['nationality'],
        ':place_of_birth' => $data['place_of_birth'],
        ':mobile_number' => $data['mobile_number'],
        ':email' => $data['email'],
        ':religion' => $religion,
        ':telephone_number' => $telephoneNumber,
        ':father_last_name' => $fatherLastName,
        ':father_first_name' => $fatherFirstName,
        ':father_middle_name' => $fatherMiddleName,
        ':mother_last_name' => $motherLastName,
        ':mother_first_name' => $motherFirstName,
        ':mother_middle_name' => $motherMiddleName,
        ':same_as_home_address' => $sameAsHomeAddress,
    ));
    
    // Get the ID of the person we just inserted
    $personId = (int)$pdo->lastInsertId();
    
    // Insert home address
    $homeStmt = $pdo->prepare(
        'INSERT INTO person_home_addresses (person_id, address_line, zip_code)
         VALUES (:person_id, :address_line, :zip_code)'
    );
    
    // Set zip_code to null if empty
    $zipCode = ($data['zip_code'] !== '') ? $data['zip_code'] : null;
    
    $homeStmt->execute(array(
        ':person_id' => $personId,
        ':address_line' => $data['home_address'],
        ':zip_code' => $zipCode,
    ));
    
    // Return the person ID so we can use it later
    return $personId;
}

// Function to read/get a person by ID
function read_person($id)
{
    // Get database connection
    $pdo = db();
    
    // Prepare SQL to get person data
    // We join with civil_statuses table to get the civil status name
    $stmt = $pdo->prepare(
        'SELECT p.*, cs.code AS civil_status_code, cs.name AS civil_status_name
         FROM persons p
         JOIN civil_statuses cs ON p.civil_status_id = cs.id
         WHERE p.id = :id LIMIT 1'
    );
    $stmt->execute(array(':id' => $id));
    $person = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If person not found, return null
    if (!$person) {
        return null;
    }
    
    // Get home address for this person
    $homeStmt = $pdo->prepare('SELECT * FROM person_home_addresses WHERE person_id = :id LIMIT 1');
    $homeStmt->execute(array(':id' => $id));
    $person['home_address'] = $homeStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get all dependents (spouse, children, others) for this person
    $depStmt = $pdo->prepare('SELECT * FROM person_dependents WHERE person_id = :id ORDER BY dependent_type, id');
    $depStmt->execute(array(':id' => $id));
    $person['dependents'] = $depStmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $person;
}

// Function to read/get all persons
function read_all_persons()
{
    // Get database connection
    $pdo = db();
    
    // Prepare SQL to get all persons
    $stmt = $pdo->prepare(
        'SELECT p.id, p.last_name, p.first_name, p.middle_name, p.date_of_birth, p.sex, cs.name AS civil_status, p.email, p.mobile_number, p.created_at
         FROM persons p
         JOIN civil_statuses cs ON p.civil_status_id = cs.id
         ORDER BY p.created_at DESC'
    );
    $stmt->execute();
    
    // Return all results as an array
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getAddress($person_id) {
    // Get database connection
    $pdo = db();

    // Prepare SQL to get home address for the given person ID
    $stmt = $pdo->prepare(
        'SELECT address_line, zip_code
         FROM person_home_addresses
         WHERE person_id = :person_id LIMIT 1'
    );
    $stmt->execute(array(':person_id' => $person_id));
    
    // Fetch and return the address
    return $stmt->fetch(PDO::FETCH_ASSOC);

}
function calculate_age($date_of_birth) {
    $dob = new DateTime($date_of_birth);
    $today = new DateTime();
    $age = $today->diff($dob)->y;
    return $age;

}
// Function to update a person record
function update_person($id, $data)
{
    // Get database connection
    $pdo = db();
    
    // Start a transaction (all or nothing - if one fails, all fail)
    $pdo->beginTransaction();
    
    try {
        // Get civil status ID
        $stmt = $pdo->prepare('SELECT id FROM civil_statuses WHERE code = :code LIMIT 1');
        $stmt->execute(array(':code' => $data['civil_status']));
        $civilStatusRow = $stmt->fetch();
        $civilStatusId = (int)$civilStatusRow['id'];
        
        // Prepare SQL to update person data
        $personStmt = $pdo->prepare(
            'UPDATE persons SET
                last_name = :last_name, first_name = :first_name, middle_name = :middle_name,
                date_of_birth = :date_of_birth, sex = :sex,
                civil_status_id = :civil_status_id, civil_status_other = :civil_status_other,
                nationality = :nationality, place_of_birth = :place_of_birth,
                mobile_number = :mobile_number, email = :email,
                religion = :religion, telephone_number = :telephone_number,
                father_last_name = :father_last_name, father_first_name = :father_first_name, father_middle_name = :father_middle_name,
                mother_last_name = :mother_last_name, mother_first_name = :mother_first_name, mother_middle_name = :mother_middle_name,
                same_as_home_address = :same_as_home_address,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        
        // Set optional fields
        $civilStatusOther = null;
        if ($data['civil_status'] === 'others') {
            $civilStatusOther = $data['civil_status_other'];
        }
        
        $religion = ($data['religion'] !== '') ? $data['religion'] : null;
        $telephoneNumber = ($data['telephone_number'] !== '') ? $data['telephone_number'] : null;
        $fatherLastName = ($data['father_last_name'] !== '') ? $data['father_last_name'] : null;
        $fatherFirstName = ($data['father_first_name'] !== '') ? $data['father_first_name'] : null;
        $fatherMiddleName = ($data['father_middle_name'] !== '') ? $data['father_middle_name'] : null;
        $motherLastName = ($data['mother_last_name'] !== '') ? $data['mother_last_name'] : null;
        $motherFirstName = ($data['mother_first_name'] !== '') ? $data['mother_first_name'] : null;
        $motherMiddleName = ($data['mother_middle_name'] !== '') ? $data['mother_middle_name'] : null;
        $sameAsHomeAddress = ($data['same_as_home_address'] === true) ? 1 : 0;
        
        $personStmt->execute(array(
            ':last_name' => $data['last_name'],
            ':first_name' => $data['first_name'],
            ':middle_name' => $data['middle_name'],
            ':date_of_birth' => $data['date_of_birth'],
            ':sex' => $data['gender'],
            ':civil_status_id' => $civilStatusId,
            ':civil_status_other' => $civilStatusOther,
            ':nationality' => $data['nationality'],
            ':place_of_birth' => $data['place_of_birth'],
            ':mobile_number' => $data['mobile_number'],
            ':email' => $data['email'],
            ':religion' => $religion,
            ':telephone_number' => $telephoneNumber,
            ':father_last_name' => $fatherLastName,
            ':father_first_name' => $fatherFirstName,
            ':father_middle_name' => $fatherMiddleName,
            ':mother_last_name' => $motherLastName,
            ':mother_first_name' => $motherFirstName,
            ':mother_middle_name' => $motherMiddleName,
            ':same_as_home_address' => $sameAsHomeAddress,
            ':id' => $id,
        ));
        
        // Update home address (insert or update if exists)
        $homeStmt = $pdo->prepare(
            'INSERT INTO person_home_addresses (person_id, address_line, zip_code)
             VALUES (:person_id, :address_line, :zip_code)
             ON DUPLICATE KEY UPDATE address_line = VALUES(address_line), zip_code = VALUES(zip_code)'
        );
        
        $zipCode = ($data['zip_code'] !== '') ? $data['zip_code'] : null;
        
        $homeStmt->execute(array(
            ':person_id' => $id,
            ':address_line' => $data['home_address'],
            ':zip_code' => $zipCode,
        ));
        
        // Delete old dependents (they will be re-inserted in update_person.php)
        $deleteDepsStmt = $pdo->prepare('DELETE FROM person_dependents WHERE person_id = :id');
        $deleteDepsStmt->execute(array(':id' => $id));
        
        // If everything worked, commit the transaction
        $pdo->commit();
    } catch (Exception $e) {
        // If something went wrong, undo all changes
        $pdo->rollBack();
        // Re-throw the error so calling code can handle it
        throw $e;
    }
}

// Function to delete a person record
function delete_person($id)
{
    // Get database connection
    $pdo = db();
    
    // Start a transaction
    $pdo->beginTransaction();
    
    try {
        // Delete related records first (because of foreign keys)
        // Delete dependents
        $deleteDepsStmt = $pdo->prepare('DELETE FROM person_dependents WHERE person_id = :id');
        $deleteDepsStmt->execute(array(':id' => $id));
        
        // Delete home address
        $deleteHomeStmt = $pdo->prepare('DELETE FROM person_home_addresses WHERE person_id = :id');
        $deleteHomeStmt->execute(array(':id' => $id));
        
        // Delete other related tables (SE, OFW, NWS, Certifications, SSS Processing)
        $deleteSeStmt = $pdo->prepare('DELETE FROM person_self_employment WHERE person_id = :id');
        $deleteSeStmt->execute(array(':id' => $id));
        
        $deleteOfwStmt = $pdo->prepare('DELETE FROM person_ofw WHERE person_id = :id');
        $deleteOfwStmt->execute(array(':id' => $id));
        
        $deleteNwsStmt = $pdo->prepare('DELETE FROM person_nws WHERE person_id = :id');
        $deleteNwsStmt->execute(array(':id' => $id));
        
        $deleteCertStmt = $pdo->prepare('DELETE FROM person_certifications WHERE person_id = :id');
        $deleteCertStmt->execute(array(':id' => $id));
        
        $deleteSssStmt = $pdo->prepare('DELETE FROM person_sss_processing WHERE person_id = :id');
        $deleteSssStmt->execute(array(':id' => $id));
        
        // Finally, delete the person record
        $deletePersonStmt = $pdo->prepare('DELETE FROM persons WHERE id = :id');
        $deletePersonStmt->execute(array(':id' => $id));
        
        // Commit the transaction
        $pdo->commit();
    } catch (Exception $e) {
        // If something went wrong, undo all changes
        $pdo->rollBack();
        // Re-throw the error
        throw $e;
    }
}

?>
