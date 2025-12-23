<?php
/**
 * Alert Helper Class
 * Handles session-based flash messages and alert rendering
 */
class Alert {
    // Alert types
    const SUCCESS = 'success';
    const ERROR = 'danger'; // Map to bootstrap 'danger'
    const WARNING = 'warning';
    const INFO = 'info';

    /**
     * Set a flash message
     * @param string $type The type of alert (success, danger, warning, info)
     * @param string $message The message content
     */
    public static function set($type, $message) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_alert'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    public static function setSuccess($message) {
        self::set(self::SUCCESS, $message);
    }

    public static function setError($message) {
        self::set(self::ERROR, $message);
    }

    public static function setWarning($message) {
        self::set(self::WARNING, $message);
    }

    public static function setInfo($message) {
        self::set(self::INFO, $message);
    }

    /**
     * Display the flash message if it exists
     * @return void Outputs HTML directly
     */
    public static function display() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['flash_alert'])) {
            $type = $_SESSION['flash_alert']['type'];
            $message = $_SESSION['flash_alert']['message'];
            self::render($type, $message);
            
            // Clear the message
            unset($_SESSION['flash_alert']);
        }
    }

    /**
     * Render an alert immediately (for non-session usage)
     * @param string $type
     * @param string $message
     */
    public static function render($type, $message) {
        // Icon mapping
        $icon = 'info-circle';
        $title = 'Info';
        
        switch($type) {
            case 'success': 
                $icon = 'check-circle'; 
                $title = 'Success';
                break;
            case 'danger': 
                $icon = 'exclamation-circle'; 
                $title = 'Error';
                break;
            case 'warning': 
                $icon = 'exclamation-triangle'; 
                $title = 'Warning';
                break;
        }

        echo '
        <div class="alert alert-' . $type . ' alert-dismissible fade show custom-alert shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <div class="alert-icon-wrapper">
                    <i class="bi bi-' . $icon . ' fs-4"></i>
                </div>
                <div class="alert-content ms-3">
                    <h6 class="alert-heading fw-bold mb-1">' . $title . '</h6>
                    <div class="alert-message">' . $message . '</div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}
