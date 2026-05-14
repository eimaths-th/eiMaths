@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>FAQ Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    @can('faq-create')
                        <a href="{{ route('faqs.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> New FAQ
                        </a>
                    @endcan
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-circle-question"></i> FAQ List</h3>
                    </div>
                    <div class="card-body">
                        @if (Session::has('success'))
                            <script>
                                toastr.success('{{ Session::get('success') }}', { timeOut: 5000 });
                            </script>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Position</th>
                                        <th>Question</th>
                                        <th>Answer</th>
                                        <th style="width: 120px;">Published</th>
                                        @if(auth()->user()->can('faq-edit') || auth()->user()->can('faq-delete'))
                                            <th style="width: 180px;">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($faqs as $faq)
                                        <tr>
                                            <td>{{ $faq->position }}</td>
                                            <td>{{ $faq->question }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($faq->answer, 140) }}</td>
                                            <td>
                                                <span class="badge {{ $faq->is_published ? 'badge-success' : 'badge-secondary' }}">
                                                    {{ $faq->is_published ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                            @if(auth()->user()->can('faq-edit') || auth()->user()->can('faq-delete'))
                                                <td>
                                                    @can('faq-edit')
                                                        <a href="{{ route('faqs.edit', $faq) }}" class="btn btn-warning btn-sm">Edit</a>
                                                    @endcan
                                                    @can('faq-delete')
                                                        <form action="{{ route('faqs.destroy', $faq) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                        </form>
                                                    @endcan
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ auth()->user()->can('faq-edit') || auth()->user()->can('faq-delete') ? 5 : 4 }}" class="text-center text-muted">No FAQ records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $faqs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
