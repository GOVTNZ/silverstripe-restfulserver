<h1>API Documentation</h1>
<h2>List of end points</h2>
<p>These end points are available through this API:</p>
<ul>
	<% loop $EndPoints %>
		<li><a href="$Link">$Name</a><% if $Description %> ($Description)<% end_if %></li>
	<% end_loop %>
</ul>
<p>Access the data you want at: $APIBaseURL/&lt;end_point&gt;</p>
<h2>Available formats</h2>
<p>Results are available in these formats:</p>
<ul>
	<% loop $Formats %>
		<li>$Extension</li>
	<% end_loop %>
</ul>
<p>Add the extension you want to the URL of your request, eg https://govtnz-test1.cwp.govt.nz/api/v1/&lt;end_point&gt;.xml</p>