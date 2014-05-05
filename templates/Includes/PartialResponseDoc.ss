<h3>Partial response</h3>
<p>If you only need a subset of fields, use the <code>fields</code> parameter to get a partial response.</p>

<ul>
	<li>Use <code>fields</code> to set the list of fields to return &ndash; separate each one with a comma.</li>
</ul>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint<% if $ResourceID %>/$ResourceID<% end_if %><% if $RelationName %>/$RelationName<% end_if %>?fields=$AvailableFields.Last.Name</p>