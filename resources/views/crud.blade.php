@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Crud title') }}</div>

                    <div class="card-body">
                        {{ __('Card body') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
