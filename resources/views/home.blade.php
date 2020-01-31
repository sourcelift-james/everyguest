@extends('layouts.app')

@section('content')
<div id="main" class="ui one column container segment">
	<error-message></error-message>
	<router-view></router-view>
</div>
@endsection
