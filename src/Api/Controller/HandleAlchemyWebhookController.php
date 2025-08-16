<?php

namespace ShebaoTing\MoneyErc20Deposit\Api\Controller;

use Flarum\Api\Controller\AbstractSimpleController;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\ConnectionInterface;

class HandleAlchemyWebhookController extends AbstractSimpleController
{
    protected $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    protected function handle(ServerRequestInterface $request)
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $from = Arr::get($data, 'from_address');
        $to = Arr::get($data, 'to_address');
        $value = (float) (Arr::get($data, 'value', 0) / 1e18);
        $txHash = Arr::get($data, 'hash');
        $rawData = Arr::get($data, 'raw', []);
        $inputData = strtolower(Arr::get($rawData, 'data', ''));

        $depositId = null;
        foreach (str_split($inputData, 2) as $i => $byte) {
            if (substr($inputData, $i * 2, 8) === '444550') {
                $candidate = substr($inputData, $i * 2, 14);
                if (preg_match('/444550[0-9a-f]{8}/', $candidate)) {
                    $ascii = hex2bin($candidate);
                    if (preg_match('/DEP\d{8}/', $ascii, $m)) {
                        $depositId = $m[0];
                        break;
                    }
                }
            }
        }

        if (!$depositId) return $this->response->noContent(400);

        $wallet = $this->getSetting('money_erc20_wallet');
        if (!$wallet || strtolower($to) !== strtolower($wallet)) {
            return $this->response->noContent(400);
        }

        if ($this->db->table('money_deposits')->where('tx_hash', $txHash)->exists()) {
            return $this->response->noContent(200);
        }

        $user = $this->db->table('users')->where('money_deposit_id', $depositId)->first();
        if (!$user) return $this->response->noContent(404);

        $rate = (int) $this->getSetting('money_erc20_rate', 1000);
        $min = (float) $this->getSetting('money_erc20_min', 0.01);

        if ($value < $min) return $this->response->noContent(200);

        $points = (int)($value * $rate);

        $this->db->transaction(function () use ($user, $points, $txHash, $value) {
            $this->db->table('users')->where('id', $user->id)->increment('money_balance', $points);
            $this->db->table('money_deposits')->insert([
                'tx_hash' => $txHash,
                'user_id' => $user->id,
                'token_amount' => $value,
                'awarded_points' => $points,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->db->table('money_transactions')->insert([
                'user_id' => $user->id,
                'amount' => $points,
                'reason' => 'erc20_deposit',
                'created_at' => now()
            ]);
        });

        return $this->response->noContent(200);
    }

    private function getSetting($key, $default = null)
    {
        return $this->db->table('settings')->where('key', $key)->value('value') ?? $default;
    }
}
