<?php 

    if (isset($_POST["url"])) {
        $url = $_POST["url"];
        $opts = array('http'=>array('header'=>"User-Agent:MyAgent/1.0\r\n"));
        $context = stream_context_create($opts);
        $html = file_get_contents($url, false, $context);
       
        $xml = new DOMDocument();
        libxml_use_internal_errors(true);
        $xml->loadHTML($html);
        libxml_clear_errors();
        $links = $xml->getElementsByTagName('a');

        //Loop backwards through all the <a> tags and replace them with their text content
        for ($i = $links->length - 1; $i >= 0; $i--) {
            $linkNode = $links->item($i);
            $linkText = trim($linkNode->textContent);
            $newTextNode = $xml->createTextNode($linkText);
            $linkNode->parentNode->replaceChild($newTextNode, $linkNode);
        }

        echo $xml->saveHTML(); 
    }
?>
