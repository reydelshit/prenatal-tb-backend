<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['patient_id'])) {
            $patient_id_specific = $_GET['patient_id'];
            $sql = "SELECT * FROM medication WHERE patient_id = :patient_id";
        }
        if (!isset($_GET['patient_id'])) {
            $sql = "SELECT * FROM medication ORDER BY patient_id DESC";
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
        $patient_medication = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO medication (patient_name, medication_name, dosage, frequency, start, end, created_at, description, patient_id) VALUES (:patient_name, :medication_name, :dosage, :frequency, :start, :end, :created_at, :description, :patient_id)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d H:i:s');
        $stmt->bindParam(':patient_name', $patient_medication->patient_name);
        $stmt->bindParam(':medication_name', $patient_medication->medication_name);
        $stmt->bindParam(':dosage', $patient_medication->dosage);

        $stmt->bindParam(':frequency', $patient_medication->frequency);
        $stmt->bindParam(':start', $patient_medication->start);
        $stmt->bindParam(':end', $patient_medication->end);

        $stmt->bindParam(':description', $patient_medication->description);
        $stmt->bindParam(':patient_id', $patient_medication->patient_id);

        $stmt->bindParam(':created_at', $created_at);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "patient notification sent successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "patient notification sent failed"
            ];
        }

        echo json_encode($response);
        break;
}
