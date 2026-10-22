{{-- Deretan logo instansi. Latar transparan, teks logo putih: taruh di atas latar navy. Tinggi diatur pemanggil, mis. class="h-10". --}}
<span {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    @foreach ([1, 2] as $i)
        <img src="{{ asset("images/logo-simkeg{$i}.png") }}" alt="" class="h-full w-auto">
    @endforeach
</span>
