{{-- @extends('layouts.app')

@section('style')
<style>
    .pagination {
        justify-content: center;
    }
</style>
<script type="text/javascript" async
  src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js?config=TeX-MML-AM_CHTML">
</script>
@endsection

@section('content')
<div class="container">
    <h2>Questions List</h2>

    <a href="{{ route('ts.questions.create') }}" class="btn btn-success mb-3">Add New Question</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Question</th>
                <th>Answer (Fraction)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($questions as $question)
            <tr>
                <td>{{ $question->question_text }}</td>
                <td>{{ $question->answer_numerator }}/{{ $question->answer_denominator }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $questions->links() }} <!-- Pagination Links -->
</div>
@endsection --}}
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เครื่องมือสัญลักษณ์ทางคณิตศาสตร์</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .modal-overlay {
            backdrop-filter: blur(4px);
        }

        .symbol-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .tab-button.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50">
    <div class="container mx-auto p-6 max-w-4xl">
        <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">
            เครื่องมือสัญลักษณ์ทางคณิตศาสตร์
        </h1>

        <!-- Input Area -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <label class="block text-lg font-medium text-gray-700 mb-3">
                พื้นที่ข้อความ:
            </label>
            <div class="flex gap-3">
                <input type="text" id="mathInput"
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
                    placeholder="คลิกปุ่มเพื่อเลือกสัญลักษณ์ทางคณิตศาสตร์..." />
                <button id="openModalBtn"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 font-medium shadow-md hover:shadow-lg">
                    เลือกสัญลักษณ์
                </button>
                <button id="clearBtn"
                    class="px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all duration-200 shadow-md hover:shadow-lg">
                    ล้าง
                </button>
            </div>
        </div>

        <!-- Preview -->
        <div id="previewSection" class="bg-white rounded-lg shadow-md p-6 mb-6 hidden">
            <h3 class="text-lg font-medium text-gray-700 mb-3">ตัวอย่าง:</h3>
            <div id="previewText"
                class="text-2xl font-mono bg-gray-50 p-4 rounded border min-h-[60px] flex items-center">
            </div>
        </div>

        <!-- Modal -->
        <div id="mathModal"
            class="fixed inset-0 bg-black bg-opacity-50 modal-overlay flex items-center justify-center z-50 p-4 hidden min-h-screen">
            <div class="bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="flex justify-between items-center p-6 border-b bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h2 class="text-2xl font-bold text-gray-800">เลือกสัญลักษณ์ทางคณิตศาสตร์</h2>
                    <button id="closeModalBtn"
                        class="text-gray-500 hover:text-gray-700 text-3xl font-bold w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                        ×
                    </button>
                </div>

                <!-- Category Tabs -->
                <div class="flex overflow-x-auto border-b bg-gray-50" id="categoryTabs">
                    <button
                        class="tab-button active px-4 py-3 whitespace-nowrap font-medium transition-all duration-200 text-white"
                        data-category="arithmetic">
                        พื้นฐาน
                    </button>
                    <button
                        class="tab-button px-4 py-3 whitespace-nowrap font-medium transition-all duration-200 text-gray-600 hover:text-blue-600 hover:bg-gray-100"
                        data-category="comparison">
                        เปรียบเทียบ
                    </button>
                    <button
                        class="tab-button px-4 py-3 whitespace-nowrap font-medium transition-all duration-200 text-gray-600 hover:text-blue-600 hover:bg-gray-100"
                        data-category="sets">
                        เซต
                    </button>
                    <button
                        class="tab-button px-4 py-3 whitespace-nowrap font-medium transition-all duration-200 text-gray-600 hover:text-blue-600 hover:bg-gray-100"
                        data-category="logic">
                        ตรรกศาสตร์
                    </button>
                    <button
                        class="tab-button px-4 py-3 whitespace-nowrap font-medium transition-all duration-200 text-gray-600 hover:text-blue-600 hover:bg-gray-100"
                        data-category="calculus">
                        แคลคูลัส
                    </button>
                    <button
                        class="tab-button px-4 py-3 whitespace-nowrap font-medium transition-all duration-200 text-gray-600 hover:text-blue-600 hover:bg-gray-100"
                        data-category="greek">
                        กรีก
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="p-6 max-h-96 overflow-y-auto">
                    <h3 id="categoryTitle" class="text-xl font-semibold mb-4 text-gray-800">
                        การคำนวณพื้นฐาน (Basic Arithmetic)
                    </h3>
                    <div id="symbolsGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <!-- Symbols will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
                    <button id="closeModalFooterBtn"
                        class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        ปิด
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Math symbols data
        const mathSymbols = {
            arithmetic: {
                title: 'การคำนวณพื้นฐาน (Basic Arithmetic)',
                symbols: [{
                        symbol: '+',
                        name: 'บวก (Addition)'
                    },
                    {
                        symbol: '−',
                        name: 'ลบ (Subtraction)'
                    },
                    {
                        symbol: '×',
                        name: 'คูณ (Multiplication)'
                    },
                    {
                        symbol: '⋅',
                        name: 'คูณ (Dot Multiplication)'
                    },
                    {
                        symbol: '÷',
                        name: 'หาร (Division)'
                    },
                    {
                        symbol: '/',
                        name: 'หาร (Slash Division)'
                    },
                    {
                        symbol: '=',
                        name: 'เท่ากับ (Equal)'
                    },
                    {
                        symbol: '%',
                        name: 'เปอร์เซ็นต์ (Percent)'
                    },
                    {
                        symbol: '!',
                        name: 'แฟกทอเรียล (Factorial)'
                    },
                    {
                        symbol: '°',
                        name: 'องศา (Degree)'
                    }
                ]
            },
            comparison: {
                title: 'การเปรียบเทียบ (Comparison)',
                symbols: [{
                        symbol: '≠',
                        name: 'ไม่เท่ากับ (Not Equal)'
                    },
                    {
                        symbol: '<',
                        name: 'น้อยกว่า (Less Than)'
                    },
                    {
                        symbol: '>',
                        name: 'มากกว่า (Greater Than)'
                    },
                    {
                        symbol: '≤',
                        name: 'น้อยกว่าหรือเท่ากับ (Less Than or Equal)'
                    },
                    {
                        symbol: '≥',
                        name: 'มากกว่าหรือเท่ากับ (Greater Than or Equal)'
                    },
                    {
                        symbol: '≈',
                        name: 'ประมาณ (Approximately)'
                    }
                ]
            },
            sets: {
                title: 'ทฤษฎีเซต (Set Theory)',
                symbols: [{
                        symbol: '{ }',
                        name: 'วงเล็บปีกกา (Set Brackets)'
                    },
                    {
                        symbol: '∈',
                        name: 'เป็นสมาชิกของ (Element Of)'
                    },
                    {
                        symbol: '∉',
                        name: 'ไม่เป็นสมาชิกของ (Not Element Of)'
                    },
                    {
                        symbol: '⊂',
                        name: 'เป็นสับเซตของ (Subset)'
                    },
                    {
                        symbol: '⊃',
                        name: 'เป็นซูเปอร์เซตของ (Superset)'
                    },
                    {
                        symbol: '∪',
                        name: 'ยูเนียน (Union)'
                    },
                    {
                        symbol: '∩',
                        name: 'อินเตอร์เซกชัน (Intersection)'
                    },
                    {
                        symbol: '∅',
                        name: 'เซตว่าง (Empty Set)'
                    }
                ]
            },
            logic: {
                title: 'ตรรกศาสตร์ (Logic)',
                symbols: [{
                        symbol: '∀',
                        name: 'สำหรับทุกตัว (For All)'
                    },
                    {
                        symbol: '∃',
                        name: 'มีบางตัว (There Exists)'
                    },
                    {
                        symbol: '¬',
                        name: 'นิเสธ (Not)'
                    },
                    {
                        symbol: '∧',
                        name: 'และ (And)'
                    },
                    {
                        symbol: '∨',
                        name: 'หรือ (Or)'
                    },
                    {
                        symbol: '→',
                        name: 'ถ้า...แล้ว... (If...Then)'
                    },
                    {
                        symbol: '↔',
                        name: 'ก็ต่อเมื่อ (If and Only If)'
                    }
                ]
            },
            calculus: {
                title: 'แคลคูลัสและพีชคณิตขั้นสูง (Calculus & Advanced Algebra)',
                symbols: [{
                        symbol: '∑',
                        name: 'ซิกมา (Summation)'
                    },
                    {
                        symbol: '∏',
                        name: 'พายตัวใหญ่ (Product)'
                    },
                    {
                        symbol: '∫',
                        name: 'อินทิกรัล (Integral)'
                    },
                    {
                        symbol: 'd/dx',
                        name: 'อนุพันธ์ (Derivative)'
                    },
                    {
                        symbol: '∂',
                        name: 'อนุพันธ์ย่อย (Partial Derivative)'
                    },
                    {
                        symbol: '∞',
                        name: 'อนันต์ (Infinity)'
                    },
                    {
                        symbol: '√',
                        name: 'รากที่สอง (Square Root)'
                    },
                    {
                        symbol: '∛',
                        name: 'รากที่สาม (Cube Root)'
                    },
                    {
                        symbol: '|x|',
                        name: 'ค่าสัมบูรณ์ (Absolute Value)'
                    }
                ]
            },
            greek: {
                title: 'อักษรกรีก (Greek Letters)',
                symbols: [{
                        symbol: 'π',
                        name: 'พาย (Pi)'
                    },
                    {
                        symbol: 'θ',
                        name: 'ทีตา (Theta)'
                    },
                    {
                        symbol: 'α',
                        name: 'อัลฟา (Alpha)'
                    },
                    {
                        symbol: 'β',
                        name: 'เบตา (Beta)'
                    },
                    {
                        symbol: 'γ',
                        name: 'แกมมา (Gamma)'
                    },
                    {
                        symbol: 'Δ',
                        name: 'เดลตาตัวใหญ่ (Delta)'
                    },
                    {
                        symbol: 'δ',
                        name: 'เดลตาตัวเล็ก (delta)'
                    },
                    {
                        symbol: 'λ',
                        name: 'แลมบ์ดา (Lambda)'
                    },
                    {
                        symbol: 'μ',
                        name: 'มิว (Mu)'
                    },
                    {
                        symbol: 'σ',
                        name: 'ซิกมาตัวเล็ก (sigma)'
                    }
                ]
            }
        };

        // DOM elements
        const mathInput = document.getElementById('mathInput');
        const mathModal = document.getElementById('mathModal');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const closeModalFooterBtn = document.getElementById('closeModalFooterBtn');
        const clearBtn = document.getElementById('clearBtn');
        const previewSection = document.getElementById('previewSection');
        const previewText = document.getElementById('previewText');
        const categoryTitle = document.getElementById('categoryTitle');
        const symbolsGrid = document.getElementById('symbolsGrid');
        const categoryTabs = document.getElementById('categoryTabs');

        let activeCategory = 'arithmetic';

        // Initialize
        function init() {
            renderSymbols(activeCategory);
            setupEventListeners();
            updatePreview();
        }

        // Setup event listeners
        function setupEventListeners() {
            openModalBtn.addEventListener('click', openModal);
            closeModalBtn.addEventListener('click', closeModal);
            closeModalFooterBtn.addEventListener('click', closeModal);
            clearBtn.addEventListener('click', clearInput);
            mathInput.addEventListener('input', updatePreview);

            // Category tabs
            categoryTabs.addEventListener('click', (e) => {
                if (e.target.classList.contains('tab-button')) {
                    const category = e.target.dataset.category;
                    setActiveCategory(category);
                }
            });

            // Close modal on overlay click
            mathModal.addEventListener('click', (e) => {
                if (e.target === mathModal) {
                    closeModal();
                }
            });

            // Close modal on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !mathModal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        }

        // Open modal
        function openModal() {
            mathModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Close modal
        function closeModal() {
            mathModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Clear input
        function clearInput() {
            mathInput.value = '';
            updatePreview();
        }

        // Update preview
        function updatePreview() {
            const value = mathInput.value.trim();
            if (value) {
                previewText.textContent = value;
                closeModal();
                previewSection.classList.remove('hidden');
            } else {
                previewSection.classList.add('hidden');
            }
        }

        // Set active category
        function setActiveCategory(category) {
            activeCategory = category;

            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                if (btn.dataset.category === category) {
                    btn.classList.add('active', 'text-white');
                    btn.classList.remove('text-gray-600', 'hover:text-blue-600', 'hover:bg-gray-100');
                } else {
                    btn.classList.remove('active', 'text-white');
                    btn.classList.add('text-gray-600', 'hover:text-blue-600', 'hover:bg-gray-100');
                }
            });

            renderSymbols(category);
        }

        // Render symbols
        function renderSymbols(category) {
            const categoryData = mathSymbols[category];
            categoryTitle.textContent = categoryData.title;

            symbolsGrid.innerHTML = '';

            categoryData.symbols.forEach(item => {
                const button = document.createElement('button');
                button.className =
                    'symbol-button p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 group';
                button.title = item.name;
                button.innerHTML = `
                    <div class="text-2xl font-bold text-center mb-2 text-gray-800 group-hover:text-blue-600">
                        ${item.symbol}
                    </div>
                    <div class="text-xs text-gray-600 text-center leading-tight">
                        ${item.name}
                    </div>
                `;

                button.addEventListener('click', () => {
                    insertSymbol(item.symbol);
                });

                symbolsGrid.appendChild(button);
            });
        }

        // Insert symbol into input
        function insertSymbol(symbol) {
            const cursorPosition = mathInput.selectionStart;
            const currentValue = mathInput.value;
            const newValue = currentValue.slice(0, cursorPosition) + symbol + currentValue.slice(cursorPosition);

            mathInput.value = newValue;
            mathInput.focus();
            mathInput.setSelectionRange(cursorPosition + symbol.length, cursorPosition + symbol.length);

            updatePreview();
        }

        // Initialize the app
        init();
    </script>
</body>

</html>
