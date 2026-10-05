@php
    $notificationErrors = $errors ?? session('errors', new \Illuminate\Support\ViewErrorBag);
    $successMessage = session('message');
@endphp

@if($successMessage || $notificationErrors->any())
    <div class="toast-container position-fixed top-0 end-0 p-3" aria-live="polite" aria-atomic="true" style="z-index: 1090;">
        @if($successMessage)
            <div class="toast position-static m-0 mb-2 text-white bg-success" role="status" data-save-notification data-coreui-autohide="true" data-coreui-delay="5000">
                <div class="toast-header text-white bg-success">
                    <strong class="me-auto">{{ __('Success') }}</strong>
                    <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="toast" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="toast-body">{{ $successMessage }}</div>
            </div>
        @endif
        @if($notificationErrors->any())
            <div class="toast position-static m-0 mb-2 text-white bg-danger" role="alert" aria-live="assertive" data-save-notification data-coreui-autohide="true" data-coreui-delay="5000">
                <div class="toast-header text-white bg-danger">
                    <strong class="me-auto">{{ __('Error') }}</strong>
                    <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="toast" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="toast-body">
                    <ul class="mb-0 ps-3">
                        @foreach($notificationErrors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
    @push('footer-scripts')
        <script type="module">
            const initializeSaveNotifications = () => {
                document.querySelectorAll('[data-save-notification]').forEach(element => {
                    window.coreui.Toast.getOrCreateInstance(element).show();
                });
            };
            if (document.readyState === 'complete') {
                initializeSaveNotifications();
            } else {
                document.addEventListener('DOMContentLoaded', initializeSaveNotifications, { once: true });
            }
        </script>
    @endpush
@endif
