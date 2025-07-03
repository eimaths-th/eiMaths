{{-- <div>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('question.create') }}"
                            class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                            Create Question
                        </a>
                    </div>

                    <div class="mb-4 min-w-full overflow-hidden overflow-x-auto align-middle sm:rounded-md">
                        <table class="min-w-full border divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="w-16 bg-gray-50 px-6 py-3 text-left">
                                        <span
                                            class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">ID</span>
                                    </th>
                                    <th class="bg-gray-50 px-6 py-3 text-left">
                                        <span
                                            class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Text</span>
                                    </th>
                                    <th class="w-40 bg-gray-50 px-6 py-3 text-left">

                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                                @forelse($questions as $question)
                                    <tr class="bg-white">
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $question->id }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $question->text }}
                                        </td>
                                        <td>
                                            <a href="{{ route('question.edit', $question->id) }}"
                                                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                                                Edit
                                            </a>
                                            <button wire:click="delete({{ $question }})"
                                                class="rounded-md border border-transparent bg-red-200 px-4 py-2 text-xs uppercase text-red-500 hover:bg-red-300 hover:text-red-700">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3"
                                            class="px-6 py-4 text-center leading-5 text-gray-900 whitespace-no-wrap">
                                            No questions were found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $questions->links() }}
                </div>
            </div>
        </div>
    </div>
</div> --}}

@extends('layouts.app')

@section('style')
    @include('users.style')
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    {{-- <h4>Users & Roles Management</h4> --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        {{-- <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Users Management</li> --}}
                        {{-- <a href="{{ route('question2.create') }}" class="btn btn-success">
                            Create Question
                        </a> --}}
                        <button type="button" class="btn btn-success" id="CreateButton">
                            <i class="fas fa-user"></i> Create Question </button>
                        <a href="{{ route('questions-quizzes.create') }}" class="btn btn-success">Create</a>

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
                            <h3 class="card-title">
                                <i class="fa fa-user"></i>
                                Question List
                            </h3>
                            <div class="card-tools">

                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($message = Session::get('success'))
                                <script>
                                    toastr.success('{{ $message }}', {
                                        timeOut: 5000
                                    });
                                </script>
                            @endif
                            <table id="Listview" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th> # </th>
                                        <th> Code </th>
                                        <th> Text </th>
                                        <th> Type Option </th>
                                        <th> Options </th>
                                        {{-- <th> Level </th>
                                        <th> Term </th> --}}
                                        <th> </th>
                                    </tr>
                                </thead>

                                <tbody>
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

    {{-- @include('question.question-form', ['editing' => false, 'question' => null, 'options' => []]) ----}}
    @include('questionquizzes.form', ['editing' => true, 'question' => null, 'options' => []])


    {{-- @include('question.edit') --}}

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('questionquizzes.scripts')
@endsection
