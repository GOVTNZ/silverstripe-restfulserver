<h1>API Documentation</h1>
<h2>Available fields</h2>

<ul>
	<% loop $AvailableFields %>
		<li>$Name</li>
	<% end_loop %>
</ul>

<h3>Examples</h3>

<dl>
	<dt><strong>Pagination</strong> (use the start and limit parameters to page through lists)</dt>
	<dd>$APIBaseURL/$EndPoint/$ResourceID/$RelationName?start=10&limit=10</dd>
	<dt><strong>Filtering</strong> (filter the response by a specific field query)</dt>
	<dd>$APIBaseURL/$EndPoint/$ResourceID/$RelationName?$AvailableFields.Last.Name=&lt;Search Query&gt;</dd>
	<dt><strong>Sorting</strong> (sort the response by a field and choose a sort direction)</dt>
	<dd>$APIBaseURL/$EndPoint/$ResourceID/$RelationName?sort=$AvailableFields.Last.Name&order=asc</dd>
	<dt><strong>Partial response</strong> (request a response containing only certain fields)</dt>
	<dd>$APIBaseURL/$EndPoint/$ResourceID/$RelationName?fields=$AvailableFields.Last.Name</dd>
</dl>

<p>While these are all separate examples, a single request can make use of any of them.</p>
