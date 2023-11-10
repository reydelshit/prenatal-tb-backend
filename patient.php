<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['patient_id'])) {
            $patient_id_specific = $_GET['patient_id'];
            $sql = "SELECT *
            FROM patient
            WHERE patient_id = :patient_id";
        }

        if (!isset($_GET['patient_id'])) {
            $sql = "SELECT * FROM patient ORDER BY patient_id DESC";
        }

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            if (isset($patient_id_specific)) {
                $stmt->bindParam(':patient_id', $patient_id_specific);
            }

            $stmt->execute();
            $patient = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($patient);
        }



        break;

    case "POST":
        $patients = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO patient (patient_id, patient_name, patient_middlename, patient_lastname, patient_birthday, patient_age, patient_gender, patient_email, patient_phone, patient_type, created_at) 
        VALUES (null, :patient_name, :patient_middlename, :patient_lastname, :patient_birthday, :patient_age, :patient_gender, :patient_email, :patient_phone, :patient_type, :created_at)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d H:i:s');
        $stmt->bindParam(':patient_name', $patients->patient_name);
        $stmt->bindParam(':patient_middlename', $patients->patient_middlename);
        $stmt->bindParam(':patient_lastname', $patients->patient_lastname);
        $stmt->bindParam(':patient_birthday', $patients->patient_birthday);
        $stmt->bindParam(':patient_age', $patients->patient_age);
        $stmt->bindParam(':patient_gender', $patients->patient_gender);
        $stmt->bindParam(':patient_email', $patients->patient_email);
        $stmt->bindParam(':patient_phone', $patients->patient_phone);
        $stmt->bindParam(':patient_type', $patients->patient_type);

        $stmt->bindParam(':created_at', $created_at);


        if ($stmt->execute()) {

            $lastInsertedId = $conn->lastInsertId();


            $sql4 = "INSERT INTO visits (visit_id, patient_id, visit_date, created_at) VALUES (null, :patient_id, :visit_date, :created_at)";

            $stmt4 = $conn->prepare($sql4);
            $visit_date = date('Y-m-d H:i:s');

            $stmt4->bindParam('patient_id', $lastInsertedId);
            $stmt4->bindParam('visit_date', $visit_date);
            $stmt4->bindParam('created_at', $created_at);

            $stmt4->execute();

            $sql3 = "INSERT INTO users (user_id, user_username, user_password, user_type, patient_id, created_at) VALUES (null, :user_username, :user_password, :user_type, :patient_id, :created_at)";

            $stmt3 = $conn->prepare($sql3);

            $pat = 'patient';
            $stmt3->bindParam("user_username", $patients->user_username);
            $stmt3->bindParam("user_password", $patients->user_password);
            $stmt3->bindParam("user_type", $pat);
            $stmt3->bindParam("created_at", $visit_date);
            $stmt3->bindParam("patient_id", $lastInsertedId);

            $stmt3->execute();


            $tuberculosisData = $patients->tuberculosisData;
            $prenatalData = $patients->prenatalData;


            if ($patients->patient_type === 'Tuberculosis') {
                foreach ($tuberculosisData as $patient) {
                    $sql2 = "INSERT INTO info_answers (info_answer_id, patient_id, question_id, answer_text, answer_type)"
                        . " VALUES (null, :patient_id, :question_id, :answer_text, :answer_type)";

                    $stmt2 = $conn->prepare($sql2);

                    $stmt2->bindParam(':patient_id', $lastInsertedId);
                    $stmt2->bindParam(':question_id', $patient->question_id);
                    $stmt2->bindParam(':answer_text', $patient->answer_text);
                    $stmt2->bindParam(':answer_type', $patients->patient_type);

                    $stmt2->execute();
                }
            } else {
                foreach ($prenatalData as $patient) {
                    $sql2 = "INSERT INTO info_answers (info_answer_id, patient_id, question_id, answer_text, answer_type)"
                        . " VALUES (null, :patient_id, :question_id, :answer_text, :answer_type)";

                    $stmt2 = $conn->prepare($sql2);

                    $stmt2->bindParam(':patient_id', $lastInsertedId);
                    $stmt2->bindParam(':question_id', $patient->question_id);
                    $stmt2->bindParam(':answer_text', $patient->answer_text);
                    $stmt2->bindParam(':answer_type', $patients->patient_type);

                    $stmt2->execute();
                }
            }


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
