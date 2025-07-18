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
            <button
                class="tab-button px-4 py-3 whitespace-nowrap font-medium transition-all duration-200 text-gray-600 hover:text-blue-600 hover:bg-gray-100"
                data-category="fraction">
                เศษส่วน
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
