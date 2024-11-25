<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-bs-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        Action
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
        
            {{-- <a href="{{ route('courseDetails', [$query->id]) . '?type=courseDetails' }}" class="dropdown-item"> --}}
            <a href="#" class="dropdown-item">
                Edit
            </a>
            <a href="#" class="dropdown-item">
            {{-- <a href="{{ route('courseDetails', [$query->id]) }}" class="dropdown-item"> --}}
                View
            </a>
            {{-- <a onclick="confirm_modal('{{ route('course.delete', $query->id) }}')" class="dropdown-item edit_brand"> --}}
            <a onclick="confirm_modal('#')" class="dropdown-item edit_brand">
                Delete
            </a>
    </div>
</div>
