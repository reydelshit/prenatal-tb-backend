<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        $email = $_GET['username'];
        $password = $_GET['password'];

        $sql = "SELECT * FROM users WHERE user_username = :username AND user_password = :password";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($users) {


            $response = [
                "status" => "success",
                "message" => "User login successful"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "Failed to update user login status"
            ];
        }


        echo json_encode($users);

        break;


    case "POST":
        $appointment = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO users (user_id, appointment_title, start, end, allDay, patient_id) 
                    VALUES (null, :appointment_title, :start, :end, :allDay, :patient_id)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d');
        $stmt->bindParam(':appointment_title', $appointment->appointment_title);
        $stmt->bindParam(':start', $appointment->start);
        $stmt->bindParam(':end', $appointment->end);
        $stmt->bindParam(':allDay', $appointment->allDay);
        $stmt->bindParam(':patient_id', $appointment->patient_id);



        // $stmt->bindParam(':created_at', $created_at);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "Appointment created successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "Appointment creation failed"
            ];
        }

        echo json_encode($response);
        break;
}
