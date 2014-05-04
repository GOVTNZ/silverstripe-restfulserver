<h1>Error definitions</h1>
<ul>
	<% loop $Errors %>
		<li>$Name (<a href="$Link">full description</a>)</li>
	<% end_loop %>
</ul>