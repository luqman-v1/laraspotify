<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Illuminate\Http\Request;

class SpotifyController extends Controller
{
    protected $code          = "";
    protected $access_token  = "";
    protected $token_type    = "";
    protected $expires_in    = 0;
    protected $scope         = "";
    protected $refresh_token = "";

    public function auth()
    {
        $client = new Client();
        $client->get('https://accounts.spotify.com/en/authorize', [
            'query'    => [
                'client_id'     => '50d91df190244bb182531dd2a0644c51',
                'response_type' => 'code',
                'redirect_uri'  => route('spotify.callback'),
            ],
            'on_stats' => function (TransferStats $stats) use (&$url) {
                $url = $stats->getEffectiveUri();
            },
        ])->getBody()->getContents();

        return redirect($url);
    }

    public function callback(Request $request)
    {
        $this->code = $request['code'];
        $client     = new Client();

        $response = $client->post('https://accounts.spotify.com/api/token', [
            'headers'     => [
                'Authorization' => 'Basic ' . base64_encode('50d91df190244bb182531dd2a0644c51' . ':' . 'e0545e32b3e844a8b62a150ff86c9429'),
                'Accept'        => 'application/json',
            ],
            'form_params' => [
                'grant_type'   => 'authorization_code',
                'code'         => $this->code,
                'redirect_uri' => route('spotify.callback'),
            ],
            'on_stats'    => function (TransferStats $stats) use (&$url) {
                $url = $stats->getEffectiveUri();
            },
        ])->getBody()->getContents();

        $data = json_decode($response);

        $this->access_token  = $data->access_token;
        $this->token_type    = $data->token_type;
        $this->expires_in    = $data->expires_in;
        $this->scope         = $data->scope;
        $this->refresh_token = $data->refresh_token;

        dd($this->about());
    }

    public function about()
    {
        $client = new Client();
        $res    = $client->get('https://api.spotify.com/v1/me', [
            'headers'  => [
                'Authorization' => $this->token_type . ' ' . $this->access_token,
                'Accept'        => 'application/json',
            ],
            'on_stats' => function (TransferStats $stats) use (&$url) {
                $url = $stats->getEffectiveUri();
            },
        ])->getBody()->getContents();

        return $res;
    }
}
