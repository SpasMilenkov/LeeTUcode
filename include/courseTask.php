<?php
include_once "taskSubmition.php";
class CourseTask
{

    private $_id = -1;
    private $_name ="";
    private $_description ="";
    private $_functionName = "";
    private $_functionDeclaration = "";
    private $_testCases = [];
    private $_testAnswers = [];
    private $_course_id = -1;
    private $_difficulty = "";
    private $_baseCppCode = ""; // Renamed for clarity
    private $_cppCode = ""; // Renamed for clarity
    private $_solution = "";


    function __construct($id,
                         $name,
                         $description,
                         $functionName,
                         $functionDeclaration,
                         $testCases,
                         $testAnswers,
                         $course_id,
                         $difficulty,
                         $baseCppCode,
                         $solution
    )
    {
        $this->_id = $id;
        $this->_name = $name;
        $this->_description = $description;
        $this->_functionName = $functionName;
        $this->_functionDeclaration = $functionDeclaration;
        $this->_testCases = $testCases;
        $this->_testAnswers = $testAnswers;
        $this->_course_id = $course_id;
        $this->_difficulty = $difficulty;
        $this->_baseCppCode = $baseCppCode;
        $this->_solution = $solution;
        // Create the cpp code (without the function implementation)
        $this->createCppCode();
    }


//      deprecated
//    public function addSubmition($functionImplementation)
//    {
//        // Decode HTML entities in the function implementation
//        $decodedFunctionImplementation = htmlspecialchars_decode($functionImplementation, ENT_QUOTES);
//
//        // Create the program skeleton
//        $programToCompile = $this->createProgramSkeleton();
//
//        // Append a semicolon to the function declaration and replace the placeholder
//        $functionDeclarationWithSemicolon = $this->_functionDeclaration . ';';
//        $programToCompile = str_replace("%%funDeclaration%%", $functionDeclarationWithSemicolon, $programToCompile);
//
//
//        // Replace the function implementation placeholder
//        $programToCompile = str_replace("%%funcImplementation%%", $decodedFunctionImplementation, $programToCompile);
//
//        // Build and replace the function tests
//        $funcTests = $this->buildFuncTests();
//        return str_replace("%%testCode%%", $funcTests, $programToCompile);
//    }
    public function addSubmition($functionImplementation)
    {
        // Decode HTML entities in the function implementation
        $decodedFunctionImplementation = htmlspecialchars_decode($functionImplementation, ENT_QUOTES);

        // Create the program skeleton
        $programToCompile = $this->createProgramSkeleton();

        // Append a semicolon to the function declaration and replace the placeholder
        $functionDeclarationWithSemicolon = $this->_functionDeclaration . ';';
        $programToCompile = str_replace("%%funDeclaration%%", $functionDeclarationWithSemicolon, $programToCompile);

        // Replace the student function implementation placeholder
        $programToCompile = str_replace("%%studentFuncImplementation%%", $decodedFunctionImplementation, $programToCompile);

        // Replace the teacher function implementation placeholder
        // Assuming teacher's function implementation is available in $_solution
        $programToCompile = str_replace("%%teacherFuncImplementation%%", $this->_solution, $programToCompile);

        // Build and replace the function tests
        $funcTests = $this->buildFuncTests();
        return str_replace("%%testCode%%", $funcTests, $programToCompile);
    }

    //Creates the cpp file only missing the function implementation
    private function createCppCode()
    {
        // Use the base code
        $this->_cppCode = $this->_baseCppCode;

        // Replace the function declaration
        $this->_cppCode = str_replace("%%funDeclaration%%", $this->_functionDeclaration, $this->_cppCode);

        // Replace the function tests
        $funcTests = $this->buildFuncTests();
        $this->_cppCode = str_replace("%%testCode%%", $funcTests, $this->_cppCode);
    }
    //deprecated
//    private function buildFuncTests()
//    {
//        $funcTests = "";
//        $idx = 0;
//        foreach ($this->_testCases as $case) {
//            $funcTests = $funcTests . "if(" . $this->_functionName . "(" . $case . ") !=" . $this->_testAnswers[$idx] . "){\n";
//            $funcTests = $funcTests . 'std::cout << "Input: " << "' . $case . '" << std::endl;' . "\n";
//            $funcTests = $funcTests . 'std::cout << "Your answer: " << ' . $this->_functionName . "(" . $case . ") << std::endl;\n";
//            $funcTests = $funcTests . 'std::cout << "Expected answer: " << ' . $this->_testAnswers[$idx] . " << std::endl;\n";
//            $funcTests = $funcTests . "return 0;\n";
//            $funcTests = $funcTests . "}\n\n";
//            $idx++;
//        }
//
//        return $funcTests;
//    }
    private function buildFuncTests()
    {
        $funcTests = "";
        foreach ($this->_testCases as $idx => $case) {
            // Generate the call to the student's and teacher's functions
            $studentCall = "student::" . $this->_functionName . "(" . $case . ")";
            $teacherCall = "teacher::" . $this->_functionName . "(" . $case . ")";
            $formattedCase = $this->formatTestCaseForCpp($case);
            // Generate the test code
            $funcTests .= "if(" . $studentCall . " != " . $teacherCall . ") {\n";
            $funcTests .= '    std::cout << "Input: ' . $formattedCase . ' - Test Case ' . ($idx + 1) . ': ";' . "\n";
            $funcTests .= '    std::cout << "Student\'s answer: ";' . "\n" . ' printResult(' . $studentCall . ');' . "\n";
            $funcTests .= '    std::cout << "Teacher\'s answer: ";' . "\n" . ' printResult(' . $teacherCall . ');' . "\n";
            $funcTests .= "    return 0;\n";
            $funcTests .= "}\n\n";
        }

        $funcTests .= 'std::cout << "All tests cleared!" << std::endl;' . "\n";
        return $funcTests;
    }
    private function formatTestCaseForCpp($testCase) {

        // Check if it looks like a vector of integers
        if (preg_match('/^\{.*\}$/', $testCase)) {
            // It's already in a format suitable for C++, no need to change
            return $testCase;
        }
        // Check if it's a string that should be enclosed in quotes
        elseif (preg_match('/^".*"$/', $testCase)) {
            // Escape quotes and enclose in quotes for C++
            $testCase = str_replace("\"", "\\\"", $testCase);
            return "\"" . $testCase . "\"";
        }

        // Return the original string if no special formatting is needed
        return $testCase;
    }

    private function createProgramSkeleton()
    {
        // Base template for the C++ program
        return <<<CPP
            #include <iostream>
            #include <vector>
            #include <string>
            #include <algorithm>
            
            template <typename T>
            void printResult(const T& result) {
                std::cout << result << std::endl;
            }
            template <>
            void printResult(const std::vector<int>& result) {
                for (const auto& elem : result) {
                    std::cout << elem << " ";
                }
                std::cout << std::endl;
            }
            template <>
            void printResult(const std::vector<std::string>& result) {
                for (const auto& elem : result) {
                    std::cout << "\"" << elem << "\" ";
                }
                std::cout << std::endl;
            }
            template <typename T1, typename T2>
            void printResult(const std::pair<T1, T2>& result) {
                std::cout << "(" << result.first << ", " << result.second << ")" << std::endl;
            }

            namespace teacher {
                // Teacher's function declaration and implementation
                %%funDeclaration%%
                %%teacherFuncImplementation%%
            }
        
            namespace student {
                // Student's function declaration (to be implemented by the student)
                %%funDeclaration%%
                // Student's function implementation (to be implemented by the student)
                %%studentFuncImplementation%%
            }
        
                int main() {
                    // Test code that compares the outputs of the teacher's and student's functions
                    %%testCode%%
                    return 0;
                }
CPP;
    }

    //Getters
    function getId()
    {
        return $this->_id;
    }
    function getName()
    {
        return $this->_name;
    }
    function getDescription()
    {
        return $this->_description;
    }
    function getFunnctionName()
    {
        return $this->_functionName;
    }
    function getFunctionDeclaration()
    {
        return $this->_functionDeclaration;
    }
    function getTestCases()
    {
        return $this->_testCases;
    }
    function getTestAnswers()
    {
        return $this->_testAnswers;
    }
    function getCourseId()
    {
        return $this->_course_id;
    }
    function getDifficulty()
    {
        return $this->_difficulty;
    }
    function getBaseCppCode()
    {
        return htmlspecialchars($this->_baseCppCode);
    }
    function getCppCode()
    {
        return htmlspecialchars($this->_cppCode);
    }

    
}