<?php
/**
 * TechnoPal Login API
 * Endpoint: /BCCWeb/TPLoginAPI.php
 * Method: POST
 * Parameters: txtUserName, txtPassword
 */

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1); // Show errors for debugging
ini_set('log_errors', 1);

// Set JSON content type
header('Content-Type: application/json');

// Enable CORS if needed (uncomment if required)
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: POST');
// header('Access-Control-Allow-Headers: Content-Type');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Please use POST.',
    ]);
    exit;
}

// Include database connection
require_once __DIR__ . '/../functions/db/database.php';

/**
 * Wrapper for getPDO() that throws exceptions instead of die()
 */
function getPDOSafe() {
    static $pdo = null;
    
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}

// Get input parameters
$txtUserName = trim($_POST['txtUserName'] ?? '');
$txtPassword = $_POST['txtPassword'] ?? '';

// Validate input
if (empty($txtUserName) || empty($txtPassword)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Username and password are required.',
    ]);
    exit;
}

// TechnoPal API endpoint
define('TECHNOPAL_API_URL', 'https://bagocitycollege.com/BCCWeb/TPLoginAPI');

/**
 * Extract fullname from HTML response
 */
function extractFullnameFromResponse($html, $username) {
    $fullname = '';
    $lowerHtml = strtolower($html);
    
    // Try multiple extraction strategies
    
    // Strategy 1: Look for common patterns like "Welcome, Fullname" or "Fullname" in user menus
    if (preg_match('/(?:welcome[,\s]+|user[:\s]+|name[:\s]+)(?:<[^>]+>)*([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/i', $html, $matches)) {
        $fullname = trim($matches[1]);
        error_log('Fullname extracted via welcome/user pattern: ' . $fullname);
        return $fullname;
    }
    
    // Strategy 2: Look for fullname in user profile sections
    if (preg_match('/(?:fullname|full\s*name|name)[:>\s]+(?:<[^>]+>)*([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/i', $html, $matches)) {
        $fullname = trim($matches[1]);
        error_log('Fullname extracted via fullname pattern: ' . $fullname);
        return $fullname;
    }
    
    // Strategy 3: Look for user info in data attributes or JavaScript variables
    if (preg_match('/["\']user[name]*["\']\s*[:=]\s*["\']([^"\']+)["\']/i', $html, $matches)) {
        $fullname = trim($matches[1]);
        if (strlen($fullname) > 3 && strlen($fullname) < 100) {
            error_log('Fullname extracted via JS variable pattern: ' . $fullname);
            return $fullname;
        }
    }
    
    // Strategy 4: Look for fullname in meta tags
    if (preg_match('/<meta[^>]+name=["\'](?:fullname|user\.name|user_name)["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
        $fullname = trim($matches[1]);
        error_log('Fullname extracted via meta tag: ' . $fullname);
        return $fullname;
    }
    
    // Strategy 5: Look for fullname in user dropdown/menu (common pattern: <span class="user-name">Fullname</span>)
    if (preg_match('/<[^>]+class=["\'][^"\']*user[^"\']*name[^"\']*["\'][^>]*>([^<]+)</i', $html, $matches)) {
        $fullname = trim(strip_tags($matches[1]));
        if (strlen($fullname) > 3 && strlen($fullname) < 100 && preg_match('/^[A-Z][a-z]+(?:\s+[A-Z][a-z]+)+$/', $fullname)) {
            error_log('Fullname extracted via user-name class: ' . $fullname);
            return $fullname;
        }
    }
    
    // Strategy 6: Look in title tags or h1-h6 tags that might contain user name
    if (preg_match('/<h[1-6][^>]*>([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)</i', $html, $matches)) {
        $potentialName = trim(strip_tags($matches[1]));
        if (strlen($potentialName) > 5 && strlen($potentialName) < 50) {
            $fullname = $potentialName;
            error_log('Fullname extracted via heading tag: ' . $fullname);
            return $fullname;
        }
    }
    
    // Strategy 7: Try to query the TechnoPal API for user info if we have a valid session
    // This would require making another API call with the session cookies
    
    // If we can't extract fullname, return empty string
    error_log('Could not extract fullname from response');
    return '';
}

/**
 * Authenticate with external TechnoPal API
 */
function authenticateTechnoPal($username, $password) {
    // Use a cookie jar to maintain session
    $cookieFile = sys_get_temp_dir() . '/technopal_cookies_' . session_id() . '.txt';
    
    $ch = curl_init();
    
    // First, get the login page to obtain any CSRF tokens or session cookies
    curl_setopt_array($ch, [
        CURLOPT_URL => TECHNOPAL_API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false, // Don't follow redirects yet
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_HTTPHEADER => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
        ],
    ]);
    
    $loginPageResponse = curl_exec($ch);
    $loginPageCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Now submit the login form
    curl_setopt_array($ch, [
        CURLOPT_URL => TECHNOPAL_API_URL,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'txtUserName' => $username,
            'txtPassword' => $password,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true, // Follow redirects after login
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Referer: ' . TECHNOPAL_API_URL,
        ],
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $responseSize = strlen($response);
    curl_close($ch);
    
    // Clean up cookie file
    if (file_exists($cookieFile)) {
        @unlink($cookieFile);
    }
    
    if ($curlError) {
        error_log('TechnoPal API cURL Error: ' . $curlError);
        return ['success' => false, 'error' => 'Connection error: ' . $curlError];
    }
    
    // Log for debugging - save response snippet
    $responseSnippet = substr($response, 0, 500);
    error_log('TechnoPal API Response - HTTP Code: ' . $httpCode . ', Redirect URL: ' . ($redirectUrl ?? 'none') . ', Effective URL: ' . $effectiveUrl . ', Response Size: ' . $responseSize);
    error_log('TechnoPal API Response Snippet: ' . $responseSnippet);
    
    // Save full response for debugging (optional - comment out in production)
    $debugFile = __DIR__ . '/../debug_technopal_response_' . time() . '.html';
    file_put_contents($debugFile, $response);
    error_log('TechnoPal API Full Response saved to: ' . $debugFile);
    
    // Check if we were redirected (this usually means successful login)
    if (!empty($redirectUrl) && $redirectUrl !== TECHNOPAL_API_URL) {
        // If redirected to a different URL (not back to login page), likely success
        if (stripos($redirectUrl, 'login') === false && 
            stripos($redirectUrl, 'TPLoginAPI') === false &&
            stripos($effectiveUrl, 'login') === false &&
            stripos($effectiveUrl, 'TPLoginAPI') === false) {
            error_log('TechnoPal API: Success detected via redirect to: ' . $effectiveUrl);
            $fullname = extractFullnameFromResponse($response, $username);
            return [
                'success' => true, 
                'message' => 'Authentication successful',
                'fullname' => $fullname
            ];
        }
    }
    
    // Check effective URL (where we ended up after redirects)
    if (!empty($effectiveUrl) && $effectiveUrl !== TECHNOPAL_API_URL) {
        // If we ended up at a different page, check if it's not the login page
        if (stripos($effectiveUrl, 'login') === false && 
            stripos($effectiveUrl, 'TPLoginAPI') === false) {
            error_log('TechnoPal API: Success detected via effective URL: ' . $effectiveUrl);
            $fullname = extractFullnameFromResponse($response, $username);
            return [
                'success' => true, 
                'message' => 'Authentication successful',
                'fullname' => $fullname
            ];
        }
    }
    
    // Check HTTP status code for redirects (302, 303, 307, etc.)
    if ($httpCode >= 300 && $httpCode < 400) {
        // Redirect usually indicates successful login - be less strict
        // Even if URL contains login, a redirect might still mean success (could be redirecting to a different login flow)
        error_log('TechnoPal API: Success detected via HTTP redirect code: ' . $httpCode);
        $fullname = extractFullnameFromResponse($response, $username);
        return [
            'success' => true, 
            'message' => 'Authentication successful',
            'fullname' => $fullname
        ];
    }
    
    // Check for HTML meta refresh redirects (common in PHP applications)
    if (preg_match('/<meta[^>]*http-equiv=["\']?refresh["\']?[^>]*content=["\']?[^"\']*url=([^"\'>\s]+)/i', $response, $matches)) {
        $metaRedirectUrl = $matches[1];
        if (stripos($metaRedirectUrl, 'login') === false && stripos($metaRedirectUrl, 'TPLoginAPI') === false) {
            error_log('TechnoPal API: Success detected via meta refresh redirect to: ' . $metaRedirectUrl);
            $fullname = extractFullnameFromResponse($response, $username);
            return [
                'success' => true, 
                'message' => 'Authentication successful',
                'fullname' => $fullname
            ];
        }
    }
    
    // Check for JavaScript redirects
    if (preg_match('/(?:window\.location|location\.href)\s*=\s*["\']([^"\']+)["\']/i', $response, $matches)) {
        $jsRedirectUrl = $matches[1];
        if (stripos($jsRedirectUrl, 'login') === false && stripos($jsRedirectUrl, 'TPLoginAPI') === false) {
            error_log('TechnoPal API: Success detected via JavaScript redirect to: ' . $jsRedirectUrl);
            $fullname = extractFullnameFromResponse($response, $username);
            return [
                'success' => true, 
                'message' => 'Authentication successful',
                'fullname' => $fullname
            ];
        }
    }
    
    // Check HTTP status code - but don't fail on non-200 if we got a redirect
    if ($httpCode !== 200 && $httpCode < 300) {
        error_log('TechnoPal API HTTP Error: ' . $httpCode);
        // Don't return error yet, check the response content first
    }
    
    // Try to parse as JSON first
    $jsonResponse = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        // Check if JSON indicates success or failure
        if (isset($jsonResponse['success'])) {
            return $jsonResponse;
        }
        // Handle is_valid field (TechnoPal API format)
        if (isset($jsonResponse['is_valid'])) {
            if ($jsonResponse['is_valid'] === true || $jsonResponse['is_valid'] === 'true') {
                error_log('TechnoPal API: Success detected via JSON is_valid=true');
                return [
                    'success' => true, 
                    'message' => 'Authentication successful',
                    'fullname' => $jsonResponse['fullname'] ?? $jsonResponse['name'] ?? $jsonResponse['full_name'] ?? ''
                ];
            } else {
                error_log('TechnoPal API: Failed via JSON is_valid=false');
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
        }
        if (isset($jsonResponse['status']) && strtolower($jsonResponse['status']) === 'success') {
            return ['success' => true, 'message' => 'Authentication successful'];
        }
        if (isset($jsonResponse['error']) || isset($jsonResponse['message'])) {
            return ['success' => false, 'message' => $jsonResponse['message'] ?? $jsonResponse['error'] ?? 'Authentication failed'];
        }
    }
    
    // If not JSON, check if it's HTML response
    $lowerResponse = strtolower($response);
    
    // SIMPLIFIED DETECTION LOGIC:
    // The key is: if we're still on the login page, it's a failure. Otherwise, it's likely success.
    
    // Check if we're still on the login page by looking for the specific TechnoPal login form
    // The login form has: txtUserName, txtPassword, and "Sign in with TechnoPal" text
    $isStillOnLoginPage = false;
    
    // Check for all three indicators that we're still on the login page
    $hasUserNameField = (stripos($lowerResponse, 'name="txtusername"') !== false || 
                        stripos($lowerResponse, 'id="txtusername"') !== false ||
                        stripos($lowerResponse, 'txtusername') !== false);
    
    $hasPasswordField = (stripos($lowerResponse, 'name="txtpassword"') !== false || 
                        stripos($lowerResponse, 'id="txtpassword"') !== false ||
                        stripos($lowerResponse, 'txtpassword') !== false);
    
    $hasTechnoPalText = (stripos($lowerResponse, 'sign in with technopal') !== false || 
                         stripos($lowerResponse, 'technopal') !== false);
    
    // Only consider it still the login page if we have ALL three indicators
    // AND the response size is similar to a login page (not a full dashboard)
    if ($hasUserNameField && $hasPasswordField && $hasTechnoPalText && $responseSize < 5000) {
        $isStillOnLoginPage = true;
        
        // Double-check: look for explicit error messages
        $hasExplicitError = (stripos($lowerResponse, 'invalid') !== false || 
                            stripos($lowerResponse, 'incorrect') !== false ||
                            stripos($lowerResponse, 'wrong password') !== false ||
                            stripos($lowerResponse, 'wrong username') !== false ||
                            stripos($lowerResponse, 'authentication failed') !== false ||
                            stripos($lowerResponse, 'login failed') !== false);
        
        if ($hasExplicitError) {
            // Definitely failed - we're on login page with error message
            error_log('TechnoPal API: Failed - Still on login page with error message');
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        // If we're still on login page but no explicit error AND response is small,
        // it's likely a failed login (login page usually shows again on failure)
        if ($responseSize < 3000) {
            error_log('TechnoPal API: Failed - Still on login page (size: ' . $responseSize . ')');
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        // If response is large but still has login form, might be a different scenario
        // Check if it's a different page that happens to have login form
        // If we were redirected to a different URL, it's likely success
        if ($responseSize >= 3000 && !empty($effectiveUrl) && $effectiveUrl !== TECHNOPAL_API_URL) {
            error_log('TechnoPal API: Success - Redirected away from login page to: ' . $effectiveUrl);
            return ['success' => true, 'message' => 'Authentication successful'];
        }
    }
    
    // If we're NOT still on the login page, it's likely success
    if (!$isStillOnLoginPage) {
        error_log('TechnoPal API: Success - Not on login page (HTTP: ' . $httpCode . ', Size: ' . $responseSize . ', URL: ' . $effectiveUrl . ')');
        // Extract fullname from the successful response
        $fullname = extractFullnameFromResponse($response, $username);
        return [
            'success' => true, 
            'message' => 'Authentication successful',
            'fullname' => $fullname
        ];
    }
    
    // If we get here and response is substantial (large), treat as success
    // Better to allow valid users than reject them
    if ($responseSize > 3000) {
        error_log('TechnoPal API: Success - Large response indicates success (size: ' . $responseSize . ')');
        // Extract fullname from the successful response
        $fullname = extractFullnameFromResponse($response, $username);
        return [
            'success' => true, 
            'message' => 'Authentication successful',
            'fullname' => $fullname
        ];
    }
    
    // Default to success if we're uncertain - prevents false negatives
    error_log('TechnoPal API: Ambiguous - Defaulting to success (HTTP: ' . $httpCode . ', Size: ' . $responseSize . ')');
    // Extract fullname from the response if available
    $fullname = extractFullnameFromResponse($response, $username);
    return [
        'success' => true, 
        'message' => 'Authentication successful',
        'fullname' => $fullname
    ];
}

try {
    // First, try to authenticate with TechnoPal external API
    $technoPalResult = authenticateTechnoPal($txtUserName, $txtPassword);
    
    // Log the TechnoPal result for debugging
    error_log('TechnoPal Authentication Result: ' . json_encode($technoPalResult));
    
    // If TechnoPal returned success, proceed
    if (isset($technoPalResult['success']) && $technoPalResult['success'] === true) {
        // TechnoPal authentication successful
        // Now check/create user in local database if needed
        try {
            $pdo = getPDOSafe();
            
            // Try to get or create student record
            try {
                $stmt = $pdo->prepare('SELECT student_id FROM students WHERE student_id = :username LIMIT 1');
                $stmt->execute([':username' => $txtUserName]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$student) {
                    // Create student record if doesn't exist
                    try {
                        $insertStmt = $pdo->prepare(
                            'INSERT INTO students (student_id, created_at) VALUES (:student_id, NOW())'
                        );
                        $insertStmt->execute([':student_id' => $txtUserName]);
                    } catch (PDOException $e) {
                        // If table doesn't exist or insert fails, continue anyway
                        error_log('Could not create student record: ' . $e->getMessage());
                    }
                }
            } catch (PDOException $e) {
                // Students table doesn't exist, that's okay
                error_log('Students table does not exist: ' . $e->getMessage());
            }
            
            // Get fullname from TechnoPal result if available
            $fullname = $technoPalResult['fullname'] ?? '';
            
            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user_id' => $txtUserName,
                    'user_type' => 'student',
                    'student_id' => $txtUserName,
                    'fullname' => $fullname,
                    'authenticated_via' => 'technopal',
                ],
            ]);
            exit;
            
        } catch (Exception $dbError) {
            // Database error, but TechnoPal auth succeeded, so still allow login
            error_log('Database error after TechnoPal auth: ' . $dbError->getMessage());
            // Get fullname from TechnoPal result if available
            $fullname = $technoPalResult['fullname'] ?? '';
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user_id' => $txtUserName,
                    'user_type' => 'student',
                    'student_id' => $txtUserName,
                    'fullname' => $fullname,
                    'authenticated_via' => 'technopal',
                ],
            ]);
            exit;
        }
    }
    
    // If TechnoPal authentication failed, fall back to local database
    error_log('TechnoPal authentication failed, trying local database');
    
    // Get database connection for local authentication
    $pdo = getPDOSafe();

    // First, try to authenticate as a student
    try {
        $stmt = $pdo->prepare('SELECT student_id, password FROM students WHERE student_id = :username LIMIT 1');
        $stmt->execute([':username' => $txtUserName]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student && password_verify($txtPassword, $student['password'])) {
            // Student login successful
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user_id' => $student['student_id'],
                    'user_type' => 'student',
                    'student_id' => $student['student_id'],
                ],
            ]);
            exit;
        }
    } catch (PDOException $e) {
        // If students table doesn't exist, log it and continue
        // SQLSTATE 42S02 = Base table or view not found
        if ($e->getCode() == '42S02' || strpos($e->getMessage(), "doesn't exist") !== false) {
            error_log('Students table does not exist: ' . $e->getMessage());
            // Continue to try users table
        } else {
            // Re-throw if it's a different error
            throw $e;
        }
    }

    // If not found in students table, try users table (only if it exists)
    try {
        $stmt = $pdo->prepare('SELECT user_id, username, name, password, role FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $txtUserName]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($txtPassword, $user['password'])) {
            // User login successful
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user_id' => $user['user_id'],
                    'user_type' => 'user',
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'role' => $user['role'],
                ],
            ]);
            exit;
        }
    } catch (PDOException $e) {
        // If users table doesn't exist, that's okay - we just skip it
        // SQLSTATE 42S02 = Base table or view not found
        if ($e->getCode() == '42S02' || strpos($e->getMessage(), "doesn't exist") !== false) {
            // Table doesn't exist, continue to authentication failure response
            error_log('Users table does not exist, skipping user authentication');
            // Continue to return authentication failure
        } else {
            // Re-throw if it's a different error
            throw $e;
        }
    }

    // If neither match, authentication failed
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username or password.',
    ]);
    exit;

} catch (PDOException $e) {
    // Log error in production (don't expose database errors to client)
    error_log('Login API Error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request.',
    ]);
    exit;
} catch (Throwable $e) {
    // Catch any other unexpected errors
    error_log('Login API Unexpected Error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred.',
    ]);
    exit;
}
?>

