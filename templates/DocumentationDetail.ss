<h1>API Documentation</h1>
<h2>Available fields</h2>

<ul>
	<% loop $AvailableFields %>
		<li>$Name</li>
	<% end_loop %>
</ul>

<h3>Examples</h3>

<dl>
	<dt><strong>Partial response</strong> (request a response containing only certain fields)</dt>
	<dd>$APIBaseURL/$EndPoint/$ResourceID?fields=$AvailableFields.Last.Name</dd>
</dl>

<h2>Relations</h2>

<ul>
	<% loop $Relations %>
		<li><a href="$Top.APIBaseURL/$Top.EndPoint/$Top.ResourceID/$Name">$Name</a></li>
	<% end_loop %>
</ul>

<p>Relations are accessed via: $APIBaseURL/$EndPoint/$ResourceID/&lt;Relation Name&gt;</p>