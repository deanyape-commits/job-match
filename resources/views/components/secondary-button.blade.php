<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-white border border-slate-300 rounded-lg font-semibold text-sm text-slate-800 shadow-sm hover:bg-crema focus:outline-none focus:ring-2 focus:ring-coral-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 active:scale-[0.97]']) }}>
    {{ $slot }}
</button>
