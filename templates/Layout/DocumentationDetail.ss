<h1>API Documentation</h1>
<h2>Available fields</h2>

<p>The following fields can be accessed on the $EndPoint end point.</p>

<ul>
	<% loop $AvailableFields %>
		<li>$Name</li>
	<% end_loop %>
</ul>

<h2>Example Usage</h2>

<h3>Partial response</h3>
<p>If you only need a subset of fields in the response you can use the <code>fields</code> parameter to get a partial response.</p>

<% if $Relations %>
	<h2>Relations</h2>

	<p>The following relationships can be accessed through the $EndPoint end point:</p>

	<ul>
		<% loop $Relations %>
			<li><a href="$Top.APIBaseURL/$Top.EndPoint/$Top.ResourceID/$Name">$Name</a></li>
		<% end_loop %>
	</ul>

	<p>Relations are accessed via: $APIBaseURL/$EndPoint/$ResourceID/&lt;Relation Name&gt;</p>
<% end_if %>
