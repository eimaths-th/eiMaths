@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>ย้ายสาขา</h2>
    <p>ย้ายสังกัดผู้ใช้หรือนักเรียน โดยเก็บเอกสารและประวัติเดิมไว้ที่สาขาผู้ออก</p>
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    <form method="POST" action="{{ route('branch-transfers.store') }}">
        @csrf
        <div class="form-group"><label for="transfer-type">ประเภท</label>
            <select name="type" id="transfer-type" class="form-control"><option value="student">นักเรียน</option><option value="user">ผู้ใช้</option></select>
        </div>
        <div class="form-group"><label for="transfer-search">ค้นหาชื่อหรือรหัสนักเรียน</label>
            <div class="input-group"><input id="transfer-search" class="form-control" minlength="2" maxlength="100">
            <div class="input-group-append"><button type="button" id="find-transfer" class="btn btn-secondary">ค้นหา</button></div></div>
            <label for="record-id" class="mt-2">เลือกคนที่ต้องการย้าย</label>
            <select id="record-id" name="record_id" required class="form-control"><option value="">กรุณาค้นหาและเลือก</option></select>
            <small id="transfer-search-status" role="status" class="form-text text-muted"></small>
        </div>
        <div class="form-group"><label for="branch-id">สาขาปลายทาง</label><select id="branch-id" name="to_branch_id" class="form-control">@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
        <div class="form-group" id="transfer-position-group" hidden><label for="transfer-position">แผนกในสาขาปลายทาง</label>
            <select id="transfer-position" name="position_id" class="form-control"><option value="">ยังไม่กำหนดแผนก</option></select>
        </div>
        <div class="form-group"><label for="transfer-reason">เหตุผล</label><textarea id="transfer-reason" name="reason" minlength="5" maxlength="1000" required class="form-control">{{ old('reason') }}</textarea></div>
        <button class="btn btn-primary">ยืนยันย้ายสาขา</button>
    </form>
</div>
@endsection


@section('script')
<script>
(() => {
    const positions = @json($positions);
    const type = document.getElementById('transfer-type');
    const results = document.getElementById('record-id');
    const status = document.getElementById('transfer-search-status');
    const destination = document.getElementById('branch-id');
    const position = document.getElementById('transfer-position');
    function setPositions() {
        document.getElementById('transfer-position-group').hidden = type.value !== 'user';
        position.disabled = type.value !== 'user';
        position.replaceChildren(new Option('ยังไม่กำหนดแผนก', ''));
        positions.filter(item => String(item.department_id) === destination.value)
            .forEach(item => position.add(new Option(item.name, item.id)));
    }
    type.addEventListener('change', () => {
        results.replaceChildren(new Option('กรุณาค้นหาและเลือก', ''));
        setPositions();
    });
    destination.addEventListener('change', setPositions);
    document.getElementById('find-transfer').addEventListener('click', async () => {
        const q = document.getElementById('transfer-search').value.trim();
        if (q.length < 2) { status.textContent = 'กรุณากรอกอย่างน้อย 2 ตัวอักษร'; return; }
        const searchedType = type.value;
        results.replaceChildren(new Option('กรุณาเลือก', ''));
        status.textContent = 'กำลังค้นหา…';
        try {
            const url = new URL(@json(route('branch-transfers.targets')));
            url.searchParams.set('type', searchedType);
            url.searchParams.set('q', q);
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            if (!response.ok) { throw new Error('ค้นหาไม่สำเร็จ กรุณาตรวจสอบสิทธิ์หรือลองใหม่'); }
            const payload = await response.json();
            if (type.value !== searchedType) { return; }
            payload.data.forEach(item => results.add(new Option(item.label, item.id)));
            status.textContent = payload.data.length ? 'เลือกจากผลการค้นหา (สูงสุด 30 รายการ)' : 'ไม่พบข้อมูล';
        } catch (error) { status.textContent = error.message; }
    });
    setPositions();
})();
</script>
@endsection
