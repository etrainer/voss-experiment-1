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

    $result = $conn->query($condition3Query);

    $index = 0;
   
     while ($row = $result->fetch_assoc()) {
        $rowArray[$index] = $row;
        $index++;
    }

    $randNum = rand(0, count($rowArray) - 1);
    $prettyHistory = "<ol>";
    $explodedHistory = explode("\n", $rowArray[$randNum]['editedHistory']);
    
    for ($x = 0; $x < count($explodedHistory)-1; $x++)
        $prettyHistory .= "<li>$explodedHistory[$x]</li>";
    $prettyHistory .= "</ol>";
    $description_id = $rowArray[$randNum]['descriptionID'];
    
    $description = $rowArray[$randNum]['description'];

    $pattern = '\<\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-[\w\s]+\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\>';
    $description_elements = preg_split("/" . $pattern . "/", $description);

    $description_str = "";
    foreach ($description_elements as $item) {
        $temp = trim(strip_tags($item));
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
</head>

<body>
<form action="submit_ratings.php" method="post" onSubmit="return confirm('Are you finished with your ratings?\n\nOnly click OK when you are done with your ratings. Click Cancel to keep working.');">
    <div id="main">

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
                        <input type="radio" name="rating" value="1"><i></i>
                        <input type="radio" name="rating" value="2"><i></i>
                        <input type="radio" name="rating" value="3"><i></i>
                        <input type="radio" name="rating" value="4"><i></i>
                        <input type="radio" name="rating" value="5"><i></i>
                        <?php echo "<input type='hidden' value='$turk_id' name='raterID'>";?>
                        <?php echo "<input type='hidden' value='$description_id' name='descriptionID'>";?>
                    </span>
                    <strong class="choice">Choose a rating</strong>
                </td>
            </tr>
        </table>
    </div>
    <input type="submit" value="Submit Ratings" style="display: block; margin: 0 auto" />
</form>
</body>
</html>
