<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        $sql = "SELECT * FROM patient ORDER BY patient_id DESC";


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);



            $stmt->execute();
            $product = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($product);
        }


        break;

    case "POST":
        $patient = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO patient (patient_id, patient_name, patient_middlename, patient_lastname, patient_birthday, patient_age, patient_gender, patient_email, patient_phone) 
        VALUES (null, :patient_name, :patient_middlename, :patient_lastname, :patient_birthday, :patient_age, :patient_gender, :patient_email, :patient_phone)";
        $stmt = $conn->prepare($sql);
        // $created_at = date('Y-m-d');
        $stmt->bindParam(':patient_name', $patient->patient_name);
        $stmt->bindParam(':patient_middlename', $patient->patient_middlename);
        $stmt->bindParam(':patient_lastname', $patient->patient_lastname);
        $stmt->bindParam(':patient_birthday', $patient->patient_birthday);
        $stmt->bindParam(':patient_age', $patient->patient_age);
        $stmt->bindParam(':patient_gender', $patient->patient_gender);
        $stmt->bindParam(':patient_email', $patient->patient_email);
        $stmt->bindParam(':patient_phone', $patient->patient_phone);
        // $stmt->bindParam(':created_at', $created_at);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "Patient created successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "Patient creation failed"
            ];
        }

        echo json_encode($response);
        break;

    case "PUT":
        $user = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE users SET name= :name, email=:email, gender=:gender, profile_picture=:profile_picture, address=:address, profile_description=:profile_description, updated_at=:updated_at WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $updated_at = date('Y-m-d');
        $stmt->bindParam(':user_id', $user->user_id);
        $stmt->bindParam(':name', $user->name);
        $stmt->bindParam(':email', $user->email);
        $stmt->bindParam(':profile_picture', $user->profile_picture);
        $stmt->bindParam(':address', $user->address);
        $stmt->bindParam(':gender', $user->gender);
        $stmt->bindParam(':profile_description', $user->profile_description);
        $stmt->bindParam(':updated_at', $updated_at);

        if ($stmt->execute()) {

            $response = [
                "status" => "success",
                "message" => "User updated successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "User update failed"
            ];
        }

        echo json_encode($response);
        break;

    case "DELETE":
        $sql = "DELETE FROM users WHERE id = :id";
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $path[2]);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "User deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "User deletion failed"
            ];
        }
}
