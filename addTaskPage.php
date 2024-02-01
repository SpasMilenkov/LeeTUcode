<?php include_once "components/head.php"; ?>

<body>
    <?php
    include_once "include/dbHandler.php";
    include_once "include/Course.php";
    include_once "components/header.php";
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    //The raw course array from db
    $courseArray = $dbHandler->getCourseById($_GET["course_id"]);

    //Course object
    $course = new Course($courseArray["id"], $courseArray["name"], $courseArray["requirements"], $courseArray["description"], $courseArray["creator_id"]);
    ?>
    <main>
        <?php
        if (isset($user) && $course->getCreatorID() == $user->getID()) { ?>

            <div class="container" style="margin: 50px auto;">
                <div class="form-container border border-secondary rounded p-4">
                    <form id="addTaskForm" action="addTestCasesPage.php" method="POST">
                        <div class="d-none">
                            <input type="number" class="form-control" name="course_id" id="course_id_input"
                                value="<?php echo $_GET["course_id"]; ?>" required>
                        </div>
                        <div class="row mx-1">
                            <label for="recipient-name" class="col-form-label">Difficulty:</label>
                            <div class="col-2">
                                <select name="difficulty" id="difficulty_input" required>
                                    <option value="easy" selected>Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mx-1">
                            <label for="recipient-name" class="col-form-label">Task name:</label>
                            <input type="text" class="form-control" name="name" id="name_input" required>
                        </div>
                        <div class="row mx-1">
                            <label for="recipient-name" class="col-form-label">Description:</label> <br>
                            <textarea name="description" id="description_input" placeholder="Description"
                                required></textarea>
                        </div>
                        <div class="row mx-1">
                            <label for="recipient-name" class="col-form-label">Function name:</label>
                            <input type="text" class="form-control" name="func_name" id="func_name_input" required>
                        </div>
                        <div class="row mx-1">
                            <label for="recipient-name" class="col-form-label">Function declaration:</label>
                            <input type="text" class="form-control" name="func_declaration" id="func_declaration_input" required>
                        </div>
                        <div class="row mx-1">
                            <label for="student_template" class="col-form-label">Student Template:</label> <br>
                            <textarea name="student_template" id="student_template" rows="10" cols="50" placeholder="Student template code" required></textarea>
                        </div>
                        <div class="row mx-1">
                            <label for="teacher_solution" class="col-form-label">Teacher's Solution:</label> <br>
                            <textarea name="teacher_solution" id="teacher_solution" rows="10" cols="50" placeholder="Teacher's solution code" required></textarea>
                        </div>
                        <div class="row mx-1">
                            <label for="recipient-name" class="col-form-label">Number of tests:</label>
                            <input type="number" class="form-control" name="num_tests" id="num_tests_input" step="1" min="0" max="50" required>
                        </div>
                        <div class="row mx-1 mb-5">
                            <label id="add_task_error" class="col-form-label text-danger"></label>
                        </div>
                        <input type="Submit" name="submit" value="Add test cases" class="btn btn-primary" onclick="setFormAction('addTestCasesPage.php');">
                        <input type="Submit" value="Generate Automatic Tests" class="btn btn-secondary" onclick="setFormAction('addAutomatedTests.php');">
                    </form>
                </div>
            </div>
        <?php } else {
            echo "You must be the course owner to add a task :)!";
        } ?>
    </main>
    <?php include_once "components/footer.php" ?>
    <script>
        function setFormAction(action) {
            document.getElementById('addTaskForm').action = action;
        }
        $(document).ready(function () {

            var errorContainer = $("#add_task_error");
            var errorText = "";

            //Get the errors if any
            <?php if (isset($_SESSION["add_task_error"])) { ?>
                errorText = "<?php echo $_SESSION["add_task_error"]; ?>";
                <?php unset($_SESSION["add_task_error"]);
            } ?>

            //Show the errors
            errorContainer.text(errorText);
        });
    </script>
</body>