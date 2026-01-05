<?php
// external_content.php - Parsare și afișare conținut din surse externe

/**
 * Parsează un feed RSS și returnează un array cu articole
 */
function parse_rss_feed($url, $limit = 5) {
    $articles = [];
    
    try {
        // Încarcă conținutul XML
        $rss = @simplexml_load_file($url);
        
        if ($rss === false) {
            return $articles;
        }
        
        $count = 0;
        foreach ($rss->channel->item as $item) {
            if ($count >= $limit) break;
            
            $articles[] = [
                'title' => (string)$item->title,
                'description' => (string)$item->description,
                'link' => (string)$item->link,
                'pubDate' => (string)$item->pubDate,
                'category' => isset($item->category) ? (string)$item->category : 'General'
            ];
            $count++;
        }
    } catch (Exception $e) {
        error_log("Error parsing RSS feed: " . $e->getMessage());
    }
    
    return $articles;
}

/**
 * Parsează date JSON de la un API REST
 */
function fetch_api_data($url) {
    $data = [];
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
        }
    } catch (Exception $e) {
        error_log("Error fetching API data: " . $e->getMessage());
    }
    
    return $data;
}

/**
 * Parsează prețuri cryptocurrency de la un API public
 */
function get_crypto_prices() {
    // Folosim API-ul public CoinGecko
    $url = "https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum,litecoin&vs_currencies=usd,eur";
    return fetch_api_data($url);
}

/**
 * Parsează știri despre securitate și transport din RSS feed
 */
function get_security_news() {
    // Exemplu: RSS feed de știri despre securitate
    $rss_url = "https://feeds.feedburner.com/TheHackersNews";
    return parse_rss_feed($rss_url, 5);
}

/**
 * Parsează date meteo pentru planificarea rutelor
 */
function get_weather_data($city = "Bucharest") {
    // Exemplu de API meteo (necesită API key în producție)
    // Pentru demo, returnăm date mock
    return [
        'city' => $city,
        'temperature' => rand(15, 30),
        'condition' => ['Clear', 'Cloudy', 'Rainy'][rand(0, 2)],
        'humidity' => rand(40, 80),
        'wind_speed' => rand(5, 25)
    ];
}

/**
 * Parsează cursuri valutare de la BNR
 */
function get_exchange_rates() {
    $url = "https://www.bnr.ro/nbrfxrates.xml";
    $rates = [];
    
    try {
        $xml = @simplexml_load_file($url);
        
        if ($xml === false) {
            return $rates;
        }
        
        foreach ($xml->Body->Cube->Rate as $rate) {
            $currency = (string)$rate['currency'];
            $value = (float)$rate;
            $rates[$currency] = $value;
        }
    } catch (Exception $e) {
        error_log("Error fetching exchange rates: " . $e->getMessage());
    }
    
    return $rates;
}

/**
 * Cache pentru date externe - evită requests repetate
 */
function get_cached_data($cache_key, $fetch_function, $cache_duration = 3600) {
    $cache_file = sys_get_temp_dir() . "/bsl_cache_" . md5($cache_key) . ".json";
    
    // Verifică dacă cache-ul există și este valid
    if (file_exists($cache_file)) {
        $cache_time = filemtime($cache_file);
        if (time() - $cache_time < $cache_duration) {
            $cached_data = file_get_contents($cache_file);
            return json_decode($cached_data, true);
        }
    }
    
    // Fetch new data
    $data = call_user_func($fetch_function);
    
    // Salvează în cache
    if (!empty($data)) {
        file_put_contents($cache_file, json_encode($data));
    }
    
    return $data;
}
?>
