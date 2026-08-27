@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>FAQ Categories</h1></div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('faqs.index') }}" class="btn btn-outline-secondary mr-2">FAQ List</a>
                @can('faq-create')
                    <a href="{{ route('faq-categories.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> New Category</a>
                @endcan
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-folder"></i> Category List</h3></div>
            <div class="card-body">
                @if (Session::has('success'))
                    <script>toastr.success('{{ Session::get('success') }}', { timeOut: 5000 });</script>
                @endif
                @if (Session::has('error'))
                    <script>toastr.error('{{ Session::get('error') }}', { timeOut: 5000 });</script>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Position</th>
                                <th>Thai</th>
                                <th>English</th>
                                <th style="width: 100px;">FAQs</th>
                                <th style="width: 120px;">Published</th>
                                @if(auth()->user()->can('faq-edit') || auth()->user()->can('faq-delete'))
                                    <th style="width: 180px;">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td>{{ $category->position }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->name_en ?: '-' }}</td>
                                    <td>{{ $category->faqs_count }}</td>
                                    <td><span class="badge {{ $category->is_published ? 'badge-success' : 'badge-secondary' }}">{{ $category->is_published ? 'Yes' : 'No' }}</span></td>
                                    @if(auth()->user()->can('faq-edit') || auth()->user()->can('faq-delete'))
                                        <td>
                                            @can('faq-edit')
                                                <a href="{{ route('faq-categories.edit', $category) }}" class="btn btn-warning btn-sm">Edit</a>
                                            @endcan
                                            @can('faq-delete')
                                                <form action="{{ route('faq-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ auth()->user()->can('faq-edit') || auth()->user()->can('faq-delete') ? 6 : 5 }}" class="text-center text-muted">No FAQ categories found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
