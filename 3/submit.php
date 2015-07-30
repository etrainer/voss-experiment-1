<?php
  

    $fname = $_POST["filename"];
    if (isset($_POST["filename"])) {
        $myfile = fopen("$fname" . " edited.txt", "w") or die ("Unable to open file!");

        $history = $_POST["historyItem"]; 
        if (isset($_POST["historyItem"])) {
            foreach ($history as $item) {
                fwrite($myfile, $item . "\n");
            }
            echo "<h1>Data submitted successfully!</h1>";
        }   
        else {
        }
        fclose($myfile);
    }
    else
        echo "The file name is not set!";
    
?>

