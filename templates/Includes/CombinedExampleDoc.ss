<h3>Combined example</h3>
<p>Pagination, filtering, sorting, and partial response can be used individually or in combination.</p>
<p>
	Here's an example using all of them:<br>
	$APIBaseURL/$EndPoint<% if $ResourceID %>/$ResourceID<% end_if %><% if $RelationName %>/$RelationName<% end_if %>?offset=10&limit=10&sort=$AvailableFields.Last.Name&order=desc&$AvailableFields.Last.Name=&lt;search_query&gt;&fields=$AvailableFields.Last.Name
</p>
