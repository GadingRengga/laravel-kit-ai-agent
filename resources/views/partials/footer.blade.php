<footer live-spa-region="footer"
    class="border-t border-indigo-100 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 lg:px-8 py-4 shrink-0">
    <div
        class="flex flex-col sm:flex-row items-center justify-between gap-2 text-[12px] text-surface-400 dark:text-slate-500">
        <p>&copy; {{ date('Y') }} <span
                class="font-medium text-indigo-600 dark:text-indigo-400">{{ config('app.name', 'Netra UI') }}</span>. All
            rights reserved.</p>
        <div class="flex items-center gap-4">
            <a href="#" class="hover:text-indigo-600 transition-colors">Privacy</a>
            <a href="#" class="hover:text-indigo-600 transition-colors">Terms</a>
            <a href="#" class="hover:text-indigo-600 transition-colors">Support</a>
            <span class="text-surface-300 dark:text-slate-700">v1.0.0</span>
        </div>
    </div>

    <x-modal.shell id="modal-xs" size="xs"></x-modal.shell>
    <x-modal.shell id="modal-sm" size="sm"></x-modal.shell>
    <x-modal.shell id="modal-md" size="md"></x-modal.shell>
    <x-modal.shell id="modal-lg" size="lg"></x-modal.shell>
    <x-modal.shell id="modal-xl" size="xl"></x-modal.shell>
    <x-modal.shell id="modal-full" size="full"></x-modal.shell>
</footer>
