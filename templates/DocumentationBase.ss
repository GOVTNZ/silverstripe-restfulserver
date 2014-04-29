<!doctype html>
<html>
	<head>
		<title>API documentation</title>
	</head>
	<body>
		<h1>List of end points</h1>
		<p>These end points are available through this API:</p>
		<ul>
			<% loop $EndPoints %>
				<li><a href="$Link">$Name</a><% if $Description %> ($Description)<% end_if %></li>
			<% end_loop %>
		</ul>
		<p>Access the data you want at: $APIBaseURL/&lt;end_point&gt;</p>
		<h1>Available formats</h1>
		<p>Results are available in these formats:</p>
		<ul>
			<% loop $Formats %>
				<li>$Extension</li>
			<% end_loop %>
		</ul>
		<p>Add the extension you want to the URL of your request, eg https://govtnz-test1.cwp.govt.nz/api/v1/&lt;end_point&gt;.xml</p>
	</body>
</html>