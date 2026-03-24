<?php
function fetchWeatherForecast(string $destination): array {
  $url = WEATHER_API_URL . '?q=' . urlencode($destination) . '&appid=' . WEATHER_API_KEY . '&units=metric&cnt=40';
  $resp = @file_get_contents($url);
  if ($resp === false) {
    return ['ok' => false, 'message' => 'Unable to fetch weather right now.', 'days' => []];
  }
  $data = json_decode($resp, true);
  if (!isset($data['list'])) {
    return ['ok' => false, 'message' => 'Invalid destination or weather response.', 'days' => []];
  }

  $grouped = [];
  foreach ($data['list'] as $item) {
    $day = date('Y-m-d', strtotime($item['dt_txt']));
    if (!isset($grouped[$day])) {
      $grouped[$day] = [];
    }
    $grouped[$day][] = $item;
  }

  $result = [];
  foreach ($grouped as $day => $entries) {
    $temps = array_map(fn($e) => (float)$e['main']['temp'], $entries);
    $rains = array_map(fn($e) => (int)($e['pop'] * 100), $entries);
    $icon = $entries[0]['weather'][0]['icon'] ?? '01d';
    $desc = $entries[0]['weather'][0]['description'] ?? '';
    $humidity = (int)$entries[0]['main']['humidity'];
    $wind = (float)$entries[0]['wind']['speed'];

    $result[] = [
      'day' => $day,
      'high' => max($temps),
      'low' => min($temps),
      'rain' => max($rains),
      'humidity' => $humidity,
      'wind' => $wind,
      'icon' => $icon,
      'desc' => $desc,
    ];
    if (count($result) >= 7) {
      break;
    }
  }

  return ['ok' => true, 'message' => '', 'days' => $result];
}
?>
