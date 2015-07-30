<?php
  

    if (isset($_POST["filename"]) && isset($_POST["historyItem"])) {
        $fname = $_POST["filename"];
        $myfile = fopen("$fname" . " edited.txt", "w") or die ("Unable to open file!");
        $history = $_POST["historyItem"]; 

        foreach ($history as $item) {
            fwrite($myfile, $item . "\n");
        }
        echo "<h1>Data submitted successfully!</h1>";
        fclose($myfile);
    }
    else
        echo "There was an error!";
    
?>

