<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    trigger_error("Debugging - post content here" . var_dump($_POST));
    $course_id = $_POST["course_id"];
    $difficulty = $_POST["difficulty"];
    $name = $_POST["name"];
    $description = $_POST["description"];
    $func_name = $_POST["func_name"];
    $func_declaration = $_POST["func_declaration"];
    $num_tests = $_POST["num_tests"];
    $studentTemplate = trim(isset($_POST["student_template"]) ? html_entity_decode($_POST["student_template"], ENT_QUOTES, 'UTF-8') : '');
    $teacherSolution = trim(isset($_POST["teacher_solution"]) ? html_entity_decode($_POST["teacher_solution"], ENT_QUOTES, 'UTF-8') : '');

    $testCasesArr = [];
    $answersArr = [];

    try {
        require_once "sessionConfig.php";
        require_once "dbHandler.php";
        require_once "utils.php";
        require_once "isInputEmpty.php";


        //ERROR HANDLERS
        $error = "";

        for ($i = 0; $i < intval($num_tests); $i++) {

            //Add the test cases to the array
            $testCasesArr[] = $_POST["test" . $i];

            //Check if the test cases were filled
            if ($testCasesArr[$i] == "") {
                $error = "Test cases were not filled correctly!";
                break;
            }

            //Add the answers to the array
            $answersArr[] = $_POST["answer" . $i];

            //Check if answers were filled
            if ($answersArr[$i] == "") {
                $error = "Answers were not filled correctly!";
                break;
            }
        }

        if (utils\isInputEmpty($difficulty, $name, $description, $func_name, $func_declaration, $num_tests, $studentTemplate, $teacherSolution)) {
            $error = "Fill in all fields, please!";
        } else {
            $position = strpos($func_declaration, '(');
            $position2 = strpos($func_declaration, ')');
            $position3 = strpos($func_declaration, ' ');

            if ($position == false || $position2 == false || $position3 == false || $position > $position2) {
                $error = "Incorrect function declaration!";

            } else {
                // Extract the substring before "("
                $result = substr($func_declaration, $position3 + 1, $position - ($position3 + 1));
                if ($result != $func_name) {
                    $error = "Function name must match the name in declaration!";
                }
            }
        }
        //TO DO but prob wont do it: check if the provided function successfully with the test cases and answers

        if (!$error) //if there were no errors
        {
            //Build test cases and answers
            $testCases = "";
            $answers = "";
            for ($i = 0; $i < intval($num_tests) - 1; $i++) {
                //Add test case
                $testCases = $testCases . $testCasesArr[$i] . "@@@";

                //Add answer
                $answers = $answers . $answersArr[$i] . "@@@";
            }

            //Add final test case ans answer
            $testCases = $testCases . $testCasesArr[$num_tests - 1];
            $answers = $answers . $answersArr[$num_tests - 1];


            $dbHandler->createCourseTask($name, $description, $func_name, $func_declaration, $testCases, $answers, $course_id, $difficulty, $studentTemplate, $teacherSolution);
            header('Location: ../course.php?id=' . $course_id . '$addTask=success');
            die(); //Kill the script

        } else //if there were errors
        {
            $_SESSION["add_task_error"] = $error;

            header('Location: ../addTaskPage.php?course_id=' . $course_id . '&addTask=fail'); //Redirect the user to the home page
            die();

        }
    } catch (mysqli_sql_exception $e) {
        die("Query failed: " . $e->getMessage());
    } catch (Exception $e) {
        die("Query failed: " . $e->getMessage());
    }
} else {
    header('Location: ../index.php'); //Redirect the user to home page
    die();
}


