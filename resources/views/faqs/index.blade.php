@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('content-management.faq.management_title') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    @can('faq-list')
                        <a href="{{ route('faq-categories.index') }}" class="btn btn-outline-primary mr-2">
                            <i class="fas fa-folder"></i> {{ __('content-management.faq.manage_categories') }}
                        </a>
                    @endcan
                    @can('faq-create')
                        <a href="{{ route('faqs.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> {{ __('content-management.faq.new') }}
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
                        <h3 class="card-title"><i class="fas fa-circle-question"></i> {{ __('content-management.faq.list_title') }}</h3>
                    </div>
                    <div class="card-body">
                        @if (Session::has('success'))
                            <script>
                                toastr.success('{{ Session::get('success') }}', { timeOut: 5000 });
                            </script>
                        @endif
                        @if (Session::has('error'))
                            <script>
                                toastr.error('{{ Session::get('error') }}', { timeOut: 5000 });
                            </script>
                        @endif

                        <form method="GET" action="{{ route('faqs.index') }}" class="form-inline mb-3">
                            <label for="category-filter" class="mr-2">{{ __('content-management.faq.filters.category') }}</label>
                            <select id="category-filter" name="category_id" class="form-control mr-2">
                                <option value="">{{ __('content-management.faq.filters.all_categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (string) $selected_category_id === (string) $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}{{ $category->name_en ? ' / ' . $category->name_en : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary">{{ __('content-management.faq.filters.filter') }}</button>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">{{ __('content-management.faq.table.position') }}</th>
                                        <th>{{ __('content-management.faq.table.category') }}</th>
                                        <th>{{ __('content-management.faq.table.question') }}</th>
                                        <th>{{ __('content-management.faq.table.answer') }}</th>
                                        <th style="width: 120px;">{{ __('content-management.faq.table.published') }}</th>
                                        @if(auth()->user()->can('faq-edit') || auth()->user()->can('faq-delete'))
                                            <th style="width: 180px;">{{ __('content-management.faq.table.actions') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($faqs as $faq)
                                        <tr>
                                            <td>{{ $faq->position }}</td>
                                            <td>{{ optional($faq->category)->name ?? '-' }}</td>
                                            <td>{{ $faq->question }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($faq->answer, 140) }}</td>
                                            <td>
                                                <span class="badge {{ $faq->is_published ? 'badge-success' : 'badge-secondary' }}">
                                                    {{ $faq->is_published ? __('content-management.blog.show.yes') : __('content-management.blog.show.no') }}
                                                </span>
                                            </td>
                                            @if(auth()->user()->can('faq-edit') || auth()->user()->can('faq-delete'))
                                                <td>
                                                    @can('faq-edit')
                                                        <a href="{{ route('faqs.edit', $faq) }}" class="btn btn-warning btn-sm">{{ __('common.actions.edit') }}</a>
                                                    @endcan
                                                    @can('faq-delete')
                                                        <form action="{{ route('faqs.destroy', $faq) }}" method="POST" style="display:inline;" onsubmit="return confirm(@json(__('content-management.blog.actions.confirm_delete'))) ">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">{{ __('common.actions.delete') }}</button>
                                                        </form>
                                                    @endcan
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ auth()->user()->can('faq-edit') || auth()->user()->can('faq-delete') ? 6 : 5 }}" class="text-center text-muted">{{ __('content-management.faq.empty') }}</td>
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
