function create_menu(basepath)
{
	var base = (basepath == 'null') ? '' : basepath;

	document.write(
		'<table cellpadding="0" cellspaceing="0" border="0" style="width:98%"><tr>' +
		'<td class="td" valign="top">' +

		'<ul>' +
		'<li><a href="'+base+'index.html">User Guide Home</a></li>' +	
		'<li><a href="'+base+'toc.html">Table of Contents Page</a></li>' +
		'</ul>' +	

		'<h3>Basic Info</h3>' +
		'<ul>' +
			'<li><a href="'+base+'license.html">License Agreement</a></li>' +
			'<li><a href="'+base+'changelog.html">Change Log</a></li>' +
			'<li><a href="'+base+'upgrade.html">Upgrading from previous version</a></li>' +
			'<li><a href="'+base+'general/credits.html">Credits</a></li>' +
		'</ul>' +	
		
		'<h3>Installation</h3>' +
		'<ul>' +
			'<li><a href="'+base+'installation/downloads.html">Downloading DX Auth</a></li>' +
			'<li><a href="'+base+'installation/index.html">Installation Instructions</a></li>' +
			'<li><a href="'+base+'installation/schema.html">Database schema</a></li>' +
		'</ul>' +
						
		'</td><td class="td_sep" valign="top">' +

		'<h3>General Topics</h3>' +
		'<ul>' +
			'<li><a href="'+base+'general/index.html">Getting Started</a></li>' +
			'<li><a href="'+base+'general/functions.html">Functions</a></li>' +
			'<li><a href="'+base+'general/events.html">Events</a></li>' +
			'<li><a href="'+base+'general/config.html">Config</a></li>' +
			'<li><a href="'+base+'general/models.html">Models</a></li>' +
			'<li><a href="'+base+'general/tables.html">Tables anatomy</a></li>' +
			'<li><a href="'+base+'general/troubleshooting.html">Troubleshooting</a></li>' +
		'</ul>' +
		
		'</td><td class="td_sep" valign="top">' +

		'<h3>Examples</h3>' +
		'<ul>' +
			'<li><a href="'+base+'examples/simple.html">Simple example</a></li>' +
			'<li><a href="'+base+'examples/advanced.html">Advanced example</a></li>' +
			'<li><a href="'+base+'examples/recaptcha.html">Recaptcha example</a></li>' +
			'<li><a href="'+base+'examples/permission.html">Permission example</a></li>' +
		'</ul>' +
		
		'</td></tr></table>');
}