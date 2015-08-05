<?php
  
    require "../../../.db.pw";
    if (isset($_POST["ID"]) && isset($_POST["history"]) && isset($_POST["description"]) && isset($_POST["condition"])) {
    
        $conn = new mysqli("localhost", $MYDB_USER, $MYDB_PW, "vossdb") or die(mysql_error());
        if ($conn->connect_error) {
	        die("Connection failed: " . $conn->connect_error);
        } 
 
        $turk_id = $conn->real_escape_string($_POST["ID"]);
        $content = $conn->real_escape_string($_POST["description"]);
        $history = $_POST["history"];
        $condition = $conn->real_escape_string($_POST["condition"]);
        $historyItems = json_decode($history);
        $history_str = "";
        foreach ($historyItems as $item)
            $history_str .= $item->eventTime . ": " . $item->description . "\n";
    
        $query = "SELECT * FROM `participants` WHERE `turkID`='$turk_id'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
	        echo "It looks like you've already completed this HIT. Thank you for your participation!";
            die();
        }

        $sql = "INSERT INTO `participants` (`turkID`, `condition`, `history`, `description`) VALUES ('$turk_id', '$condition', '$history_str', '$content')";
        if ($conn->query($sql) == TRUE) {
            echo "Your descriptions have been sent! To finish this task, take a short survey: ";
        } 
        else {
            echo "Error: " . $sql . "<br/>" . $conn->error;
        }
    }
    else
        echo "There was an error!";
?>

