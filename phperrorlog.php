<?php
// PHP Error Logging System for Netflix Clone

class NetflixErrorLogger {
    private $logFile;
    private $maxLogSize;
    private $logLevel;
    
    public function __construct($logFile = 'logs/netflix_errors.log', $maxLogSize = 10485760, $logLevel = E_ALL) {
        $this->logFile = $logFile;
        $this->maxLogSize = $maxLogSize; // 10MB default
        $this->logLevel = $logLevel;
        
        // Create logs directory if it doesn't exist
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Set custom error handler
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleFatalError']);
    }
    
    public function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorType = $this->getErrorType($severity);
        $this->logError($errorType, $message, $file, $line);
        
        // Don't execute PHP internal error handler
        return true;
    }
    
    public function handleException($exception) {
        $this->logError(
            'EXCEPTION',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
    }
    
    public function handleFatalError() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->logError(
                'FATAL',
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }
    
    private function logError($type, $message, $file, $line, $trace = null) {
        $timestamp = date('Y-m-d H:i:s');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $url = $_SERVER['REQUEST_URI'] ?? 'Unknown';
        $userId = $_SESSION['user_id'] ?? 'Guest';
        
        $logEntry = sprintf(
            "[%s] %s: %s in %s on line %d\n" .
            "URL: %s\n" .
            "User ID: %s\n" .
            "IP: %s\n" .
            "User Agent: %s\n",
            $timestamp,
            $type,
            $message,
            $file,
            $line,
            $url,
            $userId,
            $ip,
            $userAgent
        );
        
        if ($trace) {
            $logEntry .= "Stack Trace:\n" . $trace . "\n";
        }
        
        $logEntry .= str_repeat('-', 80) . "\n";
        
        $this->writeToLog($logEntry);
        
        // Send critical errors via email (optional)
        if (in_array($type, ['FATAL', 'EXCEPTION'])) {
            $this->sendCriticalErrorEmail($type, $message, $file, $line);
        }
    }
    
    private function writeToLog($entry) {
        // Rotate log if it's too large
        if (file_exists($this->logFile) && filesize($this->logFile) > $this->maxLogSize) {
            $this->rotateLog();
        }
        
        file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
    }
    
    private function rotateLog() {
        $backupFile = $this->logFile . '.' . date('Y-m-d-H-i-s') . '.bak';
        rename($this->logFile, $backupFile);
        
        // Keep only last 5 backup files
        $this->cleanupOldLogs();
    }
    
    private function cleanupOldLogs() {
        $logDir = dirname($this->logFile);
        $pattern = basename($this->logFile) . '.*.bak';
        $files = glob($logDir . '/' . $pattern);
        
        if (count($files) > 5) {
            // Sort by modification time
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Remove oldest files
            $filesToRemove = array_slice($files, 0, count($files) - 5);
            foreach ($filesToRemove as $file) {
                unlink($file);
            }
        }
    }
    
    private function getErrorType($severity) {
        $errorTypes = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED'
        ];
        
        return $errorTypes[$severity] ?? 'UNKNOWN';
    }
    
    private function sendCriticalErrorEmail($type, $message, $file, $line) {
        // Implement email notification for critical errors
        // This is optional and requires mail configuration
        /*
        $to = 'admin@yournetflixclone.com';
        $subject = 'Critical Error in Netflix Clone';
        $body = "Critical error occurred:\n\n";
        $body .= "Type: $type\n";
        $body .= "Message: $message\n";
        $body .= "File: $file\n";
        $body .= "Line: $line\n";
        $body .= "Time: " . date('Y-m-d H:i:s') . "\n";
        
        mail($to, $subject, $body);
        */
    }
    
    public function logCustomError($message, $context = []) {
        $contextStr = empty($context) ? '' : ' Context: ' . json_encode($context);
        $this->logError('CUSTOM', $message . $contextStr, __FILE__, __LINE__);
    }
    
    public function getRecentErrors($limit = 50) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES);
        $errors = [];
        $currentError = [];
        
        foreach (array_reverse($lines) as $line) {
            if (strpos($line, str_repeat('-', 80)) !== false) {
                if (!empty($currentError)) {
                    $errors[] = implode("\n", array_reverse($currentError));
                    $currentError = [];
                    
                    if (count($errors) >= $limit) {
                        break;
                    }
                }
            } else {
                $currentError[] = $line;
            }
        }
        
        return $errors;
    }
}

// Initialize error logger
$errorLogger = new NetflixErrorLogger();

// Function to log custom application errors
function logNetflixError($message, $context = []) {
    global $errorLogger;
    $errorLogger->logCustomError($message, $context);
}

// Error display page for administrators
if (isset($_GET['view_errors']) && isset($_SESSION['user_id'])) {
    // Check if user is admin (you should implement proper admin check)
    $isAdmin = true; // Replace with actual admin check
    
    if ($isAdmin) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error Logs - Netflix Admin</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: 'Helvetica Neue', Arial, sans-serif;
                    background-color: #141414;
                    color: white;
                    padding: 2rem;
                }

                .header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 2rem;
                    padding-bottom: 1rem;
                    border-bottom: 1px solid #333;
                }

                .logo {
                    font-size: 2rem;
                    font-weight: bold;
                    color: #e50914;
                }

                .back-btn {
                    background: #e50914;
                    color: white;
                    padding: 0.5rem 1rem;
                    border: none;
                    border-radius: 4px;
                    text-decoration: none;
                    cursor: pointer;
                }

                .error-log {
                    background: #222;
                    border: 1px solid #444;
                    border-radius: 8px;
                    margin-bottom: 1rem;
                    overflow: hidden;
                }

                .error-header {
                    background: #333;
                    padding: 1rem;
                    border-bottom: 1px solid #444;
                    font-weight: bold;
                }

                .error-content {
                    padding: 1rem;
                    font-family: 'Courier New', monospace;
                    font-size: 0.9rem;
                    line-height: 1.4;
                    white-space: pre-wrap;
                    max-height: 300px;
                    overflow-y: auto;
                }

                .error-fatal { border-left: 4px solid #ff4444; }
                .error-exception { border-left: 4px solid #ff8800; }
                .error-warning { border-left: 4px solid #ffaa00; }
                .error-notice { border-left: 4px solid #4488ff; }

                .no-errors {
                    text-align: center;
                    padding: 3rem;
                    color: #666;
                }

                .refresh-btn {
                    position: fixed;
                    bottom: 2rem;
                    right: 2rem;
                    background: #e50914;
                    color: white;
                    border: none;
                    border-radius: 50%;
                    width: 60px;
                    height: 60px;
                    font-size: 1.5rem;
                    cursor: pointer;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo">NETFLIX - Error Logs</div>
                <a href="index.php" class="back-btn">← Back to Home</a>
            </div>

            <?php
            $errors = $errorLogger->getRecentErrors(20);
            if (empty($errors)): ?>
                <div class="no-errors">
                    <h2>No recent errors found</h2>
                    <p>Your Netflix clone is running smoothly!</p>
                </div>
            <?php else: ?>
                <?php foreach ($errors as $index => $error): ?>
                    <?php
                    $errorClass = 'error-notice';
                    if (strpos($error, 'FATAL') !== false) $errorClass = 'error-fatal';
                    elseif (strpos($error, 'EXCEPTION') !== false) $errorClass = 'error-exception';
                    elseif (strpos($error, 'WARNING') !== false) $errorClass = 'error-warning';
                    ?>
                    <div class="error-log <?php echo $errorClass; ?>">
                        <div class="error-header">
                            Error #<?php echo $index + 1; ?>
                        </div>
                        <div class="error-content"><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <button class="refresh-btn" onclick="location.reload()" title="Refresh">🔄</button>

            <script>
                // Auto-refresh every 30 seconds
                setInterval(function() {
                    location.reload();
                }, 30000);
            </script>
        </body>
        </html>
        <?php
        exit();
    }
}
?>
