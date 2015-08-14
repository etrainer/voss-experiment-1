<?php   

    require "../../.db.pw";
    $turk_id = $_GET["mturkworkerID"];

    if (empty($turk_id)) {
        echo "Invalid worker ID detected! Please return to the HIT page and start again.";
        die();
    }

    $rowArray = array();

    $conn = new mysqli("localhost", $MYDB_USER, $MYDB_PW, "vossdb") or die(mysql_error());
    if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
    }
    
     
    $query = "SELECT * FROM `description_ratings` WHERE `turkID`='$turk_id'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
	    echo "It looks like you've already completed this HIT. Thank you for your participation!";
        die();
    }

   
    $condition3Query = "SELECT `descriptionID`, `editedHistory`, `description` FROM `descriptions` WHERE `condition`=3 AND `isEvaluated`=0";
    $condition1and2Query = "SELECT `descriptionID`, `history`, `description` FROM `descriptions` WHERE `condition` in (1,2) AND `isEvaluated`=0";
  
    $resultCondition3 = $conn->query($condition3Query);
    $resultCondition1and2 = $conn->query($condition1and2Query);

    $index = 0;
    $isCondition3 = false;
   
     while ($row = $resultCondition3->fetch_assoc()) {
        $rowArray[$index] = $row;
        $index++;
    }

    while ($row = $resultCondition1and2->fetch_assoc()) {
        $rowArray[$index] = $row;
        $index++;
    }

    $randNum = rand(0, count($rowArray) - 1);
    $prettyHistory = "<ol>";

    //If there's no editedHistory, we're in condition 1 or 2. Else we're in condition 3
    if ($rowArray[$randNum]['editedHistory'] != NULL)
        $explodedHistory = explode("\n", $rowArray[$randNum]['editedHistory']);
    else {        
        $explodedHistory = explode("\n", $rowArray[$randNum]['history']);
    }

    for ($x = 0; $x < count($explodedHistory)-1; $x++) {
        if (strpos($explodedHistory[$x], "focus") === FALSE) {
            if (strpos($explodedHistory[$x], "Navigated") !==FALSE) {
                $prettyHistory .= "<li>" . substr($explodedHistory[$x], strpos($explodedHistory[$x], "Navigated")) . "</li>";
            }
            else {
                $prettyHistory .= "<li>" . substr($explodedHistory[$x], strpos($explodedHistory[$x], "Searched")) . "</li>";
            }   
        }
    }

    $prettyHistory .= "</ol>";
    $description_id = $rowArray[$randNum]['descriptionID'];
    
    $description = html_entity_decode($rowArray[$randNum]['description']);

    $pattern = '\<\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-[\w\s]+\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\>';
    $description_elements = preg_split("/" . $pattern . "/", $description);

    $description_str = "";
    foreach ($description_elements as $item) {
        $temp = trim(strip_tags($item, '<br><br/>'));
        if ($temp != "")
            $description_str .= "<p>" . $temp . "</p>";
    }

?>
<!doctype html>
<html>
<head>
    <title>Evaluate these Descriptions</title>
    <link type='text/css' rel='stylesheet' href='style.css'/>
    <link type='text/css' rel='stylesheet' href='rating.css'/>
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js'></script>
    <script type='text/javascript' src='rating.js'></script>
    <script type='text/javascript'>
        function btnCheckForm_onclick() {
            var myForm = document.form1;
            if (document.getElementById('rating_1').checked || document.getElementById('rating_2').checked || document.getElementById('rating_3').checked || document.getElementById('rating_4').checked || document.getElementById('rating_5').checked) {
                if (confirm('Are you finished with your ratings?\n\nOnly click \"OK\" when you are done with your ratings. Click \"Cancel\" to keep working.'))
                    myForm.submit();
            }
            else {
                alert("Please rate this description to proceed.");
            }
        }

        function openInstructions() {
            var w = 450;
            var h = 450;
            var left = (screen.width/2)-(w/2);
            var t = (screen.height/2)-(h/2);
            var newWindow = window.open('rating_instructions.html', 'myWindow', 'width='+w+',height='+h+',left='+left+',top='+t+',scrollbars=yes,toolbar=no,resizable=yes,location=no');
        }
    </script>
</head>

<body>
<form action="submit_ratings.php" method="post" name="form1" onSubmit="return confirm('Are you finished with your ratings?\n\nOnly click OK when you are done with your ratings. Click Cancel to keep working.');">
    <div id="main">
        <table style="width: 100%">
            <tr>
                <td style="text-align: right"><span class="nav"><a href="#" onclick="openInstructions(); return false;">Instructions</a></span></td>
            </tr>
        </table>
        <table class="descriptions">
            <tr>
                <!--<th class="row-1 row-ID">Number</th>-->
                <th class="row-history">Search History</th>
                <th class="row-description">Description</th>
                <th style="row-rating">Your Rating</th>
            </tr>

            <tr>
                <!--<td>1</td>-->
                <td><?php echo $prettyHistory ?></td>
                <td><?php echo $description_str?></td>
                <td>
                    <span class="star-rating">
                        <input type="radio" name="rating" id="rating_1" value="1"><i></i>
                        <input type="radio" name="rating" id="rating_2" value="2"><i></i>
                        <input type="radio" name="rating" id="rating_3" value="3"><i></i>
                        <input type="radio" name="rating" id="rating_4" value="4"><i></i>
                        <input type="radio" name="rating" id="rating_5" value="5"><i></i>
                        <?php echo "<input type='hidden' value='$turk_id' name='raterID'>";?>
                        <?php echo "<input type='hidden' value='$description_id' name='descriptionID'>";?>
                    </span>
                    <strong class="choice">Choose a rating</strong>
                </td>
            </tr>
        </table>
    </div>
    <input type="button" value="Submit Ratings" style="font-size: 14px; width: 150px; height: 50px; border-radius: 5px; display: block; margin: 0 auto; background-color: #0266c8; color: white;" onclick="btnCheckForm_onclick()">
</form>
</body>
</html>
