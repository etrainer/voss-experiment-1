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
    
    
    $query = "SELECT * FROM participants WHERE turkID='$turk_id'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
	    echo "It looks like you've already completed this HIT. Thank you for your participation!";
    }

    else {
	    //redirect to index.html
	    header("Location: http://home.dev/voss-experiment/consent_form.html?mturkworkerID=$turk_id");
	    die();
    } 
?>
