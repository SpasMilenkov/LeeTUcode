<?php include_once "components/head.php"; ?>

<body>
    <?php
    include_once "include/dbHandler.php";
    include_once "include/Course.php";
    require_once "include/isInputEmpty.php";
    include_once "components/header.php";

    //Check if course id is set
    if (!isset($_POST["course_id"])) {
        echo "Error: No course id ;/";
        die();
    }
    //The raw course array from db
    $courseArray = $dbHandler->getCourseById($_POST["course_id"]);

    //Course object
    $course = new Course($courseArray["id"], $courseArray["name"], $courseArray["requirements"], $courseArray["description"], $courseArray["creator_id"]);
    ?>
    <main>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($user) && $course->getCreatorID() == $user->getID()) { ?>
            <?php
            $course_id = $_POST["course_id"];
            $difficulty = $_POST["difficulty"];
            $name = $_POST["name"];
            $description = $_POST["description"];
            $func_name = $_POST["func_name"];
            $func_declaration = $_POST["func_declaration"];
            $num_tests = $_POST["num_tests"];
            $student_template = $_POST["student_template"];
            $teacher_solution = $_POST["teacher_solution"];
            //ERROR HANDLERS
            $error = "";

            if (utils\isInputEmpty($difficulty, $name, $description, $func_name, $func_declaration, $num_tests)) {
                $error = "Fill in all fields, please!";
            }

            if ($error) //if there were errors
            {
                $_SESSION["test_cases_error"] = $error;

                header('Location: addTaskPage.php?course_id=' . $course_id . '&createCourse=fail'); //Redirect the user
                die();
            }
            ?>
            <div class="container" style="margin-top: 50px;">
                <div class="form-container border border-secondary rounded p-4">
                    <form id="addTaskForm" action="include/addTask.php" method="POST">
                        <div class="d-none">
                            <input type="text" class="form-control" name="course_id" id="course_id_input"
                                value="<?php echo $course_id; ?>" required>

                            <input type="text" class="form-control" name="difficulty" id="difficulty_input"
                                value="<?php echo $difficulty; ?>" required>

                            <input type="text" class="form-control" name="name" id="name_input"
                                value="<?php echo $name; ?>" required>

                            <input type="text" class="form-control" name="description" id="description_input"
                                value="<?php echo $description; ?>" required>

                            <input type="text" class="form-control" name="func_name" id="func_name_input"
                                value="<?php echo $func_name; ?>" required>

                            <input type="text" class="form-control" name="func_declaration" id="func_declaration_input"
                                value="<?php echo $func_declaration; ?>" required>

                            <input type="text" class="form-control" name="num_tests" id="num_tests_input"
                                value="<?php echo $num_tests; ?>" required>
                            <textarea name="student_template" id="student_template" rows="10" cols="50" required>
                                <?php echo htmlspecialchars($student_template ?? ''); ?>
                            </textarea>
                            <textarea name="teacher_solution" id="teacher_solution" rows="10" cols="50" required>
                                <?php echo htmlspecialchars($teacher_solution ?? ''); ?>
                            </textarea>
                        </div>
                        <?php for ($i = 0; $i < intval($num_tests); $i++) { ?>
                            <div class="row">
                                <div class="col-1"></div>
                                <div class="col-4">
                                    <label for="recipient-name" class="col-form-label">
                                        Test case
                                        <?php echo $i; ?>:
                                    </label>
                                    <input type="text" class="form-control" name="test<?php echo $i; ?>"
                                        id="test<?php echo $i; ?>_input" required>
                                </div>
                                <div class="col-2"></div>
                                <div class="col-4">
                                    <label for="recipient-name" class="col-form-label">
                                        Answer
                                        <?php echo $i; ?>:
                                    </label>
                                    <input type="text" class="form-control" name="answer<?php echo $i; ?>"
                                        id="answer<?php echo $i; ?>_input" required>
                                </div>
                                <div class="col-1"></div>
                            </div>
                        <?php } ?>
                        <div class="row mx-1 mb-5">
                            <label id="test_cases_error" class="col-form-label text-danger"></label>
                        </div>
                        <input type="Submit" name="submit" value="Finish task" class="btn btn-primary">
                    </form>
                </div>
            </div>
        <?php } else {
            echo "Error: Forbiden way of getting to this page!";
        } ?>
    </main>
    <?php include_once "components/footer.php" ?>
    <script>
        $(document).ready(function () {

            var errorContainer = $("#test_cases_error");
            var errorText = "";

            //Get the errors if any
            <?php if (isset($_SESSION["test_cases_error"])) { ?>
                errorText = "<?php echo $_SESSION["test_cases_error"]; ?>";
                <?php unset($_SESSION["test_cases_error"]);
            } ?>

            //Show the errors
            errorContainer.text(errorText);
        });
    </script>
</body>