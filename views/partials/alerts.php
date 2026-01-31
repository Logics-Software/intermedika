<?php
// Render flash messages using core Message class
// Use a different approach to avoid conflict with models/Message
$flashMessages = '';
$types = ['success', 'error', 'warning', 'info'];
$toastIndex = 0;

foreach ($types as $type) {
    $message = Session::flash($type);
    if ($message) {
        $alertType = $type === 'error' ? 'danger' : $type;
        $toastId = 'toast-' . $type . '-' . $toastIndex++;
        $messageEscaped = htmlspecialchars($message);
        
        // Get icon
        $icons = [
            'success' => '<svg class="me-2" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="flex-shrink: 0;"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 4.384 6.323a.75.75 0 0 0-1.06 1.061l3.5 3.5a.75.75 0 0 0 1.137-.089l4-5.5a.75.75 0 0 0-.022-1.08z"/></svg>',
            'danger' => '<svg class="me-2" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="flex-shrink: 0;"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/></svg>',
            'warning' => '<svg class="me-2" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="flex-shrink: 0;"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>',
            'info' => '<svg class="me-2" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="flex-shrink: 0;"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451.081.082.381 1.29.39zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>'
        ];
        $icon = $icons[$alertType] ?? $icons['info'];
        
        $flashMessages .= <<<HTML
<div id="{$toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
    <div class="toast-header bg-{$alertType} text-white">
        {$icon}
        <strong class="me-auto">{$alertType}</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
        {$messageEscaped}
    </div>
</div>
HTML;
    }
}

if ($flashMessages) {
    echo '<div class="toast-container position-fixed bottom-0 start-0 p-3" style="z-index: 1055;">' . $flashMessages . '</div>';
}
?>

