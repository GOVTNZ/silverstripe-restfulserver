<% if $Relations %>
	<h2>Relations</h2>

	<p>These relationships can be accessed for each $SingularName:</p>

	<ul>
		<% loop $Relations %>
			<% if $Top.ResourceID %>
				<li><a href="$Top.APIBaseURL/$Top.EndPoint/$Top.ResourceID/$Name">$Name</a></li>
			<% else %>
				<li>$Name</li>
			<% end_if %>
		<% end_loop %>
	</ul>

	<p>Access relations at: $APIBaseURL/$EndPoint/<% if $ResourceID %>$ResourceID<% else %>&lt;id&gt;<% end_if %>/&lt;relation_name&gt;</p>
<% end_if %>
