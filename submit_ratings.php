<?php   

    require "../../.db.pw";
    if (isset($_POST["raterID"]) && isset($_POST["descriptionID"]) && isset($_POST["rating"])) {

        $conn = new mysqli("localhost", $MYDB_USER, $MYDB_PW, "vossdb") or die(mysql_error());
        if ($conn->connect_error) {
	        die("Connection failed: " . $conn->connect_error);
        }
        
        $turk_id = $conn->real_escape_string($_POST["raterID"]);
        $description_id = $conn->real_escape_string($_POST["descriptionID"]);
        $rating = $conn->real_escape_string($_POST["rating"]);

        $sql = "INSERT INTO `description_ratings` (`descriptionID`, `turkID`, `rating`) VALUES ('$description_id', '$turk_id', '$rating')";
        if ($conn->query($sql) == TRUE) {
            $sql = "UPDATE `descriptions` SET `isEvaluated`=1 WHERE `descriptionID`='$description_id'";
            if ($conn->query($sql) == TRUE) {
                echo "Your ratings have been sent! To finish this task, take a short survey: ";
            }
            else {
                echo "Error: could not update descriptions.";
            }
        } 
        else {
            echo "Error: " . $sql . "<br/>" . $conn->error;
        }

    }
    else {
        echo "Error: POST variables not set.";
    }

?>
