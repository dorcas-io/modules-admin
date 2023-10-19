@extends('layouts.tabler')
@section('body_content_header_extras')

@endsection

@section('body_content_main')

@include('layouts.blocks.tabler.alert')

<div class="row">

	@include('layouts.blocks.tabler.sub-menu')
	<div class="col-md-9">
		<div class="row">

			<div class="col-md-6 col-lg-4">
				<div class="card">
					<div class="card-status bg-blue"></div>
					<div class="card-header">
						<h3 class="card-title">Marketplace</h3>
						<div class="card-options">
							<a href="#" class="btn btn-primary btn-sm">Edit</a>
						</div>
					</div>
					<div class="card-body">
						Manage Marketplace settings
					</div>
				</div>
			</div>

			<div class="col-md-6 col-lg-4">
				<div class="card">
					<div class="card-status bg-green"></div>
					<div class="card-header">
						<h3 class="card-title">Accounts</h3>
						<div class="card-options">
							<a href="#" class="btn btn-primary btn-sm">Edit</a>
						</div>
					</div>
					<div class="card-body">
						Manage account settings</strong>
					</div>
				</div>
			</div>

			<div class="col-md-6 col-lg-4">
				<div class="card">
					<div class="card-status bg-red"></div>
					<div class="card-header">
						<h3 class="card-title">Analytics Settings</h3>
						<div class="card-options">
							<a href="#" class="btn btn-primary btn-sm">Edit</a>
						</div>
					</div>
					<div class="card-body">
						Manage Data & Analytics settings
					</div>
				</div>
			</div>
			
		</div>
	
	</div>

</div>

@endsection
@section('body_js')
    
@endsection
