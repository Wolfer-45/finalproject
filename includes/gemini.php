<?php
function callGemini(string $prompt, array $options = []): string {
  $temperature = isset($options['temperature']) ? (float)$options['temperature'] : 0.7;
  $maxTokens = isset($options['max_tokens']) ? (int)$options['max_tokens'] : 4096;

  $payload = json_encode([
    'contents' => [
      ['parts' => [['text' => $prompt]]]
    ],
    'generationConfig' => [
      'temperature' => $temperature,
      'maxOutputTokens' => $maxTokens,
    ]
  ]);

  $opts = stream_context_create([
    'http' => [
      'method' => 'POST',
      'header' => "Content-Type: application/json\r\n",
      'content' => $payload,
      'timeout' => 30,
    ]
  ]);

  $url = GEMINI_API_URL . '?key=' . GEMINI_API_KEY;
  $response = @file_get_contents($url, false, $opts);

  if ($response === false) {
    return 'Sorry, AI is temporarily unavailable. Please try again.';
  }

  $data = json_decode($response, true);
  return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response from AI. Please try again.';
}
?>
