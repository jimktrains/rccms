<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>Red Colony :: Sign Up</title>
	<meta><meta name="robots" content="noindex,nofollow"></meta>
	<link rel="stylesheet" href="_css/main.css" type="text/css" media="screen">
	<link rel="stylesheet" href="_css/print.css" type="text/css" media="print">
	<script type="text/javascript">
	<!--
	    function toggle_display(id) {
	       var e = document.getElementById(id);
	       if(e.style.display == 'block')
	          e.style.display = 'none';
	       else
	          e.style.display = 'block';
	    }
	//-->
	</script>
</head>
<body>
<?php include("_includes/top.php"); ?>
	<div id="white-space">
		<div id="subpage-controls">
			<a href="">Search members</a>
			<a href="">Search groups</a>
		</div>
	</div>
	<div id="main">
		<h1>Become a member of the Red Colony Community</h1><br><br>
		<div class="two-column" style="width:200px; padding-right:5px">
			<img src="_images/structure/bullet-big.gif" align="left" style="padding-right:7px"><h2>Submit content</h2>
			<p style="margin-bottom:30px">Submit articles, fiction, images, news, and more!</p>
			<img src="_images/structure/bullet-big.gif" align="left" style="padding-right:7px"><h2>Discuss ideas</h2>
			<p style="margin-bottom:30px">Meet members, talk about articles, share new ideas</p>
			<img src="_images/structure/bullet-big.gif" align="left" style="padding-right:7px"><h2>Change a world</h2>
			<p>Join the community that will create real change</p>
		</div>
		<div class="two-column" style="width:460px; padding-left:5px; background-color:#eee; border:1px solid #ccc;">
			<form>
			<table>
				<tr>
					<td class="strong">Choose a Username<br><span class="small" style="color:#999">15 characters or fewer</span></td>
					<td><input name="username" id="username" type="text" style="width:160px" maxlength="15" /></td>
				</tr>
				<tr>
					<td class="strong">Email Address</td>
					<td><input name="email" id="email" type="text" style="width:225px" /></td>
				</tr>
				<tr>
					<td class="strong">Retype Your Email Address</td>
					<td><input name="email2" id="email2" type="text" style="width:225px" /></td>
				</tr>
				<tr>
					<td class="strong">Type a Password</td>
					<td><input name="password" id="password" type="password" style="width:180px" /></td>
				</tr>
				<tr>
					<td class="strong">Retype your Password</td>
					<td><input name="password2" id="password2" type="password" style="width:180px" /></td>
				</tr>
				<tr>
					<td class="strong">Are you a robot?</td>
					<td><span class="small">Type this text below</span><br><input name="password2" id="password2" type="password" style="width:180px" /><br><br><img src="" height="50">image here</td>
				</tr>
				<tr>
					<td colspan="2" class="strong" style="padding-top:10px"><input type="checkbox" style="margin-right:10px">I agree to the <a href="">Terms and Conditions</a> for using Red Colony.</td>
				</tr>
				<tr>
					<td colspan="2" style="padding-top:20px" class="submit"><input type="submit" value="Join Red Colony!"></td>
				</tr>
			</table>
			</form>
		</div>
	</div>
<?php include("_includes/bottom.php"); ?>
</body>
</html>