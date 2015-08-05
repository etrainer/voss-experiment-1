<html lang="en">
<head>
    <title>Edit Your History</title>
</head>

<body>
    <form action="submit.php" method="post" onSubmit="return confirm('Clicking OK will send the checked items and your feature descriptions along for evaluation.');">
        <?php
            if (isset($_POST["history"]) && isset($_POST["description"]) && isset($_POST["ID"]) && isset($_POST["condition"])) {
        
                echo "Your search history is displayed below. Checked items will be sent along for evaluation. You may check or uncheck items as you wish.<br/><br/>";
                $jsonData = json_decode($_POST["history"]);
                $id = trim($_POST["ID"]);
                $condition = $_POST["condition"];
                $content = $_POST["description"];
                $history_str = "";
                foreach ($jsonData as $item) {
                    $history_str .=  $item->eventTime . ": " . $item->description . "\n";
                }
                
                
                foreach ($jsonData as $item) {
                    if (!($item->description == "Lost focus") && !($item->description == "Got focus"))
                        echo "<input type='checkbox' name='historyItem[]' checked='checked' value='$item->description'>$item->description<br/>";
                }
                echo "<input type='hidden' value='$id' name='ID'>";
                echo "<input type='hidden' value='$content' name='description'>";
                echo "<input type='hidden' value='$history_str' name='history'>";
                echo "<input type='hidden' value='$condition' name='condition'>";
            }
        ?>
         <input type="submit" value="Submit" />
    </form>

</body>
</html>
