@extends('layouts.app')

@section('style')
@include('blogs.style')
@endsection

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    @can('blog-create')
                    <a href="{{ route('blogs.create') }}" class="btn btn-success" id="CreateButton"><i
                            class="fas fa-plus"></i>
                        New Blog </a>
                    @endcan
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="overlay" id="overlay">
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-blog"></i> Blog List</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                <i class="fas fa-expand"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (Session::has('success'))
                        <script>
                            toastr.success('{{ Session::get('success') }}', { timeOut: 5000 });
                        </script>
                        @endif
                        <div style="overflow: auto; white-space: nowrap;">
                            <table id="blogs-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Thumbnail</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Pen Name</th>
                                        <th>Published</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                               
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#blogs-table').DataTable({
            processing: true,
            serverSide: false,
             ajax:"{{ route('blogs.index')}}",
            columns: [
                {data: 'thumbnail',name:'thumbnail',orderable:false,searchable:false},
                {data: 'title',name:'title'},
                {data: 'author',name:'user.name'},
                {data: 'pen_name',name:'pen_name'},
                {data: 'is_published',name:'is_published'},
                {data: 'action',name:'action',orderable:false,searchable:false}
            ]
            // searching: true,
            // ordering: true,
            // paging: true,
            // pageLength: 10,
            // lengthChange: true,
            // autoWidth: false,
            // responsive: true,
            // language: {
            //     processing: "Loading...",
            //     search: "Search:",
            //     lengthMenu: "Show _MENU_ entries",
            //     info: "Showing _START_ to _END_ of _TOTAL_ entries",
            //     infoEmpty: "Showing 0 to 0 of 0 entries",
            //     infoFiltered: "(filtered from _MAX_ total entries)",
            //     paginate: {
            //         first: "First",
            //         previous: "Previous",
            //         next: "Next",
            //         last: "Last"
            //     }
            // }
        });
    });
</script>
@endsection