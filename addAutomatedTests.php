<?php
include_once "components/head.php";
include_once "include/dbHandler.php";
include_once "include/Course.php";
include_ONCE "include/TestGenerator.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//The raw course array from db
$courseArray = $dbHandler->getCourseById($_POST["course_id"]);

//Course object
$course = new Course($courseArray["id"], $courseArray["name"], $courseArray["requirements"], $courseArray["description"], $courseArray["creator_id"]);

function getParameterTypes($funcDeclaration) {
    $pattern = '/\(([^)]*)\)/'; // Matches everything inside the parentheses
    $paramTypes = [];

    if (preg_match($pattern, $funcDeclaration, $matches)) {
        $params = explode(',', $matches[1]);
        foreach ($params as $param) {
            $typePattern = '/(?:const\s+)?([a-zA-Z0-9_:]+)(?:<([a-zA-Z0-9_:]+)>)?/';
            if (preg_match($typePattern, trim($param), $typeMatches)) {
                $paramTypes[] = !empty($typeMatches[2]) ? $typeMatches[1] . '<' . $typeMatches[2] . '>' : $typeMatches[1];
            }
        }
    }
    return $paramTypes;
}
function generateMultipleTestCases($funcDeclaration, $numTests, $maxVectorListSize = 30, $maxInt = 100, $maxStringLength = 10) {
    $testGen = new TestGenerator();
    $allTestCases = [];
    $paramTypes = getParameterTypes($funcDeclaration);

    $typeToGenerator = [
        'int' => 'generateInt',
        'float' => 'generateFloat',
        'double' => 'generateFloat',
        'char' => 'generateChar',
        'bool' => 'generateBool',
        'std::string' => 'generateString',
        'std::vector<int>' => 'generateContainer',
        'std::list<int>' => 'generateContainer',
    ];

    for ($i = 0; $i < $numTests; $i++) {
        $testCases = [];
        foreach ($paramTypes as $type) {
            $generator = $typeToGenerator[$type] ?? null;
            if ($generator && method_exists($testGen, $generator)) {
                if ($type === 'std::vector<int>' || $type === 'std::list<int>') {
                    $testCases[] = $testGen->$generator('Int', $maxVectorListSize, 0, $maxInt);
                }
                else if ($type === 'std::string') {
                    $testCases[] = $testGen->generateString(5, 15);
                }
                else {
                    $testCases[] = $testGen->$generator(0, $maxInt, $maxStringLength);
                }
            }
        }
        $allTestCases[] = implode(', ', $testCases);
    }

    return $allTestCases;
}



// Ensure all expected POST data is present
$expectedPostFields = ["course_id", "difficulty", "name", "description", "func_name", "func_declaration", "num_tests", "student_template", "teacher_solution"];
foreach ($expectedPostFields as $field) {
    if (!isset($_POST[$field])) {
        $_SESSION["test_cases_error"] = "Error: Missing required field $field.";
        header('Location: addTaskPage.php?course_id=' . $_POST["course_id"] . '&createCourse=fail'); // Adjust the redirection URL as needed
        exit();
    }
}

// Retrieve course data
$courseArray = $dbHandler->getCourseById($_POST["course_id"]);
$course = new Course($courseArray["id"], $courseArray["name"], $courseArray["requirements"], $courseArray["description"], $courseArray["creator_id"]);

// Collect form data
$course_id = $_POST["course_id"];
$difficulty = $_POST["difficulty"];
$name = $_POST["name"];
$description = $_POST["description"];
$func_name = $_POST["func_name"];
$func_declaration = $_POST["func_declaration"];
$num_tests = $_POST["num_tests"];
$student_template = $_POST["student_template"];
$teacher_solution = $_POST["teacher_solution"];

// Initialize the array to hold all test cases
$testCases = [];

$testCases = generateMultipleTestCases($func_declaration, $num_tests);

$maxVectorSize = $_POST["maxVectorSize"] ?? 30;
$maxIntValue = $_POST["maxIntValue"] ?? 100;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["generateTestCases"])) {

    $maxVectorSize = $_POST["maxVectorSize"] ?? 30;
    $maxIntValue = $_POST["maxIntValue"] ?? 100;

    $testCases = generateMultipleTestCases($func_declaration, $num_tests, $maxVectorSize, $maxIntValue);
}

?>

<body>
<?php include_once "components/header.php"; ?>

<main>
    <div class="container" style="margin-top: 50px;">
        <div class="form-container border border-secondary rounded p-4">
            <!-- Separate form for test case generation -->
            <form id="testGenerationForm" action="addAutomatedTests.php" method="POST">
                <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>">
                <input type="hidden" name="difficulty" value="<?php echo htmlspecialchars($difficulty); ?>">
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($name); ?>">
                <input type="hidden" name="description" value="<?php echo htmlspecialchars($description); ?>">
                <input type="hidden" name="func_name" value="<?php echo htmlspecialchars($func_name); ?>">
                <input type="hidden" name="func_declaration" value="<?php echo htmlspecialchars($func_declaration); ?>">
                <input type="hidden" name="num_tests" value="<?php echo htmlspecialchars($num_tests); ?>">
                <input type="hidden" name="student_template" value="<?php echo htmlspecialchars($student_template); ?>">
                <input type="hidden" name="teacher_solution" value="<?php echo htmlspecialchars($teacher_solution); ?>">
                <div class="form-group">
                    <label for="maxVectorSize">Max Vector Size:</label>
                    <input type="number" class="form-control" name="maxVectorSize" id="maxVectorSize" value="<?php echo htmlspecialchars($maxVectorSize); ?>" required>
                </div>
                <div class="form-group">
                    <label for="maxIntValue">Max Integer Value:</label>
                    <input type="number" class="form-control" name="maxIntValue" id="maxIntValue" value="<?php echo htmlspecialchars($maxIntValue); ?>" required>
                </div>
                <input type="hidden" name="generateTestCases" value="1"> <!-- Hidden input to check if test cases should be generated -->
                <button type="submit" class="btn btn-secondary mt-3">Generate Test Cases</button>
            </form>
            <form id="addAutomatedTestsForm" action="include/addTask.php" method="POST">
                <!-- Include all previous form data as hidden fields -->
                <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>">
                <input type="hidden" name="difficulty" value="<?php echo htmlspecialchars($difficulty); ?>">
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($name); ?>">
                <input type="hidden" name="description" value="<?php echo htmlspecialchars($description); ?>">
                <input type="hidden" name="func_name" value="<?php echo htmlspecialchars($func_name); ?>">
                <input type="hidden" name="func_declaration" value="<?php echo htmlspecialchars($func_declaration); ?>">
                <input type="hidden" name="num_tests" value="<?php echo htmlspecialchars($num_tests); ?>">
                <input type="hidden" name="student_template" value="<?php echo htmlspecialchars($student_template); ?>">
                <input type="hidden" name="teacher_solution" value="<?php echo htmlspecialchars($teacher_solution); ?>">

                <!-- Display editable fields for test cases -->
                <?php foreach ($testCases as $index => $testCase) : ?>
                    <div class="form-group">
                        <label for="test_case_<?php echo $index; ?>">Test Case <?php echo $index + 1; ?>:</label>
                        <input type="text" class="form-control" name="test<?php echo $index; ?>" id="test<?php echo $index; ?>" value="<?php echo htmlspecialchars($testCase); ?>" required>
                        <input type="hidden" class="form-control" name="answer<?php echo $index; ?>" id="test<?php echo $index; ?>" value="automatic_test_no_answer_provided" required>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary mt-3">Submit Task</button>
            </form>
        </div>
    </div>
</main>

<?php include_once "components/footer.php"; ?>
</body>