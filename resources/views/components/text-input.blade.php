@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-300 focus:border-coral-600 focus:ring-coral-600 rounded-lg shadow-sm']) }}>
