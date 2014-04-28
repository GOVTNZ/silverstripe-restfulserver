<h1>API Documentation</h1>
<h2>Available fields</h2>

<p>The following fields are available on the $RelationName relation of the  $EndPoint end point.</p>

<ul>
	<% loop $AvailableFields %>
		<li>$Name</li>
	<% end_loop %>
</ul>

<h2>Example Usage</h2>

<h3>Pagination</h3>
<p>To page through results, the <code>start</code> and <code>limit</code> parameters must be used.</p>

<h4>Offset</h4>
<p>The n<sup>th</sup> record from which to start displaying results.</p>

<h4>Limit</h4>
<p>Determines the number of records to show per page.</p>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint/$ResourceID/$RelationName?offset=10&limit=10</p>

<h3>Filtering</h3>
<p>To filter results on a certain field use the name of the field you want to filter by as the parameter.</p>
<p>A partial match will be done on your search query and any results matched will be returned.</p>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint/$ResourceID/$RelationName?$AvailableFields.Last.Name=&lt;search_query&gt;</p>

<h3>Sorting</h3>
<p>To change the sorting of results the <code>sort</code> and <code>order</code> parameters are needed.</p>

<h4>Sort</h4>
<p>The name of the column to sort by</p>

<h4>Order</h4>
<p>The direction of the sorting. Valid options are <code>asc</code> and <code>desc</code>.</p>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint/$ResourceID/$RelationName?sort=$AvailableFields.Last.Name&order=asc</p>

<h3>Partial response</h3>
<p>If you only need a subset of fields in the response you can use the <code>fields</code> parameter to get a partial response.</p>

<h4>Fields</h4>
<p>A comma separated list of fields. Only these fields will be returned in the response.</p>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint/$ResourceID/$RelationName?fields=$AvailableFields.Last.Name</p>

<h3>Combined example</h3>
<p>Pagination, filtering, sorting, and partial response can be used individually or a combination of them can be used.</p>
<p>
	Here is an example using all of them:<br>
	$APIBaseURL/$EndPoint/$ResourceID/$RelationName?offset=10&limit=10&sort=$AvailableFields.Last.Name&order=desc&$AvailableFields.Last.Name=&lt;search_query&gt;&fields=$AvailableFields.Last.Name
</p>
