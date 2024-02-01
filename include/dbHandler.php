<?php
include_once "utils.php";

class dbHandler
{
    private $mysqli;
    public function __construct()
    {
        try {
            $host = "127.0.0.1"; // If port 3306 is mapped to the host's port 3306
            $dbname = "leetucode";
            $dbusername = "root";
            $dbpassword = "admin"; // The MYSQL_ROOT_PASSWORD you set for the container


            $this->mysqli = new mysqli($host, $dbusername, $dbpassword, $dbname);
            if ($this->mysqli->connect_errno) {
                throw new Exception("Failed to connect to MySQL: " . $this->mysqli->connect_error);
            }
            mysqli_query($this->mysqli, "SET NAMES 'UTF8'");

        } catch (mysqli_sql_exception $e) {
            die("MySQL error: " . $e->getMessage());
        } catch (Exception $e) {
            die("General error: " . $e->getMessage());
        }

    }

    //=====================================================User=============================================
    public function createUser($username, $email, $pass, $accountType)
    {
        $hashedPassword = hashPassword($pass); //hash the password

        $myQuery = $this->mysqli->prepare("INSERT INTO users (username, email, pass, account_type) VALUES (?,?,?,?);"); //Prepare the sql query
        $myQuery->bind_param("ssss", $username, $email, $hashedPassword, $accountType); //bind the params in place of the '?'
        $myQuery->execute(); //Execute the statement
        $myQuery->close(); //Free/close the statement
    }

    public function getUserByCredentials($user_password, $user_name, $email)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM users WHERE (username = ? OR email = ?)");
        $myQuery->bind_param("ss", $user_name, $email);
        $myQuery->execute();

        $result = $myQuery->get_result();

        // Fetch the first row as an associative array
        $userArray = $result->fetch_assoc();

        $myQuery->close();

        $hashedPassword = $userArray["pass"]; //get the hashed password from the database

        if (password_verify($user_password, $hashedPassword)) {
            return $userArray;
        } else {
            return "Incorrect password";
        }


    }

    public function getUserByUsernaame($username)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM users WHERE username = ?");
        $myQuery->bind_param("s", $username);
        $myQuery->execute();

        $result = $myQuery->get_result();

        // Fetch the first row as an associative array
        $userArray = $result->fetch_assoc();

        $myQuery->close();

        return $userArray;
    }

    public function getUserById($id)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM users WHERE id = ?");
        $myQuery->bind_param("i", $id);
        $myQuery->execute();

        $result = $myQuery->get_result();

        // Fetch the first row as an associative array
        $userArray = $result->fetch_assoc();

        $myQuery->close();

        return $userArray;
    }

    function getUsername(string $username)
    {
        $fetchedUsername = "";
        $myQuery = $this->mysqli->prepare("SELECT username FROM users WHERE username = ?;"); //Prepare the sql query
        $myQuery->bind_param("s", $username); //bind the params in place of the '?'
        $myQuery->execute(); //Execute the statement

        // Bind the result to a variable
        $myQuery->bind_result($fetchedUsername);

        // Fetch the result
        $myQuery->fetch();
        $myQuery->close(); //Free/close the statement
        return $fetchedUsername;
    }

    function getEmail(string $email)
    {
        $fetchedEmail = "";
        $myQuery = $this->mysqli->prepare("SELECT email FROM users WHERE email = ?;"); //Prepare the sql query
        $myQuery->bind_param("s", $email); //bind the params in place of the '?'
        $myQuery->execute(); //Execute the statement

        // Bind the result to a variable
        $myQuery->bind_result($fetchedEmail);

        // Fetch the result
        $myQuery->fetch();
        $myQuery->close(); //Free/close the statement
        return $fetchedEmail;
    }

    function isUsernameTaken(string $username)
    {
        return (bool) $this->getUsername($username);
    }

    function isEmailRegistered(string $email)
    {
        return (bool) $this->getEmail($email);
    }

    function isPasswordCorrect(string $username, string $pass)
    {
        $user = $this->getUserByUsernaame($username);

        if ($user) //the user exists
        {
            $hashedPassword = $user["pass"]; //get the hashed password from the database

            return password_verify($pass, $hashedPassword);
        } else //the user does not exist
        {
            return false;
        }
    }
    //===============================================TaskSubmition==========================================
    public function createTaskSubmition($submited_function, $submition_status, $task_id, $user_id)
    {
        $myQuery = $this->mysqli->prepare("INSERT INTO task_submitions (submited_function, submition_status, task_id, user_id) VALUES (?,?,?,?);"); //Prepare the sql query
        $myQuery->bind_param("ssii", $submited_function, $submition_status, $task_id, $user_id); //bind the params in place of the '?'
        $myQuery->execute(); //Execute the statement
        $myQuery->close(); //Free/close the statement   
    }

    public function getTaskSubmitionsOfUserForTask($user_id, $task_id)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM task_submitions 
                WHERE user_id = ? AND task_id = ?");
        $myQuery->bind_param("ii", $user_id, $task_id);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $taskSubArray = [];
        while ($row = $result->fetch_assoc()) {
            $taskSubArray[] = $row;
        }

        $myQuery->close();

        return $taskSubArray;
    }

    public function getTaskSubmitionById($id)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM task_submitions 
                WHERE id = ?");
        $myQuery->bind_param("i", $id);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $taskSubArray = $result->fetch_assoc();

        print_r($taskSubArray); // Fetch the first row as an associative array 
        $myQuery->close();

        return $taskSubArray;
    }

    public function getLastInsertedTaskSubmition()
    {
        $result = $this->mysqli->query("SELECT * FROM task_submitions ORDER BY id DESC LIMIT 1");

        if ($result && $result->num_rows > 0) {
            $lastInsertedTask = $result->fetch_assoc();
            $result->free_result();
            return $lastInsertedTask;
        } else {
            return null; // No records found
        }
    }

    public function updateTaskSubmitionStatus($submition_status, $id)
    {
        $myQuery = $this->mysqli->prepare("UPDATE task_submitions SET submition_status = ? WHERE id = ?"); // Prepare the SQL query
        $myQuery->bind_param("si", $submition_status, $id); // Bind the parameters in place of the '?'
        $myQuery->execute(); // Execute the statement
        $myQuery->close(); // Free/close the statement
    }

    public function updateTaskSubmitionResponse($response, $id)
    {
        $myQuery = $this->mysqli->prepare("UPDATE task_submitions SET response = ? WHERE id = ?"); // Prepare the SQL query
        $myQuery->bind_param("si", $response, $id); // Bind the parameters in place of the '?'
        $myQuery->execute(); // Execute the statement
        $myQuery->close(); // Free/close the statement
    }

    public function hasUserSolvedTask($userID, $taskID)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM task_submitions 
        WHERE user_id = ? AND task_id = ? AND submition_status = 'success';");
        $myQuery->bind_param("ii", $userID, $taskID);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $hasSolved = $result->num_rows > 0;

        $myQuery->close();

        return $hasSolved;
    }

    //==============================================CourseTask==============================================
    public function createCourseTask($name,
                                     $description,
                                     $function_name,
                                     $function_declaration,
                                     $test_cases,
                                     $test_answers,
                                     $course_id,
                                     $difficulty,
                                     $student_template,
                                     $teacher_solution)
    {
        $myQuery = $this->mysqli->prepare("INSERT INTO course_tasks (name, description, function_name, function_declaration, test_cases, test_answers, course_id, difficulty, student_template, teacher_solution) VALUES (?,?,?,?,?,?,?,?,?,?);"); //Prepare the sql query
        $myQuery->bind_param("ssssssisss", $name, $description, $function_name, $function_declaration, $test_cases, $test_answers, $course_id, $difficulty, $student_template, $teacher_solution); //bind the params in place of the '?'
        $myQuery->execute(); //Execute the statement
        $myQuery->close(); //Free/close the statement         
    }

    public function getCourseTasksByCourseId($id)
    { //Returns all tasks from course
        $myQuery = $this->mysqli->prepare("SELECT * FROM course_tasks 
                WHERE course_id = ?");
        $myQuery->bind_param("i", $id);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $courseTasksArray = [];

        while ($row = $result->fetch_assoc()) // Fetch the result as an associative array 
        {
            $courseTasksArray[] = $row;
        }
        $myQuery->close();

        return $courseTasksArray;
    }

    public function getCourseTaskByCourseId($name, $id)
    { //Get task from course with specific name
        $myQuery = $this->mysqli->prepare("SELECT * FROM course_tasks 
                WHERE course_id = ? AND name = ?");
        $myQuery->bind_param("is", $id, $name);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $courseTasksArray = $result->fetch_assoc();

        print_r($courseTasksArray); // Fetch the first row as an associative array 
        $myQuery->close();

        return $courseTasksArray;
    }

    public function getCourseTaskById($id)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM course_tasks 
                WHERE id = ?");
        $myQuery->bind_param("i", $id);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $courseTasksArray = $result->fetch_assoc();

        $myQuery->close();

        return $courseTasksArray;
    }

    //=============================================CourseMember=============================================
    public function joinCourse($course_id, $user_id)
    {
        $myQuery = $this->mysqli->prepare("INSERT INTO course_members (course_id, user_id) VALUES (?,?);");
        //Prepare the sql query
        $myQuery->bind_param("ii", $course_id, $user_id); //bind the params in place of the '?'
        $myQuery->execute(); //Execute the statement
        $myQuery->close(); //Free/close the statement 
    }

    public function leaveCourse($course_id, $user_id)
    {
        $myQuery = $this->mysqli->prepare("DELETE FROM course_members WHERE course_id = ? AND user_id = ?");
        // Prepare the SQL query
        $myQuery->bind_param("ii", $course_id, $user_id); // Bind the params in place of the '?'
        $myQuery->execute(); // Execute the statement
        $myQuery->close(); // Free/close the statement
    }

    public function getMembersOfCourse($course_id)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM course_members 
                WHERE course_id = ?");
        $myQuery->bind_param("i", $course_id);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $courseMembersArray = [];
        while ($row = $result->fetch_assoc()) {
            $courseMembersArray[] = $this->getUserById($row["user_id"]);
        }
        
        $myQuery->close();

        return $courseMembersArray;
    }

    public function getCoursesJoinedByUser($user_id)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM course_members 
                WHERE user_id = ?");
        $myQuery->bind_param("i", $user_id);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $joindCoursesArray = [];
        while ($row = $result->fetch_assoc()) {
            $joindCoursesArray[] = $this->getCourseById($row["course_id"]);
        }

        $myQuery->close();

        return $joindCoursesArray;
    }

    public function getCourseMemberById($id)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM course_members 
                WHERE id = ?");
        $myQuery->bind_param("i", $id);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $courseMembersArray = $result->fetch_assoc();

        $myQuery->close();

        return $courseMembersArray;
    }

    public function hasUserJoinedCourse($userID, $courseID)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM course_members 
                WHERE user_id = ? AND course_id = ?");
        $myQuery->bind_param("ii", $userID, $courseID);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $hasJoined = $result->num_rows > 0;

        $myQuery->close();

        return $hasJoined;
    }
    //==============================================Course==================================================
    public function createCourse($name, $requirements, $description, $creator_id)
    {
        $myQuery = $this->mysqli->prepare("INSERT INTO courses (name, requirements, description, creator_id) VALUES (?,?,?,?);");
        // Prepare the SQL query
        $myQuery->bind_param("sssi", $name, $requirements, $description, $creator_id);
        // Bind the params in place of the '?'
        $myQuery->execute(); // Execute the statement

        // Get the ID of the last inserted row
        $lastInsertedId = $myQuery->insert_id;

        // Close the statement
        $myQuery->close();

        // Return the ID of the newly created course
        return $lastInsertedId;
    }

    public function getCourses()
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM courses");
        $myQuery->execute();
        $result = $myQuery->get_result();

        $courseArray = [];

        while ($row = $result->fetch_assoc()) {
            // Fetch result as an associative array and append to $courseArray
            $courseArray[] = $row;
        }

        $myQuery->close();

        return $courseArray;
    }

    public function getCoursesByCreatorId($id)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM courses 
                WHERE creator_id = ?");
        $myQuery->bind_param("i", $id);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $courseArray = [];

        while ($row = $result->fetch_assoc()) {
            // Fetch result as an associative array and append to $courseArray
            $courseArray[] = $row;
        }

        $myQuery->close();

        return $courseArray;
    }

    public function getCourseFromCreator($id, $name)
    { //Get course from specific creator with specific name 
        $myQuery = $this->mysqli->prepare("SELECT * FROM courses 
                WHERE creator_id = ? AND name = ?");
        $myQuery->bind_param("is", $id, $name);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $courseArray = $result->fetch_assoc();

        $myQuery->close();

        return $courseArray;
    }

    public function getCourseById($id)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM courses 
                WHERE id = ?");
        $myQuery->bind_param("i", $id);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $courseArray = $result->fetch_assoc();

        $myQuery->close();

        return $courseArray;
    }

    public function getCourseByName($name)
    {
        $myQuery = $this->mysqli->prepare("SELECT * FROM courses 
                WHERE name = ?");
        $myQuery->bind_param("s", $name);
        $myQuery->execute();
        $result = $myQuery->get_result();

        $courseArray = $result->fetch_assoc();

        $myQuery->close();

        return $courseArray;
    }

    function isCourseNameTaken($course_name)
    {
        return (bool) $this->getCourseByName($course_name);
    }

}

// Report all mysqli errors as exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$dbHandler = new dbHandler();
//$dbHandler->createUser("student2", "student2@example.com", "1234", "student");

//==============================================Course==================================================
//$dbHandler->createCourse("Busted course 2","ludak2 :)", "za ludaci2", 1); //Test course creation
//$dbHandler->getCoursesByCreatorId(1); //Test getCoursesByCreatorId
//$dbHandler->getCourseFromCreator(1, "Busted course 2"); //Test getCourseFromCreator
//$dbHandler->getCourseById(1); Test get course by id

//==============================================CourseTask==================================================
//$dbHandler->createCourseTask("busted task3", "napishi busted funkciq", "bustedFunc", "int bustedFunc(int a, int b)","1,2@@@2,5@@@5,5", "3@@@7@@@10", 1, "easy");
//$dbHandler->getCourseTasksByCourseId(1);
//$dbHandler->getCourseTaskFromCourseWithId("mega task", 2);
//$dbHandler->getCourseTaskById(3);

//===============================================TaskSubmition==========================================
//$dbHandler->createTaskSubmition("int bustedFunc(int a, int b) {return a+b;}", "fail", 1, 1);
//$dbHandler->getTaskSubmitionsOfUserForTask(2,1);
//$dbHandler->getTaskSubmitionById(1);
//$dbHandler->updateTaskSubmitionStatus("success", 1);

//=============================================CourseMember=============================================
//$dbHandler->createCourseMember(1,3);
//$dbHandler->getMembersOfCourse(1);
//$dbHandler->getCoursesJoinedByUser(1);





