@php($selected = $selected ?? [])
@foreach ($permissionGroups as $group => $permissions)
    <h6 class="fw-semibold mt-3 d-flex align-items-center">
        {{ $group }}
        <button type="button" class="btn btn-sm btn-link p-0 ms-2" style="font-size: 12px;"
            onclick="this.closest('.modal-body, .card-body').querySelectorAll('.perm-group-{{ Str::slug($group) }}').forEach(function (cb) { cb.checked = true; });">
            select all
        </button>
        <button type="button" class="btn btn-sm btn-link p-0 ms-2 text-muted" style="font-size: 12px;"
            onclick="this.closest('.modal-body, .card-body').querySelectorAll('.perm-group-{{ Str::slug($group) }}').forEach(function (cb) { cb.checked = false; });">
            clear
        </button>
    </h6>
    <div class="row g-2">
        @foreach ($permissions as $key => $label)
            <div class="col-md-4">
                <div class="form-check">
                    <input class="form-check-input perm-group-{{ Str::slug($group) }}" type="checkbox"
                        name="permissions[]" value="{{ $key }}" id="perm-{{ $prefix }}-{{ Str::slug($key) }}"
                        @checked(in_array($key, $selected, true))>
                    <label class="form-check-label font-size-13" for="perm-{{ $prefix }}-{{ Str::slug($key) }}">{{ $label }}</label>
                </div>
            </div>
        @endforeach
    </div>
@endforeach
