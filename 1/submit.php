<?php
  
    if (isset($_POST["ID"])) {
        $fileName = trim($_POST["ID"]);
    }

    $myfile = fopen("$fileName", "w") or die ("Unable to open file!");
    $myfile2 = fopen("$fileName" . " description.html", "w") or die ("Unable to open file!");
    if (isset($_POST["history"]) && isset($_POST["description"])) {
        $history = $_POST["history"];
        $content = $_POST["description"];
        $items = json_decode($_POST["history"]);
        foreach ($items as $item) {
            fwrite($myfile, $item->eventTime . ": " . $item->description . "\n");
        }

        fwrite($myfile2, $content);
        echo "<h1>Data submitted successfully!</h1>";
    }
    else {
        echo "There was an error!";
    }
    fclose($myfile);
    fclose($myfile2);
    
?>

