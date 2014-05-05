<h1>API Documentation</h1>

<% include AvailableFieldsDoc %>

<h2>Example usage</h2>

<% include PartialResponseDoc %>

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
