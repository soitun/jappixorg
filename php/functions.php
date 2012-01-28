<?php

// Read a cache file
function readCache($file) {
	return file_get_contents('./cache/'.$file.'.cache');
}

// Write a cache file
function writeCache($file, $data) {
	if(is_writable('./cache')) {
		file_put_contents('./cache/'.$file.'.cache', $data);
		return true;
	}
	
	return false;
}

// Checks if a cache file is valid
function validCache($file) {
	$path = './cache/'.$file.'.cache';
	
	// Not valid cache if not exist or too old (1 day)
	if((file_exists($path) && (time() - (filemtime($path)) >= 86400)) || !file_exists($path))
		return false;
	
	return true;
}

// Read a file
function readCacheFile($file, $path) {
	if(!validCache($file)) {
		$data = file_get_contents($path);
		writeCache($file, $data);
	}
	
	else
		$data = readCache($file);
	
	return $data;
}

// Gets the submitted page
function getPage() {
	if(isset($_GET['p']) && !empty($_GET['p']))
		return str_replace('/', '', $_GET['p']);
	else
		return 'welcome';
}

// Checks if the asked page exists
function existPage($page) {
	$checked = './php/'.$page.'.php';
	
	// Page exists
	if(file_exists($checked))
		return true;
	
	return false;
}

// Put a marker on the current opened tab
function currentTab($current, $page) {
	if($current == $page)
		echo ' class="current"';
}

// Parses a XML file (returns an array)
function parseXML($data) {
$array = array();

// Any data?
if($data) {
// Get the XML content
$xml = new SimpleXMLElement($data);

// Parse the XML content
foreach($xml->children() as $child)
$array[$child->getName()] = $child;
}

return $array;
}

?>
