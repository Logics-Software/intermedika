<?php
class Message {
    /**
     * Set success message
     */
    public static function success($message) {
        Session::flash('success', $message);
    }
    
    /**
     * Set error message
     */
    public static function error($message) {
        Session::flash('error', $message);
    }
    
    /**
     * Set warning message
     */
    public static function warning($message) {
        Session::flash('warning', $message);
    }
    
    /**
     * Set info message
     */
    public static function info($message) {
        Session::flash('info', $message);
    }
    
    /**
     * Render all flash messages as Bootstrap toasts
     * Returns HTML string of all toasts
     */
    public static function render() {
        $html = '<div class="toast-container position-fixed bottom-0 start-0 p-3" style="z-index: 1055;">';
        $types = ['success', 'error', 'warning', 'info'];
        $toastIndex = 0;
        
        foreach ($types as $type) {
            $message = Session::flash($type);
            if ($message) {
                // Map error to danger for Bootstrap
                $alertType = $type === 'error' ? 'danger' : $type;
                $toastId = 'toast-' . $type . '-' . $toastIndex++;
                $html .= self::renderToast($alertType, $message, $toastId);
            }
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render single toast
     */
    private static function renderToast($type, $message, $toastId) {
        $message = htmlspecialchars($message);
        $icon = self::getIcon($type);
        return <<<HTML
<div id="{$toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
    <div class="toast-header bg-{$type} text-white">
        {$icon}
        <strong class="me-auto">{$type}</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
        {$message}
    </div>
</div>
HTML;
    }
    
    /**
     * Get icon for toast type
     */
    private static function getIcon($type) {
        $icons = [
            'success' => '<svg class="me-2" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="flex-shrink: 0;"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 4.384 6.323a.75.75 0 0 0-1.06 1.061l3.5 3.5a.75.75 0 0 0 1.137-.089l4-5.5a.75.75 0 0 0-.022-1.08z"/></svg>',
            'danger' => '<svg class="me-2" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="flex-shrink: 0;"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/></svg>',
            'warning' => '<svg class="me-2" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="flex-shrink: 0;"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>',
            'info' => '<svg class="me-2" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="flex-shrink: 0;"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451.081.082.381 1.29.39zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>'
        ];
        return $icons[$type] ?? $icons['info'];
    }
    
    /**
     * Check if there are any messages
     */
    public static function hasAny() {
        return Session::has('flash');
    }
}

