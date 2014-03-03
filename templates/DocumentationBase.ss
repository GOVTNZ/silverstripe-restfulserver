<!doctype html>
<html>
	<head>
		<title>API documentation</title>
	</head>
	<body>
		<h1>List of end points</h1>
		<ul>
			<% loop $EndPoints %>
				<li><a href="$Link">$Name</a><% if $Description %> ($Description)<% end_if %></li>
			<% end_loop %>
		</ul>
	</body>
</html>