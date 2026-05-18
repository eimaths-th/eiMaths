<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @php 
                   $isLocked = isset($campaign) && in_array($campaign->status, ['processing', 'completed']);
                @endphp

                <div class="form-group">
                    <label>Campaign Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $campaign->title ?? '') }}" required {{ $isLocked ? 'readonly' : '' }}>
                </div>

                <div class="form-group">
                    <label>Message (LINE Push) <span class="text-danger">*</span></label>
                    <textarea name="message" id="messageInput" rows="6" class="form-control" required {{ $isLocked ? 'readonly' : '' }}>{{ old('message', $campaign->message ?? '') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Image URL (Optional)</label>
                    <input type="url" name="image_url" id="imageInput" class="form-control" placeholder="https://..." value="{{ old('image_url', $campaign->image_url ?? '') }}" {{ $isLocked ? 'readonly' : '' }}>
                </div>

                <hr>
                <h5><i class="fas fa-users"></i> Segmentation</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Segment Level</label>
                            <select name="segment_level" class="form-control" {{ $isLocked ? 'disabled' : '' }}>
                                <option value="">-- All Levels --</option>
                                @foreach(['ม.1','ม.2','ม.3','ม.4','ม.5','ม.6','TCAS'] as $lvl)
                                    <option value="{{ $lvl }}" {{ old('segment_level', $campaign->segment_level ?? '') == $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Segment Interest</label>
                            <select name="segment_interest" class="form-control" {{ $isLocked ? 'disabled' : '' }}>
                                <option value="">-- All Interests --</option>
                                @foreach(['เพิ่มเกรด','สอบเข้า','ปรับพื้นฐาน','ทดลองเรียน','TCAS'] as $int)
                                    <option value="{{ $int }}" {{ old('segment_interest', $campaign->segment_interest ?? '') == $int ? 'selected' : '' }}>{{ $int }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Segment Status</label>
                            <select name="segment_status" class="form-control" {{ $isLocked ? 'disabled' : '' }}>
                                <option value="">-- All Statuses --</option>
                                @foreach(['new_lead','warm_lead','hot_lead','waiting_admin','customer'] as $stat)
                                    <option value="{{ $stat }}" {{ old('segment_status', $campaign->segment_status ?? '') == $stat ? 'selected' : '' }}>{{ $stat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <hr>
                <h5><i class="fas fa-clock"></i> Scheduling & Status</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" id="statusSelect" class="form-control" {{ $isLocked ? 'disabled' : '' }}>
                                <option value="draft" {{ old('status', $campaign->status ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="scheduled" {{ old('status', $campaign->status ?? '') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                @if(isset($campaign) && !in_array($campaign->status, ['draft', 'scheduled']))
                                <option value="{{ $campaign->status }}" selected>{{ ucfirst($campaign->status) }}</option>
                                @else
                                <option value="cancelled" {{ old('status', $campaign->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                @endif
                            </select>
                            @if($isLocked)
                                <input type="hidden" name="status" value="{{ $campaign->status }}">
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Schedule At</label>
                            <input type="datetime-local" name="schedule_at" id="scheduleInput" class="form-control" value="{{ old('schedule_at', isset($campaign->schedule_at) ? \Carbon\Carbon::parse($campaign->schedule_at)->format('Y-m-d\TH:i') : '') }}" {{ $isLocked ? 'readonly' : '' }}>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-footer">
                @if(!$isLocked)
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Campaign</button>
                @endif
            </div>
        </div>
    </div>

    <!-- LINE Preview Box -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header" style="background-color: #06C755; color: white;">
                <h3 class="card-title"><i class="fab fa-line"></i> LINE Preview</h3>
            </div>
            <div class="card-body" style="background-color: #749dd6; border-radius: 0 0 0.25rem 0.25rem; min-height: 400px; display: flex; flex-direction: column;">
                <div class="chat-bubble" style="background: white; border-radius: 15px; padding: 10px; position: relative; margin-top: 20px; align-self: flex-start; max-width: 90%; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                    
                    <img id="previewImage" src="{{ old('image_url', $campaign->image_url ?? '') }}" style="max-width: 100%; border-radius: 10px; margin-bottom: 10px; display: {{ old('image_url', $campaign->image_url ?? '') ? 'block' : 'none' }};">
                    
                    <div id="previewMessage" style="white-space: pre-wrap; word-wrap: break-word;">{{ old('message', $campaign->message ?? 'พิมพ์ข้อความที่นี่...') }}</div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const messageInput = document.getElementById('messageInput');
        const previewMessage = document.getElementById('previewMessage');
        const imageInput = document.getElementById('imageInput');
        const previewImage = document.getElementById('previewImage');
        const statusSelect = document.getElementById('statusSelect');
        const scheduleInput = document.getElementById('scheduleInput');

        if(messageInput) {
            messageInput.addEventListener('input', function() {
                previewMessage.textContent = this.value || 'พิมพ์ข้อความที่นี่...';
            });
        }

        if(imageInput) {
            imageInput.addEventListener('input', function() {
                if (this.value) {
                    previewImage.src = this.value;
                    previewImage.style.display = 'block';
                } else {
                    previewImage.style.display = 'none';
                }
            });
        }

        if(statusSelect && scheduleInput) {
            function toggleSchedule() {
                if (statusSelect.value === 'scheduled') {
                    scheduleInput.setAttribute('required', 'required');
                } else {
                    scheduleInput.removeAttribute('required');
                }
            }
            statusSelect.addEventListener('change', toggleSchedule);
            toggleSchedule();
        }
    });
</script>
