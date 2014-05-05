<h1>API Documentation</h1>

<% include AvailableFieldsDoc %>

<h2>Example usage</h2>

<% include PaginationDoc %>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint?offset=10&limit=10</p>

<% include FilterDoc %>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint?$AvailableFields.Last.Name=&lt;search_query&gt;</p>

<% include SortingDoc %>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint?sort=$AvailableFields.Last.Name&order=asc</p>

<% include PartialResponseDoc %>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint?fields=$AvailableFields.Last.Name</p>

<h3>Combined example</h3>
<p>Pagination, filtering, sorting, and partial response can be used individually or in combination.</p>
<p>
	Here's an example using all of them:<br>
	$APIBaseURL/$EndPoint?offset=10&limit=10&sort=$AvailableFields.Last.Name&order=desc&$AvailableFields.Last.Name=&lt;search_query&gt;&fields=$AvailableFields.Last.Name
</p>

<% if $Relations %>
	<h2>Relations</h2>

	<p>These relationships can be accessed for each $SingularName:</p>

	<ul>
		<% loop $Relations %>
			<li>$Name</li>
		<% end_loop %>
	</ul>

	<p>Access relations at: $APIBaseURL/$EndPoint/&lt;id&gt;/&lt;relation_name&gt;</p>
<% end_if %>
