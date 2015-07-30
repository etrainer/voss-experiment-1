<?php
  

    if (isset($_POST["filename"])) {
        $fname = $_POST["filename"];
        $myfile = fopen("$fname" . " edited.txt", "w") or die ("Unable to open file!");
        $history = $_POST["historyItem"];
        if ($history == NULL) 
            fwrite($myfile, "All history removed.");
        {
        else { 
            foreach ($history as $item) {
                fwrite($myfile, $item . "\n");
            }
        }
        echo "<h1>Data submitted successfully!</h1>";
        echo "<p>You have completed the task. Please close your browser window.</p>";
        fclose($myfile);
    }
    else
        echo "There was an error!";
    
?>

