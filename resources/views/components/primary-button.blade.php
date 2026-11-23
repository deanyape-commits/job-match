<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-coral-700 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-coral-800 focus:bg-coral-800 active:bg-coral-900 focus:outline-none focus:ring-2 focus:ring-coral-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150 active:scale-[0.97]']) }}>
    {{ $slot }}
</button>
