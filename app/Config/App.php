<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Base Site URL
     * --------------------------------------------------------------------------
     *
     * This is set dynamically in the __construct() method below.
     * It detects the correct URL based on trusted proxy headers and a
     * whitelist of allowed hosts to prevent Host Header Injection attacks.
     *
     * IMPORTANT: Make sure 'app.baseURL' is COMMENTED OUT in your .env file!
     */
    public string $baseURL = 'http://dana-web-app.test/';

    /**
     * Allowed Hostnames in the Site URL other than the hostname in the baseURL.
     *
     * @var list<string>
     */
    public array $allowedHostnames = [];

    /**
     * --------------------------------------------------------------------------
     * Index File
     * --------------------------------------------------------------------------
     *
     * Empty string because Nginx handles URL rewriting (no index.php in URLs).
     */
    public string $indexPage = '';

    /**
     * --------------------------------------------------------------------------
     * URI Protocol
     * --------------------------------------------------------------------------
     */
    public string $uriProtocol = 'REQUEST_URI';

    /*
    |--------------------------------------------------------------------------
    | Allowed URL Characters
    |--------------------------------------------------------------------------
    |
    | This lets you specify which characters are permitted within your URLs.
    | When someone tries to submit a URL with disallowed characters they will
    | get a warning message.
    |
    | As a security measure you are STRONGLY encouraged to restrict URLs to
    | as few characters as possible.
    |
    | By default, only these are allowed: `a-z 0-9~%.:_-`
    |
    | Set an empty string to allow all characters -- but only if you are insane.
    |
    | The configured value is actually a regular expression character group
    | and it will be used as: '/\A[<permittedURIChars>]+\z/iu'
    |
    | DO NOT CHANGE THIS UNLESS YOU FULLY UNDERSTAND THE REPERCUSSIONS!!
    |
    */
    public string $permittedURIChars = 'a-z 0-9~%.:_\-';

    /**
     * --------------------------------------------------------------------------
     * Default Locale
     * --------------------------------------------------------------------------
     */
    public string $defaultLocale = 'en';

    /**
     * --------------------------------------------------------------------------
     * Negotiate Locale
     * --------------------------------------------------------------------------
     */
    public bool $negotiateLocale = false;

    /**
     * --------------------------------------------------------------------------
     * Supported Locales
     * --------------------------------------------------------------------------
     *
     * @var list<string>
     */
    public array $supportedLocales = ['en'];

    /**
     * --------------------------------------------------------------------------
     * Application Timezone
     * --------------------------------------------------------------------------
     *
     * @see https://www.php.net/manual/en/timezones.php
     */
    public string $appTimezone = 'UTC';

    /**
     * --------------------------------------------------------------------------
     * Default Character Set
     * --------------------------------------------------------------------------
     *
     * @see http://php.net/htmlspecialchars for a list of supported charsets.
     */
    public string $charset = 'UTF-8';

    /**
     * --------------------------------------------------------------------------
     * Force Global Secure Requests
     * --------------------------------------------------------------------------
     *
     * Set to false because our internal Docker network uses HTTP.
     * If you add HTTPS via Let's Encrypt later, set this to true.
     */
    public bool $forceGlobalSecureRequests = false;

    /**
     * --------------------------------------------------------------------------
     * Reverse Proxy IPs
     * --------------------------------------------------------------------------
     *
     * Trust the entire 'shared-infra' Docker network (172.30.0.0/16).
     * This allows CI4 to correctly read the X-Forwarded-For header from Nginx
     * to determine the real visitor IP address.
     *
     * NOTE: CI4 uses this property specifically for client IP detection.
     * The value must be a SINGLE header name (not comma-separated).
     * Our custom detectHost/detectScheme/detectPort methods handle the
     * other X-Forwarded-* headers independently.
     *
     * @var array<string, string>
     */
    public array $proxyIPs = [
        '0.0.0.0/0' => 'X-Forwarded-For',
    ];

    /**
     * --------------------------------------------------------------------------
     * Content Security Policy
     * --------------------------------------------------------------------------
     */
    public bool $CSPEnabled = false;

    // =========================================================================
    // DYNAMIC BASE URL LOGIC
    // =========================================================================

    /**
     * Whitelist of trusted hostnames.
     * Any Host header NOT in this list will be rejected and the
     * default fallback URL will be used instead.
     *
     * @var list<string>
     */
    private array $trustedHosts = [
        'dana-web-app.test',
        '192.168.0.3',
    ];

    /**
     * Default fallback URL used when:
     * - Running in CLI mode (e.g., `php spark`)
     * - An untrusted/spoofed Host header is detected
     */
    private string $defaultBaseURL = 'http://dana-web-app.test/';

    /**
     * --------------------------------------------------------------------------
     * Constructor - Dynamic Base URL Detection
     * --------------------------------------------------------------------------
     *
     * Sets $baseURL dynamically based on the incoming request.
     * Uses X-Forwarded-* headers (set by the Nginx reverse proxy) and
     * validates the host against a whitelist to prevent Host Header Injection.
     */
    public function __construct()
    {
        parent::__construct();

        // -----------------------------------------------------------------
        // Step 1: CLI Detection
        // -----------------------------------------------------------------
        if ($this->isCli()) {
            $this->baseURL = $this->defaultBaseURL;
            return;
        }

        // -----------------------------------------------------------------
        // Step 2: Determine the Host
        // -----------------------------------------------------------------
        $host = $this->detectHost();

        // -----------------------------------------------------------------
        // Step 3: Strip port from host (e.g., "192.168.0.3:8082" → "192.168.0.3")
        // -----------------------------------------------------------------
        $hostWithoutPort = $this->stripPort($host);

        // -----------------------------------------------------------------
        // Step 4: Security Check — validate against trusted hosts whitelist
        // -----------------------------------------------------------------
        if (! $this->isHostTrusted($hostWithoutPort)) {
            log_message(
                'warning',
                'App::__construct() - Untrusted Host header detected: "{host}". Falling back to default.',
                ['host' => $host]
            );
            $this->baseURL = $this->defaultBaseURL;
            return;
        }

        // -----------------------------------------------------------------
        // Step 5: Determine the Protocol (scheme)
        // -----------------------------------------------------------------
        $scheme = $this->detectScheme();

        // -----------------------------------------------------------------
        // Step 6: Determine the Port
        // -----------------------------------------------------------------
        $port = $this->detectPort($scheme);

        // -----------------------------------------------------------------
        // Step 7: Build the final baseURL
        // -----------------------------------------------------------------
        $portSuffix = $this->buildPortSuffix($scheme, $port);

        $this->baseURL = "{$scheme}://{$hostWithoutPort}{$portSuffix}/";
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Detect if the application is running in CLI mode.
     */
    private function isCli(): bool
    {
        return defined('STDIN')
            || php_sapi_name() === 'cli'
            || ! isset($_SERVER['HTTP_HOST']);
    }

    /**
     * Detect the hostname from headers/server variables.
     *
     * Priority:
     *   1. X-Forwarded-Host (set by Nginx proxy_pass)
     *   2. HTTP_HOST (direct access, may include port)
     *   3. SERVER_NAME (fallback)
     *   4. Default fallback host
     */
    private function detectHost(): string
    {
        if (! empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            // X-Forwarded-Host may contain multiple hosts (comma-separated)
            // when there are chained proxies. Use the first one.
            $hosts = explode(',', $_SERVER['HTTP_X_FORWARDED_HOST']);
            return trim($hosts[0]);
        }

        if (! empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        if (! empty($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }

        return 'dana-web-app.test';
    }

    /**
     * Strip port number from a host string.
     * Handles IPv6 addresses like [::1]:8080 and IPv4 like 192.168.0.3:8082.
     */
    private function stripPort(string $host): string
    {
        // Handle IPv6 addresses like [::1]:8080
        if (str_contains($host, ']')) {
            return preg_replace('/\]:\d+$/', ']', $host) ?? $host;
        }

        return strtok($host, ':') ?: $host;
    }

    /**
     * Check if a host (without port) is in the trusted whitelist.
     * Comparison is case-insensitive.
     */
    private function isHostTrusted(string $host): bool
    {
        return in_array(
            strtolower($host),
            array_map('strtolower', $this->trustedHosts),
            true
        );
    }

    /**
     * Detect the URL scheme (http or https).
     *
     * Priority:
     *   1. X-Forwarded-Proto (set by Nginx)
     *   2. HTTPS server variable
     *   3. Default to 'http'
     */
    private function detectScheme(): string
    {
        if (! empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            // May be comma-separated when chained through multiple proxies.
            return strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]));
        }

        if (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return 'https';
        }

        return 'http';
    }

    /**
     * Detect the port number.
     *
     * Priority:
     *   1. X-Forwarded-Port (set by Nginx)
     *   2. SERVER_PORT
     *   3. Default based on scheme (80/443)
     */
    private function detectPort(string $scheme): int
    {
        // 1. Check for our custom Docker-injected external port
        if (! empty($_SERVER['MY_EXTERNAL_PORT'])) {
            return (int) $_SERVER['MY_EXTERNAL_PORT'];
        }
    
        // 2. Check X-Forwarded-Port (set by proxy/tunnel)
        if (! empty($_SERVER['HTTP_X_FORWARDED_PORT'])) {
            return (int) trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PORT'])[0]);
        }
    
        // 3. If behind a proxy/tunnel (X-Forwarded-Proto is set),
        //    use the standard port for the detected scheme.
        //    This prevents appending :80 to HTTPS URLs when Cloudflare
        //    terminates TLS and forwards over plain HTTP internally.
        if (! empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            return ($scheme === 'https') ? 443 : 80;
        }
    
        // 4. Fallback to standard detection
        if (! empty($_SERVER['SERVER_PORT'])) {
            return (int) $_SERVER['SERVER_PORT'];
        }
    
        return ($scheme === 'https') ? 443 : 80;
    }

    /**
     * Build the port suffix string for the URL.
     * Returns empty string for standard ports (80 for http, 443 for https).
     */
    private function buildPortSuffix(string $scheme, int $port): string
    {
        if (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
            return '';
        }

        return ":{$port}";
    }
}