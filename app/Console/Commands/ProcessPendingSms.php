<?php

namespace App\Console\Commands;

use App\Models\SmsProfile;
use App\Models\SmsTextTable;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessPendingSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-pending-sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending SMS';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function beforeExecution()
    {
        Log::channel('process_log')->info('ProcessPendingSms -> running');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->beforeExecution();
        $fileName = base_path('companies.txt');
        $path = $fileName;

        $smsProfile = [];
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $companyIds = explode(",", $content);

            $smsProfile = SmsProfile::wherein('companyid', $companyIds)->get();

            if (count($smsProfile) <= 0) {
                Log::channel('process_log')->error("No sms profile found.", [
                    'path' => $path,
                    'companyIds' => implode(',', $companyIds),
                ]);
            }
        } else {

            Log::channel('process_log')->info("$fileName not found.");

        }

        foreach ($smsProfile as $profile) {

            $pendingSms = SmsTextTable::where([
                'companyidno' => $profile->companyid,
                'status' => 0,
            ])->orderBy('messageID', 'asc')->get();

            foreach ($pendingSms as $index => $sms) {
                $this->send($index + 1, $profile, $sms);
            }

        }

        return 0;

    }

    protected function send($num = 0, SmsProfile $profile, SmsTextTable $sms)
    {
        $systemlog_column_length = 100;
        $provider = $profile->sms_provider;

        $to = $sms->number;
        $message = $sms->message;

        $baseUrl = 'https://messagingsuite.smart.com.ph/cgphttp/servlet/sendmsg';
        // $baseUrl = $profile->https_url_send;

        $params = [
            'destination' => $to,
            'text' => $message,
            'source' => $profile->sender_idno,
            'username' => $profile->username,
            'password' => $profile->accesscode,
        ];

        $tryCount = $this->getTryCount($sms->systemlog);
        $tryCount++;

        try {

            if (Str::of($to)->trim()->isEmpty()) {
                throw new Exception('recipient is required', 400);
            }

            if (Str::of($message)->trim()->isEmpty()) {
                throw new Exception('message is required', 400);
            }
            $response = Http::get($baseUrl, $params);
            $responseBody = $response->body();
            $responseBody = str_replace("\n", " ", $responseBody);
            $responseBody = Str::of($responseBody)->trim();

            if (!$response->successful()) {
                throw new Exception($responseBody, 400);
            }

            if (Str::of($responseBody)->contains('ERROR', true)) {
                throw new Exception($responseBody, 400);
            }

            $sms->update([
                'status' => 1,
                'successfullysent' => 1,
                'systemlog' => $this->generateSystemLog($responseBody, $tryCount),
                'systemlog_time' => now(),
                'sms_provider' => $provider
            ]);

            Log::channel('process_log')->info(" [$num]: [CompanyID:$profile->companyid][messageID:$sms->messageID] -> SUCCESS");

        } catch (Exception $e) {

            $errorResponse = $e->getMessage();
            $error_message = strlen($errorResponse) <= $systemlog_column_length ? $errorResponse : 'ERROR - Something went wrong';

            Log::channel('process_log')->error("[$num]: [CompanyID:$profile->companyid][messageID:$sms->messageID] -> FAILED : $error_message");

            // Log::channel('process_log')->error(json_encode([
            //     'success' => false,
            //     'endpoint' => $baseUrl,
            //     'params' => $params,
            //     'response' => $errorResponse,
            // ], JSON_PRETTY_PRINT));

            $sms->update([
                'status' => $tryCount < 5 ? 0 : 2,
                'successfullysent' => 0,
                'systemlog' => $this->generateSystemLog($error_message, $tryCount),
                'systemlog_time' => now(),
                'sms_provider' => $provider
            ]);

        }

    }

    protected function generateSystemLog($result = '', $tryCount = 0)
    {
        return [
            'tryCount' => $tryCount,
            'response' => $result,
        ];
    }

    protected function getTryCount($systemLog = ''): int
    {
        $tryCount = json_decode($systemLog, true)['tryCount'] ?? 0;
        if (!is_numeric($tryCount)) {
            $tryCount = 0;
        }
        return $tryCount * 1;
    }

}
