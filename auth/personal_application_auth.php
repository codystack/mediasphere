<?php
// submit_application.php
include "../config/db.php"; // Your PDO connection
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);   // hide from output
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log'); // log into file


header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Handle file uploads (Step 4)
        $uploadDir = __DIR__ . '../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadFields = ['means_of_identity','proof_of_travel','utility_bill','passport_photo','signature_sample'];
        foreach ($uploadFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $fileTmp  = $_FILES[$field]['tmp_name'];
                $fileName = uniqid() . "_" . basename($_FILES[$field]['name']);
                $filePath = $uploadDir . $fileName;
                move_uploaded_file($fileTmp, $filePath);

                // Store relative path in $_POST for SQL
                $_POST[$field] = 'uploads/' . $fileName;
            }
        }

        $sql = "INSERT INTO personal_application 
            (user_id, 
            first_name, last_name, other_names, mothers_maiden_name, email_address, phone_number, date_of_birth, place_of_birth, gender, nationality, state_of_origin, lga, hometown, marital_status, religion, occupation, workplace, workplace_address,
            kin_first_name, kin_last_name, kin_email, kin_phone, kin_date_of_birth, kin_gender, kin_residential_address, kin_relationship,
            loan_amount, bank_type, loan_duration_months, loan_start_date, loan_end_date, purpose_of_fund,
            id_type, means_of_identity, id_number, id_expiry_date, nin, proof_of_travel, utility_bill, passport_photo, signature_sample, 
            status)
            VALUES 
            (:user_id, 
            :first_name, :last_name, :other_names, :mothers_maiden_name, :email_address, :phone_number, :date_of_birth, :place_of_birth, :gender, :nationality, :state_of_origin, :lga, :hometown, :marital_status, :religion, :occupation, :workplace, :workplace_address,
            :kin_first_name, :kin_last_name, :kin_email, :kin_phone, :kin_date_of_birth, :kin_gender, :kin_residential_address, :kin_relationship,
            :loan_amount, :bank_type, :loan_duration_months, :loan_start_date, :loan_end_date, :purpose_of_fund,
            :id_type, :means_of_identity, :id_number, :id_expiry_date, :nin, :proof_of_travel, :utility_bill, :passport_photo, :signature_sample,
            'pending'
            )
            ON DUPLICATE KEY UPDATE 
            other_names=VALUES(other_names), mothers_maiden_name=VALUES(mothers_maiden_name), 
            date_of_birth=VALUES(date_of_birth), place_of_birth=VALUES(place_of_birth),
            gender=VALUES(gender), nationality=VALUES(nationality),
            state_of_origin=VALUES(state_of_origin), lga=VALUES(lga),
            hometown=VALUES(hometown), marital_status=VALUES(marital_status),
            religion=VALUES(religion), occupation=VALUES(occupation),
            workplace=VALUES(workplace), workplace_address=VALUES(workplace_address),

            kin_first_name=VALUES(kin_first_name), kin_last_name=VALUES(kin_last_name), kin_email=VALUES(kin_email),
            kin_phone=VALUES(kin_phone), kin_date_of_birth=VALUES(kin_date_of_birth), kin_gender=VALUES(kin_gender),
            kin_residential_address=VALUES(kin_residential_address), kin_relationship=VALUES(kin_relationship),

            loan_amount=VALUES(loan_amount), bank_type=VALUES(bank_type), loan_duration_months=VALUES(loan_duration_months),
            loan_start_date=VALUES(loan_start_date), loan_end_date=VALUES(loan_end_date), purpose_of_fund=VALUES(purpose_of_fund),

            id_type=VALUES(id_type), means_of_identity=VALUES(means_of_identity), id_number=VALUES(id_number),
            id_expiry_date=VALUES(id_expiry_date), nin=VALUES(nin),
            proof_of_travel=VALUES(proof_of_travel), utility_bill=VALUES(utility_bill), 
            passport_photo=VALUES(passport_photo), signature_sample=VALUES(signature_sample)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':user_id' => $_POST['user_id'] ?? null,
            ':first_name' => $_POST['first_name'] ?? null,
            ':last_name' => $_POST['last_name'] ?? null,
            ':other_names' => $_POST['other_names'] ?? null,
            ':mothers_maiden_name' => $_POST['mothers_maiden_name'] ?? null,
            ':email_address' => $_POST['email_address'] ?? null,
            ':phone_number' => $_POST['phone_number'] ?? null,
            ':date_of_birth' => $_POST['date_of_birth'] ?? null,
            ':place_of_birth' => $_POST['place_of_birth'] ?? null,
            ':gender' => $_POST['gender'] ?? null,
            ':nationality' => $_POST['nationality'] ?? null,
            ':state_of_origin' => $_POST['state_of_origin'] ?? null,
            ':lga' => $_POST['lga'] ?? null,
            ':hometown' => $_POST['hometown'] ?? null,
            ':marital_status' => $_POST['marital_status'] ?? null,
            ':religion' => $_POST['religion'] ?? null,
            ':occupation' => $_POST['occupation'] ?? null,
            ':workplace' => $_POST['workplace'] ?? null,
            ':workplace_address' => $_POST['workplace_address'] ?? null,

            ':kin_first_name' => $_POST['kin_first_name'] ?? null,
            ':kin_last_name' => $_POST['kin_last_name'] ?? null,
            ':kin_email' => $_POST['kin_email'] ?? null,
            ':kin_phone' => $_POST['kin_phone'] ?? null,
            ':kin_date_of_birth' => $_POST['kin_date_of_birth'] ?? null,
            ':kin_gender' => $_POST['kin_gender'] ?? null,
            ':kin_residential_address' => $_POST['kin_residential_address'] ?? null,
            ':kin_relationship' => $_POST['kin_relationship'] ?? null,

            ':loan_amount' => $_POST['loan_amount'] ?? null,
            ':bank_type' => $_POST['bank_type'] ?? null,
            ':loan_duration_months' => $_POST['loan_duration_months'] ?? null,
            ':loan_start_date' => $_POST['loan_start_date'] ?? null,
            ':loan_end_date' => $_POST['loan_end_date'] ?? null,
            ':purpose_of_fund' => $_POST['purpose_of_fund'] ?? null,

            ':id_type' => $_POST['id_type'] ?? null,
            ':means_of_identity' => $_POST['means_of_identity'] ?? null,
            ':id_number' => $_POST['id_number'] ?? null,
            ':id_expiry_date' => $_POST['id_expiry_date'] ?? null,
            ':nin' => $_POST['nin'] ?? null,
            ':proof_of_travel' => $_POST['proof_of_travel'] ?? null,
            ':utility_bill' => $_POST['utility_bill'] ?? null,
            ':passport_photo' => $_POST['passport_photo'] ?? null,
            ':signature_sample' => $_POST['signature_sample'] ?? null,
        ]);

        echo json_encode(['success'=>true,'message'=>'Application saved successfully.']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}