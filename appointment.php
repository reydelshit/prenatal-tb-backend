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
            FROM appointments
            WHERE patient_id = :patient_id
              AND CURDATE() >= appointments.start
              AND CURDATE() <= appointments.end";
        }

        if (isset($_GET['patient_id']) && isset($_GET['next_appointment'])) {
            $patient_id_next_appointment = $_GET['patient_id'];
            $sql = "SELECT *
            FROM appointments
            WHERE patient_id = :patient_id
              AND appointments.start >= CURRENT_TIMESTAMP
            ORDER BY appointments.start
            LIMIT 1";
        }

        if (isset($_GET['patient_id']) && isset($_GET['all_appointments'])) {
            $patient_id_all_appointment = $_GET['patient_id'];
            $sql = "SELECT appointment_id AS id, appointment_title AS title, start, end, allDay FROM appointments WHERE patient_id = :patient_id";
        }

        if (!isset($_GET['patient_id'])) {
            $sql = "SELECT appointment_id AS id, appointment_title AS title, start, end, allDay FROM appointments";
        }
        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            if (isset($patient_id_specific)) {
                $stmt->bindParam(':patient_id', $patient_id_specific);
            }

            if (isset($patient_id_next_appointment)) {
                $stmt->bindParam(':patient_id', $patient_id_next_appointment);
            }

            if (isset($patient_id_all_appointment)) {
                $stmt->bindParam(':patient_id', $patient_id_all_appointment);
            }

            $stmt->execute();
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($appointments);
        }


        break;

    case "POST":
        $appointment = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO appointments (appointment_id, appointment_title, start, end, allDay, patient_id, :appointment_status) 
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
