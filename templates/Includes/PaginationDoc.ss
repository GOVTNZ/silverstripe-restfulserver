<h3>Pagination</h3>
<p>To page through results, use the <code>offset</code> and <code>limit</code> parameters.</p>

<ul>
	<li>Use <code>offset</code> to pick which record to start displaying results from.</li>
	<li>Use <code>limit</code> to determine the number of records to show on each page.</li>
</ul>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint<% if $ResourceID %>/$ResourceID<% end_if %><% if $RelationName %>/$RelationName<% end_if %>?offset=10&limit=10</p>