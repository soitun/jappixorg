<?php

// PostPro website

// Hide the PHP errors
ini_set('display_errors', 'off');

// Get the functions
require_once('./php/functions.php');

// Get the asked page
$page = getPage();
$exist = existPage($page);

// If page does not exist, put a 404 header
if(!$exist || ($page == '404')) {
	$page = '404';
	header('HTTP/1.0 404 Not Found');
}

?>
<!DOCTYPE html>
<html lang=en>

<head>
	<?php include('./php/head.php'); ?>
</head>

<header>
	<?php include('./php/menu.php'); ?>
</header>
<div style="clear: both;"></div>
<section>
<?php

	$include = './php/'.$page.'.php';
	
	if($exist)
		include($include);
	else
		include('./php/404.php');
	
?>
</section>
<div style="clear: both;"></div>
<footer>	
	<?php include('./php/foot.php'); ?>
</footer>
<!-- Piwik -->
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://julienbarrier.fr/piwik/" : "http://julienbarrier.fr/piwik/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 4);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
} catch( err ) {}
</script><noscript><p><img src="http://julienbarrier.fr/piwik/piwik.php?idsite=4" style="border:0" alt="" /></p></noscript>
<!-- End Piwik Tracking Code -->
</body>

</html>
