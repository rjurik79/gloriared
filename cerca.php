<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Documento senza titolo</title>
</head>

<body>
	<?php
		$buttonvalue = "Search";
		$search_at = "Search on";
		$search_result = "gave this result";
		$pages = "Number of pages with hits";
		$to_small = "At least two characters is required";
		$recursive = true;
		$html = <<<HTML
		
		HTML;
		
		echo $html;
		
		function textpart($body, $search) {
		// Displays the text after the title
		$length = 30;
		$text = substr($body, max(stripos($body,$search) - $length, 0), strripos($body,$search) - stripos($body,$search) + strlen($search) + 2 * $length);
		if (strripos($text, " ") < strripos($text,$search)) {
		$text = $text . " ";
		}
		if (stripos($text, " ") != strripos($text, " ")) {
		$text = substr($text, stripos($text, " "), strripos($text, " ") - stripos($text, " "));
		}
		$temp = $text;
		$stop = substr($text, strripos($text, $search) + strlen($search));
		if (strlen($stop) > $length) {
		$stop = substr($text, strripos($text, $search) + strlen($search), $length);
		$stop = substr($stop, 0, strripos($stop, " "));
		}
		$text = "... ";
		while (stripos($temp,$search)) {
		$temp = substr_replace($temp, "", stripos($temp, $search), 0);
		$temp = substr_replace($temp, "", stripos($temp, $search) + strlen($search), 0);
		$text = $text . substr($temp, 0, stripos($temp, "[/b]") + 4);
		$temp = substr($temp, stripos($temp, "[/b]") + 4);
		if(stripos($temp, $search) > (2 * $length)) {
		$text = $text . substr($temp, 0, $length);
		$text = substr($text, 0, strripos($text, " ")) . " ... ";
		$temp = substr($temp, stripos($temp, $search) - $length);
		$temp = substr($temp, stripos($temp, " "));
		}
		}
		$text = $text . $stop . " ... ";
		echo $text;
		return;
		}
		
		function compress($string, $first, $last) {
		// Removes everything in $string from $first to $last including $first and $last
		while(stripos($string,$first) && stripos($string,$last)) {
		$string = substr_replace($string, "", stripos($string,$first), stripos($string,$last) - stripos($string,$first) + strlen($last));
		}
		return $string;
		}
		
		function directoryToArray($directory, $recursive) {
		// This function by XoloX was downloaded from http://snippets.dzone.com/user/XoloX
		$array_items = array();
		if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != "..") {
		if (is_dir($directory. "/" . $file)) {
		if($recursive) {
		$array_items = array_merge($array_items, directoryToArray($directory. "/" . $file, $recursive));
		}
		} else {
		$file = $directory . "/" . $file;
		$array_items[] = preg_replace("/\/\//si", "/", $file);
		}
		}
		}
		closedir($handle);
		}
		return $array_items;
		}
		
		function filewalk($file, $search, $counter, $webpath) {
		// Selects and treats files with the extension .htm and .html and .asp and .php
		if (strtolower(substr($file, stripos($file, ".htm"))) == ".htm"
		|| strtolower(substr($file, stripos($file, ".html"))) == ".html"
		|| strtolower(substr($file, stripos($file, ".asp"))) == ".asp"
		|| strtolower(substr($file, stripos($file, ".php"))) == ".php") {
		$all = file_get_contents($file);
		$body = substr($all, stripos($all,"<body"),stripos($all,"</body>") - stripos($all,"<body"));
		$body = preg_replace('/<br \/>/i', ' ', $body);
		$body = preg_replace('/
		/i', ' ', $body);
		$body = compress($body,"<noscript","</noscript>");
		$body = compress($body,"<script","</script>");
		$body = compress($body,"<iframe","</iframe>");
		$body = compress($body,"<noframe","</noframe>");
		$body = strip_tags($body);
		$body = html_entity_decode($body, ENT_QUOTES);
		$body = preg_replace('/\s+/', ' ', $body);
		// Scans and displays the results
		if (stripos($body, $search)) {
		$title = substr($all, stripos($all,"<title>") + 7,stripos($all,"</title>") - stripos($all,"<title>") - 7);
		$title = html_entity_decode($title, ENT_QUOTES);
		$title = preg_replace('/\s+/', ' ', $title);
		echo '
		
		' . $title . ' (' . $file . ')</br>';
		echo '<span id="webpath">' . $webpath . substr($file, stripos($file, "/")) . '</span>
		';
		echo textpart($body, $search) . '</p>';
		$counter = $counter + 1;
		}
		}
		return $counter;
		}
		
		// Reads the search text from the page's URL
		$url = $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
		$url .= $_SERVER['SERVER_PORT'] != '81' ? $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"] : $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		
		if (stripos($url,"?search=")) $search = $_GET['search'];
		
		$webpath = dirname($url);
		
		// Starts searching
		if (strlen($search) < 2 && trim($search) <> "") {
		echo '
		
		' . $to_small . '!</p>';
		$search = "";
		}
		
		if (trim($search) <> "") {
		echo "
		
		" . $search_at . " '" . $search . "' " . $search_result . ".</p>";
		$counter = 0;
		// Path to the folder containing this file
		$curdir = getcwd();
		// Opens the folder and read its contents
		if ($dir = opendir($curdir)) {
		$files = directoryToArray("./", $recursive);
		foreach ($files as $file) {
		$counter = filewalk($file, $search, $counter, $webpath);
		}
		closedir($dir);
		}
		echo "
		
		" . $pages . ": " . $counter . "</p>";
		}
    ?>
</body>
</html>