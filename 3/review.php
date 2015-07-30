<html lang="en">
<head>
    <title>Edit Page</title>
</head>

<body>
    <form action="submit.php" method="post" onSubmit="return confirm('Clicking OK will send the checked items and your feature descriptions along for evaluation.');">
        <?php
            if (isset($_POST["history"]) && isset($_POST["description"]) && isset($_POST["ID"])) {
        
                echo "<p>Your search history is displayed below. Checked items will be sent along for evaluation. You may check or uncheck items as you wish.</p>";
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
