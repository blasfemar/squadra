<?php
// api/analyze.php

header('Content-Type: application/json');

// Ensure this script is accessed via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Only POST is accepted.']);
    exit;
}

// Get and validate URL from POST data
$url = $_POST['url'] ?? '';

if (empty($url)) {
    echo json_encode(['success' => false, 'error' => 'URL is required.']);
    exit;
}

// Basic URL validation
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    // Try to prepend http:// if scheme is missing and then re-validate
    if (strpos($url, '://') === false) {
        $url = 'http://' . $url;
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            echo json_encode(['success' => false, 'error' => 'Invalid URL format provided.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid URL format provided.']);
        exit;
    }
}

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the transfer as a string
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);        // Limit redirects
curl_setopt($ch, CURLOPT_TIMEOUT, 15);          // Timeout in seconds (as specified: 15s)
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);   // Connection timeout
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.102 Safari/537.36'); // Realistic User-Agent
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verify SSL certificate
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);   // Check common name and verify host

// Execute cURL session
$html_content = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error_no = curl_errno($ch);
$curl_error_msg = curl_error($ch);

curl_close($ch);

// Handle cURL errors
if ($curl_error_no) {
    echo json_encode(['success' => false, 'error' => "cURL Error ({$curl_error_no}): " . htmlspecialchars($curl_error_msg)]);
    exit;
}

// Handle HTTP status code errors (4xx, 5xx)
if ($http_code >= 400) {
    echo json_encode(['success' => false, 'error' => "HTTP Error: Received status code {$http_code} for URL: " . htmlspecialchars($url)]);
    exit;
}

// At this point, $html_content should contain the fetched HTML.
// The next steps (DOM parsing, analysis) will be added here.
// For now, as a placeholder for this subtask's completion:
if (empty($html_content)) {
     echo json_encode(['success' => false, 'error' => 'Fetched HTML content is empty. URL: ' .htmlspecialchars($url)]);
     exit;
}

// Initialize DOMDocument and DOMXPath
$dom = new DOMDocument();
$xpath = null; // Initialize xpath to null
$load_errors = []; // Initialize to avoid issues if $html_content is empty later but somehow not caught

// Suppress errors from malformed HTML and use internal error handling
libxml_use_internal_errors(true);

// This check is technically redundant if the one above exits, but good for clarity
if (!empty($html_content)) {
    // Attempt to load the HTML content
    if (!$dom->loadHTML('<?xml encoding="UTF-8">' . $html_content)) {
        libxml_clear_errors(); // Clear any previous libxml errors
        echo json_encode(['success' => false, 'error' => 'Failed to parse HTML content. The document may be severely malformed.']);
        exit;
    }

    // Check for libxml errors (warnings/errors during parsing)
    $load_errors = libxml_get_errors();
    libxml_clear_errors(); // Clear errors after getting them

    $xpath = new DOMXPath($dom);
} else {
    // This case should have been caught by the check before this block.
    // If somehow $html_content became empty between the check and here.
    echo json_encode(['success' => false, 'error' => 'HTML content was empty just before attempting to parse (this should not happen).']);
    exit;
}

// If we've reached here, $dom and $xpath should be ready for querying.

// --- Helper Functions for SEO Analysis ---

/**
 * Gets the text content of the <title> tag and its length.
 */
function getTitleData($xpath, $dom) {
    $title_text = '';
    $title_length = 0;
    $title_nodes = $xpath->query('//title');
    if ($title_nodes && $title_nodes->length > 0) {
        $title_text = trim($title_nodes->item(0)->textContent);
        $title_length = mb_strlen($title_text, 'UTF-8'); // Use mb_strlen for multi-byte character safety
    }
    return ['text' => $title_text, 'length' => $title_length];
}

/**
 * Gets the content of the meta description tag and its length.
 */
function getMetaDescriptionData($xpath, $dom) {
    $description_text = '';
    $description_length = 0;
    // Case-insensitive query for attribute name 'description'
    $description_nodes = $xpath->query('//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "description"]/@content');
    if ($description_nodes && $description_nodes->length > 0) {
        $description_text = trim($description_nodes->item(0)->nodeValue);
        $description_length = mb_strlen($description_text, 'UTF-8');
    }
    return ['text' => $description_text, 'length' => $description_length];
}

/**
 * Gets H1 count and text of all H1-H6 headings.
 */
function getHeadingsData($xpath, $dom) {
    $h1_nodes = $xpath->query('//h1');
    $h1_count = $h1_nodes ? $h1_nodes->length : 0;

    $all_headings_text = [];
    $heading_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    foreach ($heading_tags as $tag) {
        $nodes = $xpath->query('//' . $tag);
        if ($nodes) {
            foreach ($nodes as $node) {
                $all_headings_text[] = trim($node->textContent);
            }
        }
    }
    return ['h1Count' => $h1_count, 'allHeadings' => $all_headings_text];
}

/**
 * Counts total images and images missing 'alt' attributes.
 */
function getImageData($xpath, $dom) {
    $image_nodes = $xpath->query('//img');
    $total_images = $image_nodes ? $image_nodes->length : 0;

    $missing_alt_count = 0;
    if ($image_nodes) {
        foreach ($image_nodes as $img_node) {
            if (!$img_node->hasAttribute('alt') || trim($img_node->getAttribute('alt')) === '') {
                $missing_alt_count++;
            }
        }
    }
    return ['total' => $total_images, 'missingAlt' => $missing_alt_count];
}

/**
 * Extracts visible text from the body and counts words.
 * This is a basic implementation. More sophisticated word counting might be needed for accuracy.
 */
function getWordCount($xpath, $dom) {
    $body_node = $xpath->query('//body')->item(0);
    $visible_text = '';

    if ($body_node) {
        // Attempt to remove script and style tags before extracting text
        $scripts = $xpath->query('//script', $body_node);
        foreach ($scripts as $script) {
            $script->parentNode->removeChild($script);
        }
        $styles = $xpath->query('//style', $body_node);
        foreach ($styles as $style) {
            $style->parentNode->removeChild($style);
        }

        // Get text content from the modified body
        $body_text_content = trim($body_node->textContent);

        // Replace multiple whitespaces/newlines with a single space
        $visible_text = preg_replace('/\s+/', ' ', $body_text_content);
    }

    if (!empty($visible_text)) {
        $words = explode(' ', $visible_text);
        return count(array_filter($words, function($word) {
            return !empty(trim($word));
        }));
    }
    return 0;
}

// --- End of Helper Functions ---

// After $xpath is initialized:
$analysis_data = [];
$analysis_data['title'] = getTitleData($xpath, $dom);
$analysis_data['metaDescription'] = getMetaDescriptionData($xpath, $dom);
$analysis_data['headings'] = getHeadingsData($xpath, $dom);
$analysis_data['images'] = getImageData($xpath, $dom);
$analysis_data['wordCount'] = getWordCount($xpath, $dom);


/**
 * Calculates a basic SEO score based on extracted data.
 * @param array $data The analysis data.
 * @return int The calculated SEO score (0-100).
 */
function calculateSeoScore($data) {
    $score = 100; // Start with a perfect score

    // Title checks
    if (empty($data['title']['text'])) {
        $score -= 20; // Major issue: no title
    } else {
        if ($data['title']['length'] < 10) $score -= 5;  // Title too short
        if ($data['title']['length'] > 70) $score -= 5;  // Title too long (common recommendation is 50-60, 70 is a lenient max)
    }

    // Meta Description checks
    if (empty($data['metaDescription']['text'])) {
        $score -= 15; // Significant issue: no meta description
    } else {
        if ($data['metaDescription']['length'] < 70) $score -= 5; // Too short
        if ($data['metaDescription']['length'] > 160) $score -= 5; // Too long (common recommendation is 150-160)
    }

    // Headings checks
    if ($data['headings']['h1Count'] === 0) {
        $score -= 15; // Major issue: no H1 tag
    } elseif ($data['headings']['h1Count'] > 1) {
        $score -= 10; // Issue: multiple H1 tags
    }
    // Could add penalties for empty headings in $data['headings']['allHeadings']

    // Images checks
    if ($data['images']['total'] > 0 && $data['images']['missingAlt'] > 0) {
        // Penalize proportionally, e.g., up to 10 points
        $penalty = min(10, round(($data['images']['missingAlt'] / $data['images']['total']) * 10));
        $score -= $penalty;
    }

    // Word Count check
    if ($data['wordCount'] < 300 && $data['wordCount'] > 0) { // Penalize if content exists but is very short
        $score -= 5; // Minor issue: low word count
    } elseif ($data['wordCount'] === 0 && !empty($data['title']['text'])) { // Has title but no body words?
        $score -=10; // More significant if page seems to be blank but has a title
    }

    return max(0, min(100, $score)); // Ensure score is between 0 and 100
}

// Calculate SEO Score
$analysis_data['seoScore'] = calculateSeoScore($analysis_data);

// Construct and output the final JSON response
echo json_encode([
    'success' => true,
    'data' => $analysis_data, // This now includes the 'seoScore'
    'http_code' => $http_code,
    'libxml_errors_count' => count($load_errors ?? [])
]);
exit;

// TODO: Construct the final JSON response with analysis data AND SEO Score. // This TODO is now resolved.

?>
