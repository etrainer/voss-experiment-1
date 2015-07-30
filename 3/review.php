<html lang="en">
<head>
    <title>Edit Page</title>
</head>

<body>
    <form action="submit.php" method="post" onSubmit="return confirm('Are you sure? Clicking OK will send your saved search history and description along for evaluation.');">
        <?php
            if (isset($_POST["history"]) && isset($_POST["description"]) && isset($_POST["ID"])) {
        
                echo "<p>Your search history is displayed below. You can edit what is sent along for evaluation by unchecking or checking the checkboxes next to each item.</p>";
                $jsonData = json_decode($_POST["history"]);
                $content = $_POST["description"];
                $fileName = trim($_POST["ID"]);
                $myfile = fopen("$fileName" . " original.txt", "w") or die ("Unable to open file!");
                $myfile2 = fopen("$fileName" . " description.html", "w") or die ("Unable to open file!");
                foreach ($jsonData as $item) {
                    fwrite($myfile, $item->eventTime . ": " . $item->description . "\n");
                }
                fclose($myfile);
                
                fwrite($myfile2, $content);
                fclose($myfile2);
                
                foreach ($jsonData as $item) {
                    echo "<input type='checkbox' name='historyItem[]' checked='checked' value='$item->description'>$item->description<br/>";
                }
                echo "<input type='hidden' value='$fileName' name='filename'>";
            }
        ?>
   
         <input type="submit" value="Submit" />
    </form>

</body>
</html>
