<?php
/**
 * BookSharing API Configuration
 * 
 * Security settings and constants for API endpoints
 */

// Environment detection
define('ENVIRONMENT', getenv('APP_ENV') ?? 'production');
define('DEBUG_MODE', ENVIRONMENT === 'development');

// API configuration
define('API_VERSION', '1.0');
define('API_MAX_LIMIT', 50);
define('API_DEFAULT_LIMIT', 20);

// File upload configuration
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'avif']);

// Search limits
define('MAX_SEARCH_LENGTH', 100);
define('MIN_SEARCH_LENGTH', 2);

// CORS configuration
define('ALLOWED_ORIGINS', [
    'http://localhost',
    'http://localhost:80',
    'http://localhost:8080',
    // Add your domain here when hosting
    // 'https://yourdomain.com'
]);

// Rate limiting (requests per minute)
define('RATE_LIMIT_ENABLED', false); // Set to true in production
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 60);

// Error logging
define('LOG_ERRORS', true);
define('LOG_DIR', __DIR__ . '/../logs/');
define('LOG_FILE', LOG_DIR . 'api_errors.log');

// Ensure log directory exists
if (LOG_ERRORS && !is_dir(LOG_DIR)) {
    @mkdir(LOG_DIR, 0755, true);
}

/**
 * Log API errors
 * @param string $message Error message
 * @param string $level Error level (ERROR, WARNING, INFO)
 */
function logApiError($message, $level = 'ERROR') {
    if (!LOG_ERRORS) return;
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] " . $_SERVER['REQUEST_URI'] . " - " . $message . "\n";
    
    @error_log($logMessage, 3, LOG_FILE);
}

/**
 * Get origin URL
 * @return string Origin domain
 */
function getOrigin() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'];
}

/**
 * Check if origin is allowed
 * @param string $origin Origin URL
 * @return bool
 */
function isOriginAllowed($origin) {
    // Allow all origins in development
    if (DEBUG_MODE) {
        return true;
    }
    
    // Check against whitelist in production
    return in_array($origin, ALLOWED_ORIGINS, true);
}

/**
 * Validate file extension
 * @param string $filename Filename
 * @return bool
 */
function isAllowedFile($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ALLOWED_EXTENSIONS, true);
}

/**
 * Check rate limiting
 * @param string $identifier Client identifier (IP or user ID)
 * @return bool True if allowed, false if rate limit exceeded
 */
function checkRateLimit($identifier) {
    if (!RATE_LIMIT_ENABLED) {
        return true;
    }
    
    $cacheKey = 'api_rate_limit_' . md5($identifier);
    
    // Note: In production, use Redis or Memcached for this
    // For now, using file-based cache
    $cacheFile = sys_get_temp_dir() . '/' . $cacheKey;
    
    if (file_exists($cacheFile)) {
        $data = unserialize(file_get_contents($cacheFile));
        
        if ($data['time'] > time() - RATE_LIMIT_WINDOW) {
            if ($data['count'] >= RATE_LIMIT_REQUESTS) {
                return false; // Rate limit exceeded
            }
            $data['count']++;
        } else {
            $data['count'] = 1;
            $data['time'] = time();
        }
    } else {
        $data = ['count' => 1, 'time' => time()];
    }
    
    file_put_contents($cacheFile, serialize($data));
    return true;
}

/**
 * Get client IP address
 * @return string Client IP
 */
function getClientIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

?>
