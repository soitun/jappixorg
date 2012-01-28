<?php $version = parseXML(readCacheFile('version', 'http://jappix.org/xml/version.xml'));
 ?>
<h1>“ Get the freedom you need in your social talks ”<small> Julien Barrier, jappix founder</small></h1>
<p style="clear:both; height:20px;"></p>
	<a href="<?php echo($version['url2']); ?>" class="get" style="float: right; position: relative; right: 200px;">
		<img src="/img/images/get.png" alt="">
		<span class=first>Zip File</span>
		<span class=second>Jappix <?php echo($version['id']); ?> − (.zip)</span>
	</a>

	<a href="<?php echo($version['url']); ?>" class="get">
		<img src="/img/images/get.png" alt="">
		<span class=first>Tarball File</span>
		<span class=second>Jappix <?php echo($version['id']); ?> − (.tar.bz2)</span>
	</a>
<p style="clear:both; height:30px;"></p>
<p style="font-size: 1.2em; line-height: 1.3em;">Thousands of individuals and organizations have downloaded Jappix. Why won’t you do?</p>
<p style="font-size: 1.2em; line-height: 1.3em;"><a href=http://download.jappix.org/jappixorg>get other Jappix releases</a></p>
