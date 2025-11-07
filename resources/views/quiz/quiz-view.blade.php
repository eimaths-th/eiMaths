@extends('layouts.app')

@section('style')
    @include('users.style')
    <script type="text/javascript" async
        src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js?config=TeX-MML-AM_CHTML"></script>

    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Sarabun', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .quiz-header {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .question-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .question-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .question-number {
            background: #f97316;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .question-type-badge {
            background: #fef3c7;
            color: #92400e;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 12px;
        }

        .option-item {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin: 8px 0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .option-item:hover {
            border-color: #f97316;
            background: #fff7ed;
        }

        .option-item.correct {
            background: #dcfce7;
            border-color: #16a34a;
        }

        .check-btn {
            background: #f97316;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .check-btn:hover {
            background: #ea580c;
            transform: translateY(-1px);
        }

        .answer-display {
            background: #f0f9ff;
            border: 2px solid #0ea5e9;
            border-radius: 8px;
            padding: 12px;
            margin-top: 12px;
        }

        .fraction-display {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
        }
        /* Compact styles used in sort mode */
        .sort-compact .question-card {
            padding: 12px;
            margin-bottom: 12px;
        }
        .sort-compact .question-card .question-number {
            width: 28px; height: 28px; font-size: 12px;
        }
        .sort-compact .question-card .question-type-badge {
            font-size: 10px; padding: 2px 8px;
        }
        .sort-compact .question-card .question-type-badge,
        .sort-compact .question-card .q-check,
        .sort-compact .question-card .answer-display,
        .sort-compact .question-card .option-item,
        .sort-compact .question-card .edit-question-btn,
        .sort-compact .question-card .check-button,
        .sort-compact .question-card .uncheck-button {
            display: none !important;
        }
        .sort-compact .question-card .q-text{
            font-size: 13px !important;
            line-height: 1.3 !important;
            margin: 0 !important;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* show only 2 lines */
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            max-height: 3.2em;
        }
        /* hide everything after the question text */
        .sort-compact .question-card .q-text ~ * { display: none !important; }
        /* hide all blocks after header row inside the card (includes explanation and actions) */
        .sort-compact .question-card > *:not(:first-child) { display: none !important; }
        /* In compact mode, allow images inside the header row to be visible (e.g., image-only questions) */
        .sort-compact .question-card > *:first-child img { display: inline-block !important; max-height: 64px; width: auto; }
        .drag-ghost { opacity: 0.6; }
    </style>
@endsection

@section('content')
    <div style="padding: 20px; max-width: 1200px; margin: 0 auto;">
        <!-- Quiz Header -->
        <div class="quiz-header">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
                <div>
                    <h1 style="margin: 0; font-size: 28px; font-weight: bold;">{{ $quizzes->title }}</h1>
                    <div style="margin-top: 12px; display: flex; gap: 24px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 20 20">
                                <path
                                    d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                            </svg>
                            <span style="font-weight: 500;">ระดับชั้น: {{ $quizzes->level }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span style="font-weight: 500;">เทอม: {{ $quizzes->term }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 20 20">
                                <path
                                    d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                            </svg>
                            <span style="font-weight: 500;">หมวด: {{ $quizzes->section }}</span>
                        </div>
                    </div>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 24px; font-weight: bold;">{{ count($questions) }}</div>
                    <div style="font-size: 14px; opacity: 0.9;">คำถาม</div>
                </div>
            </div>
        </div>
        <!-- ปุ่ม mod แก้ไข  -->
        <div style="margin: 16px 0;">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
                <button id="toggle-edit" type="button" style="background:#0ea5e9;color:#fff;border:none;padding:10px 16px;border-radius:10px;font-weight:700;letter-spacing:.2px;box-shadow:0 6px 14px rgba(14,165,233,.25);cursor:pointer;display:flex;align-items:center;gap:8px;transition:all .2s ease;" onmouseover="this.style.background='#0284c7';this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 18px rgba(14,165,233,.35)';" onmouseout="this.style.background='#0ea5e9';this.style.transform='translateY(0)';this.style.boxShadow='0 6px 14px rgba(14,165,233,.25)';">
                    <svg style="width:18px;height:18px;fill:currentColor;" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h8.586L10.293 6.707a1 1 0 111.414-1.414l4 4 .007.007a.997.997 0 010 1.414l-.007.007-4 4a1 1 0 11-1.414-1.414L12.586 11H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                    ย้าย Question
                </button>
                <button id="toggle-sort" type="button" style="background:#0ea5e9;color:#fff;border:none;padding:10px 16px;border-radius:10px;font-weight:700;letter-spacing:.2px;box-shadow:0 6px 14px rgba(14,165,233,.25);cursor:pointer;display:flex;align-items:center;gap:8px;transition:all .2s ease;" onmouseover="this.style.background='#0284c7';this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 18px rgba(14,165,233,.35)';" onmouseout="this.style.background='#0ea5e9';this.style.transform='translateY(0)';this.style.boxShadow='0 6px 14px rgba(14,165,233,.25)';">
                    <svg style="width:18px;height:18px;fill:currentColor;" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M3 5a1 1 0 011-1h8a1 1 0 110 2H4A1 1 0 013 5zm0 10a1 1 0 011 1v6.586l1.293-1.293a1 1 0 011.414 1.414l-3 3-.007.007a.997.997 0 01-1.414 0l-.007-.007-3-3a1 1 0 111.414-1.414L13 15.586V9a1 1 0 011-1zm0-6a1 1 0 00-1 1v2.586L12.707 5.293a1 1 0 10-1.414 1.414l3 3 .007.007a.997.997 0 001.414 0l.007-.007 3-3a1 1 0 10-1.414-1.414L15 6.586V3a1 1 0 00-1-1z"/>
                    </svg>
                    ย้าย ตำเเหน่ง
                </button>
                <button id="confirm-sort" type="button" style="display:none;background:#10b981;color:#fff;border:none;padding:10px 16px;border-radius:10px;font-weight:700;letter-spacing:.2px;box-shadow:0 6px 14px rgba(16,185,129,.25);cursor:pointer;display:flex;align-items:center;gap:8px;transition:all .2s ease;" onmouseover="this.style.background='#059669';this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 18px rgba(16,185,129,.35)';" onmouseout="this.style.background='#10b981';this.style.transform='translateY(0)';this.style.boxShadow='0 6px 14px rgba(16,185,129,.25)';">
                    <svg style="width:18px;height:18px;fill:currentColor;" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    ยืนยันลำดับ
                </button>
            </div>
            <!-- Confirm Sort Modal -->
            <div id="confirmSortModal" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
                <div style="background: #fff; border-radius: 12px; width: 90%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden;">
                    <div style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb; display:flex; align-items:center; gap:10px;">
                        <svg style="width:20px; height:20px; color:#0ea5e9;" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                        <div style="font-weight:700; color:#111827;">ยืนยันการเปลี่ยนลำดับ</div>
                    </div>
                    <div style="padding: 16px 20px; color:#374151;">
                        ต้องการยืนยันลำดับข้อคำถามใหม่ใช่หรือไม่?
                    </div>
                    <div style="padding: 12px 16px; background:#f9fafb; border-top:1px solid #e5e7eb; display:flex; gap:10px; justify-content:flex-end;">
                        <button id="cancelConfirmSort" type="button" style="background:#e5e7eb; color:#111827; border:none; padding:8px 14px; border-radius:8px; font-weight:600;">ยกเลิก</button>
                        <button id="proceedConfirmSort" type="button" style="background:#10b981; color:#fff; border:none; padding:8px 14px; border-radius:8px; font-weight:700; box-shadow:0 6px 14px rgba(16,185,129,.25);">ยืนยัน</button>
                    </div>
                </div>
            </div>
            <script>
                (function(){
                    var btn = document.getElementById('confirm-sort');
                    var modal = document.getElementById('confirmSortModal');
                    var cancelBtn = document.getElementById('cancelConfirmSort');
                    var proceedBtn = document.getElementById('proceedConfirmSort');

                    if (btn) {
                        // Open modal only on user-initiated clicks (ignore programmatic clicks on init)
                        btn.addEventListener('click', function(e){
                            if (!e.isTrusted) { return; }
                            e.preventDefault();
                            e.stopPropagation();
                            if (modal) {
                                modal.style.display = 'flex';
                            }
                        }, true);
                    }

                    function closeModal(){ if(modal){ modal.style.display='none'; } }
                    if (cancelBtn) { cancelBtn.addEventListener('click', function(){ closeModal(); }); }
                    if (proceedBtn) {
                        proceedBtn.addEventListener('click', function(){
                            closeModal();
                            // Programmatic click skips this handler (due to isTrusted=false) but allows other listeners to run
                            if (btn) { btn.click(); }
                        });
                    }
                })();
            </script>
            <div id="edit-panel" style="display:none; background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow: 0 10px 24px rgba(0,0,0,.06); padding:16px;">
                <div style="display:flex; flex-wrap:wrap; gap:16px;">
                    <div style="min-width:220px; flex:1;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Grade</div>
                        <select id="bulk-level" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; background:#fff; outline:none;">
                            <option value="" selected>Select Grade</option>
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
                    <div style="min-width:180px; flex:1;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Term</div>
                        <select id="bulk-term" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; background:#fff; outline:none;">
                            @for ($i = 1; $i <= 4; $i++)
                                <option value="{{ $i }}" {{ (int)$quizzes->term === $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div style="min-width:180px; flex:1;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Level</div>
                        <select id="bulk-section" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; background:#fff; outline:none;">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ (int)$quizzes->section === $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:14px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; color:#374151;">
                        <input id="select-all" type="checkbox" style="width:16px; height:16px;" />
                        <span>เลือกทั้งหมด</span>
                    </label>
                    <button id="bulk-confirm" type="button" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; padding:10px 18px; border-radius:10px; font-weight:700; letter-spacing:.2px; box-shadow:0 8px 18px rgba(16,185,129,.25); cursor:pointer;">ยืนยัน</button>
                    <!-- Bulk Confirm Modal -->
                    <div id="bulkConfirmModal" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
                        <div style="background: #fff; border-radius: 12px; width: 90%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden;">
                            <div style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb; display:flex; align-items:center; gap:10px;">
                                <svg style="width:20px; height:20px; color:#0ea5e9;" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                <div style="font-weight:700; color:#111827;">ยืนยันการแก้ไขข้อมูลจำนวนมาก</div>
                            </div>
                            <div style="padding: 16px 20px; color:#374151;">
                                ต้องการยืนยันการอัปเดตค่า Grade/Term/Level ให้กับคำถามที่เลือกใช่หรือไม่?
                            </div>
                            <div style="padding: 12px 16px; background:#f9fafb; border-top:1px solid #e5e7eb; display:flex; gap:10px; justify-content:flex-end;">
                                <button id="cancelBulkConfirm" type="button" style="background:#e5e7eb; color:#111827; border:none; padding:8px 14px; border-radius:8px; font-weight:600;">ยกเลิก</button>
                                <button id="proceedBulkConfirm" type="button" style="background:#10b981; color:#fff; border:none; padding:8px 14px; border-radius:8px; font-weight:700; box-shadow:0 6px 14px rgba(16,185,129,.25);">ยืนยัน</button>
                            </div>
                        </div>
                    </div>
                    <script>
                        (function(){
                            var bulkBtn = document.getElementById('bulk-confirm');
                            var modal = document.getElementById('bulkConfirmModal');
                            var cancelBtn = document.getElementById('cancelBulkConfirm');
                            var proceedBtn = document.getElementById('proceedBulkConfirm');

                            if (bulkBtn) {
                                bulkBtn.addEventListener('click', function(e){
                                    if (!e.isTrusted) { return; }
                                    e.preventDefault();
                                    e.stopPropagation();
                                    if (modal) { modal.style.display = 'flex'; }
                                }, true);
                            }

                            function closeModal(){ if(modal){ modal.style.display='none'; } }
                            if (cancelBtn) { cancelBtn.addEventListener('click', function(){ closeModal(); }); }
                            if (proceedBtn) {
                                proceedBtn.addEventListener('click', function(){
                                    closeModal();
                                    if (bulkBtn) { bulkBtn.click(); }
                                });
                            }
                        })();
                    </script>
                </div>
            </div>
        </div>

        <!-- Questions Section -->
        <div style="margin-bottom: 24px;">
            <h2
                style="color: #1f2937; font-size: 24px; font-weight: bold; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
                <svg style="width: 28px; height: 28px; fill: #f97316;" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z"
                        clip-rule="evenodd" />
                </svg>
                รายการคำถาม
            </h2>
            @if ($questions->isEmpty())
                <div
                    style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                    <svg style="width: 64px; height: 64px; fill: #d1d5db; margin-bottom: 16px;" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z"
                            clip-rule="evenodd" />
                    </svg>
                    <h3 style="color: #6b7280; margin: 0; font-size: 18px;">ไม่พบคำถาม</h3>
                    <p style="color: #9ca3af; margin: 8px 0 0 0;">ยังไม่มีคำถามในแบบทดสอบนี้</p>
                </div>
            @else
                <div id="questions-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px;">
                    @foreach ($questions as $key => $question)
                        <div class="question-card" id="question-container-{{ $question->id }}">
                            <div style="display: flex; align-items: flex-start; gap: 16px; margin-bottom: 16px;">
                                <div class="question-number">{{ $key + 1 }}</div>
                                <div class="q-check" style="display:none; margin-top:2px;">
                                    <input type="checkbox" class="q-select" value="{{ $question->id }}" />
                                </div>
                                <div style="flex: 1;">
                                    <div class="question-type-badge">
                                        @if ($question->type === 'written')
                                            คำตอบเขียน
                                        @elseif($question->type === 'options')
                                            ตัวเลือก
                                        @elseif($question->type === 'image')
                                            รูปภาพ
                                        @elseif($question->type === 'fraction')
                                            เศษส่วน
                                        @else
                                            {{ $question->type }}
                                        @endif
                                    </div>
                                    @if(trim((string) $question->text) !== '')
                                        <div class="q-text"
                                            style="font-size: 16px; font-weight: 500; color: #1f2937; line-height: 1.5; margin-bottom: 12px;">
                                            {{ replaceMixedFractions($question->text) }}
                                        </div>
                                    @endif

                                    @if ($question->img_name)
                                        <div style="margin: 12px 0;">
                                            <img src="{{ asset('img_questions/' . $question->img_name) }}"
                                                style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);"
                                                alt="รูปประกอบคำถาม">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- WRITTEN TYPE --}}
                            @if ($question->type === 'written')
                                <div class="answer-display">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                        <svg style="width: 16px; height: 16px; fill: #0ea5e9;" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span style="font-weight: 600; color: #0369a1;">คำตอบที่ถูกต้อง:</span>
                                    </div>
                                    <div style="font-size: 16px; color: #1f2937; font-weight: 500;">
                                        {{ replaceMixedFractions($question->written_answer) }}
                                    </div>
                                </div>

                                {{-- OPTIONS TYPE --}}
                            @elseif ($question->type === 'options')
                                <div style="margin-top: 16px;">
                                    @foreach ($question->options as $optionIndex => $option)
                                        <div class="option-item {{ $option->correct ? 'correct' : '' }}"
                                            style="display: flex; align-items: center; gap: 12px;">
                                            <div
                                                style="width: 24px; height: 24px; border-radius: 50%; background: {{ $option->correct ? '#16a34a' : '#e5e7eb' }}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                                                {{ chr(65 + $optionIndex) }}
                                            </div>
                                            <div style="flex: 1; font-size: 15px; color: #1f2937;">
                                                {{ replaceMixedFractions($option->text) }}
                                            </div>
                                            @if ($option->correct)
                                                <svg style="width: 20px; height: 20px; fill: #16a34a;" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                {{-- IMAGE TYPE --}}
                            @elseif ($question->type === 'image')
                                <div
                                    style="margin-top: 16px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                                    @foreach ($question->images as $imgIndex => $image)
                                        <div class="option-item {{ $image->correct ? 'correct' : '' }}"
                                            style="text-align: center; padding: 16px; position: relative;">
                                            <div
                                                style="position: absolute; top: 8px; left: 8px; width: 24px; height: 24px; border-radius: 50%; background: {{ $image->correct ? '#16a34a' : '#e5e7eb' }}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                                                {{ chr(65 + $imgIndex) }}
                                            </div>
                                            @if ($image->correct)
                                                <div style="position: absolute; top: 8px; right: 8px;">
                                                    <svg style="width: 20px; height: 20px; fill: #16a34a;"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            @endif
                                            <img src="{{ asset('images_options/' . $image->img_name) }}"
                                                style="max-width: 120px; height: auto; border-radius: 6px; margin-top: 8px;"
                                                alt="ตัวเลือกรูปภาพ">
                                        </div>
                                    @endforeach
                                </div>

                                {{-- FRACTION TYPE --}}
                            @elseif ($question->type === 'fraction')
                                @php $fraction = $question->fractions->first(); @endphp

                                {{-- Written Fraction --}}
                                @if ($fraction && $fraction->type === 'written')
                                    <div class="answer-display">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                            <svg style="width: 16px; height: 16px; fill: #0ea5e9;" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span style="font-weight: 600; color: #0369a1;">คำตอบที่ถูกต้อง:</span>
                                        </div>
                                        @if ($fraction->answer_type === 'frac')
                                            <div class="fraction-display"
                                                style="text-align: center; padding: 12px; background: #f8fafc; border-radius: 8px;">
                                                {{ toFraction($fraction->numerator, $fraction->denominator) }}
                                            </div>
                                        @else
                                            <div class="fraction-display"
                                                style="text-align: center; padding: 12px; background: #f8fafc; border-radius: 8px;">
                                                {{ toMixedFraction($fraction->numerator, $fraction->denominator,null) }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Option-based Fraction --}}
                                @else
                                    <div style="margin-top: 16px;">
                                        @foreach ($question->fractions as $fracIndex => $option)
                                            @php
                                                $textOption =
                                                    $option->answer_type === 'mixed'
                                                        ? toMixedFraction(

                                                            $option->numerator,
                                                            $option->denominator,
                                                            null
                                                        )
                                                        : toFraction($option->numerator, $option->denominator);
                                            @endphp

                                            <div class="option-item {{ $option->correct ? 'correct' : '' }}"
                                                style="display: flex; align-items: center; gap: 12px;">
                                                <div
                                                    style="width: 24px; height: 24px; border-radius: 50%; background: {{ $option->correct ? '#16a34a' : '#e5e7eb' }}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                                                    {{ chr(65 + $fracIndex) }}
                                                </div>
                                                <div style="flex: 1; font-size: 15px; color: #1f2937; text-align: center;">
                                                    <div class="fraction-display"
                                                        style="padding: 8px; background: #f8fafc; border-radius: 6px; display: inline-block;">
                                                        {{ $textOption }}
                                                    </div>
                                                </div>
                                                @if ($option->correct)
                                                    <svg style="width: 20px; height: 20px; fill: #16a34a;"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif

                            {{-- Answer Explanation Section --}}
                            @if($question->answer_explanation || $question->answer_explanation_image)
                                <div style="margin-top: 24px; padding: 20px; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius: 12px; border-left: 4px solid #3b82f6; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); transition: all 0.3s ease;"
                                     onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 16px rgba(0, 0, 0, 0.1)';"
                                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.05)';">

                                    {{-- Explanation Header --}}
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                        <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);">
                                            <svg style="width: 18px; height: 18px; fill: white;" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <h4 style="margin: 0; color: #1e293b; font-size: 16px; font-weight: 600;">คำอธิบายเฉลย</h4>
                                    </div>

                                    {{-- Explanation Text --}}
                                    @if($question->answer_explanation)
                                        <div style="background: white; padding: 16px; border-radius: 8px; margin-bottom: 16px; border: 1px solid #e2e8f0; line-height: 1.6;">
                                            <p style="margin: 0; color: #374151; font-size: 15px; white-space: pre-wrap;">{{ $question->answer_explanation }}</p>
                                        </div>
                                    @endif

                                    {{-- Explanation Image --}}
                                    @if($question->answer_explanation_image)
                                        <div style="text-align: center; margin-top: 16px;">
                                            <div style="display: inline-block; position: relative; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; min-width: 200px; max-width: 400px;"
                                                 onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 8px 24px rgba(0, 0, 0, 0.15)';"
                                                 onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 16px rgba(0, 0, 0, 0.1)';">
                                                <img src="{{ asset('img_questions/' . $question->answer_explanation_image) }}"
                                                     alt="คำอธิบายเฉลย"
                                                     style="width: 100%; height: auto; display: block; border: 2px solid white;">
                                                {{-- <div style="position: absolute; top: 8px; right: 8px; background: rgba(0, 0, 0, 0.7); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; backdrop-filter: blur(4px);">
                                                    <i class="fas fa-image" style="margin-right: 4px;"></i>เฉลย
                                                </div> --}}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                            {{-- Action Buttons --}}
                            <div
                                style="margin-top: 20px; text-align: right; border-top: 1px solid #e5e7eb; padding-top: 16px; display: flex; justify-content: space-between;">
                                <div>
                                    <button type="button" class="edit-question-btn" id="getEditQuestionData"
                                        data-id="{{ $question->id }}"
                                        style="
                                                background: linear-gradient(135deg, #3b82f6, #2563eb);
                                                color: white;
                                                border: none;
                                                padding: 8px 16px;
                                                border-radius: 6px;
                                                font-weight: 500;
                                                cursor: pointer;
                                                transition: all 0.2s ease;
                                                box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
                                            "
                                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(59, 130, 246, 0.4)';"
                                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(59, 130, 246, 0.3)';"
                                        onmousedown="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 2px rgba(59, 130, 246, 0.3)';"
                                        onmouseup="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(59, 130, 246, 0.4)';">
                                        <i class="fas fa-edit"></i> แก้ไขคำถาม
                                    </button>
                                </div>
                                <div>
                                    <button class="check-btn check-button" data-question-id="{{ $question->id }}"
                                        style="
                                                background: linear-gradient(135deg, #10b981, #059669);
                                                color: white;
                                                border: none;
                                                padding: 8px 16px;
                                                border-radius: 6px;
                                                font-weight: 500;
                                                cursor: pointer;
                                                transition: all 0.2s ease;
                                                box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
                                                margin-left: 8px;
                                            "
                                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(16, 185, 129, 0.4)';"
                                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(16, 185, 129, 0.3)';"
                                        onmousedown="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 2px rgba(16, 185, 129, 0.3)';"
                                        onmouseup="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(16, 185, 129, 0.4)';"
                                        onclick="checkAnswer({{ $question->id }})">
                                        <i class="fas fa-check-circle"></i> ตรวจคำตอบ
                                    </button>
                                    <button class="uncheck-button" data-question-id="{{ $question->id }}"
                                        style="
                                                display: none;
                                                background: linear-gradient(135deg, #ef4444, #dc2626);
                                                color: white;
                                                border: none;
                                                padding: 8px 16px;
                                                border-radius: 6px;
                                                font-weight: 500;
                                                cursor: pointer;
                                                transition: all 0.2s ease;
                                                box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
                                                margin-left: 8px;
                                            "
                                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(239, 68, 68, 0.4)';"
                                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(239, 68, 68, 0.3)';"
                                        onmousedown="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 2px rgba(239, 68, 68, 0.3)';"
                                        onmouseup="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(239, 68, 68, 0.4)';"
                                        onclick="uncheckAnswer({{ $question->id }})">
                                        <i class="fas fa-times-circle"></i> ยกเลิก
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @include('question.question-form', ['editing' => true, 'question' => null, 'options' => []])

@endsection

@section('script')
    @include('question.scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <script>
        $(document).ready(function() {
             $('#confirm-sort').hide();
        });


        let checkedQuestions = new Set(JSON.parse(localStorage.getItem('checkedQuestions')) || []);

        function updateUI(questionId, isChecked) {
            const $container = $('#question-container-' + questionId);
            const $checkBtn = $('.check-button[data-question-id="' + questionId + '"]');
            const $uncheckBtn = $('.uncheck-button[data-question-id="' + questionId + '"]');

            if (isChecked) {
                // Add checked styling to the question card
                $container.css({
                    'background': 'linear-gradient(135deg, #ecfdf5, #d1fae5)',
                    'border': '2px solid #10b981',
                    'box-shadow': '0 8px 25px rgba(16, 185, 129, 0.15)'
                });

                // Add a checkmark badge to the question number
                const $questionNumber = $container.find('.question-number');
                if (!$questionNumber.find('.check-badge').length) {
                    $questionNumber.append(
                        '<div class="check-badge" style="position: absolute; top: -8px; right: -8px; background: #10b981; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">✓</div>'
                    );
                }

                $checkBtn.hide();
                $uncheckBtn.css('display', 'inline-flex');
            } else {
                // Remove checked styling
                $container.css({
                    'background': 'white',
                    'border': '1px solid #e5e7eb',
                    'box-shadow': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'
                });

                // Remove checkmark badge
                $container.find('.check-badge').remove();

                $checkBtn.show();
                $uncheckBtn.hide();
            }
        }

        $(document).ready(function() {
            checkedQuestions.forEach(id => {
                updateUI(id, true);
            });
        });

        $(document).on('click', '.check-button', function() {
            const questionId = $(this).data('question-id');
            checkedQuestions.add(questionId);
            localStorage.setItem('checkedQuestions', JSON.stringify([...checkedQuestions]));
            updateUI(questionId, true);
        });

        $(document).on('click', '.uncheck-button', function() {
            const questionId = $(this).data('question-id');
            checkedQuestions.delete(questionId);
            localStorage.setItem('checkedQuestions', JSON.stringify([...checkedQuestions]));
            updateUI(questionId, false);
        });
    </script>

    <script>
        // Sort mode toggle and save
        let sortableInstance = null;
        const grid = document.getElementById('questions-grid');
        let originalOrder = [];
        let sortPending = false;

        function enterSortMode() {
            if (!grid) return;
            // switch to compact multi-column for overview
            grid.dataset.originalTemplate = grid.style.gridTemplateColumns;
            grid.dataset.originalGap = grid.style.gap;
            grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(180px, 1fr))';
            grid.style.gap = '6px';

            // remember original DOM order
            originalOrder = Array.from(grid.querySelectorAll('.question-card')).map(el => el.id);
            sortPending = true;

            document.querySelectorAll('.question-card').forEach((el) => {
                el.style.cursor = 'move';
            });

            document.getElementById('confirm-sort').style.display = 'inline-block';
            grid.classList.add('sort-compact');
            // hide edit-mode button while sorting
            $('#toggle-edit').hide();

            if (!sortableInstance) {
                sortableInstance = Sortable.create(grid, {
                    animation: 150,
                    handle: '.question-card',
                    ghostClass: 'drag-ghost'
                });
            }
        }

        function exitSortMode(restore = true) {
            if (!grid) return;
            // restore grid layout
            const original = grid.dataset.originalTemplate;
            if (original) grid.style.gridTemplateColumns = original;
            const originalGap = grid.dataset.originalGap;
            if (originalGap !== undefined) grid.style.gap = originalGap;

            // if not confirmed, restore DOM order
            if (restore && sortPending && originalOrder.length) {
                const map = new Map();
                Array.from(grid.querySelectorAll('.question-card')).forEach(el => map.set(el.id, el));
                originalOrder.forEach(id => {
                    const el = map.get(id);
                    if (el) grid.appendChild(el);
                });
            }

            document.querySelectorAll('.question-card').forEach((el) => {
                el.style.cursor = '';
            });

            document.getElementById('confirm-sort').style.display = 'none';
            grid.classList.remove('sort-compact');
            // show edit-mode button back
            $('#toggle-edit').show();

            if (sortableInstance) {
                sortableInstance.destroy();
                sortableInstance = null;
            }

            // clear state
            originalOrder = [];
            sortPending = false;
        }

        $(document).on('click', '#toggle-sort', function () {
            if (sortableInstance) {
                // user cancels sort — restore original order
                exitSortMode(true);
            } else {
                enterSortMode();
            }
        });

        $(document).on('click', '#confirm-sort', function () {
            if (!grid) return;
            const ids = Array.from(grid.querySelectorAll('.question-card')).map(el => {
                const id = el.id.replace('question-container-', '');
                return parseInt(id, 10);
            });

            if (!ids.length) return;

            $.ajax({
                url: "{{ url('/quizzes/' . $quizzes->id . '/order') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ordered_ids: ids
                }
            }).done(function (res) {
                Swal.fire({ icon: 'success', title: res.message || 'บันทึกลำดับสำเร็จ', timer: 1200, showConfirmButton: false });
                // confirmed — keep the new order (no restore)
                exitSortMode(false);
                setTimeout(function(){ location.reload(); }, 1200);
            }).fail(function (xhr) {
                let msg = 'เกิดข้อผิดพลาด';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                else if (xhr.responseJSON && xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                Swal.fire({ icon: 'error', title: msg });
            });
        });
    </script>
    @include('quiz.scripts')

    <script>
        // Toggle edit mode panel and checkboxes
        $(document).on('click', '#toggle-edit', function () {
            const $panel = $('#edit-panel');
            const visible = $panel.is(':visible');
            $panel.toggle(!visible);
            $('.q-check').toggle(!visible);
            // hide/show the other mode button accordingly
            if (!visible) {
                // entering edit mode -> hide sort button
                $('#toggle-sort').hide();
            } else {
                // exiting edit mode -> show sort button
                $('#toggle-sort').show();
            }
            if (visible) {
                $('#select-all').prop('checked', false).trigger('change');
                // clear highlight outlines applied in edit mode
                $('.question-card').css({ outline: 'none', outlineOffset: '0' });
                // clear all individual selections
                $('.q-check .q-select').prop('checked', false);
            }
        });

        // Select all checkboxes
        $(document).on('change', '#select-all', function () {
            const checked = $(this).is(':checked');
            $('.q-check:visible .q-select').prop('checked', checked);
            // update card outlines to match selection
            $('.q-check:visible').each(function(){
                const $cb = $(this).find('.q-select');
                const $card = $(this).closest('.question-card');
                if ($cb.prop('checked')) {
                    $card.css({ outline: '2px solid #10b981', outlineOffset: '2px' });
                } else {
                    $card.css({ outline: 'none', outlineOffset: '0' });
                }
            });
        });

        // Click on question-card toggles its checkbox in edit mode
        $(document).on('click', '.question-card', function (e) {
            if (!$('#edit-panel').is(':visible')) return;
            const tag = (e.target.tagName || '').toLowerCase();
            if (['input','button','select','a','textarea','svg','path','label'].includes(tag)) return;

            const $card = $(this);
            const $cb = $card.find('.q-check .q-select');
            if (!$cb.length) return;

            const newState = !$cb.prop('checked');
            $cb.prop('checked', newState);

            // update visual highlight
            if (newState) {
                $card.css({ outline: '2px solid #10b981', outlineOffset: '2px' });
            } else {
                $card.css({ outline: 'none', outlineOffset: '0' });
            }

            // sync select-all
            const total = $('.q-check:visible .q-select').length;
            const selected = $('.q-check:visible .q-select:checked').length;
            $('#select-all').prop('checked', total > 0 && total === selected);
        });

        // Bulk confirm submit
        $(document).on('click', '#bulk-confirm', function () {
            const ids = $('.q-select:checked').map(function(){ return $(this).val(); }).get();
            if (ids.length === 0) {
                Swal.fire({ icon: 'warning', title: 'กรุณาเลือกข้อสอบ', timer: 2000, showConfirmButton: false });
                return;
            }

            const payload = {
                _token: '{{ csrf_token() }}',
                question_ids: ids,
                level: $('#bulk-level').val(),
                term: $('#bulk-term').val(),
                section: $('#bulk-section').val(),
                // current quiz meta
                quiz_id: {{ (int) $quizzes->id }},
                quiz_title: @json($quizzes->title),
                quiz_level: @json($quizzes->level),
                quiz_term: @json($quizzes->term),
                quiz_section: @json($quizzes->section),
            };

            $.post("{{ route('questions.bulk.update') }}", payload)
                .done(function (res) {
                    Swal.fire({ icon: 'success', title: res.message || 'อัปเดตสำเร็จ', timer: 1200, showConfirmButton: false });
                    setTimeout(function(){ location.reload(); }, 1200);
                })
                .fail(function (xhr) {
                    let msg = 'เกิดข้อผิดพลาด';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    else if (xhr.responseJSON && xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    Swal.fire({ icon: 'error', title: msg });
                });
        });
    </script>

@endsection
