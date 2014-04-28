<!doctype html>
<html>
	<head>
		<title>API documentation</title>
	</head>
	<body>
		<h1>List of end points</h1>
		<p>The following end points are available through this API.</p>
		<ul>
			<% loop $EndPoints %>
				<li><a href="$Link">$Name</a><% if $Description %> ($Description)<% end_if %></li>
			<% end_loop %>
		</ul>
		<p>They can be accessed via: $APIBaseURL/&lt;end_point&gt;</p>
		<h1>Available formats</h1>
		<p>
			Results from this API are available in the following formats, just add one of the formats below as an
			extension on your request URL (e.g. $APIBaseURL/&lt;end_point&gt;.xml).
		</p>
		<ul>
			<% loop $Formats %>
				<li>$Extension</li>
			<% end_loop %>
		</ul>
	</body>
</html>