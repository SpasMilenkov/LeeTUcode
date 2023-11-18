<?php
include_once "sessionConfig.php";
include_once "courseTask.php";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $file_contents = "";
    if (isset($_FILES["submition_file"]) && $_FILES["submition_file"]["error"] == 0) {
        $file_name = $_FILES["submition_file"]["name"];
        $file_tmp = $_FILES["submition_file"]["tmp_name"];

        // Check if the file has a .txt extension
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        if ($file_extension == "txt") {
            // Read the contents of the file
            $file_contents = file_get_contents($file_tmp);

            $serializedTask = $_POST['serializedTask'];
            $task = unserialize(base64_decode($serializedTask));
            $builtCppFile = $task->addSubmition($file_contents);
            $_SESSION['cppFile'] = $builtCppFile;

            header("Location: ../taskSubmition.php");
            exit();

        } else {
            echo "Only txt files can be submited!";
        }

    } else {
        echo "File error!";
    }
} else {
    echo "Invalid way of getting to this page!";
}