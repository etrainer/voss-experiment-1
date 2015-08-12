<?php
    require "../../.db.pw";

    $turk_id = $_GET["mturkworkerID"];

    if (empty($turk_id)) {
        echo "Invalid worker ID detected! Please return to the HIT page and start again.";
        die();
    }
    $conn = new mysqli("localhost", $MYDB_USER, $MYDB_PW, "vossdb") or die(mysql_error());
    if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
    }
    
    
    $query = "SELECT `takenSurvey` FROM `descriptions` WHERE `turkID`='$turk_id'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
       if ($row = $result->fetch_assoc()) {
            if ($row["takenSurvey"] == NULL || $row["takenSurvey"] == 0) {
 
                $sql = "UPDATE `descriptions` SET `takenSurvey`=1, `isEvaluated`=0 WHERE `turkID`='$turk_id'";
                if ($conn->query($sql) == TRUE) {
                    echo "You have successfully completed this HIT. Thank you for your participation! Please close this browser window.";
                    die();
                }
                else 
                    echo "Error: " . $sql . "<br/>" . $conn->error;
            }
            else if ($row["takenSurvey"] == 1) {
                echo "Our records show you have already completed this HIT. Thank you for your participation!";
                die();
            }
        }
    }
    else {
        echo "It looks like you have managed to take the survey without completing this HIT. Please return to the HIT page for this task.";
	    die();
    } 
?>


