<?php

namespace App\Jobs;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Jiri;
use App\Notifications\JiriLaunchedNotification;
use Illuminate\Support\Facades\Log;
use Exception;

class SendJiriLaunchedEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Jiri $jiri;
    public string $name;

    /**
     * Create a new job instance.
     */
    public function __construct(Jiri $jiri, string $name)
    {
        $this->jiri = $jiri;
        $this->name = $name;
    }

    function generateToken(): string
    {
        $randomBytes = random_bytes(8);

        $hexString = bin2hex($randomBytes);

        // On prend les 16 premiers caractères (chiffres en base 16)
        $token = substr($hexString, 0, 16);

        return $token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            foreach ($this->jiri->evaluators as $evaluator) {
                $token = $this->generateToken();
                $attendance = Attendance::where('role', 'evaluator')
                    ->where('jiri_id', $this->jiri->id)
                    ->where('contact_id',$evaluator->id)
                    ->first();

                $attendance->token = $token;
                $attendance->save();
                $evaluator->notify(new JiriLaunchedNotification($this->jiri, $evaluator, $this->name, $token));
            }
        } catch (Exception $e) {
            Log::error("Error sending email for jiri: {$this->jiri->id} - " . $e->getMessage());
            //You can handle the exception here, such as logging the error or dispatching another job for retry
        }
    }
}
