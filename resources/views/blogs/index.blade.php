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
                                    toastr.success('{{ Session::get('success') }}', {
                                        timeOut: 5000
                                    });
                                </script>
                            @endif
                            <table id="blogs-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Thumbnail</th>
                                        <th>Title</th>
                                        <th>Published</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($blogs as $blog)
                                        <tr>
                                            <td><img src="{{ asset('storage/' . $blog->thumbnail) }}" alt="Thumbnail" width="100px" style="aspect-ratio: 16/9; object-fit: cover; border-radius: 12px;"></td>
                                            <td>{{ $blog->title }}</td>
                                            <td>{{ $blog->is_published ? 'Yes' : 'No' }}</td>
                                            <td>
                                                <a href="{{ route('blogs.show', $blog) }}" class="btn btn-info btn-sm">View</a>
                                                <a href="{{ route('blogs.edit', $blog) }}" class="btn btn-warning btn-sm">Edit</a>
                                                <form action="{{ route('blogs.destroy', $blog) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
    $(function () {
        $('#blogs-table').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });
    });
</script>
@endsection
