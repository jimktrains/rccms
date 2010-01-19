<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
	<title><?php echo $title ?> | RedColony</title>

		<?php foreach ($styles as $style => $media) echo HTML::style($style, array('media' => $media), TRUE), "\n" ?>
		<?php foreach ($scripts as $script) echo HTML::script($script, NULL, TRUE), "\n" ?>
		<?php foreach ($metas as $meta) echo HTML::meta($meta['name'], $meta['content']), "\n" ?>
		<?php foreach ($httpequivs as $httpequiv) echo HTML::http_quiv($meta['header'], $meta['content']), "\n" ?>
		<?php foreach ($links as $link) echo HTML::link($link['rel'], $link['href'], $link['type'], $link['title']), "\n" ?>	
	</head>
	<body>
		<!-- Header -->
		<!-- Nav -->
		<?php echo $body ?>
		
		<!-- Footer -->
	</body>
</html>
