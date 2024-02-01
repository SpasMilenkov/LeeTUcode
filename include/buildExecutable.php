<?php
include_once "sessionConfig.php";
include_once "dbHandler.php";
include_once "courseTask.php";


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["solution"]) && isset($_POST["taskId"])) {
        $functionImplementation = htmlspecialchars_decode($_POST["solution"], ENT_NOQUOTES);
        $taskId = $_POST["taskId"];

        $taskArray = $dbHandler->getCourseTaskById($taskId);

        if ($taskArray) {
            $task = new CourseTask(
                $taskArray["id"],
                $taskArray["name"],
                $taskArray["description"],
                $taskArray["function_name"],
                $taskArray["function_declaration"],
                explode("@@@", $taskArray["test_cases"]),
                explode("@@@", $taskArray["test_answers"]),
                $taskArray["course_id"],
                $taskArray["difficulty"],
                $functionImplementation,
                $taskArray["teacher_solution"]
            );

            // Building the full C++ file with the user's function implementation
            $builtCppFile = $task->addSubmition($functionImplementation);

            $_SESSION['cppFile'] = $builtCppFile;

            // Upload the task submission to the database
            $dbHandler->createTaskSubmition($functionImplementation, "fail", $task->getId(), $_SESSION["user_id"]);
            $insertedTask = $dbHandler->getLastInsertedTaskSubmition();

            header('Location: ../taskSubmitionPage.php?id=' . $insertedTask['id']);
            exit();
        } else {
            echo "Task not found!";
        }
    } else {
        echo "Solution or task ID not provided!";
    }
} else {
    echo "Invalid request method!";
}