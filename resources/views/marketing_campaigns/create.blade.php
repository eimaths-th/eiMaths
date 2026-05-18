@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Create Marketing Campaign</h2>
            <a href="{{ route('marketing-campaigns.index') }}" class="btn btn-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('marketing-campaigns.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
                </div>

                <div class="mb-3">
                    <label>Message (LINE Push Text)</label>
                    <textarea name="message" class="form-control" rows="5" required>{{ old('message') }}</textarea>
                </div>

                <div class="mb-3">
                    <label>Image URL (Optional)</label>
                    <input type="url" name="image_url" class="form-control" value="{{ old('image_url') }}">
                </div>

                <hr>
                <h5>Segmentation (Target Audience)</h5>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Level (e.g. ม.1, TCAS)</label>
                        <input type="text" name="segment_level" class="form-control" value="{{ old('segment_level') }}">
                    </div>
                    <div class="col-md-4">
                        <label>Interest (e.g. สอบเข้า, เพิ่มเกรด)</label>
                        <input type="text" name="segment_interest" class="form-control" value="{{ old('segment_interest') }}">
                    </div>
                    <div class="col-md-4">
                        <label>Status (e.g. new_lead, hot_lead)</label>
                        <input type="text" name="segment_status" class="form-control" value="{{ old('segment_status') }}">
                    </div>
                </div>

                <hr>
                <h5>Scheduling</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Schedule At</label>
                        <input type="datetime-local" name="schedule_at" class="form-control" value="{{ old('schedule_at') }}">
                    </div>
                    <div class="col-md-6">
                        <label>Campaign Status</label>
                        <select name="status" class="form-control">
                            <option value="draft">Draft</option>
                            <option value="scheduled">Scheduled</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Campaign</button>
            </form>
        </div>
    </div>
</div>
@endsection
