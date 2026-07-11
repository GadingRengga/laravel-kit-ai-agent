<x-modal.content id="trial-modal" title="Info" live-scope="TrialController">
    <div>
        <x-forms.input label="Qty" name="_qty" />
        <x-forms.input label="Price" name="_price" />
        <x-forms.datepicker label="Date" name="date" />
    </div>
    <x-slot name="footer">
        <x-button data-nt-modal-close variant="primary" live-click="trialSave" live-target="#test-container"
            live-loading="#loading">Save
        </x-button>
    </x-slot>
</x-modal.content>
