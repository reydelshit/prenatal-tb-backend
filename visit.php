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
        $patients = json_decode(file_get_contents('php://input'));
        $sql4 = "INSERT INTO visits (visit_id, patient_id, visit_date, created_at) VALUES (null, :patient_id, :visit_date, :created_at)";

        $stmt4 = $conn->prepare($sql4);
        $visit_date = date('Y-m-d H:i:s');

        $stmt4->bindParam('patient_id', $patients->patient_id);
        $stmt4->bindParam('visit_date', $visit_date);
        $stmt4->bindParam('created_at', $visit_date);


        if ($stmt4->execute()) {

            $sql5 =  "UPDATE appointments SET appointment_status= :appointment_status
                WHERE appointment_id = :appointment_id";
            $stmt5 = $conn->prepare($sql5);
            $status = 'Done';
            $stmt5->bindParam('appointment_status', $status);
            $stmt5->bindParam('appointment_id', $patients->appointment_id);

            $stmt5->execute();

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
