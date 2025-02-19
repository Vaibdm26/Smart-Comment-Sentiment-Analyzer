<?php
// Function to analyze comment sentiment using Hugging Face API
function analyze_comment_sentiment($comment_content) {
    //$api_key = ""; // Replace with your actual Hugging Face API Key
    $url = "https://api-inference.huggingface.co/models/cardiffnlp/twitter-roberta-base-sentiment";

    $data = json_encode(["inputs" => $comment_content]);

    $options = [
        "http" => [
            "header" => "Authorization: Bearer $api_key\r\nContent-Type: application/json\r\n",
            "method" => "POST",
            "content" => $data
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $result = json_decode($response, true);

    // Handle API errors
    if (!isset($result[0][0]['label'])) {
        return "ERROR"; // In case of API failure
    }

    return $result[0][0]['label']; // Adjust based on API response format
}

// Function to filter comments based on sentiment analysis
function filter_comment_sentiment($comment_data) {
    $sentiment = analyze_comment_sentiment($comment_data['comment_content']);

    if ($sentiment == "LABEL_0") { // Adjust based on API's response format
        wp_die("Your comment appears negative and cannot be posted.");
    }

    return $comment_data;
}
add_filter('preprocess_comment', 'filter_comment_sentiment');

// Display sentiment label under each comment
function display_comment_sentiment($comment_text, $comment) {
    $sentiment = analyze_comment_sentiment($comment->comment_content);

    if ($sentiment != "ERROR") {
        $sentiment_label = ($sentiment == "LABEL_2") ? "Positive" : (($sentiment == "LABEL_1") ? "Neutral" : "Negative");
        $comment_text .= "<p><strong>Sentiment:</strong> <span style='color: " . ($sentiment == "LABEL_2" ? "green" : ($sentiment == "LABEL_1" ? "blue" : "red")) . ";'>$sentiment_label</span></p>";
    }

    return $comment_text;
}
add_filter('comment_text', 'display_comment_sentiment', 10, 2);
?>
