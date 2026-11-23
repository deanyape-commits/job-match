{{-- Mensajes flash: redirect()->with('exito', '...') o ->with('error', '...') --}}
@if (session('exito'))
    <div role="status" class="mb-6 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-teal-900">
        {{ session('exito') }}
    </div>
@endif

@if (session('error'))
    <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-900">
        {{ session('error') }}
    </div>
@endif
