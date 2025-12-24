<?php
/**
 * Alert Class
 * Handles session-based flash messages
 */
class Alert {
    
    /**
     * Set success message
     */
    public static function setSuccess($message) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => $message
        ];
    }
    
    /**
     * Set error message
     */
    public static function setError($message) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['alert'] = [
            'type' => 'danger',
            'message' => $message
        ];
    }
    
    /**
     * Set warning message
     */
    public static function setWarning($message) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['alert'] = [
            'type' => 'warning',
            'message' => $message
        ];
    }
    
    /**
     * Set info message
     */
    public static function setInfo($message) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['alert'] = [
            'type' => 'info',
            'message' => $message
        ];
    }
    
    /**
     * Display and clear alert
     */
    public static function display() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['alert'])) {
            $alert = $_SESSION['alert'];
            unset($_SESSION['alert']);
            
            // Normalize type
            $type = $alert['type'];
            if ($type === 'error') $type = 'danger';
            
            echo '
            <style>
                .toast-container { position: fixed; top: 24px; right: 24px; z-index: 10000; font-family: system-ui, -apple-system, sans-serif; pointer-events: none; }
                .toast-item { 
                    background: white; border-radius: 8px; padding: 16px; margin-bottom: 10px; 
                    display: flex; align-items: flex-start; gap: 12px; 
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); 
                    border: 1px solid #f3f4f6;
                    animation: toastSlideDown 0.35s cubic-bezier(0.16, 1, 0.3, 1);
                    min-width: 320px; max-width: 420px;
                    pointer-events: auto;
                }
                .toast-item.success { border-left: 4px solid #10b981; }
                .toast-item.danger { border-left: 4px solid #ef4444; }
                .toast-item.warning { border-left: 4px solid #f59e0b; }
                .toast-item.info { border-left: 4px solid #3b82f6; }
                
                .toast-icon { font-size: 20px; line-height: 1; margin-top: 1px; }
                .toast-content { flex: 1; }
                .toast-title { font-weight: 600; font-size: 14px; color: #111827; margin-bottom: 2px; }
                .toast-message { font-size: 14px; color: #4b5563; line-height: 1.4; }
                .toast-close { background: none; border: none; color: #9ca3af; cursor: pointer; padding: 0; font-size: 18px; line-height: 1; }
                .toast-close:hover { color: #111827; }

                @keyframes toastSlideDown { from { transform: translateY(-100%) scale(0.9); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }
            </style>
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Ensure container
                let container = document.getElementById("toast-container");
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    container.className = "toast-container";
                    document.body.appendChild(container);
                }
                
                // Create toast
                const toast = document.createElement("div");
                toast.className = "toast-item ' . htmlspecialchars($type) . '";
                
                let icon = "ℹ️";
                let title = "Info";
                const type = "' . htmlspecialchars($type) . '";
                
                if (type === "success") { icon = "✅"; title = "Success"; }
                if (type === "danger") { icon = "❌"; title = "Error"; }
                if (type === "warning") { icon = "⚠️"; title = "Warning"; }
                
                toast.innerHTML = `
                    <div class="toast-icon">${icon}</div>
                    <div class="toast-content">
                        <div class="toast-title">${title}</div>
                        <div class="toast-message">' . addslashes(htmlspecialchars_decode($alert['message'])) . '</div>
                    </div>
                    <button class="toast-close" onclick="this.parentElement.remove()">×</button>
                `;
                
                container.appendChild(toast);
                
                // Remove
                setTimeout(() => {
                    toast.style.opacity = "0";
                    toast.style.transform = "translateY(-20px) scale(0.95)";
                    toast.style.transition = "all 0.3s ease";
                    setTimeout(() => toast.remove(), 300);
                }, 5000);
            });
            </script>
            ';
        }
    }
    
    /**
     * Check if alert exists
     */
    public static function hasAlert() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['alert']);
    }
}
?>
