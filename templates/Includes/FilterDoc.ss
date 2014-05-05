<h3>Filtering</h3>
<p>
	To filter results on a certain field, use the name of the field you want to filter by as the parameter.
	It will find partial matches.
</p>

<h4>Example</h4>
<p>$APIBaseURL/$EndPoint<% if $ResourceID %>/$ResourceID<% end_if %><% if $RelationName %>/$RelationName<% end_if %>?$AvailableFields.Last.Name=&lt;search_query&gt;</p>