<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":



        if (isset($_GET['question_id'])) {
            $question_specific = $_GET['question_id'];
            $sql = "SELECT question_id AS id, question_text AS title, question_type FROM questions WHERE question_id = :question_id ";
        }

        if (!isset($_GET['question_id'])) {
            $sql = "SELECT question_id AS id, question_text AS title, question_type FROM questions ORDER BY question_id DESC";
        }

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            if (isset($question_specific)) {
                $stmt->bindParam(':question_id', $question_specific);
            }



            $stmt->execute();
            $question = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($question);
        }



        break;

    case "POST":
        $question = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO questions (question_id, question_text, question_type) 
                VALUES (null, :question_text, :question_type)";
        $stmt = $conn->prepare($sql);
        // $created_at = date('Y-m-d');
        $stmt->bindParam(':question_text', $question->question_text);
        $stmt->bindParam(':question_type', $question->question_type);



        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "question created successfully"
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
        $question = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE questions SET question_text= :question_text, question_type=:question_type
                WHERE question_id = :question_id";
        $stmt = $conn->prepare($sql);
        $updated_at = date('Y-m-d');
        $stmt->bindParam(':question_id', $question->question_id);
        $stmt->bindParam(':question_text', $question->question_text);
        $stmt->bindParam(':question_type', $question->question_type);


        if ($stmt->execute()) {

            $response = [
                "status" => "success",
                "message" => "question updated successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "question update failed"
            ];
        }

        echo json_encode($response);
        break;

    case "DELETE":
        $sql = "DELETE FROM questions WHERE question_id = :id";
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $path[3]);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "Question deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "Question  deletion failed"
            ];
        }
}
