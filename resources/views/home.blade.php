@extends('layouts.app')

@section('content')
<div id="main" class="ui one column container segment">
	<error-message></error-message>
	<loader :is-visible="isLoading"></loader>
	<router-view></router-view>
</div>
@endsection
