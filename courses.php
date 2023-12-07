<?php
include_once "include/dbHandler.php";
include_once "include/Course.php";
include_once "components/head.php";

$coursesArr = $dbHandler->getCourses();
$pageNum = isset($_GET["page"])?$_GET["page"]:1;
// items per page
$pageSize = 5;
?>

<body>
    <style>
        body {
            margin: 0;
        }

        .courses-container {
            display: flex;
            flex-wrap: wrap;
        }

        .course-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 10px;
            padding: 20px;
            width: 200px;
        }

        .course-card h3 {
            color: #333;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .search-input {
            padding: 10px;
            width: 300px;
            font-size: 14px;
            box-sizing: border-box;
        }
    </style>
    <?php include_once "components/header.php" ?>
    <main>
        <div class="container my-5">
            <div class="row">
                <div class="col-lg-12 mx-auto">
                    <div class="search-container">
                        <input type="text" class="search-input" id="searchInput" placeholder="Search by course name">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 bg-light border border-secondary rounded ps-5 pt-5 pb-5 mx-auto">
                    <div class="courses-container text-center" id="coursesContainer">
                        <?php for ($i=($pageNum-1)*$pageSize; $i<$pageNum*$pageSize&&$i<count($coursesArr); $i++) { ?>
                            <a href="course.php?id=<?php echo $coursesArr[$i]["id"]; ?>" class="no-link-style">
                                <div class="course-card">
                                    <h3>
                                        <?php echo $coursesArr[$i]["name"]; ?>
                                    </h3>
                                </div>
                            </a>

                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="row my-3">
                <ul class="col-lg-6 pagination">
                    
                </ul>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script> 
    <script>
        const pCount=<?=ceil(count($coursesArr)/$pageSize)?>;
        const pNum=<?=$pageNum?>;
        //max # of page links
        //best to be odd so that the current page is centered
        const pageBarLength=9;
        const pageItem="<li class='page-item'><a class='page-link'></a></li>"
        $(document).ready(function() {
            for (let i=0; i<pCount&&i<pageBarLength; i++)
            {
                $("ul.pagination").append(pageItem);
            }

            let startPageIdx=0;
            let endPageIdx=$("ul.pagination").children().length-1;
            let curPage=1;
            if (pCount>pageBarLength)
            {
                //adds a link to the first page if there isnt space for it
                if (pNum>pageBarLength/2+1)
                {
                    $("a.page-link").eq(startPageIdx).html("1");
                    $("a.page-link").eq(startPageIdx++).attr("href", "courses.php?page=1");
                    $("a.page-link").eq(startPageIdx++).html("...");
                    curPage=Math.min(pNum-Math.floor(pageBarLength/2-2), pCount-Math.floor(pageBarLength-4)-1);
                }
                //adds a link to the last page if there isnt space for it
                if (pNum<pCount-pageBarLength/2)
                {
                    $("a.page-link").eq(endPageIdx).html(pCount);
                    $("a.page-link").eq(endPageIdx--).attr("href", "courses.php?page="+pCount);
                    $("a.page-link").eq(endPageIdx--).html("...");
                }
            }
            for (let i=startPageIdx; i<=endPageIdx; i++, curPage++)
            {
                $("a.page-link").eq(i).html(curPage);
                $("a.page-link").eq(i).attr("href", "courses.php?page="+curPage);
            }
            if (pNum>1) 
            {
                $("ul.pagination").prepend(pageItem);
                $("a.page-link").first().html("<");
                $("a.page-link").first().attr("href", "courses.php?page="+(pNum-1));
            }
            if (pNum<pCount) 
            {
                $("ul.pagination").append(pageItem);
                $("a.page-link").last().html(">");
                $("a.page-link").last().attr("href", "courses.php?page="+(pNum+1));
            }
            $(`a[href="courses.php?page=${pNum}"]`).addClass("active");
        })
        
    </script>
    <?php include_once "components/footer.php" ?>
    <script src="rec/js/filterCourses.js"></script>
</body>