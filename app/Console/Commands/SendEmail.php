<?php

namespace App\Console\Commands;

use App\Clients;
use Aws\Ses\SesClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;

class SendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envio de correo';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ses = new SesClient([
            'version' => 'latest',
            'region' => env('SES_REGION', 'us-east-1'),
            'credentials' => [
                'key' => env('SES_KEY'),
                'secret' => env('SES_SECRET'),
            ],
        ]);

        (new Clients)
            ->select(['names', 'lastNames', 'dni', 'email_corp'])
            ->chunk(30, function ($boss) use ($ses)  {
                $destinations = [];
                foreach ($boss as $item) {
                    $destinations[] = [
                        'Destination' => [
                            'ToAddresses' => [
                                'hvega@mandu.pe',
                            ],
                        ],
                        'ReplacementTemplateData' => json_encode([
                            'asunto' => 'Prueba de correo',
                            'message' => $item->email_corp,
                            'dni' => $item->dni,
                            'names' => $item->names . ' ' . $item->lastNames,
                        ]),
                    ];
                }

                $options = [
                    'Source' => utf8_encode('Mandü	') . ' <noreply@mandu.pe>',
                    'Template' => 'demo-email',
                    'Destinations' => $destinations,
                    'DefaultTemplateData' => json_encode([
                        'Send' => Carbon::now()->format('Y-m-d H:i:s')
                    ]),
                    'ReturnPath' => 'noreply@mandu.pe',
                ];

                $response = $ses->sendBulkTemplatedEmail($options);

                if ($response['Status'][0]['Status'] === 'Success') {
                    $status = json_encode([
                        'Data' => \count($destinations),
                        'sent' => Carbon::now()->format('d-m-Y H:i:s'),
                        'status' => $response['Status'][0]['Status'],
                        'response' => $response['Status'][0]['MessageId']
                    ]);
                } else {
                    $status = json_encode([
                        'Data' => \count($destinations),
                        'sent' => Carbon::now()->format('d-m-Y H:i:s'),
                        'status' => $response['Status'][0]['Status'],
                        'response' => $response['Status'][0]['Error']
                    ]);
                }

                Log::info($status);
            });


    }
}
