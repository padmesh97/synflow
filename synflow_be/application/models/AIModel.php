<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AIModel extends CI_Model
{
    private $DB_CONFIGS;

    public function __construct()
    {
        parent::__construct();   // Always call the parent constructor
        $this->load->database(); // Ensure database is loaded

        date_default_timezone_set("Asia/Kolkata");

        $query  = "SELECT property, value FROM env_config WHERE id>0";
        $result = $this->db->query($query);
        $result = $this->db->query($query)->result_array();

        foreach ($result as $row) {
            $this->DB_CONFIGS[$row['property']] = $row['value'];
        }

    }

    private $grammarly_api_key = 'YOUR_GRAMMARLY_API_KEY';

    public function generateText($formInput)
    {
        $url = "https://api.openai.com/v1/chat/completions";

        $openai_api_key = $this->DB_CONFIGS['OPENAI_KEY'];

        $prompt = "Generate a $formInput. Give me output in max 5 words.";

        $data = [
            "model"    => "gpt-4o-mini",
            "messages" => [
                ["role" => "system", "content" => "You are an AI writer."],
                ["role" => "user", "content" => $prompt],
            ],
        ];

        $response = $this->callAPI("https://api.openai.com/v1/chat/completions", $data, $openai_api_key);

        // Check if API call was successful
        if (isset($response['choices'][0]['message']['content'])) {
            return [
                "status" => "success",
                "output" => $response['choices'][0]['message']['content'],
            ];
        } else {
            return [
                "status" => "error",
                "error"  => isset($response['error']) ? $response['error']['message'] : "Unknown error",
            ];
        }
    }

    public function paraphraseText($text)
    {
        $url = "https://api.openai.com/v1/chat/completions";

        $openai_api_key = getenv('OPENAI_KEY');

        // Proper paraphrasing prompt for ChatGPT
        $prompt = "Paraphrase the following text while keeping the original meaning:\n\n\"$text\"";

        $data = [
            "model"       => "gpt-4o-mini",
            "messages"    => [
                ["role" => "system", "content" => "You are a professional paraphrasing assistant."],
                ["role" => "user", "content" => $prompt],
            ],
            "temperature" => 0.7, // Adjust creativity
            "max_tokens"  => 100,
        ];

        $response = $this->callAPI($url, $data, $openai_api_key);

        // Check for success response
        if (isset($response['choices'][0]['message']['content'])) {
            return [
                "status" => "success",
                "output" => trim($response['choices'][0]['message']['content']),
            ];
        } else {
            return [
                "status" => "error",
                "error"  => isset($response['error']) ? $response['error']['message'] : "Unknown error",
            ];
        }
    }

    public function checkGrammar($text)
    {
        $data     = ["text" => $text];
        $response = $this->callAPI("https://grammarly.com/api/check", $data, $this->grammarly_api_key);
        return ["grammar_suggestions" => $response];
    }

    private function callAPI($url, $data, $apiKey)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
}
