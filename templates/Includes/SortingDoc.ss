<h3>Sorting</h3>
<p>To change the sorting of results, use the <code>sort</code> and <code>order</code> parameters.</p>

<ul>
	<li>Use <code>sort</code> to set the name of the column to sort by.</li>
	<li>Use <code>order</code> to set the direction the results sort in &ndash; either <code>asc</code> or <code>desc</code>.</li>
</ul>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint<% if $ResourceID %>/$ResourceID<% end_if %><% if $RelationName %>/$RelationName<% end_if %>?sort=$AvailableFields.Last.Name&order=asc</p>