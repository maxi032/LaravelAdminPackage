@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <table id="posts" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Office</th>
                            <th>Progress</th>
                            <th>Start date</th>
                            <th>Salary</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Office</th>
                            <th>Progress</th>
                            <th>Start date</th>
                            <th>Salary</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @push('footer-scripts')
        @once
            <script type="text/javascript" src="{{ URL::asset ('js/custom-scripts.js') }}"></script>
        @endonce
    @endpush
@endsection
