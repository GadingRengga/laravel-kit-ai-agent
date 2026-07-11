<x-forms.input label="qty" name="qty" live-compute-format="idr" />
<x-forms.input label="Price" name="price" live-compute-format="idr" />


<div class=" my-5">
    <x-text.numeric tag="span" live-class="qty < 1 ? 'text-red-500 nt-num' : 'nt-num'" live-compute="qty *price"
        live-compute-format="idr">0</x-text.numeric>
</div>

<x-modal.button type="button" target="modal-lg" modalSize="" live-click="testModal"
    live-target="#modal-lg">Modal</x-modal.button>
<x-button href="{{ route('dashboard') }}" icon-trailing="fa-solid fa-download">Dashboard</x-button>
