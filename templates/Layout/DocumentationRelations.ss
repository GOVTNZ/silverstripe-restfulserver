<h1>API Documentation</h1>
<% include AvailableFieldsDoc %>

<h2>Example usage</h2>

<% include PaginationDoc %>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint/$ResourceID/$RelationName?offset=10&limit=10</p>

<% include FilterDoc %>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint/$ResourceID/$RelationName?$AvailableFields.Last.Name=&lt;search_query&gt;</p>

<% include SortingDoc %>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint/$ResourceID/$RelationName?sort=$AvailableFields.Last.Name&order=asc</p>

<% include PartialResponseDoc %>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint/$ResourceID/$RelationName?fields=$AvailableFields.Last.Name</p>

<h3>Combined example</h3>
<p>Pagination, filtering, sorting, and partial response can be used individually or a combination of them can be used.</p>
<p>
	Here is an example using all of them:<br>
	$APIBaseURL/$EndPoint/$ResourceID/$RelationName?offset=10&limit=10&sort=$AvailableFields.Last.Name&order=desc&$AvailableFields.Last.Name=&lt;search_query&gt;&fields=$AvailableFields.Last.Name
</p>
