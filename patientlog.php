<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":



        if (isset($_GET['appointment_id'])) {
            $appointment_id_specific = $_GET['appointment_id'];
            $sql = "SELECT *
            FROM appointments
            WHERE appointment_id = :appointment_id
              AND CURDATE() >= appointments.start
              AND CURDATE() <= appointments.end";
        }

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            if (isset($appointment_id_specific)) {
                $stmt->bindParam(':appointment_id', $appointment_id_specific);
            }



            $stmt->execute();
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($appointments);
        }


        break;

    case "POST":
        $appointment = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO appointments (appointment_id, appointment_title, start, end, allDay, patient_id, appointment_status) 
                VALUES (null, :appointment_title, :start, :end, :allDay, :patient_id, :appointment_status)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d');
        $status = "Pending";
        $stmt->bindParam(':appointment_title', $appointment->appointment_title);
        $stmt->bindParam(':start', $appointment->start);
        $stmt->bindParam(':end', $appointment->end);
        $stmt->bindParam(':allDay', $appointment->allDay);
        $stmt->bindParam(':patient_id', $appointment->patient_id);
        $stmt->bindParam(':appointment_status', $status);


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

    case "PUT":
        $appointment = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE appointments SET appointment_title= :appointment_title, start=:start, end=:end, allDay=:allDay 
                WHERE appointment_id = :appointment_id";
        $stmt = $conn->prepare($sql);
        $updated_at = date('Y-m-d');
        $stmt->bindParam(':appointment_id', $appointment->appointment_id);
        $stmt->bindParam(':appointment_title', $appointment->appointment_title);
        $stmt->bindParam(':start', $appointment->start);
        $stmt->bindParam(':end', $appointment->end);
        $stmt->bindParam(':allDay', $appointment->allDay);

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
        $sql = "DELETE FROM appointments WHERE appointment_id = :id";
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $path[3]);

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
