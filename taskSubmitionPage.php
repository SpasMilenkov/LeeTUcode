<?php
include_once "components/head.php";
if (isset($_SESSION['cppFile'])) {
    $safeCppFile = htmlentities($_SESSION['cppFile'], ENT_QUOTES, 'UTF-8');
} else {
    // Handle the case where 'cppFile' is not set in the session
    $safeCppFile = ''; // Or set a default value or handle this case appropriately
}

?>

<body>
    <?php include_once "components/header.php" ?>
    <main>
        <div class="container mt-5">
            <div class="row">
                <div class="submition-result col-12 bg-light border border-secondary rounded ps-3 pt-2">
                    <h3>Submission result:</h3>
                    <p id="codeoutput" style="min-height: 100px;"></p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-4">
                    <button type="button" class="btn btn-primary btn-lg" onclick="goBack()">Back</button>
                </div>
            </div>
        </div>
    </main>
    <?php include_once "components/footer.php" ?>
    <script>
        function decodeHtmlEntities(str) {
            return str.replace(/&amp;/g, '&')
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>')
                .replace(/&quot;/g, '"')
                .replace(/&#39;/g, "'")
                .replace(/&#039;/g, "'");
        }
        var cppFile = decodeHtmlEntities(`<?php echo $safeCppFile ?>`);
        var sanitizedCppCode = decodeHtmlEntities(cppFile);
        console.log(cppFile);
        console.log(sanitizedCppCode)
        var settings = {
            "url": "https://api.codex.jaagrav.in",
            "method": "POST",
            "timeout": 0,
            "headers": {
                "Content-Type": "application/json",
                "Authorization": "Basic dXZLREoyRXVmUkpWRlN4T3FweTo="
            },
            "data": JSON.stringify({
                "code": cppFile,
                "language": "cpp",
                "input": ""
            }),
        };


        $.ajax(settings).done(function (response) {
            var submitionStatus = "fail";
            var formattedOutput = "";
            var compilationResponse = "";
            console.log(response["status"]);
            if (response["output"] != "") {
                // Replace newline characters with <br> tags
                formattedOutput = response["output"].replace(/\n/g, '<br>');

                //Response for the db
                compilationResponse = response["output"];

                //Set the submition status
                if (formattedOutput == "All tests cleared!") {
                    submitionStatus = "success";
                }
            }
            else {
                //The output that shows to the user
                formattedOutput = "Program did not compile correctly! <br>" + response["error"];

                //Response for the db
                compilationResponse = "Program did not compile correctly!\n" + response["error"];

                //Set the submition status
                submitionStatus = "error";
            }

            // Set the formatted output in <p> tag
            $("#codeoutput").html(formattedOutput);

            // Update the submission status in the database using AJAX
            $.ajax({
                type: "POST",
                url: "include/updateSubmission.php",
                data: { submitionStatus: submitionStatus, taskId: <?php echo $_GET["id"]; ?>, compilationResponse: compilationResponse },
                success: function (result) {
                    console.log("update status success!");
                }
            });
        }).fail(function (jqXHR, textStatus, errorThrown) {
            // Handle errors here
            console.error("Call to Jaagrav CodexAPI failed. Server responded with code: " + jqXHR.status);

            //Call back up api
            var data = {
                code: cppFile,
                input: ""
            };

            // fetch the API => get response => show response in console
            fetch("https://compiler.pascaltheelf.workers.dev", {
                method: 'POST',
                body: JSON.stringify(data)
            }).then(res => res.json()).then(data => {
                showSubmitionResult(data);
            });
        });

        function showSubmitionResult(response) {
            var submitionStatus = "fail";
            var formattedOutput = "";
            var compilationResponse = "";
            console.log(response["status"]);
            if (response["output"] != "") {
                // Replace newline characters with <br> tags
                formattedOutput = response["output"].replace(/\n/g, '<br>');

                //Response for the db
                compilationResponse = response["output"];

                //Set the submition status
                if (formattedOutput == "All tests cleared!") {
                    submitionStatus = "success";
                }
                else if (!formattedOutput.startsWith("Input:")) {
                    //If the program did not run correctly
                    submitionStatus = "error";
                }
            }
            else {
                //The output that shows to the user
                formattedOutput = "Program did not compile correctly! <br>" + response["error"];

                //Response for the db
                compilationResponse = "Program did not compile correctly!\n" + response["error"];

                //Set the submition status
                submitionStatus = "error";
            }

            // Set the formatted output in <p> tag
            $("#codeoutput").html(formattedOutput);

            // Update the submission status in the database using AJAX
            $.ajax({
                type: "POST",
                url: "include/updateSubmission.php",
                data: { submitionStatus: submitionStatus, taskId: <?php echo $_GET["id"]; ?>, compilationResponse: compilationResponse },
                success: function (result) {
                    console.log("update status success!");
                }
            });
        }
    </script>
</body>