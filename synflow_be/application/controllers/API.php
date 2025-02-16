<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class API extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();

        // header('Access-Control-Allow-Origin: *');
        // header("Access-Control-Allow-Methods: GET,POST, OPTIONS");
        // header("Access-Control-Allow-Headers: Content-Type,Authorization,Access-Control-Allow-Origin, Content-Length, Accept-Encoding");

        $this->load->model('AIModel');
    }

    public function generateContent_post()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (! isset($data['input'])) {
            $response['status'] = 'error';
            $response['error']  = 'Input field \'input\' required';
            $this->response($response, 400);
        } else {
            $formInput = $data['input'];
            $response  = $this->AIModel->generateText($formInput);
            $this->response($response, 200);
        }
    }

    public function paraphraseText_post()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (! isset($data['input'])) {
            $response['status'] = 'error';
            $response['error']  = 'Input field \'input\' required';
            $this->response($response, 400);
        } else {
            $formInput = $data['input'];
            $response  = $this->AIModel->paraphraseText(trim($formInput));
            $this->response($response, 200);
        }
    }

    public function checkGrammar()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $text = $data['text'];

        $response = $this->AIModel->checkGrammar($text);
        echo json_encode($response);
    }
}
