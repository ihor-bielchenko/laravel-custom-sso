<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyAfterRegister extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * @var array
	 */
	public $details;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(array $details)
	{
		$this->details = $details;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this
			->subject('Account Verification')
			->view('emails.verifyAfterRegister')
			->with([
				'name' => $this->details['name'],
				'link' => env('APP_URL') .'/api/verify/?email='. $this->details['email'] .'&signature='. $this->details['verify_key'],
			]);
	}
}
