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
    <style>
        .custom-file-input {
            position: relative;
            display: inline-block;
            overflow: hidden;
            cursor: pointer;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            height: 100%;
            width: 100%;
        }

        .custom-file-input .btn-custom-file {
            background-color: #007bff;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid transparent;
            display: inline-block;
            cursor: pointer;
        }

        .custom-file-input .btn-custom-file:hover {
            background-color: #0056b3;
        }

        .custom-file-input .file-name {
            margin-left: 10px;
            vertical-align: middle;
            font-size: 14px;
        }

        .img-thumbnail {
            max-width: auto;
            /* Adjust size as needed */
            height: 100px;
            /* Maintain aspect ratio */
        }

        .image-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .image-item {
            /* flex: 1 1 calc(50% - 10px); */
            /* Two items per row with a gap */
            box-sizing: border-box;
            /* text-align: center; */
        }

        .switch_toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            /* spacing between text and toggle */
        }

        .switch_toggle input[type="checkbox"] {
            display: none;
        }

        .slider {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 20px;
            background-color: #ccc;
            border-radius: 34px;
            cursor: pointer;
            transition: 0.4s;
        }

        .slider::before {
            content: "";
            position: absolute;
            height: 16px;
            width: 16px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            border-radius: 50%;
            transition: 0.4s;
        }

        input:checked+.slider {
            background-color: #ffb515;
        }

        input:checked+.slider::before {
            transform: translateX(20px);
        }
    </style>
    <script type="text/javascript" async
        src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js?config=TeX-MML-AM_CHTML"></script>
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

                    </ol>

                </div>
            </div>
        </div>

    </section>
    <section class="content">

        <div class="container-fluid">
            {{-- <h1>
                test \(\frac{6}{7}\) + \(\frac{7}{8}\)
            </h1> --}}
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

                            {{-- center between checkbox and text    --}}

                            {{-- i want to add 3 select box for grade, term, level and script for search --}}

                            <div class="row searchQuestion mb-3">
                                <div class="col-md-3">
                                    <label class="switch_toggle">
                                        <span>My Question</span>
                                        <input type="checkbox" class="my-question">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div class="col-md-3">
                                    <select id="filter-grade" class="form-control">
                                        <option value="">All Grades</option>
                                        <option value="K1">K1</option>
                                        <option value="K2">K2</option>
                                        <option value="P1">P1</option>
                                        <option value="P2">P2</option>
                                        <option value="P3">P3</option>
                                        <option value="P4">P4</option>
                                        <option value="P5">P5</option>
                                        <option value="P6">P6</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filter-term" class="form-control">
                                        <option value="">All Terms</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filter-section" class="form-control">
                                        <option value="">All Level</option>
                                        @for ($i = 1; $i <= 20; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <table id="Listview" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th> # </th>
                                        <th> Code </th>
                                        <th> Text </th>
                                        <th> Grade </th>
                                        <th> Term </th>
                                        <th> Lavel </th>
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

    @include('question.question-form', ['editing' => true, 'question' => null, 'options' => []])
@endsection

@section('script')
    <script>
        window.ListviewInitByPage = true;
        let qTable = null;
        // Initialize (once) and let ajax.data read current filters each time
        function initDataTable() {
            if ($.fn.DataTable.isDataTable('#Listview')) {
                qTable = $('#Listview').DataTable();
                return;
            }

            qTable = $('#Listview').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                destroy: true,
                drawCallback: function() {
                    if (typeof MathJax !== "undefined") {
                        MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
                    }
                },
                ajax: {
                    url: '{{ route('questions') }}',
                    data: function(d) {
                        const f = getCurrentFilters();
                        d.check = f.check;
                        d.grade = f.grade;
                        d.term = f.term;
                        d.section = f.section;
                    }
                },
                columns: [{
                        data: '#',
                        name: '#',
                        orderable: false,
                        searchable: false,
                        createdCell: function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-nowrap').css('width', '1%');
                        }
                    },
                    {
                        data: 'code',
                        name: 'code',
                        createdCell: function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-nowrap').css('width', '1%');
                        }
                    },
                    {
                        data: 'text',
                        name: 'text',
                        render: function(data, type, row) {
                            // if (type === 'display' && data.length > 500) {
                            //     return '<div style="max-width: 500px; word-wrap: break-word; white-space: normal;">' +
                            //         data.substring(0, 500) + '...' +
                            //         '</div>';
                            // }
                            return '<div style="max-width: 750px; word-wrap: break-word; white-space: normal;">' +
                                data + '</div>';
                        }
                    },
                    {
                        data: 'level',
                        name: 'level'
                    },
                    {
                        data: 'term',
                        name: 'term'
                    },
                    {
                        data: 'section',
                        name: 'section'
                    },
                    {
                        data: 'type',
                        name: 'type',
                        orderable: false
                    },
                    {
                        data: 'option',
                        name: 'option',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        createdCell: function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-nowrap').css('width', '1%');
                        }
                    }
                ]
            });
        }
        // Function to get current filter values
        function getCurrentFilters() {
            return {
                check: $('.my-question').is(':checked') ? 1 : 0,
                grade: $('#filter-grade').val(),
                term: $('#filter-term').val(),
                section: $('#filter-section').val()
            };
        }

        // Event handler for My Question checkbox
        $(document).on('change', '.my-question', function() {
            const filters = getCurrentFilters();

            // No need to re-init; just reload with new params
            initDataTable();
            qTable.ajax.reload();
        });

        // Event handlers for filter select boxes
        $(document).on('change', '#filter-grade, #filter-term, #filter-section', function() {
            initDataTable();
            qTable.ajax.reload();
        });

        // Initialize table on first load so global search input works
        $(document).ready(function() {
            initDataTable();
            // Hook global search triggers server-side
            $('#Listview_filter input').off('keyup.DT input.DT').on('keyup', function(){
                qTable.search(this.value).draw();
            });
        });
    </script>
    @include('question.scripts')
@endsection
