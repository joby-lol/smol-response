# smolResponse

A straightforward, type-safe HTTP response builder for PHP. Build and send HTTP responses with proper headers, cache control, content types, and status codes. Designed to minimize footguns, maximize productivity, and provide useful hinting and IntelliSense features in your IDE.

**Features:**

* Type-safe response building with fluent interfaces
* First-class cache control headers
* Automatic content type detection and charset handling
* Range request support for efficient streaming
* Built-in content types: strings, JSON, files, callbacks, and empty responses
* Requires PHP 8.4+

## Installation

```bash
composer require joby/smol-response
```

## Quick Start

```php
use Joby\Smol\Response\Response;

// Simple text response
$response = new Response(200, 'Hello World');

// JSON response
$response = Response::json(['status' => 'ok', 'data' => $results]);

// File download
$response = Response::file('/path/to/file.pdf');

// Redirect
$response = Response::redirect('https://example.com', permanent: true);

// Render the response
$renderer = new \Joby\Smol\Response\Renderer();
$renderer->render($response);
```

## Core Components

### Response (`Response`)

The main response builder. Create responses with various content types and configurations.

**Factory Methods:**

* `json($data, $status = 200)` - Create JSON response
* `file($path, $status = 200)` - Create file response
* `redirect($url, $permanent = false, $preserve_method = false)` - Create redirect response

**Fluent Methods:**

```php
$response = (new Response(201, 'Created'))
    ->cachePublicContent()
    ->setStatus(200);
```

### Status (`Status`)

Type-safe HTTP status codes with standard reason phrases:

```php
$status = new Status(404);
echo $status->code;           // 404
echo $status->reason_phrase;  // "Not Found"
```

### Cache Control (`CacheControl`)

First-class cache control with support for modern caching strategies:

**Presets:**

* `publicContent($max_age = 300, $max_stale_age = 86400)` - Public HTML pages
* `publicMedia($max_age = 31536000, $max_stale_age = 31536000)` - Public static assets
* `privateContent($max_age = 300, $max_stale_age = 600)` - Private HTML pages
* `privateMedia($max_age = 31536000, $max_stale_age = 31536000)` - Private static assets
* `neverCached()` - No caching (CSRF forms, CAPTCHAs)

```php
$response->cache = CacheControl::publicMedia();
// Renders: public, max-age=31536000, s-maxage=31536000, 
//          stale-while-revalidate=31536000, stale-if-error=31536000

$response->cachePublicContent(); // Fluent helper method
```

### Headers (`Headers`)

Normalized header collection with automatic header name formatting:

```php
$response->headers['content-type'] = 'application/json';
$response->headers['X-Custom-Header'] = 'value';

// Headers are automatically normalized to proper case
foreach ($response->headers as $name => $value) {
    echo "$name: $value\n";
    // Content-Type: application/json
    // X-Custom-Header: value
}
```

### Content Types

All content types implement `ContentInterface` with automatic metadata handling.

#### StringContent

For text and HTML content:

```php
// With optional filename parameter (defaults to 'page.html')
$content = new StringContent('<h1>Hello World</h1>', 'custom.html');
$response->content = $content;

// Or set filename separately
$content = new StringContent('<h1>Hello World</h1>');
$content->setFilename('page.html');
$response->content = $content;
```

#### CallbackContent

For dynamic content generation with callbacks:

```php
// Simple callback with optional filename parameter (defaults to 'page.html')
$content = new CallbackContent(function() {
    echo renderTemplate('page.html', ['title' => 'Welcome']);
}, 'welcome.html');

// With external data
$data = fetchDataFromDatabase();
$content = new CallbackContent(function() use ($data) {
    foreach ($data as $item) {
        echo "<li>{$item->name}</li>";
    }
});

// Supports any callable type
$content = new CallbackContent([$templateEngine, 'render'], 'output.html');
$response->content = $content;
```

#### JsonContent

For JSON responses:

```php
$content = new JsonContent(['status' => 'ok', 'items' => $items]);
$response->content = $content;
```

#### FileContent

For serving files with automatic MIME detection:

```php
// Filename defaults to basename of the file path
$content = new FileContent('/path/to/file.pdf');
$response->content = $content;

// Or override with custom filename
$content = new FileContent('/path/to/file.pdf', 'document.pdf');
$response->content = $content;
```

#### EmptyContent

For responses with no body:

```php
$response = new Response(204, new EmptyContent());
```

## Advanced Features

### Range Requests

Efficiently serve partial content for video streaming and resumable downloads:

```php
// Content automatically supports range requests
$content = new FileContent('/path/to/video.mp4');

// Apply a range manually
$ranged = new AppliedRangeContent($content, 0, 1024);
$response->content = $ranged;

// Renderer automatically sets 206 status and Content-Range header
```

### Custom Content Types

Create your own content types by implementing `ContentInterface` or extending `AbstractContent`:

```php
class TemplateContent extends AbstractContent
{
    public function __construct(private string $template, private array $data) {
        $this->filename = 'page.html';
    }
    
    public function render(): void {
        echo $this->engine->render($this->template, $this->data);
    }
    
    public function size(): int|null {
        return null; // Unknown until rendered
    }
}
```

### Response Rendering

The `Renderer` handles all HTTP output:

```php
$renderer = new Renderer();
$renderer->render($response);

// Or test header generation without sending
$headers = $renderer->buildHeaders($response);
```

**Automatic Header Generation:**

* `Content-Type` with charset for text content
* `Content-Length` for known-size content
* `Content-Disposition` with filename for downloads
* `Etag` from content hashes
* `Last-Modified` from file modification times
* `Accept-Ranges` for range-capable content
* `Content-Range` for partial content responses

### Fluent Response Building

Chain methods for readable response construction:

```php
$response = Response::json($data)
    ->setStatus(201)
    ->cachePublicContent();

$response->headers['Location'] = '/api/resource/123';
```

### Content Metadata

All content types provide rich metadata:

```php
echo $content->filename();       // 'document.pdf'
echo $content->mime();           // 'application/pdf'
echo $content->contentType();    // 'application/pdf'
echo $content->size();           // File size in bytes
echo $content->etag();           // MD5 hash for caching
echo $content->lastModified();   // DateTime of last modification
```

## Use Cases

### Static File Router

Build a simple static file server with aggressive caching:

```php
$path = '/var/www/public' . $_SERVER['REQUEST_URI'];

if (file_exists($path)) {
    $response = Response::file($path)->cachePublicMedia();
    (new Renderer())->render($response);
}
```

### API Responses

Consistent JSON API responses:

```php
function apiSuccess($data, $code = 200) {
    return Response::json(['success' => true, 'data' => $data], $code)
        ->cacheNever();
}

function apiError($message, $code = 400) {
    return Response::json(['success' => false, 'error' => $message], $code)
        ->cacheNever();
}
```

### Content Transformations

Transform content before serving:

```php
$djot = file_get_contents('content.djot');
$html = renderDjotToHtml($djot);

$response = (new Response(200, $html))
    ->cachePublicContent();

$response->headers['Content-Type'] = 'text/html; charset=UTF-8';
```

### Dynamic Content Generation

Use callbacks for complex rendering logic:

```php
// Template rendering with caching
$response = new Response(200, new CallbackContent(function() use ($templateEngine, $data) {
    echo $templateEngine->render('dashboard.html', $data);
}));
$response->cachePublicContent();

// Streaming output
$response = new Response(200, new CallbackContent(function() {
    foreach (generateLargeDataset() as $chunk) {
        echo json_encode($chunk) . "\n";
    }
}));
```

## Requirements

Fully tested on PHP 8.3+, static analysis for PHP 8.1+.

## License

MIT License - See [LICENSE](LICENSE) file for details.