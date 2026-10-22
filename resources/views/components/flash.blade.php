@if (session('status'))
    <div class="flex items-center gap-2 rounded-md border border-success/30 bg-success-soft px-4 py-3 text-sm text-success" role="status">
        <x-icon name="circle-check" /> {{ session('status') }}
    </div>
@endif
@if (session('error'))
    <div class="flex items-center gap-2 rounded-md border border-danger/30 bg-danger-soft px-4 py-3 text-sm text-danger" role="alert">
        <x-icon name="triangle-alert" /> {{ session('error') }}
    </div>
@endif
