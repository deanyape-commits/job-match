{{-- Foto de perfil: vista previa con Alpine.js antes de subirla --}}
<section x-data="{ vista: null }">
    <header>
        <h2 class="text-lg font-semibold text-slate-900">Foto de perfil</h2>
    </header>

    <div class="mt-5 flex flex-wrap items-center gap-6">
        {{-- Vista previa: la foto elegida, la actual o las iniciales --}}
        <div class="relative">
            <template x-if="vista">
                <img :src="vista" alt="Vista previa de la nueva foto" class="h-24 w-24 rounded-full object-cover ring-4 ring-coral-100">
            </template>
            <div x-show="!vista">
                <x-avatar :usuario="$user" class="h-24 w-24 text-3xl" />
            </div>
        </div>

        <div class="space-y-3">
            <form method="POST" action="{{ route('profile.foto.update') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                @csrf
                <label for="foto" class="sr-only">Elegir foto</label>
                <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" required
                       @change="const f = $event.target.files[0]; vista = f ? URL.createObjectURL(f) : null"
                       class="block text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:font-semibold file:text-slate-800 hover:file:bg-slate-200">
                <x-primary-button x-show="vista" x-cloak>Guardar foto</x-primary-button>
            </form>

            @if ($user->avatar_ruta)
                <form method="POST" action="{{ route('profile.foto.destroy') }}" onsubmit="return confirm('¿Quitar tu foto de perfil?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm font-semibold text-red-700 hover:text-red-900">Quitar foto</button>
                </form>
            @endif

            <x-input-error :messages="$errors->foto->get('foto')" />
        </div>
    </div>
</section>
