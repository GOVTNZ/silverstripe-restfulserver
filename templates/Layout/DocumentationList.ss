<h1>API Documentation</h1>
<h2>Available fields</h2>

<p>These fields can be accessed on the $EndPoint end point. Results can be sorted and filtered by each field.</p>

<ul>
	<% loop $AvailableFields %>
		<li>$Name</li>
	<% end_loop %>
</ul>

<h2>Example usage</h2>

<h3>Pagination</h3>
<p>To page through results, use the <code>offset</code> and <code>limit</code> parameters.</p>

<ul>
	<li>Use <code>offset</code> to pick which record to start displaying results from.</li>
	<li>Use <code>limit</code> to determine the number of records to show on each page.</li>
</ul>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint?offset=10&limit=10</p>

<h3>Filtering</h3>
<p>To filter results on a certain field, use the name of the field you want to filter by as the parameter.</p>
<p>A partial match will be done on your search query and any results matched will be returned.</p>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint?$AvailableFields.Last.Name=&lt;search_query&gt;</p>

<h3>Sorting</h3>
<p>To change the sorting of results, use the <code>sort</code> and <code>order</code> parameters.</p>

<ul>
	<li>Use <code>sort</code> to set the name of the column to sort by.</li>
	<li>Use <code>order</code> to set the direction the results sort in &ndash; either <code>asc</code> or <code>desc</code>.</li>
</ul>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint?sort=$AvailableFields.Last.Name&order=asc</p>

<h3>Partial response</h3>
<p>If you only need a subset of fields, use the <code>fields</code> parameter to get a partial response.</p>

<ul>
	<li>Use <code>fields</code> to set the list of fields to return &ndash; separate each one with a comma.</li>
</ul>

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

	<p>These relationships can be accessed through the $EndPoint end point:</p>

	<ul>
		<% loop $Relations %>
			<li>$Name</li>
		<% end_loop %>
	</ul>

	<p>Access relations at: $APIBaseURL/$EndPoint/&lt;id&gt;/&lt;relation_name&gt;</p>
<% end_if %>
